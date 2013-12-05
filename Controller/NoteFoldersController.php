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
			throw new NotFoundException(__('Invalid note folder'));
		}
		$options = array('conditions' => array('NoteFolder.' . $this->NoteFolder->primaryKey => $id));

		$result = $this->NoteFolder->find('first', $options);

		if (!$this->_aclCheck($result['NoteFolder']['id'], 'read'))
			throw new NotFoundException(__('Invalid note folder'));

		$this->set('noteFolder', $result);
	}

/**
 * add method
 *
 * TODO: Acl check. Also limit the options for parent NoteFolder according to Acl checks.
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->NoteFolder->create();
			if ($this->NoteFolder->save($this->request->data)) {
				$this->Session->setFlash(__('The note folder has been saved.'));

				$this->_setDefaultPermissions($this->request->data);

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note folder could not be saved. Please, try again.'));
			}
		}
		$noteFolders = $this->NoteFolder->ParentNoteFolder->find('list');
		$users = $this->NoteFolder->User->find('list');
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
			throw new NotFoundException(__('Invalid note folder'));
		}
		if (!$this->_aclCheck($id, 'update'))
			throw new NotFoundException(__('Invalid note folder'));
		if ($this->request->is(array('post', 'put'))) {
			if (!$this->_aclCheck($this->request->data['NoteFolder']['id'], 'update'))
				throw new NotFoundException(__('Invalid note folder'));

			// Get old owner. Need to remove permissions if ownership has changed.
			$user = $this->NoteFolder->User->findById($this->Note->User->id);

			if ($this->NoteFolder->save($this->request->data)) {
				$this->Session->setFlash(__('The note folder has been saved.'));

				if ($this->NoteFolder->user_id != $user['id']) {
					// TODO: Set permissions to inherit.
				}

				$this->_setDefaultPermissions();

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note folder could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('NoteFolder.' . $this->NoteFolder->primaryKey => $id));
			$this->request->data = $this->NoteFolder->find('first', $options);
		}
		$noteFolders = $this->NoteFolder->ParentNoteFolder->find('list');
		$users = $this->NoteFolder->User->find('list');
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
			throw new NotFoundException(__('Invalid note folder'));
		}
		$this->request->onlyAllow('post', 'delete');
		if (!$this->_aclCheck($id, 'delete'))
			throw new NotFoundException(__('Invalid note folder'));

		if ($this->NoteFolder->delete()) {
			$this->Session->setFlash(__('The note folder has been deleted.'));
			// Acl tables automatically handled.
		} else {
			$this->Session->setFlash(__('The note folder could not be deleted. Please, try again.'));
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

}
