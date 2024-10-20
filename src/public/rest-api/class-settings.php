<?php
/**
 * REST API: Settings class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\Options;

/**
 * Class to register and handle custom REST API endpoints which manage this plugin's settings.
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
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				),
			)
		);
	}

	/**
	 * Handles a GET request to retrieve plugin settings data for the current user.
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

		$settings_for_user = Options::get_settings_for_user( get_current_user_id() );

		if ( ! empty( $settings_for_user['workspace']['connected_workspace_users'] ) ) {
			foreach ( $settings_for_user['workspace']['connected_workspace_users'] as $asana_gid => &$wp_user ) {
				$settings_for_user['workspace']['connected_workspace_users'][ $asana_gid ] = array(
					'ID'           => $wp_user->ID,
					'display_name' => $wp_user->display_name,
					'user_email'   => $wp_user->user_email,
				);
			}
		}

		return new \WP_REST_Response( $settings_for_user, 200 );
	}
}
