<?php
/**
 * Tests for the CreatorModifierBehavior Class.
 */
namespace CreatorModifier\Test\TestCase\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CreatorModifier\Model\Behavior\CreatorModifierBehavior;
use \RuntimeException;

/**
 * \CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior
 *
 * Expose the protected methods for the CreatorModifierBehavior class for unit
 * testing.
 */
class TestCreatorModifierBehavior extends CreatorModifierBehavior {
	public function updateField(Entity $entity, $field) {
		return parent::updateField($entity, $field);
	}

	public function sessionUserId() {
		return parent::sessionUserId();
	}
}

/**
 * \CreatorModifier\Test\TestCase\Model\Behavior\CreatorModifierBehaviorTest
 *
 * Tests for the CreatorModifierBehavior class.
 *
 * @coversDefaultClass \CreatorModifier\Model\Behavior\CreatorModifierBehavior
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
	public $fixtures = [];

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
			'Model.beforeSave' => 'handleEvent',
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
			'Something.special' => 'handleEvent',
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
	 * Test what happens when everything works and a modifier_id value for the
	 * Entity is not yet set.
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
	 * Test what happens when everything works and a modifier_id value for the
	 * Entity is already set.
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
	 * Test what happens when we setup invalid configuration for an Event.
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
	 * Test the method ::getUserId, should return our mocked return.
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
	 * Test that getUserId persists and returns the same value for multiple calls.
	 *
	 * @depends testGetUserId
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
	 * Test the Behavior in the event everything works normally.
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
	 * Test the Behavior when an event is fired that doesn't have any data set
	 * for it from the configs.
	 *
	 * @return void
	 */
	public function testCreatedOrModifiedEmptyEvent() {
		$table = $this->getMock('Cake\ORM\Table');
		$config = [
			'events' => [
				'Model.beforeSave' => [],
			],
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
	 * Test the CreatedModifiedBehavior in the event that nothing is expected to
	 * happen.
	 *
	 * @return void
	 */
	public function testCreatedOrModifiedNoop() {
		$table = $this->getMock('Cake\ORM\Table');
		$config = [
			'events' => [
				'Model.beforeSave' => [
					'created' => 'new',
				],
			],
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
	 * Test handling a custom fired event.
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

	/**
	 * Test the ::updateField method when the field is marked as dirty.
	 *
	 * @return void
	 */
	public function testUpdateFieldIsDirty() {
		$existingValue = '54108b70-a178-4590-9df4-1a900a00020f';
		$field = 'modifier_id';
		$entity = $this->getMock(
			'\Cake\ORM\Entity',
			['dirty'],
			[['name' => 'Foo', $field => $existingValue]]
		);
		$entity->expects($this->once())
			->method('dirty')
			->with($field)
			->will($this->returnValue(true));

		$table = $this->getMock('Cake\ORM\Table');
		$behavior = $this->getMock(
			'\CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior',
			['getUserId'],
			[$table]
		);
		$behavior->expects($this->never())
			->method('getUserId')
			->will($this->returnValue($this->mockedUserUUID));

		$return = $behavior->updateField($entity, 'modifier_id');
		$this->assertNull(
			$return,
			'When attempting to update a field marked already dirty, ::updateField returns null and does nothing.'
		);
	}

	/**
	 * Test the ::updateField method when the field is marked as dirty.
	 *
	 * @return void
	 */
	public function testUpdateFieldIsClean() {
		$existingValue = '54108b70-a178-4590-9df4-1a900a00020f';
		$field = 'modifier_id';
		$entity = $this->getMock(
			'\Cake\ORM\Entity',
			['dirty', 'set'],
			[['name' => 'Foo', $field => $existingValue]]
		);
		$entity->expects($this->once())
			->method('dirty')
			->with($field)
			->will($this->returnValue(false));
		$entity->expects($this->once())
			->method('set')
			->with($field, $this->mockedUserUUID)
			->will($this->returnValue(true));

		$table = $this->getMock('\Cake\ORM\Table');
		$behavior = $this->getMock(
			'\CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior',
			['getUserId'],
			[$table]
		);
		$behavior->expects($this->once())
			->method('getUserId')
			->will($this->returnValue($this->mockedUserUUID));

		$return = $behavior->updateField($entity, 'modifier_id');
		$this->assertNull(
			$return,
			'When attempting to update a field marked clean, ::updateField returns null and sets the return from ::getUserId to the field for the Entity.'
		);
	}

	/**
	 * Test ::sessionUserId when the session exists, is started and returns a value.
	 *
	 * @return void
	 */
	public function testSessionUserIdSessionExists() {
		$request = $this->getMock(
			'\Cake\Network\Request',
			['session', 'started', 'read']
		);
		$request->expects($this->any())
			->method('session')
			->with()
			->will($this->returnSelf());
		$request->expects($this->once())
			->method('started')
			->with()
			->will($this->returnValue(true));
		$request->expects($this->once())
			->method('read')
			->with('Auth.User.id')
			->will($this->returnValue($this->mockedUserUUID));

		$table = $this->getMock('\Cake\ORM\Table');

		$behavior = $this->getMock(
			'\CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior',
			['newRequest'],
			[$table]
		);
		$behavior->expects($this->once())
			->method('newRequest')
			->with()
			->will($this->returnValue($request));

		$output = $behavior->sessionUserId();
		$this->assertEquals(
			$this->mockedUserUUID,
			$output,
			'On the session being started and returns a mocked value, that mocked value should be returned.'
		);
	}

	/**
	 * Test ::sessionUserId when the session is not started.
	 *
	 * @return void
	 */
	public function testSessionUserIdSessionNotStarted() {
		$request = $this->getMock(
			'\Cake\Network\Request',
			['session', 'started', 'read', 'start']
		);
		$request->expects($this->any())
			->method('session')
			->with()
			->will($this->returnSelf());
		$request->expects($this->once())
			->method('started')
			->with()
			->will($this->returnValue(false));
		$request->expects($this->once())
			->method('start')
			->with()
			->will($this->returnValue(true));
		$request->expects($this->once())
			->method('read')
			->with('Auth.User.id')
			->will($this->returnValue($this->mockedUserUUID));

		$table = $this->getMock('\Cake\ORM\Table');

		$behavior = $this->getMock(
			'\CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior',
			['newRequest', 'log'],
			[$table]
		);
		$behavior->expects($this->once())
			->method('newRequest')
			->with()
			->will($this->returnValue($request));
		$behavior->expects($this->once())
			->method('log')
			->with($this->anything(), 'debug')
			->will($this->returnValue(true));

		$output = $behavior->sessionUserId();
		$this->assertEquals(
			$this->mockedUserUUID,
			$output,
			'On the session not being already started and then manually started and returns a mocked value, that mocked value should be returned.'
		);
	}

	/**
	 * Test ::sessionUserId when the session is not started.
	 *
	 * @return void
	 */
	public function testSessionUserIdSessionNotStartedStartingThrowsException() {
		$exception = new RuntimeException();

		$request = $this->getMock(
			'\Cake\Network\Request',
			['session', 'started', 'read', 'start']
		);
		$request->expects($this->any())
			->method('session')
			->with()
			->will($this->returnSelf());
		$request->expects($this->once())
			->method('started')
			->with()
			->will($this->returnValue(false));
		$request->expects($this->once())
			->method('start')
			->with()
			->will($this->throwException($exception));
		$request->expects($this->never())
			->method('read')
			->with('Auth.User.id')
			->will($this->returnValue($this->mockedUserUUID));

		$table = $this->getMock('\Cake\ORM\Table');

		$behavior = $this->getMock(
			'\CreatorModifier\Test\TestCase\Model\Behavior\TestCreatorModifierBehavior',
			['newRequest', 'log'],
			[$table]
		);
		$behavior->expects($this->once())
			->method('newRequest')
			->with()
			->will($this->returnValue($request));
		$behavior->expects($this->once())
			->method('log')
			->with($this->anything(), 'debug')
			->will($this->returnValue(true));

		$output = $behavior->sessionUserId();
		$this->assertNull(
			$output,
			'On the session not being started, a `null` value should be returned.'
		);
	}
}
