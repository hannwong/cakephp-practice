<?php
App::uses('AppModel', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
/**
 * User Model
 *
 * @property GroupMember $GroupMember
 * @property Note $Note
 */
class User extends AppModel {

	public $actsAs = array('Acl' => array('type' => 'requester'));

	public $displayField = 'username';

/**
 * All User(s) are listed under this root ARO. Permissions are defined on these "User" nodes.
 *
 * Each NoteFolder/Note will belong to a User, and that User's CRUD permissions for said NoteFolder/Note will be
 * stored in that User's linked ARO node.
 *
 * When performing an ACL check, the "User Permissions" will be checked first. Since each User (as well as
 * NoteFolder/Note) will belong to only 1 User, this check is cheapest and should be performed first. Each User
 * may belong to more than 1 Group, so the check for "Group Permissions" will likely be more expensive.
 *
 * The alternative to this layout is to have an ARO node under each GroupMember's ARO node. That alternative
 * takes more space, more time to write the likely many ARO nodes upon each change of Group/User
 * ownership/permissions for each NoteFolder/Note. Given our current limitation to Tree for AclNode, it seems best
 * to treat User(s) and GroupMember(s) as separate ARO types.
 */
	public function parentNode() {
		return "Users";
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'username' => array(
			'notEmpty' => array(
				'rule' => array('notEmpty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'password' => array(
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'GroupMember' => array(
			'className' => 'GroupMember',
			'foreignKey' => 'user_id',
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
			'foreignKey' => 'user_id',
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

	public $hasAndBelongsToMany = array(
		'Group' => array(
			'className' => 'Group',
			'joinTable' => 'group_members',
			'foreign_key' => 'user_id',
			'associationForeignKey' => 'group_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => ''
		)
	);

	public function beforeSave($options = array()) {
		if (isset($this->data[$this->alias]['password'])) {
			$passwordHasher = new SimplePasswordHasher();
			$this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
		}
		return true;
	}

}
