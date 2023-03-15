<?php
/**
 * REST API: Attachments class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const \PTC_Completionist\PLUGIN_PATH;
use const \PTC_Completionist\REST_API_NAMESPACE_V1;

use \PTC_Completionist\Asana_Interface;
use \PTC_Completionist\Options;
use \PTC_Completionist\HTML_Builder;
use \PTC_Completionist\Request_Tokens;
use \PTC_Completionist\Util;

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
require_once PLUGIN_PATH . 'src/public/class-request-tokens.php';
require_once PLUGIN_PATH . 'src/includes/class-util.php';

/**
 * Class to register and handle custom REST API endpoints for Asana attachments.
 *
 * @since [unreleased]
 */
class Attachments {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {
		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/attachments',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_attachment' ),
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
	 * Handles a GET request to retrieve Asana attachment data.
	 *
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_attachment(
		\WP_REST_Request $request
	) {

		$request_tokens = new Request_Tokens( $request['post_id'] );

		// Abort if token is invalid.
		if ( ! $request_tokens->exists( $request['token'] ) ) {
			return new \WP_Error(
				'bad_token',
				'Failed to get Asana attachment. Invalid request.',
				array( 'status' => 400 )
			);
		}

		// !! NOTE !! This request token does not use caching
		// since it is intended for making fresh requests.

		try {

			// Perform request.

			$args = $request_tokens->get_request_args( $request['token'] );

			$auth_user_id = $args['auth_user'] ?: Options::get( Options::FRONTEND_AUTH_USER_ID );

			if ( -1 === $auth_user_id ) {
				// There is no user for Asana authentication.
				return new \WP_Error(
					'no_auth',
					'Failed to get Asana attachment. Authentication user was not specified.',
					array( 'status' => 401 )
				);
			}

			// Perform request.
			Asana_Interface::get_client( (int) $auth_user_id );
			$attachment = Asana_Interface::get_attachment_data(
				$args['attachment_gid']
			);

			if ( empty( $attachment ) ) {
				// An empty response is unexpected.
				return new \WP_Error(
					'empty_content',
					'Failed to get Asana attachment. There is no attachment data.',
					array( 'status' => 409 )
				);
			}

			// Add request token for retrieving the attachment again.
			$attachment->_ptc_refresh_url = add_query_arg(
				array(
					'token' => $request_tokens->save(
						array(
							'attachment_gid' => $attachment->gid,
							'auth_user' => $args['auth_user'],
						)
					),
					'post_id' => $request_tokens->get_post_id(),
				),
				rest_url( REST_API_NAMESPACE_V1 . '/attachments' )
			);

			// Ensure GID is stripped.
			unset( $attachment->gid );

			// Return response.
			return new \WP_REST_Response( $attachment, 200 );
		} catch ( \Exception $e ) {
			$error_code = HTML_Builder::get_error_code( $e );
			if ( $error_code < 400 ) {
				// Prevent code 0 for odd errors like "could not resolve host name".
				$error_code = 400;
			}
			return new \WP_Error(
				'asana_error',
				'Failed to get Asana attachment. ' . HTML_Builder::get_error_message( $e ),
				array( 'status' => $error_code )
			);
		}

		// This shouldn't be reachable.
		return new \WP_Error(
			'unknown_error',
			'Failed to get Asana attachment. Unknown error.',
			array( 'status' => 500 )
		);
	}
}
