<?php
/**
 * REST API: Projects class
 *
 * @since 3.4.0
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const \PTC_Completionist\PLUGIN_PATH;
use const \PTC_Completionist\REST_API_NAMESPACE_V1;

use \PTC_Completionist\Asana_Interface;
use \PTC_Completionist\Options;
use \PTC_Completionist\HTML_Builder;
use \PTC_Completionist\Request_Tokens;

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
require_once PLUGIN_PATH . 'src/public/class-request-tokens.php';

/**
 * Class to register and handle custom REST API endpoints for Asana projects.
 *
 * @since 3.4.0
 */
class Projects {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 3.4.0
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
	 * @since 3.4.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_project(
		\WP_REST_Request $request
	) {

		$request_tokens = new Request_Tokens( $request['post_id'] );

		// Abort if token is invalid.
		if ( ! $request_tokens->exists( $request['token'] ) ) {
			return new \WP_Error(
				'bad_token',
				'Failed to get Asana project. Invalid request.',
				array( 'status' => 400 )
			);
		}

		// Check the cached response.
		$cached_response = $request_tokens->get_cached_response( $request['token'], false );
		if ( false !== $cached_response ) {
			// Return cached data if available.
			return new \WP_REST_Response( $cached_response, 200 );
		}

		try {

			// Perform request.

			$args = $request_tokens->get_request_args( $request['token'] );

			$auth_user_id = $args['auth_user'] ?: Options::get( Options::FRONTEND_AUTH_USER_ID );

			if ( -1 === $auth_user_id ) {
				// There is no user for Asana authentication.
				return new \WP_Error(
					'no_auth',
					'Failed to get Asana project. Authentication user was not specified.',
					array( 'status' => 401 )
				);
			}

			// Perform request.

			Asana_Interface::get_client( (int) $auth_user_id );
			$project_data = Asana_Interface::get_project_data(
				$args['project_gid'],
				$args
			);

			if ( empty( $project_data ) ) {
				// An empty response is unexpected. Do not cache this.
				return new \WP_Error(
					'empty_content',
					'Failed to get Asana project. There is no project data.',
					array( 'status' => 409 )
				);
			}

			// Cache response and return.
			$request_tokens->save_response( $request['token'], $project_data );
			return new \WP_REST_Response( $project_data, 200 );
		} catch ( \Exception $e ) {
			$error_code = HTML_Builder::get_error_code( $e );
			if ( $error_code < 400 ) {
				// Prevent code 0 for odd errors like "could not resolve host name".
				$error_code = 400;
			}
			return new \WP_Error(
				'asana_error',
				'Failed to get Asana project. ' . HTML_Builder::get_error_message( $e ),
				array( 'status' => $error_code )
			);
		}

		// This shouldn't be reachable.
		return new \WP_Error(
			'unknown_error',
			'Failed to get Asana project. Unknown error.',
			array( 'status' => 500 )
		);
	}
}