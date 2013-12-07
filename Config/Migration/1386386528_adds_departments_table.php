<?php
class AddsDepartmentsTable1386386528 extends CakeMigration {

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
				'departments' => array(
					'id' => array('type' => 'integer', 'key' => 'primary'),
					'name' => array('type' => 'string', 'null' => false),
					'created' => array('type' => 'datetime', 'null' => true),
					'modified' => array('type' => 'datetime', 'null' => true),
					'indexes' => array(
						'UNIQUE_DEPT_NAME' => array(
							'column' => 'name',
							'unique' => true
						)
					)
				),
			),
			'create_field' => array(
				'users' => array('department_id' => array('type' => 'integer', 'null' => false))
			)
		),
		'down' => array(
			'drop_table' => array('departments'),
			'drop_field' => array('users' => array('department_id'))
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

		if ($direction == 'up') {
			$transaction = $dataSource->begin();
			if ($transaction)
				echo "Transaction was already started.\n";
			else
				echo "Transaction starting.\n";

			$data = $this->createDepartments();
			$this->assignDepartmentUsers($data);

			$transaction = $dataSource->commit();
			echo "Transaction committed. Commit code: " . $transaction . "\n";
		}
		if ($direction = 'down') {
		}
		return true;
	}

	private function createDepartments() {
		$Department = ClassRegistry::init('Department');

		$data['Department'] = array(
			'dept_1' => array('name' => 'Department One'),
			'dept_2' => array('name' => 'Department 2'),
			'dept_3' => array('name' => 'Department Tres')
		);
		foreach ($data['Department'] as $key => $dept) {
			$Department->create();
			if (!$Department->save($dept))
				return; // Fail fast.
			$data['Department'][$key]['id'] = $Department->getInsertID();
		}
		echo "3 departments created.\n";
		return $data;
	}

	private function assignDepartmentUsers($data) {
		$User = ClassRegistry::init('User');

		$fields = array('id'); // DboSource::query(), l 557.
		$data['User']['admin1'] = $User->findByUsername('admin1', $fields)['User']; // DboSource::query(), l 540.
		$data['User']['manager1'] = $User->findByUsername('manager1', $fields)['User'];
		$data['User']['manager2'] = $User->findByUsername('manager2', $fields)['User'];
		$data['User']['user1'] = $User->findByUsername('user1', $fields)['User'];

		$data['User']['admin1']['department_id'] = $data['Department']['dept_1']['id'];
		$data['User']['manager1']['department_id'] = $data['User']['manager2']['department_id'] =
			$data['Department']['dept_2']['id'];
		$data['User']['user1']['department_id'] = $data['Department']['dept_3']['id'];

		foreach ($data['User'] as $key => $value) {
			$User->create();
			if (!$User->save($value))
				return;
		}

		echo "Assigned 4 users to 3 departments.\n";
	}
}
