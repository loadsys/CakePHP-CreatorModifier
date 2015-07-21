<?php
namespace CreatorModifier\Test\TestCase\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CreatorModifier\Model\Behavior\CreatorModifierBehavior;

/**
 * CreatorModifierBehaviorTest Class
 */
class CreatorModifierBehaviorTest extends TestCase {

	/**
	 * Mocked User UUID Value
	 *
	 * @var string
	 */
	protected $mockedUserUUID = "03c129be-1e89-4240-8f5f-5cb9c9388833";

	/**
	 * fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		//'CreatorModifier.users'
	];

	/**
	 * Setup the Tests
	 *
	 * @return void
	 */
	public function setUp() {
		$table = $this->getMock('Cake\ORM\Table');
		$this->Behavior = $this->getMock(
			'\CreatorModifier\Model\Behavior\CreatorModifierBehavior',
			['sessionUserId'],
			[$table]
		);
		$this->Behavior->expects($this->any())
			->method('sessionUserId')
			->with()
			->will($this->returnValue($this->mockedUserUUID));
	}

	/**
	 * Sanity check Implemented events
	 *
	 * @return void
	 */
	public function testImplementedEventsDefault() {
		$expected = [
			'Model.beforeSave' => 'handleEvent'
		];
		$this->assertEquals($expected, $this->Behavior->implementedEvents());
	}

	/**
	 * The behavior allows for handling any event - test an example
	 *
	 * @return void
	 */
	public function testImplementedEventsCustom() {
		$table = $this->getMock('Cake\ORM\Table');
		$settings = ['events' => ['Something.special' => ['date_specialed' => 'always']]];
		$this->Behavior = new CreatorModifierBehavior($table, $settings);

		$expected = [
			'Something.special' => 'handleEvent'
		];
		$this->assertEquals($expected, $this->Behavior->implementedEvents());
	}

	/**
	 * Test when the creator_id field is absent from the Entity
	 *
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testCreatorIdAbsent() {
		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo']);

		$return = $this->Behavior->handleEvent($event, $entity);
		$this->assertTrue($return, 'Handle Event is expected to always return true');
		$this->assertInternalType('string', $entity->creator_id);
		$this->assertSame(
			$this->mockedUserUUID,
			$entity->creator_id,
			'Creator_id Field Equals The Mocked Value'
		);
	}

	/**
	 * Test when the creator_id field is present in the Entity
	 *
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testCreatorIdPresent() {
		$existingValue = "54108b70-a178-4590-9df4-1a900a00020f";

		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo', 'creator_id' => $existingValue]);

		$return = $this->Behavior->handleEvent($event, $entity);
		$this->assertTrue($return, 'Handle Event is expected to always return true');
		$this->assertInternalType('string', $entity->creator_id);
		$this->assertSame(
			$existingValue,
			$entity->creator_id,
			'Creator_id Field Equals The Existing Value'
		);
	}

	/**
	 * Test the creator_id is not added when the entity is not new.
	 *
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testCreatorIdNotNew() {
		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo']);
		$entity->isNew(false);

		$return = $this->Behavior->handleEvent($event, $entity);
		$this->assertTrue($return, 'Handle Event is expected to always return true');
		$this->assertNull(
			$entity->creator_id,
			'Creator_id Field Is Expected To Be Untouched If The Entity Is Not New'
		);
	}

	/**
	 * Test when the modifier id is absent
	 *
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testModifierIdAbsent() {
		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo']);

		$return = $this->Behavior->handleEvent($event, $entity);
		$this->assertTrue($return, 'Handle Event is expected to always return true');
		$this->assertInternalType('string', $entity->modifier_id);
		$this->assertSame(
			$this->mockedUserUUID,
			$entity->modifier_id,
			'Modifier_id Field Equals The Mocked Value'
		);
	}

	/**
	 * testModifiedPresent
	 *
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testModifierIdPresent() {
		$existingValue = "54108b70-a178-4590-9df4-1a900a00020f";

		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo', 'modifier_id' => $existingValue]);
		$entity->clean();
		$entity->isNew(false);

		$return = $this->Behavior->handleEvent($event, $entity);
		$this->assertTrue($return, 'Handle Event is expected to always return true');
		$this->assertInternalType('string', $entity->modifier_id);
		$this->assertSame(
			$this->mockedUserUUID,
			$entity->modifier_id,
			'Modifier_id Field Equals The Mocked Value as it Should be edited'
		);
	}

	/**
	 * testInvalidEventConfig
	 *
	 * @expectedException \UnexpectedValueException
	 * @expectedExceptionMessage When should be one of "always", "new" or "existing". The passed value "fat fingers" is invalid
	 * @return void
	 * @triggers Model.beforeSave
	 */
	public function testInvalidEventConfig() {
		$table = $this->getMock('Cake\ORM\Table');
		$settings = ['events' => ['Model.beforeSave' => ['creator_id' => 'fat fingers']]];
		$this->Behavior = new CreatorModifierBehavior($table, $settings);

		$event = new Event('Model.beforeSave');
		$entity = new Entity(['name' => 'Foo']);
		$this->Behavior->handleEvent($event, $entity);
	}

