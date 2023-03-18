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

			// Handle if $args['proxy_field'] which should
			// instead respond with the same response as requesting
			// the desired field, such as 'view_url'.
			// The browser should cache this response.
			if ( ! empty( $args['proxy_field'] ) ) {
				if ( ! empty( $attachment->{$args['proxy_field']} ) ) {

					$ch = curl_init();
					$response_headers = array();
					curl_setopt_array(
						$ch,
						array(
							CURLOPT_HTTPGET => true,
							CURLOPT_URL => $attachment->{$args['proxy_field']},
							CURLOPT_FOLLOWLOCATION => true,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_HEADERFUNCTION => function ( $curl, $header ) use ( &$response_headers ) {

								$len = strlen( $header );

								$header = trim( $header );

								if (
									 ! empty( $header ) &&
									0 !== stripos( $header, 'x-' )
								) {
									// Ignore extra, custom headers.
									if (
										true === Util::str_starts_with_any(
											$header,
											array(
												'Accept-Ranges:',
												'Content-Disposition:',
												'Content-Length:',
												'Content-Type:',
												'Date:',
												'ETag:',
												'Last-Modified:',
											),
											false
										)
									) {
										// Collect trusted header.
										$response_headers[] = $header;
									}
								}

								// Return the header's original length.
								return $len;
							},
						)
					);

					$response_body = curl_exec( $ch );
					curl_close( $ch );

					// Configure proxy response and exit.
					foreach ( $response_headers as $header ) {
						header( $header );
					}
					// Asana serves user profile photos from AWS S3
					// with `max-age=1209600`, which is 14 days, so let's
					// just use that duration here as well. New assets
					// would be stored under a different attachment GID
					// and a new location, anyways, so this asset is
					// actually expected to never change.
					header( 'Cache-Control: max-age=' . 14 * DAY_IN_SECONDS );
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
				$request_tokens->get_post_id(),
				$args['auth_user']
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
