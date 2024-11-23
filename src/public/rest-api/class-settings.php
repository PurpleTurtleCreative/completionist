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
use PTC_Completionist\Request_Token;

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
						'action'       => array(
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

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$settings_for_user = Options::get_settings_for_user( get_current_user_id() );

			// Clean up user output to only what's needed.
			$compact_user_data = function ( array &$wp_users ) {
				$avatar_args = array(
					'size'    => '40',
					'default' => 'mystery',
				);
				foreach ( $wp_users as $key => $wp_user ) {
					if ( is_a( $wp_user, '\WP_User' ) ) {
						$wp_users[ $key ] = array(
							'ID'           => $wp_user->ID,
							'display_name' => $wp_user->display_name,
							'user_email'   => $wp_user->user_email,
							'roles'        => $wp_user->roles,
							'avatar_url'   => get_avatar_url( $wp_user, $avatar_args ),
						);
					}
				}
			};

			if (
				! empty( $settings_for_user['workspace']['found_workspace_users'] ) &&
				is_array( $settings_for_user['workspace']['found_workspace_users'] )
			) {
				$compact_user_data( $settings_for_user['workspace']['found_workspace_users'] );
			}

			if (
				! empty( $settings_for_user['workspace']['connected_workspace_users'] ) &&
				is_array( $settings_for_user['workspace']['connected_workspace_users'] )
			) {
				$compact_user_data( $settings_for_user['workspace']['connected_workspace_users'] );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Settings loaded successfully!',
				'data'    => $settings_for_user,
			);
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
					if ( empty( $request['asana_pat'] ) ) {
						throw new \Exception( 'Missing required parameter: asana_pat', 400 );
					}

					$asana_pat = Options::sanitize( Options::ASANA_PAT, $request['asana_pat'] );
					if ( empty( $asana_pat ) ) {
						throw new \Exception( 'Invalid Asana Personal Access Token.', 400 );
					}

					if ( Options::get( Options::ASANA_PAT ) !== $asana_pat ) {
						// Only save if different to prevent "save failure" error.
						if ( ! Options::save( Options::ASANA_PAT, $asana_pat ) ) {
							throw new \Exception( 'Failed to update your Asana Personal Access Token.', 500 );
						}
					}

					$maybe_exception = null;
					try {
						// Test saved Asana PAT to get user's GID.
						Asana_Interface::get_client();
						$me = Asana_Interface::get_me();
						if ( Options::get( Options::ASANA_USER_GID ) !== $me->gid ) {
							// Only save if different to prevent "save failure" error.
							$did_save_gid = Options::save( Options::ASANA_USER_GID, $me->gid );
						} else {
							$did_save_gid = true;
						}
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
						'message' => 'Your Asana account is successfully connected!',
						'data'    => null,
					);
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
						throw new \Exception( 'You do not have permission to change the frontend Asana user.', 403 );
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
						throw new \Exception( 'You do not have permission to change the Asana cache duration.', 403 );
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
				case 'clear_asana_cache':
						$rows_affected = Request_Token::clear_cache_data();
						$res           = array(
							'status'  => 'success',
							'code'    => 200,
							'message' => sprintf(
								'Cleared %s cache record(s). The latest data will be fetched from Asana the next time it\'s needed.',
								intval( $rows_affected )
							),
							'data'    => null,
						);
					break;
				// . ////////////////////////////////////////////////// .
				case 'update_asana_workspace_tag':
					if ( ! current_user_can( 'manage_options' ) ) {
						throw new \Exception( 'You do not have permission to change the Asana workspace or site tag.', 403 );
					} elseif ( empty( $request['workspace_gid'] ) ) {
						throw new \Exception( 'Missing required parameter(s): workspace_gid', 400 );
					} else {

						// Save workspace.

						$workspace_gid = Options::sanitize( Options::ASANA_WORKSPACE_GID, $request['workspace_gid'] );
						if ( '' === $workspace_gid ) {
							throw new \Exception( 'Invalid workspace identifier.', 400 );
						}

						if ( Options::get( Options::ASANA_WORKSPACE_GID ) !== $workspace_gid ) {
							// Only save if different to prevent "save failure" error.
							if ( Options::save( Options::ASANA_WORKSPACE_GID, $workspace_gid ) ) {
								// Delete all pinned tasks since the workspace has changed.
								Options::delete( Options::PINNED_TASK_GID, -1 );
								$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
							} else {
								throw new \Exception( 'Failed to update workspace.', 500 );
							}
						}

						// Save site tag.

						$current_tag_gid = Options::get( Options::ASANA_TAG_GID );
						if ( ! empty( $request['tag_name'] ) ) {
							// Create new tag.

							$tag_name = sanitize_text_field( $request['tag_name'] );
							if ( empty( $tag_name ) ) {
								throw new \Exception( 'Invalid name for new tag.', 400 );
							}

							try {
								$asana   = Asana_Interface::get_client( get_current_user_id() );
								$new_tag = $asana->tags->createTag(
									array(
										'name'      => $tag_name,
										'workspace' => $workspace_gid,
									)
								);
								$tag_gid = $new_tag->gid;
							} catch ( \Asana\Errors\NotFoundError $e ) {
								Options::delete( Options::ASANA_WORKSPACE_GID );
								throw new \Exception( 'The specified workspace does not exist.', 404 );
							}
						} elseif ( ! empty( $request['tag_gid'] ) ) {
							// Validate existing tag.

							$tag_gid = Options::sanitize( Options::ASANA_TAG_GID, $request['tag_gid'] );
							if ( $current_tag_gid !== $tag_gid ) {
								try {
									$asana   = Asana_Interface::get_client( get_current_user_id() );
									$the_tag = $asana->tags->getTag( $tag_gid, array( 'opt_fields' => 'gid,workspace,workspace.gid' ) );
									$tag_gid = $the_tag->gid;
									if (
										isset( $the_tag->workspace->gid )
										&& $workspace_gid !== $the_tag->workspace->gid
									) {
										throw new \Exception( 'Tag does not belong to the saved workspace.', 409 );
									}
								} catch ( \Asana\Errors\NotFoundError $e ) {
									throw new \Exception( 'Tag does not exist.', 404 );
								}
							}
						} else {
							// Tag could not be determined.
							throw new \Exception( 'Missing required parameter: tag_gid or tag_name', 400 );
						}

						// Save the determined tag.

						if ( empty( $tag_gid ) ) {
							throw new \Exception( 'Invalid tag identifier.', 400 );
						}

						if ( $current_tag_gid !== $tag_gid ) {
							// Only save if different to prevent "save failure" error.
							if ( ! Options::save( Options::ASANA_TAG_GID, $tag_gid ) ) {
								throw new \Exception( 'Failed to save the Asana workspace and site tag.', 500 );
							}
						}

						$res = array(
							'status'  => 'success',
							'code'    => 200,
							'message' => 'The Asana workspace and site tag were successfully saved!',
							'data'    => null,
						);
					}//end if asana_workspace_save
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