	/**
	 * testGetTimestamp
	 *
	 * @return void
	 */
	public function testGetUserId() {
		$return = $this->Behavior->getUserId();
		$this->assertInternalType('string', $return);
		$this->assertSame(
			$this->mockedUserUUID,
			$return,
			'Return from the getUserId method'
		);
	}

	/**
	 * test that get user id persists
	 *
	 * @depends testGetTimestamp
	 * @return void
	 */
	public function testGetUserIdPersists($behavior) {
		$initialValue = $this->Behavior->getUserId();
		$postValue = $this->Behavior->getUserId();

		$this->assertSame(
			$initialValue,
			$postValue,
			'The getUserId should be exactly the same object'
		);
	}

	/**
	 * testCreatedOrModified
	 *
	 * @return void
	 */
	public function testCreatedOrModified() {
		$entity = new Entity(['name' => 'Foo', 'creator_id' => null]);
		$return = $this->Behavior->createdOrModifed($entity);
		$this->assertTrue(
			$return,
			'createdOrModifed is expected to return true if it sets a field value'
		);
		$this->assertSame(
			$this->mockedUserUUID,
			$entity->modifier_id,
			'Modifier ID was set as the mocked value.'
		);
	}

	/**
	 * testTouchNoop
	 *
	 * @return void
	 */
	public function testCreatedOrModifiedNoop() {
		$table = $this->getMock('Cake\ORM\Table');
		$config = [
			'events' => [
				'Model.beforeSave' => [
					'created' => 'new',
				]
			]
		];

		$this->Behavior = $this->getMock(
			'\CreatorModifier\Model\Behavior\CreatorModifierBehavior',
			['sessionUserId'],
			[$table, $config]
		);
		$this->Behavior->expects($this->any())
			->method('sessionUserId')
			->will($this->returnValue($this->mockedUserUUID));

		$entity = new Entity(['username' => 'timestamp test']);
		$return = $this->Behavior->createdOrModifed($entity);
		$this->assertFalse($return, 'createdOrModifed is expected to do nothing and return false');
		$this->assertNull($entity->modifier_id, 'modifier_id field is NOT expected to change');
		$this->assertNull($entity->creator_id, 'creator_id field is NOT expected to change');
	}

	/**
	 * testTouchCustomEvent
	 *
	 * @return void
	 */
	public function testCreatedOrModifiedCustomEvent() {
		$table = $this->getMock('Cake\ORM\Table');
		$settings = ['events' => ['Something.special' => ['user_special' => 'always']]];

		$this->Behavior = $this->getMock(
			'\CreatorModifier\Model\Behavior\CreatorModifierBehavior',
			['sessionUserId'],
			[$table, $settings]
		);
		$this->Behavior->expects($this->any())
			->method('sessionUserId')
			->will($this->returnValue($this->mockedUserUUID));
		$this->Behavior->getUserId();

		$entity = new Entity(['username' => 'timestamp test']);
		$return = $this->Behavior->createdOrModifed($entity, 'Something.special');
		$this->assertTrue(
			$return,
			'createdOrModifed is expected to return true if it sets a field value'
		);
		$this->assertSame(
			$this->mockedUserUUID,
			$entity->user_special,
			'modifier_id field is expected to be the mocked value'
		);
		$this->assertNull(
			$entity->creator_id,
			'Creator_id field is NOT expected to change'
		);
	}
}
