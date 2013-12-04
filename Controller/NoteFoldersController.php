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
		$this->set('noteFolders', $this->Paginator->paginate());
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
		$this->set('noteFolder', $this->NoteFolder->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->NoteFolder->create();
			if ($this->NoteFolder->save($this->request->data)) {
				$this->Session->setFlash(__('The note folder has been saved.'));

				$this->_setDefaultPermissions();

				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The note folder could not be saved. Please, try again.'));
			}
		}
		$noteFolders = $this->NoteFolder->ChildNoteFolder->find('list');
		$this->set(compact('noteFolders'));
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
		if ($this->request->is(array('post', 'put'))) {
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
		$noteFolders = $this->NoteFolder->NoteFolder->find('list');
		$this->set(compact('noteFolders'));
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

		if ($this->NoteFolder->delete()) {
			$this->Session->setFlash(__('The note folder has been deleted.'));
			// Acl tables automatically handled.
		} else {
			$this->Session->setFlash(__('The note folder could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}

/**
 * Sets default permissions for thie Note (allow CRUD by default)
 * Expects $this->Note->User and $this->Note->Group to be set.
 */
	public function _setDefaultPermissions() {
		$this->NoteFolder->User->id = $this->NoteFolder->data['User']['id'];

		// Allow group and user permissions.
		$this->Acl->allow($this->NoteFolder->User, $this->NoteFolder);
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
