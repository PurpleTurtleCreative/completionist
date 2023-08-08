<?php
/**
 * REST_Server class
 *
 * @since 3.4.0
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/public/rest-api/class-attachments.php';
require_once PLUGIN_PATH . 'src/public/rest-api/class-projects.php';
require_once PLUGIN_PATH . 'src/public/rest-api/class-settings.php';

/**
 * Class to register all custom REST API endpoints.
 *
 * @since 3.4.0
 */
class REST_Server {

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 3.4.0
	 */
	public static function register() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 3.4.0
	 */
	public static function register_routes() {
		REST_API\Attachments::register_routes();
		REST_API\Projects::register_routes();
		REST_API\Settings::register_routes();
	}
}
