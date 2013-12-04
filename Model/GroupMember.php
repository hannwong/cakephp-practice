<?php
App::uses('AppModel', 'Model');
/**
 * GroupMember Model
 *
 * @property Group $Group
 * @property User $User
 */
class GroupMember extends AppModel {

	public $actsAs = array('Acl' => array('type' => 'requester'));

/**
 * All GroupMember(s) AROs are listed under their respective Group AROs. Permissions are NOT defined on these
 * "GroupMember" nodes, but on the "Group" nodes instead.
 *
 * When performing an ACL check for Group Permissions, these nodes are the nodes to be checked.
 * For efficiency, "User Permissions" will be checked first. See User::parentNode() for details.
 */
	public function parentNode() {
		if (!$this->id && empty($this->data)) {
			return null;
		}
		if (isset($this->data['GroupMember']['group_id'])) {
			$groupId = $this->data['GroupMember']['group_id'];
		} else {
			$groupId = $this->field('group_id');
		}
		if (!$groupId) {
			return null;
		}

		return array('Group' => array('id' => $groupId)); // AclNode (l 141).
	}

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'group_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'user_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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
		'Group' => array(
			'className' => 'Group',
			'foreignKey' => 'group_id',
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
}
