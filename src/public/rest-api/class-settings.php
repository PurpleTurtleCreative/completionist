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
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'handle_update_settings' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
					'args'                => array(
						'action' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
						'action_nonce' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => function ( $value, $request ) {
								return ( false !== wp_verify_nonce( $value, "ptc_completionist_{$request['action']}" ) );
							},
						),
						// Whatever other args the specified action requires. Undisclosed.
					),
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

	/**
	 * Handles a PUT request to update plugin settings data for the current user.
	 *
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_update_settings(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {
			switch ( $request['action'] ) {
				// . ////////////////////////////////////////////////// .
				case 'connect_asana':
					$asana_pat = Options::sanitize( Options::ASANA_PAT, $request['asana_pat'] );
					if ( empty( $asana_pat ) ) {
						throw new \Exception( 'Invalid Asana Personal Access Token.', 400 );
					} elseif ( Options::save( Options::ASANA_PAT, $asana_pat ) ) {

						$maybe_exception = null;

						try {
							// Test saved Asana PAT to get user's GID.
							Asana_Interface::get_client();
							$me           = Asana_Interface::get_me();
							$did_save_gid = Options::save( Options::ASANA_USER_GID, $me->gid );
						} catch ( \Exception $e ) {
							$did_save_gid    = false;
							$maybe_exception = $e; // Remember actual error that occurred.
						}

						if ( ! $did_save_gid ) {

							// Ensure no bad data is saved.
							Options::delete( Options::ASANA_PAT );
							Options::delete( Options::ASANA_USER_GID );

							/*
							This is incorrect if the user is already authenticated and
							simply trying to update their Asana PAT with a new one and
							an unrelated Asana error is encountered, like a 429...
							They could be an automation user or frontend authentication
							user and we just nuked their credentials and identity...
							*/

							// Notify of error.
							if ( ! empty( $maybe_exception ) ) {
								throw $maybe_exception;
							}
							throw new \Exception( 'An unknown error occurred, so your Asana account could not be connected.', 500 );
						}

						$res = array(
							'status'  => 'success',
							'code'    => 200,
							'message' => 'Your Asana account was successfully connected!',
							'data'    => null,
						);
					}
					break; // end connect_asana.
				// . ////////////////////////////////////////////////// .
				case 'disconnect_asana':
					if ( Options::delete( Options::ASANA_PAT ) ) {

						Options::delete( Options::ASANA_USER_GID );

						if ( get_current_user_id() === intval( Options::get( Options::FRONTEND_AUTH_USER_ID ) ) ) {
							Options::delete( Options::FRONTEND_AUTH_USER_ID );
						}

						$res = array(
							'status'  => 'success',
							'code'    => 200,
							'message' => 'Your Asana account was successfully disconnected.',
							'data'    => null,
						);
					} else {
						throw new \Exception( 'Your Asana account could not be disconnected.', 500 );
					}
					break; // end disconnect_asana.
				// . ////////////////////////////////////////////////// .
				case 'update_frontend_auth_user':
					if ( ! current_user_can( 'manage_options' ) ) {
						throw new \Exception( 'You do not have permission to manage this option.', 403 );
					} elseif ( empty( $request['user_id'] ) ) {
						throw new \Exception( 'Missing required parameter: user_id', 400 );
					} else {
						$submitted_wp_user_id = (int) Options::sanitize( Options::FRONTEND_AUTH_USER_ID, $request['user_id'] );

						// Save the frontend authentication user ID.
						Options::save(
							Options::FRONTEND_AUTH_USER_ID,
							(string) $submitted_wp_user_id,
							true
						);

						// Get the saved and validated user ID.
						$retrieved_wp_user_id = (int) Options::get( Options::FRONTEND_AUTH_USER_ID );

						// Confirm that it was saved successfully.
						if ( $retrieved_wp_user_id === $submitted_wp_user_id ) {
							$res = array(
								'status'  => 'success',
								'code'    => 200,
								'message' => 'The frontend authentication user was successfully saved!',
								'data'    => null,
							);
						} else {
							throw new \Exception( 'Failed to save the frontend authentication user.', 500 );
						}
					}
					break;
				// . ////////////////////////////////////////////////// .
				case 'update_asana_cache_ttl':
					if ( ! current_user_can( 'manage_options' ) ) {
						throw new \Exception( 'You do not have permission to manage this option.', 403 );
					} elseif ( empty( $request['asana_cache_ttl'] ) ) {
						throw new \Exception( 'Missing required parameter: asana_cache_ttl', 400 );
					} else {

						if ( ! is_numeric( $request['asana_cache_ttl'] ) ) {
							throw new \Exception( 'Invalid value for parameter: asana_cache_ttl', 400 );
						}

						// Sanitize submitted value.
						$submitted_ttl = (int) Options::sanitize( Options::CACHE_TTL_SECONDS, $request['asana_cache_ttl'] );

						// Save the value.
						Options::save(
							Options::CACHE_TTL_SECONDS,
							(string) $submitted_ttl,
							true
						);

						// Get the saved and validated value.
						$retrieved_ttl = (int) Options::get( Options::CACHE_TTL_SECONDS );

						// Confirm that it was saved successfully.
						if ( $retrieved_ttl === $submitted_ttl ) {
							$res = array(
								'status'  => 'success',
								'code'    => 200,
								'message' => 'The Asana data cache duration was successfully saved!',
								'data'    => null,
							);
						} else {
							throw new \Exception( 'Failed to save the Asana data cache duration.', 500 );
						}
					}
					break;
				// . ////////////////////////////////////////////////// .
				default:
					throw new \Exception( 'Unsupported action.', 400 );
			}
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
