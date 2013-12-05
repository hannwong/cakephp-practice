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
		$options = array('conditions' => array('Note.' . $this->Note->primaryKey => $id));
		$result = $this->Note->find('first', $options);

		if (!$this->_aclCheck($result['Note']['id'], 'read'))
			throw new NotFoundException(__('Invalid note'));

		$this->set('note', $result);
	}

/**
 * add method
 *
 * TODO: Perform Acl checks for this? Disable this action, and put in in NoteFoldersController?
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->Note->create();
			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved.'));

				$this->_setDefaultPermissions();

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		}
		$users = $this->Note->User->find('list');
		$noteFolders = $this->Note->NoteFolder->find('list');
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
			if (!$this->_aclCheck($this->request->data['Note']['id'], 'update'))
				throw new NotFoundException(__('Invalid note'));

			// Get old owner. Need to remove permissions if ownership has changed.
			$user = $this->Note->User->findById($this->Note->User->id);

			if ($this->Note->save($this->request->data)) {
				$this->Session->setFlash(__('The note has been saved.'));

				if ($this->Note->user_id != $user['id']) {
					// Onwership has changed!
					// TODO: Set permissions to inherit.
				}

				$this->_setDefaultPermissions();

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('Note.' . $this->Note->primaryKey => $id));
			$this->request->data = $this->Note->find('first', $options);
		}
		$users = $this->Note->User->find('list');
		$noteFolders = $this->Note->NoteFolder->find('list');

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
 * Expects $this->Note->User to be set.
 */
	public function _setDefaultPermissions() {
		$this->Note->User->id = $this->Note->data['User']['id'];

		// Allow CRUD permissions for owner.
		$this->Acl->allow($this->Note->User, $this->Note);
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

	public function _allowGroupPermissions($groupId, $action = '*') {
		$this->Acl->allow(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}
	public function _denyGroupPermissions($groupId, $action = '*') {
		$this->Acl->deny(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}
	public function _inheritGroupPermissions($groupId, $action = '*') {
		$this->Acl->inherit(array('Group' => array('id' => $groupId)), $this->Note, $action);
	}

}
