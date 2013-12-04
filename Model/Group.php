<?php
App::uses('AppModel', 'Model');
/**
 * Group Model
 *
 * @property GroupMember $GroupMember
 */
class Group extends AppModel {

	public $actsAs = array('Acl' => array('type' => 'requester'));

/**
 * All Group(s) are listed under this root ARO.
 * Group Permissions are defined on these "Group" nodes.
 * World Permissions are defined on the root ARO "World".
 * (No more World permissions! No longer doing Linux file permissions.)
 *
 * Each NoteFolder/Note will belong to a Group, and that Group's CRUD permissions for said NoteFolder/Note will be
 * stored in that Group's linked ARO node.
 * (Not valid anymore! No longer doing Linux file permissions.)
 *
 * When performing an ACL check, the "User Permissions" will be checked first. See User::parentNode() for
 * details. Also see GroupMember::parentNode().
 */
	public function parentNode() {
		return "World";
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
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'GroupMember' => array(
			'className' => 'GroupMember',
			'foreignKey' => 'group_id',
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
		'User' => array(
			'className' => 'User',
			'joinTable' => 'group_members',
			'foreign_key' => 'group_id',
			'associationForeignKey' => 'user_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => ''
		)
	);

}
