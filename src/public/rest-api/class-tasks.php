<?php
/**
 * REST API: Tasks class
 *
 * @since 4.0.0
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\Options;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\REST_Server;
use PTC_Completionist\Request_Token;
use PTC_Completionist\Util;

/**
 * Class to register and handle custom REST API endpoints
 * for managing Asana tasks.
 *
 * @since 4.0.0
 */
class Tasks {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 4.0.0
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tasks',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle_create_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce' => REST_Server::get_arg_def_nonce( 'ptc_completionist_create_task' ),
						'task'  => array(
							'type'              => 'object',
							'required'          => true,
							'sanitize_callback' => array( Asana_Interface::class, 'prepare_task_args' ),
						),
					),
				),
			)
		);

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tasks/(?P<task_gid>[0-9]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_task' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'token' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'handle_update_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'    => REST_Server::get_arg_def_nonce( 'ptc_completionist_update_task' ),
						'task_gid' => REST_Server::get_arg_def_gid( true ),
						'updates'  => array(
							'type'              => 'object',
							'required'          => true,
							'sanitize_callback' => array( Asana_Interface::class, 'prepare_task_args' ),
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_delete_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'    => REST_Server::get_arg_def_nonce( 'ptc_completionist_delete_task' ),
						'task_gid' => REST_Server::get_arg_def_gid( true ),
					),
				),
			)
		);

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tasks/(?P<task_gid>[0-9]+)/pins',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle_pin_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'    => REST_Server::get_arg_def_nonce( 'ptc_completionist_pin_task' ),
						'task_gid' => REST_Server::get_arg_def_gid( true ),
						'post_id'  => REST_Server::get_arg_def_post_id( true ),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_unpin_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'    => REST_Server::get_arg_def_nonce( 'ptc_completionist_unpin_task' ),
						'task_gid' => REST_Server::get_arg_def_gid( true ),
						'post_id'  => REST_Server::get_arg_def_post_id( false ),
					),
				),
			)
		);

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tasks/(?P<task_gid>[0-9]+)/pins/(?P<post_id>[0-9]+)',
			array(
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_unpin_task' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'    => REST_Server::get_arg_def_nonce( 'ptc_completionist_unpin_task' ),
						'task_gid' => REST_Server::get_arg_def_gid( true ),
						'post_id'  => REST_Server::get_arg_def_post_id( true ),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a request to create an Asana task.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_create_task(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {
			Asana_Interface::get_client(); // Use current user.
			$task = Asana_Interface::create_task( $request['task'] );
			$res  = array(
				'status'  => 'success',
				'code'    => 201,
				'message' => 'Successfully created a new task.',
				'data'    => array( 'task' => $task ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to create task.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a GET request to retrieve Asana task data.
	 *
	 * @since 4.3.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_task(
		\WP_REST_Request $request
	) {

		$request_token = new Request_Token( $request['token'] );

		// Abort if token is invalid.
		if ( ! $request_token->exists() ) {
			return new \WP_Error(
				'bad_token',
				'Failed to get Asana task. Invalid request.',
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
					'Failed to get Asana task. Authentication user was not specified.',
					array( 'status' => 401 )
				);
			}

			// Perform request.

			Asana_Interface::get_client( (int) $args['auth_user'] );
			$task = Asana_Interface::maybe_get_task_data(
				$request['task_gid'],
				$args['opt_fields']
			);

			if ( empty( $task ) ) {
				// An empty response is unexpected. Do not cache this.
				return new \WP_Error(
					'empty_content',
					'Failed to get Asana task. There is no task data.',
					array( 'status' => 409 )
				);
			}

			// Localize task.
			Asana_Interface::localize_task( $task, false );

			// Load subtasks if desired.
			$do_remove_subtasks_sort_field = false;
			if ( $args['show_subtasks'] ) {

				$subtask_fields = $args['opt_fields'];

				if (
					$args['sort_subtasks_by'] &&
					false === in_array(
						$args['sort_subtasks_by'],
						explode( ',', $subtask_fields ),
						true
					)
				) {
					// Ensure sorting field is returned for sorting purposes.
					// Always add "name" subfield in case its an object like "assignee".
					$subtask_fields               .= ",{$args['sort_subtasks_by']},{$args['sort_subtasks_by']}.name";
					$do_remove_subtasks_sort_field = true;
				}

				if ( ! $args['show_completed'] ) {
					// Loading subtasks doesn't support requesting
					// incomplete tasks only, so must request the
					// 'completed' field for filtering later.
					$subtask_fields .= ',completed';
				}

				$tasks_arr = array( $task );
				Asana_Interface::load_subtasks( $tasks_arr, $subtask_fields );
				$task = $tasks_arr[0];

				// Process subtasks.
				if ( isset( $task->subtasks ) ) {

					foreach ( $task->subtasks as $subtasks_i => &$subtask ) {

						if ( isset( $subtask->completed ) ) {
							if ( ! $args['show_completed'] ) {
								if ( $subtask->completed ) {
									// Don't show completed tasks.
									unset( $task->subtasks[ $subtasks_i ] );
									continue;
								} else {
									// Don't show completed status
									// for incomplete tasks.
									unset( $subtask->completed );
								}
							}
						}

						// Now recursively localize tasks since
						// no further subtasks will be removed.
						//
						// Though note that recursion isn't actually
						// needed here since only one level of subtasks
						// was loaded, anyways.
						Asana_Interface::localize_task( $subtask, true );
					}//end foreach.

					// Fix index gaps from possible removals.
					if ( ! $args['show_completed'] ) {
						$task->subtasks = array_values( $task->subtasks );
					}

					// Asana doesn't currently sort subtasks when the
					// view's sort is changed, but we will.
					if ( $args['sort_subtasks_by'] ) {
						Asana_Interface::sort_tasks_by( $task->subtasks, $args['sort_subtasks_by'] );
					}
				}
			}//endif subtasks.

			if (
				$args['sort_subtasks_by'] &&
				true === $do_remove_subtasks_sort_field
			) {
				// Remove extra field only used for sorting, not for display.
				Util::deep_unset_prop( $task, $args['sort_subtasks_by'] );
			}

			// Remove all GIDs if desired.
			if ( ! $args['show_gids'] ) {
				Util::deep_unset_prop( $task, 'gid' );
			}

			// Cache response and return.
			$request_token->update_cache_data( $task );
			return new \WP_REST_Response( $task, 200 );
		} catch ( \Exception $e ) {
			$error_code = HTML_Builder::get_error_code( $e );
			if ( $error_code < 400 ) {
				// Prevent code 0 for odd errors like "could not resolve host name".
				$error_code = 400;
			}
			return new \WP_Error(
				'asana_error',
				'Failed to get Asana task. ' . HTML_Builder::get_error_message( $e ),
				array( 'status' => $error_code )
			);
		}

		// This shouldn't be reachable.
		return new \WP_Error(
			'unknown_error',
			'Failed to get Asana task. Unknown error.',
			array( 'status' => 500 )
		);
	}

	/**
	 * Handles a request to update an Asana task.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_update_task(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {
			Asana_Interface::get_client(); // Use current user.
			$task = Asana_Interface::update_task( $request['task_gid'], $request['updates'] );
			$res  = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully updated the task.',
				'data'    => array( 'task' => $task ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to update task.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to delete an Asana task.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_delete_task(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => array( 'task_gid' => $request['task_gid'] ),
		);

		try {
			Asana_Interface::get_client(); // Use current user.
			Asana_Interface::delete_task( $request['task_gid'] );
			Options::delete( Options::PINNED_TASK_GID, -1, $request['task_gid'] );
			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully deleted the task.',
				'data'    => array( 'task_gid' => $request['task_gid'] ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to delete task.' ),
				'data'    => array( 'task_gid' => $request['task_gid'] ),
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to pin an Asana task to a WordPress post.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled within try-catch block.
	 */
	public static function handle_pin_task(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			Asana_Interface::get_client(); // Use current user.

			// Ensure task is valid and can be retrieved.
			$task = Asana_Interface::maybe_get_task_data( $request['task_gid'] );
			if ( ! $task ) {
				throw new \Exception( 'Task data could not be retrieved from Asana.', 500 );
			}

			if (
				! Asana_Interface::pin_task(
					$request['task_gid'],
					$request['post_id']
				)
			) {
				throw new \Exception( 'Something went wrong.', 500 );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully pinned the task.',
				'data'    => array( 'task' => $task ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to pin task.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to unpin an Asana task.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled within try-catch block.
	 */
	public static function handle_unpin_task(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			Asana_Interface::get_client(); // Use current user.

			if (
				! Asana_Interface::unpin_task(
					$request['task_gid'],
					$request['post_id'] ?? -1
				)
			) {
				throw new \Exception( 'Something went wrong.', 500 );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully unpinned the task.',
				'data'    => array( 'task_gid' => $request['task_gid'] ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to unpin task.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
