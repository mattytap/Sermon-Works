<?php
/**
 * Main admin file.
 *
 * @package SM/Core/Admin
 */

defined( 'ABSPATH' ) or die;

/**
 * Sermon Works Admin
 *
 * @since 2.9
 */
class SM_Admin {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_action( 'current_screen', array( $this, 'conditional_includes' ) );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {
		include_once 'sm-admin-functions.php';
		include_once 'class-sm-admin-post-types.php';
		include_once 'class-sm-admin-menus.php';
		include_once 'class-sm-admin-assets.php';
	}

	/**
	 * Include admin files conditionally.
	 */
	public function conditional_includes() {

	}
}

return new SM_Admin();
