<?php
/**
 * REST API: Projects class
 *
 * @since 3.4.0
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\Request_Token;

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
						'token' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
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

		$request_token = new Request_Token( $request['token'] );

		// Abort if token is invalid.
		if ( ! $request_token->exists() ) {
			return new \WP_Error(
				'bad_token',
				'Failed to get Asana project. Invalid request.',
				array( 'status' => 400 )
			);
		}

		// Check the cached response.
		$cached_response = $request_token->get_cache_data();
		if ( ! empty( $cached_response ) ) {
			// Return cached data if available.
			return new \WP_REST_Response( $cached_response, 200 );
		}

		try {

			// Get Asana authentication.

			$args = $request_token->get_args();
			if ( empty( $args['auth_user'] ) ) {
				// There is no user for Asana authentication.
				return new \WP_Error(
					'no_auth',
					'Failed to get Asana project. Authentication user was not specified.',
					array( 'status' => 401 )
				);
			}

			// Perform request.

			Asana_Interface::get_client( (int) $args['auth_user'] );
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
			$request_token->update_cache_data( $project_data );
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
