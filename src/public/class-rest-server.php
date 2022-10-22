<?php
/**
 * REST_Server class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';

/**
 * Class to register and handle custom REST API endpoints.
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
		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/projects',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_project' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'token'   => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'post_id' => array(
							'type'              => 'integer',
							'required'          => true,
							'validate_callback' => function( $value, $request, $param ) {
								return is_numeric( $value );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Handles a GET request to retrieve Asana project data.
	 *
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_project(
		\WP_REST_Request $request
	) {

		$request_tokens = new Request_Tokens( $request['post_id'] );
		$cached_response = $request_tokens->get_cached_response( $request['token'], false );

		if ( false !== $cached_response ) {
			// Return cached data if available and valid.
			return new \WP_REST_Response( $cached_response, 200 );
		}

		try {

			// Perform request.

			$args = $request_tokens->get_request_args( $request['token'] );

			$auth_user_id = $args['auth_user'] ?: Options::get( Options::FRONTEND_AUTH_USER_ID );

			Asana_Interface::get_client( (int) $auth_user_id );
			$project_data = Asana_Interface::get_project_data( $args['project_gid'], $args );

			// Cache response and return.

			$request_tokens->save_response( $request['token'], $project_data );
			return new \WP_REST_Response( $project_data, 200 );
		} catch ( \Exception $e ) {
			return new \WP_Error( $e->getCode(), 'Failed to get Asana project. ' . $e->getMessage() );
		}

		// This shouldn't be reachable.
		return new \WP_Error( 500, 'Failed to get Asana project. Unknown error.' );
	}
}
