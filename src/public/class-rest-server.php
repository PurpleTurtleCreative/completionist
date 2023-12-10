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
		REST_API\Automations::register_routes();
		REST_API\Tags::register_routes();
		REST_API\Posts::register_routes();
		Admin_Notices::register_routes();
	}

	/**
	 * Gets the route argument definition for a nonce field.
	 *
	 * @since 4.0.0
	 *
	 * @param string $nonce_action The nonce action to verify.
	 * @return array The argument definition.
	 */
	public static function get_arg_def_nonce( string $nonce_action ) : array {
		return array(
			'type'              => 'string',
			'required'          => true,
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => function ( $value ) use ( $nonce_action ) {
				return ( false !== wp_verify_nonce( $value, $nonce_action ) );
			},
		);
	}

	/**
	 * Gets the route argument definition for an Asana GID field.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $required Optional. If the argument is required.
	 * Default true.
	 * @return array The argument definition.
	 */
	public static function get_arg_def_gid( bool $required = true ) : array {
		return array(
			'type'              => 'string',
			'required'          => $required,
			'sanitize_callback' => function ( $value ) {
				return Options::sanitize( 'gid', $value );
			},
			'validate_callback' => function ( $value ) {
				return ( ! empty( $value ) );
			},
		);
	}

	/**
	 * Gets the route argument definition for an integer ID field.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $required Optional. If the argument is required.
	 * Default true.
	 * @return array The argument definition.
	 */
	public static function get_arg_def_id( bool $required = true ) : array {
		return array(
			'type'              => 'integer',
			'required'          => $required,
			'minimum'           => 1,
			'sanitize_callback' => function ( $value ) {
				return intval( $value );
			},
		);
	}

	/**
	 * Gets the route argument definition for a WordPress post ID field.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $required Optional. If the argument is required.
	 * Default true.
	 * @return array The argument definition.
	 */
	public static function get_arg_def_post_id( bool $required = true ) : array {
		return array(
			'type'              => 'integer',
			'required'          => $required,
			'minimum'           => 1,
			'sanitize_callback' => function ( $value ) {
				return intval( $value );
			},
			'validate_callback' => 'get_post',
		);
	}
}
