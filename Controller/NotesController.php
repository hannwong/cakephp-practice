<?php
App::uses('AppController', 'Controller');
/**
 * Notes Controller
 *
 * @property Note $Note
 * @property PaginatorComponent $Paginator
 */
class NotesController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->Note->recursive = 0;
		// Get all rows as requested.
		// Don't worry about size/efficiency; list is already limited (20, by default).
		$result = $this->Paginator->paginate();

		// Run each ID through Acl. Remove those that fail Acl check.
		$passed = array();
		foreach ($result as $row) {
			if (!$this->_aclCheck($row['Note']['id'], 'read'))
				continue;
			array_push($passed, $row);
		}

		$this->set('notes', $passed);

		// TODO: Optimize this via $conditions.
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->Note->exists($id)) {
			throw new NotFoundException(__('Invalid note'));
		}
		if (!$this->_aclCheck($id, 'read'))
			throw new NotFoundException(__('Invalid note'));

		$options = array(
			'conditions' => array('Note.' . $this->Note->primaryKey => $id),
			// Grab User and Group permissions too.
			'recursive' => 2,
			// Prune off associations we don't need. (ContainableBehavior::containments())
			'contain' => array(
				'User', 'NoteFolder',
				'Aco' => array('Aro' => array(
						       'fields' => array('model', 'foreign_key'),
						       'Permission' => array())
				)
			)
		);
		$result = $this->Note->find('first', $options);

		$Group = ClassRegistry::init('Group');
		$result['groupPerms'] = array();
		$result['userPerms'] = array();
		foreach ($result['Aco']['Aro'] as $aro) {
			if ($aro['model'] == 'Group') {
				$aro = array_merge($Group->find('first',
					array(
						'conditions' => array('Group.id' => $aro['foreign_key']),
						'recursive' => -1
					)
				), $aro);
				array_push($result['groupPerms'], $aro);
			}
			else if ($aro['model'] == 'User') {
				$aro = array_merge($this->Note->User->find('first',
					array(
						'conditions' => array('User.id' => $aro['foreign_key']),
						'recursive' => -1
					)
				), $aro);
				array_push($result['userPerms'], $aro);
			}
		}

		$this->set('note', $result);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Note->create();

			// Check User's 'create' permission on folder.
			if (!$this->_aclCheckFolder($this->request->data['Note']['note_folder_id'], 'create'))
				throw new NotFoundException(__('Invalid note'));
			// Check note's owner. Only 'administrators' Group can create Notes with any owner.
			if ($this->request->data['Note']['user_id'] != $this->Auth->user('id') &&
			    !$this->_isInGroup(1))
				throw new NotFoundException(__('Invalid note'));

			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved.'));

				$this->_setDefaultPermissions($this->request->data);

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		}

		// Only display folders that our User has 'create' permissions on.
		$noteFolders = $this->_getAllowedFoldersList('create');
		// Only display users that our User can assign as owners for Notes.
		$users = $this->_getAllowedOwnersList();

		// Note: The above rules involve AclComponent. Not possible to do inside Models with data validation.

		$this->set(compact('users', 'groups', 'noteFolders'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->Note->exists($id)) {
			throw new NotFoundException(__('Invalid note'));
		}
		if (!$this->_aclCheck($id, 'update'))
			throw new NotFoundException(__('Invalid note'));
		if ($this->request->is(array('post', 'put'))) {
			// Have update permission on the Note?
			if (!$this->_aclCheck($this->request->data['Note']['id'], 'update'))
				throw new NotFoundException(__('Invalid note'));

			// Check User's 'create' permission on folder.
			// TODO: If folder hasn't changed, allow Note edit.
			//   If it has changed, check 'create' permission for target folder.
			if (!$this->_aclCheckFolder($this->request->data['Note']['note_folder_id'], 'create'))
				throw new NotFoundException(__('Invalid note'));

			// Check note's owner. Only 'administrators' Group can create Notes with any owner.
			if ($this->request->data['Note']['user_id'] != $this->Auth->user('id') &&
			    !$this->_isInGroup(1))
				throw new NotFoundException(__('Invalid note'));

			// Get old owner. Need to remove permissions if ownership has changed.
			$this->Note->id = $id; $this->Note->read();
			$oldUserId = $this->Note->data['User']['id'];

			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved.'));

				if (intval($this->request->data['Note']['user_id']) != $oldUserId) {
					// Onwership has changed!
					// Set permissions to inherit ("remove" explicit permissions)
					$this->_removeDefaultPermissions($oldUserId);
				}

				$this->_setDefaultPermissions($this->request->data);

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Note.' . $this->Note->primaryKey => $id));
			$this->request->data = $this->Note->find('first', $options);
		}

		// Only display folders that our User has 'create' permissions on.
		$noteFolders = $this->_getAllowedFoldersList('create');
		// Only display users that our User can assign as owners for Notes.
		$users = $this->_getAllowedOwnersList();

		$this->set(compact('users', 'groups', 'noteFolders'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Note->id = $id;
		if (!$this->Note->exists()) {
			throw new NotFoundException(__('Invalid note'));
		}
		$this->request->onlyAllow('post', 'delete');
		if (!$this->_aclCheck($id, 'delete'))
			throw new NotFoundException(__('Invalid note'));

		if ($this->Note->delete()) {
			$this->Session->setFlash(__('The note has been deleted.'));
			// Acl tables automatically handled.
		} else {
			$this->Session->setFlash(__('The note could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}


/**
 * Sets default permissions for thie Note (allow CRUD by default)
 * Expects $this->Note->id to be set.
 *
 * @param array $data $data['NoteFolder']['user_id'] must contain the owner.
 */
	public function _setDefaultPermissions($data = null) {
		$userId = null;
		$noteId = $this->Note->id;
		// Assume $this->Note->read() was done with valid $this->Note->id.
		if (empty($data)) {
			$userId = $this->Note->data['User']['id'];
		}
		else {
			$userId = $data['Note']['user_id'];
		}

		// Allow user permissions.
		$this->Acl->allow(array('User' => array('id' => $userId)),
				  array('Note' => array('id' => $noteId)));
	}

/**
 * Effectively the inverse of _setDefaultPermissions(). Setting value to 'inherit'.
 *
 * @param integer $userId Old user ID. Note should have a new owner now.
 */
	public function _removeDefaultPermissions($userId) {
		$noteId = $this->Note->id;

		// "Remove" user permissions.
		$result = $this->Acl->inherit(array('User' => array('id' => $userId)),
					      array('Note' => array('id' => $noteId)));
	}

/**
 * Acl check. ARO is the logged in User.
 *
 * @param string $id ID of Note (the ACO).
 * @param string $action permission key to check for (*, create, read, update, delete).
 * @return boolean Success
 */
	public function _aclCheck($id = null, $action = '*') {
		if (empty($id)) return false;
		return $this->Acl->check(array('User' => array('id' => $this->Auth->user('id'))),
					 array('Note' => array('id' => $id), $action));
	}

/**
 * Acl check on NoteFolder. ARO is the logged in User.
 *
 * @param string $id ID of NoteFolder (the ACO).
 * @param string $action permission key to check for (*, create, read, update, delete).
 * @return boolean Success
 */
	public function _aclCheckFolder($id = null, $action = '*') {
		if (empty($id)) return false;
		return $this->Acl->check(array('User' => array('id' => $this->Auth->user('id'))),
					 array('NoteFolder' => array('id' => $id), $action));
	}

	public function _allowGroupPermissions($groupId, $action = '*') {
		$this->Acl->allow(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}
	public function _denyGroupPermissions($groupId, $action = '*') {
		$this->Acl->deny(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}
	public function _inheritGroupPermissions($groupId, $action = '*') {
		$this->Acl->inherit(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}

/**
 * Checks if the logged in user is in a Group.
 * @param integer $groupId The group membership to check for.
 */
	public function _isInGroup($groupId) {
		$this->Note->User->id = $this->Auth->user('id'); $this->Note->User->read();
		foreach ($this->Note->User->data['GroupMember'] as $membership) {
			if ($membership['group_id'] == $groupId)
				return true;
		}
		return false;
	}

/**
 * Returns an array folders that our User has 'create' permissions on. In 'list' format.
 *
 * @param string $action Action for which to check for permission.
 */
	public function _getAllowedFoldersList($action = '*') {
		$temp = $this->Note->NoteFolder->find('all', array('fields' => array('id', 'name'), 'recursive' => -1));
		$noteFolders = array();
		foreach ($temp as $folder) {
			if ($this->_aclCheckFolder($folder['NoteFolder']['id'], $action))
				array_push($noteFolders, array('NoteFolder.id' => $folder['NoteFolder']['id']));
		}
		$noteFolders = $this->Note->NoteFolder->find('list', array('conditions' => array('OR' => $noteFolders)));
		return $noteFolders;
	}

/**
 * Returns an array of users for which our User can assign as owner for Notes. In 'list' format.
 *
 * TODO: Create a set of ACOs for Groups/Users so that we can set permissions on creating Notes for users
 *   other than our logged in User.
 * For now, hardcode these permissions here:
 *  Only Group 'administrators' can create Notes with any user as owner. Nobody else can
 *  masquerade anybody else, or spam new Notes with any arbitrary owner.
 */
	public function _getAllowedOwnersList() {
		$users = null;
		if ($this->_isInGroup(1))
			$users = $this->Note->User->find('list');
		else
			$users = $this->Note->User->find('list',
				 array('conditions' => array('User.id' => $this->Auth->user('id'))));
		return $users;
	}

}
