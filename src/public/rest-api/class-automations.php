<?php
/**
 * REST API: Automations class
 *
 * @since 4.0.0
 */

namespace PTC_Completionist\REST_API;

defined( 'ABSPATH' ) || die();

use const PTC_Completionist\REST_API_NAMESPACE_V1;

use PTC_Completionist\Asana_Interface;
use PTC_Completionist\Automation;
use PTC_Completionist\Automations\Data;
use PTC_Completionist\Options;
use PTC_Completionist\HTML_Builder;
use PTC_Completionist\REST_Server;

/**
 * Class to register and handle custom REST API endpoints
 * for managing Automations.
 *
 * @since 4.0.0
 */
class Automations {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 4.0.0
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/automations',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_automations' ),
					'permission_callback' => 'is_user_logged_in',
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
				array(
					'methods'             => 'POST',
					'callback'            => array( __CLASS__, 'handle_create_automation' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'      => REST_Server::get_arg_def_nonce( 'ptc_completionist_create_automation' ),
						'automation' => array(
							'type'     => 'object',
							'required' => true,
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
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_automation' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_get_automation' ),
						'automation_id' => REST_Server::get_arg_def_id( true ),
					),
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( __CLASS__, 'handle_update_automation' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_update_automation' ),
						'automation_id' => REST_Server::get_arg_def_id( true ),
						'automation'    => array(
							'type'     => 'object',
							'required' => true,
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( __CLASS__, 'handle_delete_automation' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_delete_automation' ),
						'automation_id' => REST_Server::get_arg_def_id( true ),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a request to get Automations.
	 *
	 * @since 4.0.0
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

	/**
	 * Handles a request to save a new Automation.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_create_automation(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$automation = $request['automation'];
			if ( is_array( $automation ) ) {
				// Must be converted to stdClass objects.
				$automation = json_decode( wp_json_encode( $automation ), false );
			}

			if (
				! is_a( $automation, '\stdClass' ) ||
				! isset( $automation->ID )
			) {
				throw new \Exception( 'Invalid automation data.', 400 );
			}

			if ( 0 !== $automation->ID ) {
				throw new \Exception( 'Creating a new automation from an existing one is not supported.', 400 );
			}

			$automation = Data::save_automation( $automation );
			if ( ! isset( $automation->ID ) || $automation->ID <= 0 ) {
				throw new \Exception( 'There was an issue writing to the database.', 500 );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 201,
				'message' => 'Successfully saved the new automation.',
				'data'    => array( 'automation' => $automation ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to save the new automation.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to update an existing Automation.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_update_automation(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$automation = $request['automation'];
			if ( is_array( $automation ) ) {
				// Must be converted to stdClass objects.
				$automation = json_decode( wp_json_encode( $automation ), false );
			}

			if (
				! is_a( $automation, '\stdClass' ) ||
				! isset( $automation->ID )
			) {
				throw new \Exception( 'Invalid automation data.', 400 );
			}

			if ( $request['automation_id'] !== $automation->ID ) {
				throw new \Exception( 'Automation record does not match route.', 400 );
			}

			if ( 0 === $automation->ID ) {
				throw new \Exception( 'Invalid automation ID.', 400 );
			}

			$automation = Data::save_automation( $automation );
			if (
				! isset( $automation->ID ) ||
				$request['automation_id'] !== $automation->ID
			) {
				throw new \Exception( 'There was an issue writing to the database.', 500 );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully updated the automation.',
				'data'    => array( 'automation' => $automation ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to update the automation.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to get an Automation.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_automation(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$automation = ( new Automation( $request['automation_id'] ) )->to_std_class();

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully retrieved the automation.',
				'data'    => array( 'automation' => $automation ),
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

	/**
	 * Handles a request to delete an Automation.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_delete_automation(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			if ( ! Data::delete_automation( $request['automation_id'] ) ) {
				throw new \Exception( 'The database record could not be deleted.', 500 );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully deleted the automation.',
				'data'    => array( 'automation_id' => $request['automation_id'] ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to delete automation.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
