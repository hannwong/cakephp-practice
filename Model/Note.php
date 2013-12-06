<?php
App::uses('AppModel', 'Model');
/**
 * Note Model
 *
 * @property User $User
 * @property NoteFolder $NoteFolder
 */
class Note extends AppModel {

	public $actsAs = array(
		'Acl' => array('type' => 'controlled'),
		'Containable'
	);

/**
 * See NoteFolder::parentNode().
 *
 * Parent nodes for Note(s) will always be other NoteFolder(s).
 */
	public function parentNode() {
		if (!$this->id && empty($this->data)) {
			return null;
		}
		if (isset($this->data['Note']['note_folder_id'])) {
			$folderId = $this->data['Note']['note_folder_id'];
		} else {
			$folderId = $this->field('note_folder_id');
		}
		if (!$folderId) {
			return null; // Should not happen. TODO: Throw exception here.
		} else {
			return array('NoteFolder' => array('id' => $folderId));
		}
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				// Every Note must belong to a User.
				'allowEmpty' => false, // Bake doesn't do these from 'null' key in schema! Yet.
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'note_folder_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				// Every Note must belong to a NoteFolder.
				'allowEmpty' => false,
				'required' => true,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'title' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'NoteFolder' => array(
			'className' => 'NoteFolder',
			'foreignKey' => 'note_folder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'Aco' => array(
			'className' => 'Aco',
			'foreignKey' => 'foreign_key',
			'conditions' => array('Aco.model' => 'Note'),
			'fields' => '',
			'order' => ''
		)
	);

/**
 * Paginate method. Called from PaginateComponent's paginate() (l 191).
 *
 * Note to self: This is only useful if we have only DB-specific filters to add. Models have no access to
 *   Controllers nor Components.
 */
	// public function paginate($conditions = null, $fields = null, $order = null, $limit = null,
	// 			 $page = null, $recursive = null, $extra = null) {
	// 	// Oops. We don't have access to AclComponent here. No go. Do this at NotesController instead.

	// 	// TODO: Optimize this via $conditions. Perhaps look at Permission::check(). Possibly
	// 	//   complex. Take this as fun research when time permits.
	// }
}
