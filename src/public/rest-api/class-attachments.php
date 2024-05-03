<?php
/**
 * REST API: Attachments class
 *
 * @since 3.5.0
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\PLUGIN_PATH;
use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\Options;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\Request_Token;
use PTC_Completionist\Util;

/**
 * Class to register and handle custom REST API endpoints for Asana attachments.
 *
 * @since 3.5.0
 */
class Attachments {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 3.5.0
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
	 * Handles a GET request to retrieve Asana attachment data.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_attachment(
		\WP_REST_Request $request
	) {

		$request_token = new Request_Token( $request['token'] );

		// Abort if token is invalid.
		if ( ! $request_token->exists() ) {
			return new \WP_Error(
				'bad_token',
				'Failed to get Asana attachment. Invalid request.',
				array( 'status' => 400 )
			);
		}

		// !! NOTE !! This request token does not use caching
		// since it is intended for making fresh requests.
		// The web browser should be handling caching for assets.

		try {

			// Get Asana authentication.

			$args = $request_token->get_args();
			if ( empty( $args['auth_user'] ) ) {
				// There is no user for Asana authentication.
				return new \WP_Error(
					'no_auth',
					'Failed to get Asana attachment. Authentication user was not specified.',
					array( 'status' => 401 )
				);
			}

			// Perform request.

			Asana_Interface::get_client( (int) $args['auth_user'] );
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

			// Handle if $args['proxy_field'] which should
			// instead respond with the same response as requesting
			// the desired field, such as 'view_url'.
			// The browser should cache this response.
			if ( ! empty( $args['proxy_field'] ) ) {
				if ( ! empty( $attachment->{$args['proxy_field']} ) ) {

					// Send proxied request.
					$response = wp_remote_get( $attachment->{$args['proxy_field']} );

					// Retrieve response data.
					$response_code    = wp_remote_retrieve_response_code( $response );
					$response_body    = wp_remote_retrieve_body( $response );
					$response_headers = wp_remote_retrieve_headers( $response );

					// Set the response code.
					if ( is_int( $response_code ) ) {
						http_response_code( $response_code );
					} else {
						// For example, if the request times out (cURL error 28).

						trigger_error(
							'Failed to proxy Asana attachment. Retrieved non-integer response code (' . esc_html( $response_code ) . ') - See the following error logs for more details.',
							\E_USER_NOTICE
						);

						error_log( 'Response headers: ' . print_r( $response_headers, true ) );
						error_log( 'Response body: ' . print_r( $response_body, true ) );

						if ( is_wp_error( $response ) ) {
							error_log( 'Response WP_Error: ' . print_r( $response, true ) );
						}

						// 502 Bad Gateway - failed upstream proxy request.
						http_response_code( 502 );
						exit;
					}

					// Remove previously set headers, like from WordPress.
					header_remove();

					// Proxy response headers.
					foreach ( $response_headers as $key => &$value ) {
						if (
							true === in_array(
								$key,
								array(
									'accept-ranges',
									'content-disposition',
									'content-length',
									'content-type',
									'date',
									'etag',
									'last-modified',
								),
								true
							)
						) {
							// Send trusted header.
							header( sprintf( '%s: %s', $key, $value ) );
						}
					}

					// Asana serves user profile photos from AWS S3
					// with `max-age=1209600`, which is 14 days, so let's
					// just use that duration here as well. New assets
					// would be stored under a different attachment GID
					// and a new location, anyways, so this asset is
					// actually expected to never change.
					header( 'Cache-Control: max-age=' . 14 * \DAY_IN_SECONDS );

					// Maintain noindex signal for search engines.
					header( 'X-Robots-Tag: noindex' );

					// Output the image data.
					print( $response_body );//phpcs:ignore
					exit;
				} else {
					return new \WP_Error(
						'bad_field',
						"Failed to proxy Asana attachment field value. Unrecognized attachment field name: {$args['proxy_field']}",
						array( 'status' => 400 )
					);
				}
			}

			// Add request token for retrieving the attachment again.
			$attachment->_ptc_refresh_url = HTML_Builder::get_local_attachment_url(
				$attachment->gid,
				array( 'auth_user' => $args['auth_user'] )
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
