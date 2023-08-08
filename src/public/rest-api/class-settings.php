<?php
/**
 * REST API: Settings class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const \PTC_Completionist\PLUGIN_PATH;
use const \PTC_Completionist\REST_API_NAMESPACE_V1;

use \PTC_Completionist\Asana_Interface;
use \PTC_Completionist\Options;
use \PTC_Completionist\HTML_Builder;
use \PTC_Completionist\Request_Token;
use \PTC_Completionist\Util;

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
require_once PLUGIN_PATH . 'src/public/class-request-token.php';
require_once PLUGIN_PATH . 'src/includes/class-util.php';

/**
 * Class to register and handle custom REST API endpoints for
 * plugin settings.
 *
 * @since [unreleased]
 */
class Settings {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {
		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_settings' ),
					'permission_callback' => function() {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(
						'_wpnonce' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'nonce'    => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function( $value, $request, $param ) {
								return in_array(
									wp_verify_nonce( $value, 'ptc_completionist' ),
									array( 1, 2 ),
									true
								);
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Handles a GET request to retrieve plugin settings.
	 *
	 * Note that the return varies depending on the authenticated
	 * user's capabilities.
	 *
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_settings(
		\WP_REST_Request $request
	) {

		// Prepare response data.
		$settings = array(
			'asana_user'          => null,
			'asana_workspace'     => null,
			'asana_collaborators' => null,
			'frontend_auth_user'  => null,
		);

		try {

			// Get Asana authentication.
			$asana = Asana_Interface::get_client();

			// Shared data.
			$can_manage_options  = current_user_can( 'manage_options' );
			$is_workspace_member = Asana_Interface::is_workspace_member();
			$site_collaborators  = Asana_Interface::get_site_collaborators();

			// Get Asana profile information.
			$settings['asana_user'] = array(
				'me' => Asana_Interface::get_me(),
				'can_manage_options' => $can_manage_options,
				'_links' => array(
					'disconnect' => array(
						'href' => add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'wp_rest' ),
								'nonce'    => wp_create_nonce( 'ptc_completionist_disconnect_asana' ),
							),
							rest_url( REST_API_NAMESPACE_V1 . '/settings/disconnect' )
						),
					),
				),
			);

			// Get Asana workspace information.

			if ( $can_manage_options || $is_workspace_member ) {
				// User is allowed to view Asana workspace settings.
				$settings['asana_workspace']['workspace_gid'] = Options::get( Options::ASANA_WORKSPACE_GID );
				$settings['asana_workspace']['tag_gid']       = Options::get( Options::ASANA_TAG_GID );
			}

			if ( $can_manage_options ) {
				// User is allowed to update Asana workspace settings.
				$settings['asana_workspace']['_links'] = array(
					'load_tags' => array(
						'href' => add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'wp_rest' ),
							),
							rest_url( REST_API_NAMESPACE_V1 . '/tags' )
						),
					),
					'update'    => array(
						'href' => add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'wp_rest' ),
								'nonce'    => wp_create_nonce( 'ptc_completionist_update_setting' ),
							),
							rest_url( REST_API_NAMESPACE_V1 . '/settings/workspace' )
						),
					),
				);
				// Warning insights data.
				$settings['asana_workspace']['pinned_tasks_count'] = Options::count_all_pinned_tasks();
			}

			// Get Asana workspace collaborators.
			if ( $is_workspace_member ) {
				$settings['asana_collaborators'] = $site_collaborators;
			}

			// Get the frontend authentication user.
			if ( $can_manage_options && ! empty( $site_collaborators ) ) {
				$settings['frontend_auth_user']['ID'] = Options::get( Options::FRONTEND_AUTH_USER_ID );
				$settings['frontend_auth_user']['_links'] = array(
					'update' => array(
						'href' => add_query_arg(
							array(
								'_wpnonce' => wp_create_nonce( 'wp_rest' ),
								'nonce'    => wp_create_nonce( 'ptc_completionist_update_setting' ),
							),
							rest_url( REST_API_NAMESPACE_V1 . '/settings/frontend_auth_user' )
						),
					),
				);
			}

			// Return.
			return new \WP_REST_Response( $settings, 200 );
		} catch ( \Exception $e ) {
			$error_code = HTML_Builder::get_error_code( $e );
			if ( $error_code < 400 ) {
				// Prevent code 0 for odd errors like "could not resolve host name".
				$error_code = 400;
			}
			return new \WP_Error(
				'asana_error',
				'Failed to get plugin settings. ' . HTML_Builder::get_error_message( $e ),
				array( 'status' => $error_code )
			);
		}

		// This shouldn't be reachable.
		return new \WP_Error(
			'unknown_error',
			'Failed to get plugin settings. Unknown error.',
			array( 'status' => 500 )
		);
	}
}
