<?php
App::uses('RbAcl', 'Controller/Component/Acl/');
App::uses('AclExtrasShell', 'AclExtras.Console/Command');
App::uses('SchemaShell', 'Console/Command/');

App::uses('NoteFoldersController', 'Controller');
App::uses('NotesController', 'Controller');

class CreatesInitialTablesAndData1385727631 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Actions to be performed
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'users' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'username' => array('type' => 'string', 'null' => false),
					'password' => array('type' => 'string', 'length' => 40, 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true),
					'indexes' => array(
						'UNIQUE_USERNAME' => array(
							'column' => 'username',
							'unique' => true
						)
					)
				),

				'groups' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'name' => array('type' => 'string', 'length' => 100, 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true)
				),

				// HABTM. Users can belong to any number of groups.
				'group_members' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'group_id' => array('type' => 'integer', 'null' => false),
					'user_id' => array('type' => 'integer', 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true)
				),

				// Folders contain folders and notes.
				'note_folders' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'name' => array('type' => 'string', 'null' => false),
					// Parent folder
					'note_folder_id' => array('type' => 'integer', 'null' => true),
					// Owner of the folder. Only owners can delete.
					'user_id' => array('type' => 'integer', 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true)
				),

				'notes' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'user_id' => array('type' => 'integer', 'null' => false),
					'title' => array('type' => 'string', 'null' => false),
					'body' => array('type' => 'text', 'null' => true),
					// Parent folder
					'note_folder_id' => array('type' => 'integer', 'null' => false),
					// Owner of the note. Only owners can delete.
					'user_id' => array('type' => 'integer', 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true)
				),
			),
		),
		'down' => array(
			'drop_table' => array('users', 'groups', 'group_members', 'notes', 'aros', 'acos', 'aros_acos')
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function before($direction) {
		if ($direction == 'up') {
			$SchemaShell = new SchemaShell();
			$SchemaShell->params = array('file' => 'db_acl', 'name' => 'DbAcl');
			$SchemaShell->startup();
			$SchemaShell->create();
		}
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean Should process continue
 * @access public
 */
	public function after($direction) {
		$dataSource = ConnectionManager::getDataSource("default");
		$Note = ClassRegistry::init('Note');
		$Aco = ClassRegistry::init('Aco');

		if ($direction == 'up') {
			$transaction = $dataSource->begin();
			if ($transaction)
				echo "Transaction was already started.\n";
			else
				echo "Transaction starting.\n";

			$this->createControllersACOs();
			$data = $this->createUsersAndGroups();
			$data = $this->createFoldersAndNotes($data); // And related permissions
			$this->createInitialPermissions($data); // Permissions for controller actions.
			$this->testPermissions($data);

			$transaction = $dataSource->commit();
			echo "Transaction committed. Commit code: " . $transaction . "\n";
		}
		if ($direction = 'down') {
		}
		return true;
	}

/**
 * User.Group created: root.root, manager1.manager1, user1.user1.
 * User 'manager1' is also in Group 'root'.
 * ARO roots: World (contains Group(s)), Users (contains User(s)).
 * See User::parentNode(), Group::parentNode(), GroupMember::parentNode().
 */
	private function createUsersAndGroups() {
		$Aro = ClassRegistry::init('Aro');
		$Group = ClassRegistry::init('Group');
		$User = ClassRegistry::init('User');
		$GroupMember = ClassRegistry::init('GroupMember');

		// Create ARO roots.
		$Aro->create();
		$data['Aro'] = array(
			array('parent_id' => NULL, 'alias' => 'World'), // All Group(s) are listed under this.
			array('parent_id' => NULL, 'alias' => 'Users') // All User(s) are listed under this.
		);
		if ($Aro->saveAll($data['Aro'])) {
			echo "2 Root AROs created.\n";
		}

		$data['User'] = array(
			'admin1' => array('username' => 'admin1', 'password' => 'admin1'),
			'manager1' => array('username' => 'manager1', 'password' => 'manager1'),
			'user1' => array('username' => 'user1', 'password' => 'user1')
			);
		$data['Group'] = array(
			'administrators' => array('name' => 'administrators'),
			'managers' => array('name' => 'managers'),
			'users' => array('name' => 'users')
			);

		foreach ($data['Group'] as $key => $value) {
			$Group->create();
			if (!$Group->save($value))
				continue;
			$data['Group'][$key]['id'] = $Group->getInsertID();
		}

		foreach ($data['User'] as $key => $value) {
			$User->create();
			if (!$User->save($value))
				continue;
			$data['User'][$key]['id'] = $User->getInsertID();
		}

		$data['GroupMember'] = array(
			array('user_id' => $data['User']['admin1']['id'], 'group_id' => $data['Group']['administrators']['id']),
			array('user_id' => $data['User']['manager1']['id'], 'group_id' => $data['Group']['administrators']['id']),
			array('user_id' => $data['User']['manager1']['id'], 'group_id' => $data['Group']['managers']['id']),
			array('user_id' => $data['User']['user1']['id'], 'group_id' => $data['Group']['users']['id'])
			);
		$GroupMember->create();
		if ($GroupMember->saveAll($data['GroupMember'])) {
			echo "Created 4 group memberships.\n";
		}

		return $data;
	}

/**
 * NoteFolder(s) created in nested structure: Level_1, Level_Two, Level_Trois.
 * Note(s) created for each NoteFolder above: note_1, note_2, note_3.
 *
 * Root ACO node 'Notes' created. See NoteFolder::parentNode().
 *
 * Groups allowed CRUD: 'administrators' to 'Level_1', 'managers' to 'Level_Two', 'users' to 'Level_Trois'.
 * User permissions are per default in NoteFoldersController and NotesController.
 */
        private function createFoldersAndNotes($data) {
		$Aco = ClassRegistry::init('Aco');
		$NoteFolder = ClassRegistry::init('NoteFolder');
		$Note = ClassRegistry::init('Note');

		$NoteFoldersController = new NoteFoldersController();
		$NoteFoldersController->constructClasses();
		$NoteFoldersController->NoteFolder = $NoteFolder;

		$NotesController = new NotesController();
		$NotesController->constructClasses();
		$NotesController->Note = $Note;

		// Create root ACOs.
		$data['Aco'] = array('Notes' => array('parent_id' => NULL, 'alias' => 'Notes'));
		$Aco->create();
		if (!$Aco->save($data['Aco']['Notes']))
				continue;
		echo "Root ACO for NoteFolders/Notes created.\n";
		$data['Aco']['Notes']['id'] = $Aco->getInsertID(); // Just in case. Seems I don't need it.

		$data['NoteFolder'] = array(
			'level_1' => array('name' => 'Level_1', 'user_id' => $data['User']['admin1']['id']),
			'level_2' => array('name' => 'Level_Two', 'user_id' => $data['User']['manager1']['id']),
			'level_3' => array('name' => 'Level_Trois', 'user_id' => $data['User']['user1']['id'])
		);
		$data['Note'] = array(
			'note_1' => array('title' => 'Note 1 Title', 'body' => 'Note 1 Body.', 'user_id' => $data['User']['admin1']['id']),
			'note_2' => array('title' => 'Note 2 Title', 'body' => 'Note 2 Body.', 'user_id' => $data['User']['manager1']['id']),
			'note_3' => array('title' => 'Note 3 Title', 'body' => 'Note 3 Body.', 'user_id' => $data['User']['user1']['id'])
		);

		for ($i = 1; $i < 4; $i++) {
			$NoteFolder->create(); $Note->create();
			if (!$NoteFolder->save($data['NoteFolder']['level_'.$i]))
				return;
			$NoteFolder->read(); $NoteFoldersController->_setDefaultPermissions();
			switch ($i) {
			case 1: $NoteFoldersController->_allowGroupPermissions($data['Group']['administrators']['id']); break;
			case 2: $NoteFoldersController->_allowGroupPermissions($data['Group']['managers']['id']); break;
			case 3: $NoteFoldersController->_allowGroupPermissions($data['Group']['users']['id']); break;
			}

			$data['NoteFolder']['level_'.$i]['id'] = $data['Note']['note_'.$i]['note_folder_id'] =
				$NoteFolder->getInsertID();

			if ($i < 3)
				$data['NoteFolder']['level_'.($i + 1)]['note_folder_id'] = $NoteFolder->getInsertID();

			if (!$Note->save($data['Note']['note_'.$i]))
				return;
			$data['Note']['note_'.$i]['id'] = $Note->getInsertID();
			$Note->read(); $NotesController->_setDefaultPermissions();
			echo "Level $i NoteFolder, Note and permissions created.\n";
		}

		return $data;
	}

/**
 * Controller ACOs created for ActionsAuthorize.
 * See AppController->components->Auth->authorize.
 */
	private function createControllersACOs() {
		$Aco = ClassRegistry::init('Aco');

		$rootNodeAlias = 'controllers';

		// Create root ACO.
		$data['Aco'] = array('parent_id' => NULL, 'alias' => $rootNodeAlias);
		$Aco->create();
		if (!$Aco->save($data['Aco']))
			return;
		echo "Root ACO for ActionsAuthorize created.\n";

		// Create the whole ACO tree.
		$AclExtrasShell = new AclExtrasShell();
		$AclExtrasShell->rootNode = $rootNodeAlias;
		$AclExtrasShell->startup();
		$AclExtrasShell->aco_sync();
	}

/**
 * Assign permissions to Controller ACOs:
 * - 'controllers' (top-level)
 * - 'controllers/Notes'
 * - 'controllers/Notes/add'
 * - 'controllers/Notes/edit'
 */
	private function createInitialPermissions($data) {
		$Group = ClassRegistry::init('Group');

		$rootNodeAlias = 'controllers';
		$RbAcl = new RbAcl();

		// Allow administrators to everything.
		$Group->id = $data['Group']['administrators']['id'];
		$RbAcl->allow($Group, $rootNodeAlias);

		echo "Set permissions for Group 'administrators'.\n";

		// Allow managers to delete users and groups.
		$Group->id = $data['Group']['managers']['id'];
		$RbAcl->deny($Group, $rootNodeAlias);
		// Only administrators can delete Users.
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/index');
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/add');
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/view');
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/edit');
		// Allow managers full CRUD on Groups, Notes and NoteFolders.
		$RbAcl->allow($Group, $rootNodeAlias . '/Groups');
		$RbAcl->allow($Group, $rootNodeAlias . '/Notes');
		$RbAcl->allow($Group, $rootNodeAlias . '/NoteFolders');

		echo "Set permissions for Group 'managers'.\n";

		$Group->id = $data['Group']['users']['id'];
		$RbAcl->deny($Group, $rootNodeAlias);
		// Allow users full CRUD on Notes and NoteFolders.
		$RbAcl->allow($Group, $rootNodeAlias . '/Notes');
		$RbAcl->allow($Group, $rootNodeAlias . '/NoteFolders');
		// Allow users to only view/edit Users (controller restricts that to own user account)
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/view');
		$RbAcl->allow($Group, $rootNodeAlias . '/Users/edit');
		// Deny users from CRUD on Group. Leave that to managers.

		echo "Set permissions for Group 'users'.\n";
	}

/**
 * Test of all the permissions set so far.
 */
	private function testPermissions($data) {
		$User = ClassRegistry::init('User');
		$NoteFolder = ClassRegistry::init('NoteFolder');
		$Note = ClassRegistry::init('Note');
		$RbAcl = new RbAcl();

		$userAdmin1 = array('User' => array('id' => $data['User']['admin1']['id']));
		$userManager1 = array('User' => array('id' => $data['User']['manager1']['id']));
		$userUser1 = array('User' => array('id' => $data['User']['user1']['id']));

		$noteFolderL1 = array('NoteFolder' => array('id' => $data['NoteFolder']['level_1']['id']));
		$noteFolderL2 = array('NoteFolder' => array('id' => $data['NoteFolder']['level_2']['id']));
		$noteFolderL3 = array('NoteFolder' => array('id' => $data['NoteFolder']['level_3']['id']));

		$note1 = array('Note' => array('id' => $data['Note']['note_1']['id']));
		$note2 = array('Note' => array('id' => $data['Note']['note_2']['id']));
		$note3 = array('Note' => array('id' => $data['Note']['note_3']['id']));

		if ($RbAcl->check($userAdmin1, $noteFolderL1) && $RbAcl->check($userAdmin1, $note1) &&
		    $RbAcl->check($userAdmin1, $noteFolderL2) && $RbAcl->check($userAdmin1, $note2) &&
		    $RbAcl->check($userAdmin1, $noteFolderL3) && $RbAcl->check($userAdmin1, $note3))
			echo "TEST PASSED! User 'admin1' has CRUD access to all NoteFolder(s) and Note(s).\n";

		if ($RbAcl->check($userManager1, $noteFolderL1) && $RbAcl->check($userManager1, $note1) &&
		    $RbAcl->check($userManager1, $noteFolderL2) && $RbAcl->check($userManager1, $note2) &&
		    $RbAcl->check($userManager1, $noteFolderL3) && $RbAcl->check($userManager1, $note3))
			echo "TEST PASSED! User 'manager1' has CRUD access to all NoteFolder(s) and Note(s).\n";

		if (!($RbAcl->check($userUser1, $noteFolderL1) || $RbAcl->check($userUser1, $note1) ||
		      $RbAcl->check($userUser1, $noteFolderL2) || $RbAcl->check($userUser1, $note2)))
			echo "TEST PASSED! User 'user1' has NO access to Level 1 and 2 NoteFolder(s) and Note(s).\n";

		if ($RbAcl->check($userManager1, $noteFolderL3) && $RbAcl->check($userManager1, $note3))
			echo "TEST PASSED! User 'user1' has CRUD access to Level 3 NoteFolder(s) and Note(s).\n";

	}

}
