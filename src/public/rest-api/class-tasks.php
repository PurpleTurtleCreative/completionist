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
	}
}
