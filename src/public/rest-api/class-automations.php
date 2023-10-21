<?php
/**
 * REST API: Automations class
 *
 * @since [unreleased]
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\Automations\Data;
use PTC_Completionist\Options;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\REST_Server;

/**
 * Class to register and handle custom REST API endpoints
 * for managing Automations.
 *
 * @since [unreleased]
 */
class Automations {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/automations',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_automations' ),
					'permission_callback' => function () {
						return Asana_Interface::has_connected_asana();
					},
					'args'                => array(
						'nonce'       => REST_Server::get_arg_def_nonce( 'ptc_completionist_get_automation' ),
						'return_html' => array(
							'type'     => 'boolean',
							'required' => false,
						),
						'order_by'    => array(
							'type'              => 'string',
							'required'          => false,
							'sanitize_callback' => 'sanitize_text_field',
							'enum'              => array(
								'ID',
								'title',
								'description',
								'hook_name',
								'last_modified',
								'total_conditions',
								'total_actions',
								'last_triggered',
								'total_triggered',
							),
						),
					),
				),
			)
		);

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/automations/(?P<automation_id>[0-9]+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'handle_update_automation' ),
					'permission_callback' => function () {
						return Asana_Interface::has_connected_asana();
					},
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_automations' ),
						'automation_id' => REST_Server::get_arg_def_id( true ),
						'updates'       => array(
							'type'              => 'object',
							'required'          => true,
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_delete_automation' ),
					'permission_callback' => function () {
						return Asana_Interface::has_connected_asana();
					},
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_automations' ),
						'automation_id' => REST_Server::get_arg_def_id( true ),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a request to get Automations.
	 *
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_automations(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$order_by    = ( ! empty( $request['order_by'] ) ) ? $request['order_by'] : null;
			$return_html = $request['return_html'] ?? true;

			$automation_overviews = Data::get_automation_overviews( $order_by, $return_html );

			$records_count = count( $automation_overviews );

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => "Successfully retrieved {$records_count} automation overviews.",
				'data'    => array( 'automation_overviews' => $automation_overviews ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to get automations.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
