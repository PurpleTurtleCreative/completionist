<?php
/**
 * REST_Server class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/public/rest-api/class-projects.php';

/**
 * Class to register all custom REST API endpoints.
 *
 * @since [unreleased]
 */
class REST_Server {

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {
		REST_API\Projects::register_routes();
	}
}
