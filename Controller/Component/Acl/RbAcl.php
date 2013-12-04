<?php
/**
 * @package       app.Controller.Component.Acl
 */

App::uses('DbAcl', 'Controller/Component/Acl');
App::uses('ClassRegistry', 'Utility');

/**
 * RbAcl extends DbAcl by using 2 additional tables: User and Group.
 * Because the underlying AclNode model for AROs is a Tree, users cannot belong to multiple groups (roles),
 * thus preventing the implementation of a true role-based ACL.
 */
class RbAcl extends DbAcl {

/**
 * Constructor
 *
 */
	public function __construct() {
		parent::__construct();
		$this->User = ClassRegistry::init(array('class' => 'User', 'alias' => 'User'));
	}

/**
 * Checks if the given $aro has access to action $action in $aco
 *
 * @param string $aro ARO A User model object is handled specially; everything else is passed through as normal. The requesting object identifier.
 * @param string $aco ACO The controlled object identifier. NoteFolder or Note model object.
 * @param string $action Action (defaults to *)
 * @return boolean Success (true if ARO has access to action in ACO, false otherwise)
 */
	public function check($aro, $aco, $action = "*") {
		// First, check user permissions (if $aro is a User).
		if ($this->Permission->check($aro, $aco, $action)) {
			return true;
		}

		if (!isset($aro['User']))
			return false; // Not a User ARO.

		// Next, check group permissions (if $aro is a User).
		$this->User->id = $aro['User']['id']; $this->User->read();
		foreach ($this->User->data['GroupMember'] as $membership) {
			if ($this->Permission->check(array('Group' => array('id' => $membership['group_id'])), $aco, $action)) {
				return true;
			}
		}

		return false;
	}

}
