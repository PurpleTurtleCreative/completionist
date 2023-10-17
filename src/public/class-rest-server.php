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
		REST_API\Tasks::register_routes();
		Admin_Notices::register_routes();
	}

	/**
	 * Gets the route argument definition for a nonce.
	 *
	 * @since [unreleased]
	 *
	 * @param string $nonce_action The nonce action to verify.
	 * @return array The nonce argument definition.
	 */
	public static function get_route_arg_nonce( string $nonce_action ) {
		return array(
			'type'              => 'string',
			'required'          => true,
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => function ( $value ) use ( $nonce_action ) {
				return ( false !== wp_verify_nonce( $value, $nonce_action ) );
			},
		);
	}
}
