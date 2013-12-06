<?php
App::uses('AppModel', 'Model');
/**
 * NoteFolder Model
 *
 * TODO: Look into namespace imports for CakePHP. See App.php.
 *
 * @property Folder $Folder
 * @property Folder $Folder
 * @property Note $Note
 */
class NoteFolder extends AppModel {

	public $actsAs = array(
		'Acl' => array('type' => 'controlled'),
		'Containable'
	);

/**
 * All NoteFolder(s)/Notes are listed under "Notes" root ACO.
 * User Permissions are defined against "Users/<user>" ARO nodes. See User::parentNode().
 * World and Group Permissions are defined against "World/<group>" ARO nodes. See Group::parentNode().
 *
 * Parent nodes for NoteFolder(s) will always be other NoteFolder(s).
 * NoteFolder(s) form a strict Tree, with each NoteFolder having exactly 1 parent.
 *
 * Correction! Doing a Linux file permissions system is underusing CakePHP's ACL, because it will require a
 * flattening of the above-mentioned Tree structure.
 * Remove group ownership, stick with user ownership. Use ACL's full power.
 * Keeping this comment here to remind me that I didn't see how Linux file permissions system is a terribly
 * weak demonstration of CakePHP's ACL.
 */
	public function parentNode() {
		if (!$this->id && empty($this->data)) {
			return null;
		}
		if (isset($this->data['NoteFolder']['note_folder_id'])) {
			$folderId = $this->data['NoteFolder']['note_folder_id'];
		} else {
			$folderId = $this->field('note_folder_id');
		}
		if (!$folderId) {
			return "Notes"; // Root ARO node.
		} else {
			return array('NoteFolder' => array('id' => $folderId)); // AclNode (l 141).
		}
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'name' => array(
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
		'ParentNoteFolder' => array(
			'className' => 'NoteFolder',
			'foreignKey' => 'note_folder_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'ChildNoteFolder' => array(
			'className' => 'NoteFolder',
			'foreignKey' => 'note_folder_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'Note' => array(
			'className' => 'Note',
			'foreignKey' => 'note_folder_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
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
			'conditions' => array('Aco.model' => 'NoteFolder'),
			'fields' => '',
			'order' => ''
		)
	);

}
