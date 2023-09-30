<?php
/**
 * REST_Server class
 *
 * @since 3.4.0
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

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
		REST_API\Projects::register_routes();
		REST_API\Attachments::register_routes();
		Admin_Notices::register_routes();
	}
}
