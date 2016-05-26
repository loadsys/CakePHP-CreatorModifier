<?php
namespace CreatorModifier\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	public $fields = [
		'id' => ['type' => 'string', 'length' => 36, 'null' => false, 'default' => null, 'comment' => 'Primary Key for the Users Table, UUID', 'precision' => null, 'fixed' => null],
		'email' => ['type' => 'string', 'length' => 255, 'null' => false, 'default' => null, 'comment' => 'Email Address for the User', 'precision' => null, 'fixed' => null],
		'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => 'Created datetime', 'precision' => null],
		'creator_id' => ['type' => 'string', 'length' => 36, 'null' => true, 'default' => null, 'comment' => 'ID of User who created row', 'precision' => null, 'fixed' => null],
		'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => 'Modified datetime', 'precision' => null],
		'modifier_id' => ['type' => 'string', 'length' => 36, 'null' => true, 'default' => null, 'comment' => 'ID of User who modified row', 'precision' => null, 'fixed' => null],
		'_constraints' => [
			'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
		],
		'_options' => [
			'engine' => 'InnoDB',
			'collation' => 'utf8_general_ci',
		],
	];
	// @codingStandardsIgnoreEnd

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => '08148fbc-32ba-11e4-9e39-080027506c76',
			'email' => 'person1@localhost.com',
			'created' => '2014-09-02 16:45:55',
			'creator_id' => '799763fd-32bc-11e4-9e39-080027506c76',
			'modified' => '2014-09-02 16:45:55',
			'modifier_id' => '799763fd-32bc-11e4-9e39-080027506c76',
		],
		[
			'id' => '799763fd-32bc-11e4-9e39-080027506c76',
			'email' => 'person2@localhost.com',
			'created' => '2014-09-02 16:45:55',
			'creator_id' => '799763fd-32bc-11e4-9e39-080027506c76',
			'modified' => '2014-09-02 16:45:55',
			'modifier_id' => '799763fd-32bc-11e4-9e39-080027506c76',
		],
		[
			'id' => '74708ed9-33b1-11e4-9e39-080027506c76',
			'email' => 'person3@localhost.com',
			'created' => '2014-09-02 16:45:55',
			'creator_id' => '799763fd-32bc-11e4-9e39-080027506c76',
			'modified' => '2014-09-02 16:45:55',
			'modifier_id' => '799763fd-32bc-11e4-9e39-080027506c76',
		],
	];
}
