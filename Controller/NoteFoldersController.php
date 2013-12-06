<?php
App::uses('AppController', 'Controller');
/**
 * NoteFolders Controller
 *
 * @property NoteFolder $NoteFolder
 * @property PaginatorComponent $Paginator
 */
class NoteFoldersController extends AppController {

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
		$this->NoteFolder->recursive = 0;
		$result = $this->Paginator->paginate();

		// Fully commented at NotesController::index().

		$passed = array();
		foreach ($result as $row) {
			if (!$this->_aclCheck($row['NoteFolder']['id'], 'read'))
				continue;
			array_push($passed, $row);
		}

		$this->set('noteFolders', $passed);
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->NoteFolder->exists($id)) {
			throw new NotFoundException(__('Invalid folder'));
		}
		if (!$this->_aclCheck($id, 'read'))
			throw new NotFoundException(__('Invalid folder'));

		$options = array(
			'conditions' => array('NoteFolder.' . $this->NoteFolder->primaryKey => $id),
			// Grab User and Group permissions too.
			'recursive' => 2,
			// Prune off associations we don't need. (ContainableBehavior::containments())
			'contain' => array(
				'ParentNoteFolder', 'User', 'ChildNoteFolder', 'Note',
				'Aco' => array('Aro' => array(
						       'fields' => array('model', 'foreign_key'),
						       'Permission' => array())
				)
			)
		);
		$result = $this->NoteFolder->find('first', $options);

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
				$aro = array_merge($this->NoteFolder->User->find('first',
					array(
						'conditions' => array('User.id' => $aro['foreign_key']),
						'recursive' => -1
					)
				), $aro);
				array_push($result['userPerms'], $aro);
			}
		}

		$this->set('noteFolder', $result);
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->NoteFolder->create();

			// Check User's 'create' permission on folder.
			if (!$this->_aclCheckFolder($this->request->data['NoteFolder']['note_folder_id'], 'create'))
				throw new NotFoundException(__('Invalid folder'));
			// Check folder's owner. Only 'administrators' Group can create folders with any owner.
			if ($this->request->data['NoteFolder']['user_id'] != $this->Auth->user('id') &&
			    !$this->_isInGroup(1))
				throw new NotFoundException(__('Invalid folder'));

			if ($this->NoteFolder->save($this->request->data)) {
				$this->Session->setFlash(__('The folder has been saved.'));

				$this->_setDefaultPermissions($this->request->data);

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The folder could not be saved. Please, try again.'));
			}
		}

		// Only display folders that our User has 'create' permissions on.
		$noteFolders = $this->_getAllowedFoldersList('create');
		// Only display users that our User can assign as owners for Notes.
		$users = $this->_getAllowedOwnersList();

		$this->set(compact('users', 'noteFolders'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->NoteFolder->exists($id)) {
			throw new NotFoundException(__('Invalid folder'));
		}
		if (!$this->_aclCheck($id, 'update'))
			throw new NotFoundException(__('Invalid folder'));
		if ($this->request->is(array('post', 'put'))) {
			// Have update permission on the folder?
			if (!$this->_aclCheck($this->request->data['NoteFolder']['id'], 'update'))
				throw new NotFoundException(__('Invalid note folder'));

			// Check User's 'create' permission on folder.
			// TODO: If parent folder hasn't changed, allow folder edit.
			//   If it has changed, check 'create' permission for target folder.
			if (!$this->_aclCheckFolder($this->request->data['NoteFolder']['note_folder_id'], 'create'))
				throw new NotFoundException(__('Invalid folder'));

			// Check folder's owner. Only 'administrators' Group can create folders with any owner.
			if ($this->request->data['NoteFolder']['user_id'] != $this->Auth->user('id') &&
			    !$this->_isInGroup(1))
				throw new NotFoundException(__('Invalid folder'));

			// Get old owner. Need to remove permissions if ownership has changed.
			$this->NoteFolder->id = $id; $this->NoteFolder->read();
			$oldUserId = $this->NoteFolder->data['User']['id'];

			if ($this->NoteFolder->save($this->request->data)) {
				$this->Session->setFlash(__('The folder has been saved.'));

				if (intval($this->request->data['NoteFolder']['user_id']) != $oldUserId) {
					// Onwership has changed!
					// Set permissions to inherit ("remove" explicit permissions)
					$this->_removeDefaultPermissions($oldUserId);
				}

				$this->_setDefaultPermissions($this->request->data);

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The folder could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('NoteFolder.' . $this->NoteFolder->primaryKey => $id));
			$this->request->data = $this->NoteFolder->find('first', $options);
		}

		// Only display folders that our User has 'create' permissions on.
		$noteFolders = $this->_getAllowedFoldersList('create');
		// Only display users that our User can assign as owners for Notes.
		$users = $this->_getAllowedOwnersList();

		$this->set(compact('users', 'noteFolders'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->NoteFolder->id = $id;
		if (!$this->NoteFolder->exists()) {
			throw new NotFoundException(__('Invalid folder'));
		}
		$this->request->onlyAllow('post', 'delete');
		if (!$this->_aclCheck($id, 'delete'))
			throw new NotFoundException(__('Invalid folder'));

		if ($this->NoteFolder->delete()) {
			$this->Session->setFlash(__('The folder has been deleted.'));
			// Acl tables automatically handled.
		} else {
			$this->Session->setFlash(__('The folder could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * Sets default permissions for this NoteFolder (allow CRUD by default)
 * Expects $this->NoteFolder->id to be set.
 *
 * @param array $data $data['NoteFolder']['user_id'] must contain the owner.
 */
	public function _setDefaultPermissions($data = null) {
		$userId = null;
		$noteFolderId = $this->NoteFolder->id;
		// Assume $this->NoteFolder->read() was done with valid $this->NoteFolder->id.
		if (empty($data)) {
			$userId = $this->NoteFolder->data['User']['id'];
		}
		else {
			$userId = $data['NoteFolder']['user_id'];
		}

		// Allow user permissions.
		$this->Acl->allow(array('User' => array('id' => $userId)),
				  array('NoteFolder' => array('id' => $noteFolderId)));
	}

/**
 * Effectively the inverse of _setDefaultPermissions(). Setting value to 'inherit'.
 *
 * @param integer $userId Old user ID. Note should have a new owner now.
 */
	public function _removeDefaultPermissions($userId) {
		$folderId = $this->NoteFolder->id;

		// "Remove" user permissions.
		$result = $this->Acl->inherit(array('User' => array('id' => $userId)),
					      array('NoteFolder' => array('id' => $folderId)));
	}

/**
 * Acl check. ARO is the logged in User.
 *
 * @param string $id ID of NoteFolder (the ACO).
 * @param string $action permission key to check for (*, create, read, update, delete).
 * @return boolean Success
 */
	public function _aclCheck($id = null, $action = '*') {
		if (empty($id)) return false;
		return $this->Acl->check(array('User' => array('id' => $this->Auth->user('id'))),
					 array('NoteFolder' => array('id' => $id), $action));
	}

	public function _allowGroupPermissions($groupId, $action = '*') {
		$this->Acl->allow(array('Group' => array('id' => $groupId)), $this->NoteFolder, $action);
	}
	public function _denyGroupPermissions($groupId, $action = '*') {
		$this->Acl->deny(array('Group' => array('id' => $groupId)), $this->NoteFolder, $action);
	}
	public function _inheritGroupPermissions($groupId, $action = '*') {
		$this->Acl->inherit(array('Group' => array('id' => $groupId)), $this->NoteFolder, $action);
	}

/**
 * Checks if the logged in user is in a Group.
 * @param integer $groupId The group membership to check for.
 */
	public function _isInGroup($groupId) {
		$this->NoteFolder->User->id = $this->Auth->user('id'); $this->NoteFolder->User->read();
		foreach ($this->NoteFolder->User->data['GroupMember'] as $membership) {
			if ($membership['group_id'] == $groupId)
				return true;
		}
		return false;
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

/**
 * Returns an array folders that our User has 'create' permissions on. In 'list' format.
 *
 * @param string $action Action for which to check for permission.
 */
	public function _getAllowedFoldersList($action = '*') {
		$temp = $this->NoteFolder->ParentNoteFolder->find('all', array('fields' => array('id', 'name'), 'recursive' => -1));
		$noteFolders = array();
		foreach ($temp as $folder) {
			if ($this->_aclCheckFolder($folder['ParentNoteFolder']['id'], $action))
				array_push($noteFolders, array('ParentNoteFolder.id' => $folder['ParentNoteFolder']['id']));
		}
		$noteFolders = $this->NoteFolder->ParentNoteFolder->find('list', array('conditions' => array('OR' => $noteFolders)));
		return $noteFolders;
	}

/**
 * Returns an array of users for which our User can assign as owner for folders. In 'list' format.
 *
 * TODO: Create a set of ACOs for Groups/Users so that we can set permissions on creating folders for users
 *   other than our logged in User.
 * For now, hardcode these permissions here:
 *  Only Group 'administrators' can create folders with any user as owner. Nobody else can
 *  masquerade anybody else, or spam new folders with any arbitrary owner.
 */
	public function _getAllowedOwnersList() {
		$users = null;
		if ($this->_isInGroup(1))
			$users = $this->NoteFolder->User->find('list');
		else
			$users = $this->NoteFolder->User->find('list',
				 array('conditions' => array('User.id' => $this->Auth->user('id'))));
		return $users;
	}

}
