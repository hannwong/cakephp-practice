<?php
App::uses('AppModel', 'Model');
/**
 * Note Model
 *
 * @property User $User
 * @property NoteFolder $NoteFolder
 */
class Note extends AppModel {

	public $actsAs = array('Acl' => array('type' => 'controlled'));

/**
 * See NoteFolder::parentNode().
 *
 * Parent nodes for Note(s) will always be other NoteFolder(s).
 */
	public function parentNode() {
		if (!$this->id && empty($this->data)) {
			return null;
		}
		if (isset($this->data['Note']['folder_id'])) {
			$folderId = $this->data['Note']['folder_id'];
		} else {
			$folderId = $this->field('folder_id');
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
		'folder_id' => array(
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
			'foreignKey' => 'folder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
}
