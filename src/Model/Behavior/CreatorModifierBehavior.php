<?php
/**
 * CreatorModifierBehavior is a tool to set a `creator_id` and `modifier_id`
 * on records being saved.
 */
namespace CreatorModifier\Model\Behavior;

use Cake\Event\Event;
use Cake\Log\LogTrait;
use Cake\Network\Request;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use \UnexpectedValueException;

/**
 * \CreatorModifier\Model\Behavior\CreatorModifierBehavior
 *
 * Adds the ability to use the Session provided User.primary_key
 * value to assign to the creator_id and modifier_id on saving an Entity.
 */
class CreatorModifierBehavior extends Behavior {

	// Add logging in the event of a failure with the session.
	use LogTrait;

	/**
	 * These are merged with user-provided config when the behavior is used.
	 *
	 * events - an event-name keyed array of which fields to update, and when, for a given event
	 * possible values for when a field will be updated are "always", "new" or "existing", to set
	 * the field value always, only when a new record or only when an existing record.
	 *
	 * sessionUserIdKey - The default key to read from the session for the current
	 * logged in User.id.
	 *
	 * @var array
	 */
	// @codingStandardsIgnoreStart
	protected $_defaultConfig = [
		'implementedFinders' => [],
		'implementedMethods' => [
			'getUserId' => 'getUserId',
			'createdOrModifed' => 'createdOrModifed'
		],
		'events' => [
			'Model.beforeSave' => [
				'creator_id' => 'new',
				'modifier_id' => 'always'
			]
		],
		'sessionUserIdKey' => 'Auth.User.id',
	];
	// @codingStandardsIgnoreEnd

	/**
	 * If events are specified - do *not* merge them with existing events,
	 * overwrite the events to listen on
	 *
	 * @param array $config The config for this behavior.
	 * @return void
	 */
	public function initialize(array $config) {
		if (isset($config['events'])) {
			$this->config('events', $config['events'], false);
		}
	}

	/**
	 * There is only one event handler, it can be configured to be called for any event
	 *
	 * @param \Cake\Event\Event $event Event instance.
	 * @param \Cake\ORM\Entity $entity Entity instance.
	 * @throws \UnexpectedValueException if a field's when value is misdefined
	 * @return true (irrespective of the behavior logic, the save will not be prevented)
	 * @throws \UnexpectedValueException When the value for an event is not 'always', 'new' or 'existing'
	 */
	public function handleEvent(Event $event, Entity $entity) {
		$eventName = $event->name();
		$events = $this->_config['events'];

		$new = $entity->isNew() !== false;

		foreach ($events[$eventName] as $field => $when) {
			if (!in_array($when, ['always', 'new', 'existing'])) {
				throw new UnexpectedValueException(
					sprintf('When should be one of "always", "new" or "existing". The passed value "%s" is invalid', $when)
				);
			}

			if ($when === 'always'
				|| ($when === 'new' && $new)
				|| ($when === 'existing' && !$new)
			) {
				$this->updateField($entity, $field);
			}
		}

		return true;
	}

	/**
	 * The implemented events of this behavior depend on configuration
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return array_fill_keys(array_keys($this->_config['events']), 'handleEvent');
	}

	/**
	 * Get the current logged in user id. If the Session throws an Exception, use
	 * a default User Id value provided from Configure.
	 *
	 * @return uuid|int The current logged in User.id or a default value.
	 */
	public function getUserId() {
		$userId = $this->sessionUserId();
		return $userId;
	}

	/**
	 * Modifies the Creator/Modifier fields for the entity in the beforeSave callback.
	 *
	 * @param \Cake\ORM\Entity $entity Entity instance.
	 * @param string $eventName Event name.
	 * @return bool true if a field is updated, false if no action performed
	 */
	public function createdOrModifed(Entity $entity, $eventName = 'Model.beforeSave') {
		$events = $this->_config['events'];
		if (empty($events[$eventName])) {
			return false;
		}

		$return = false;

		foreach ($events[$eventName] as $field => $when) {
			if (in_array($when, ['always', 'existing'])) {
				$return = true;
				$entity->dirty($field, false);
				$this->updateField($entity, $field);
			}
		}

		return $return;
	}

	/**
	 * Update a field, if it hasn't been updated already
	 *
	 * @param \Cake\ORM\Entity $entity Entity instance.
	 * @param string $field Field name
	 * @return void
	 */
	protected function updateField(Entity $entity, $field) {
		if ($entity->dirty($field)) {
			return;
		}

		$entity->set($field, $this->getUserId());
	}

	/**
	 * Factory method for the Request object.
	 *
	 * @return \Cake\Network\Request New instance of the Request object.
	 */
	protected function newRequest() {
		return new Request();
	}

	/**
	 * Return the User.id grabbed from the Session information.
	 *
	 * @return string The string representing the current logged in user.
	 */
	protected function sessionUserId() {
		$userId = null;
		$request = $this->newRequest();

		if ($request->session()->started()) {
			$userId = $request->session()->read($this->_config['sessionUserIdKey']);
		} else {
			$this->log('The Session is not started. This typically means a User is not logged in. In this case there is no Session value for the currently active User and therefore we will set the `creator_id` and `modifier_id` to a null value.', 'debug');
		}

		return $userId;
	}
}
