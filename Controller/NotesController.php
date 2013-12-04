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
		$this->set('notes', $this->Paginator->paginate());
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
		$this->set('note', $this->Note->find('first', $options));
	}

/**
 * add method
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
		$groups = $this->Note->Group->find('list');
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
		if ($this->request->is(array('post', 'put'))) {
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
 * Expects $this->Note->User and $this->Note->Group to be set.
 */
	public function _setDefaultPermissions() {
		$this->Note->User->id = $this->Note->data['User']['id'];

		// Allow CRUD permissions for owner.
		$this->Acl->allow($this->Note->User, $this->Note);
	}

}
