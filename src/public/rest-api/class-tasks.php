<?php
/**
 * REST API: Tasks class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\Options;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\Request_Token;
use PTC_Completionist\REST_Server;
use PTC_Completionist\Util;

/**
 * Class to register and handle custom REST API endpoints
 * for managing Asana tasks.
 *
 * @since [unreleased]
 */
class Tasks {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tasks',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle_create_task' ),
					'permission_callback' => '__return_false',
					'args'                => array(),
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
					'permission_callback' => '__return_false',
					'args'                => array(
						'task_gid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => function ( $value ) {
								return Options::sanitize( 'gid', $value );
							},
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
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'handle_update_task' ),
					'permission_callback' => '__return_false',
					'args'                => array(
						'task_gid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => function ( $value ) {
								return Options::sanitize( 'gid', $value );
							},
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
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_delete_task' ),
					'permission_callback' => function () {
						return Asana_Interface::has_connected_asana();
					},
					'args'                => array(
						'nonce'    => REST_Server::get_route_arg_nonce( 'ptc_completionist_delete_task' ),
						'task_gid' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => function ( $value ) {
								return Options::sanitize( 'gid', $value );
							},
						),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a DELETE request to delete an Asana task.
	 *
	 * @since [unreleased]
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
			'data'    => $request['task_gid'],
		);

		try {
			Asana_Interface::delete_task( $request['task_gid'] );
			Options::delete( Options::PINNED_TASK_GID, -1, $request['task_gid'] );
			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => "Successfully deleted task {$request['task_gid']}.",
				'data'    => $request['task_gid'],
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to delete task.' ),
				'data'    => $request['task_gid'],
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
