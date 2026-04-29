<?php
/**
 * Background updater class loader.
 * Sets SM related stuff and fires it.
 *
 * @package SM/Core/Updating
 */

defined( 'ABSPATH' ) or die;

if ( ! class_exists( 'SM_WP_Async_Request', false ) ) {
	include_once 'vendor/wp-async-request.php';
}

if ( ! class_exists( 'SM_WP_Background_Process', false ) ) {
	include_once 'vendor/wp-background-process.php';
}

/**
 * Adds SM options and fires it.
 *
 * @since 2.8
 */
class SM_Background_Updater extends SM_WP_Background_Process {

	/**
	 * Restrict object instantiation when unserialising queue batches.
	 * Upstream default is `true` (any class) for BC; we set false because
	 * the queue only ever carries string callable names — no legitimate
	 * object payloads exist, so disallowing them closes the deserialise-to-
	 * arbitrary-callable path entirely.
	 *
	 * @var bool|array
	 */
	protected $allowed_batch_data_classes = false;

	/**
	 * Action name.
	 *
	 * @var string
	 */
	protected $action = 'sm_updater';

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task.
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 *
	 * @return mixed
	 */
	protected function task( $callback ) {
		if ( ! defined( 'SM_UPDATING' ) ) {
			define( 'SM_UPDATING', true );
		}

		include_once 'sm-update-functions.php';

		// Only invoke string callables that match the sm_update_* prefix.
		// Defence-in-depth on top of the unserialize allowed_classes=>false
		// guard in vendor/wp-background-process.php — closes the
		// "deserialised array maps to a Closure / arbitrary callable"
		// path even if a future regression weakens that guard.
		if ( is_string( $callback )
			&& 0 === strpos( $callback, 'sm_update_' )
			&& function_exists( $callback ) ) {
			call_user_func( $callback );
		}

		return false;
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		SM_Install::update_db_version();
		parent::complete();
	}
}
