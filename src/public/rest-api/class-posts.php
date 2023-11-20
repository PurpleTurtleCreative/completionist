<?php
/**
 * REST API: Posts class
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
use PTC_Completionist\Util;

/**
 * Class to register and handle custom REST API endpoints
 * for managing WordPress posts.
 *
 * @since 4.0.0
 */
class Posts {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 4.0.0
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/posts/(?P<post_id>[0-9]+)',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_post' ),
					'permission_callback' => function ( \WP_REST_Request $request ) {
						return current_user_can( 'read_post', $request['post_id'] );
					},
					'args'                => array(
						'nonce'       => REST_Server::get_arg_def_nonce( 'ptc_completionist_get_post' ),
						'post_id'     => REST_Server::get_arg_def_post_id( true ),
						'post_fields' => array(
							'type'     => 'array',
							'required' => false,
							'minItems' => 1,
							'items'    => array(
								'type' => 'string',
								'enum' => array(
									'ID',
									'post_author',
									'post_date',
									'post_date_gmt',
									'post_content',
									'post_title',
									'post_excerpt',
									'post_status',
									'comment_status',
									'ping_status',
									'post_password',
									'post_name',
									'to_ping',
									'pinged',
									'post_modified',
									'post_modified_gmt',
									'post_content_filtered',
									'post_parent',
									'guid',
									'menu_order',
									'post_type',
									'post_mime_type',
									'comment_count',
									'filter',
								),
							),
						),
					),
				),
			)
		);

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/posts/where-title-like',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_posts_where_title_like' ),
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'args'                => array(
						'nonce'  => REST_Server::get_arg_def_nonce( 'ptc_completionist_get_post' ),
						'like'   => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => function ( $value ) {
								// Don't use $wpdb->esc_like() because that
								// escapes the content within the LIKE rather
								// than the entire LIKE value, such as escaping
								// % and _ as literals.
								return htmlentities( sanitize_text_field( $value ) );
							},
						),
						'limit'  => array(
							'type'     => 'integer',
							'required' => true,
							'minimum'  => 1,
							'maximum'  => 100,
						),
						'offset' => array(
							'type'     => 'integer',
							'required' => true,
							'minimum'  => 0,
						),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a request to get a WordPress post.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @internal This can't be replaced with a core WordPress
	 * REST API endpoint since custom post types need to explicitly
	 * opt in during registration to become available via the REST API.
	 * Otherwise, you could use the Search Results endpoint like:
	 * https://example.com/wp-json/wp/v2/search?include=5
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_get_post(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$post = get_post( $request['post_id'], \ARRAY_A, 'display' );
			if ( ! $post ) {
				throw new \Exception( 'Post not found.', 404 );
			}

			if ( ! empty( $request['post_fields'] ) ) {
				Util::deep_unset_except( $post, $request['post_fields'] );
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully retrieved the post.',
				'data'    => array( 'post' => $post ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to retrieve the post.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}

	/**
	 * Handles a request to get WordPress posts where the post
	 * title matches a SQL pattern.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_get_posts_where_title_like(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			global $wpdb;
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT ID, post_title
					FROM {$wpdb->posts}
					WHERE
						post_type NOT IN(
							'revision',
							'nav_menu_item',
							'wp_navigation',
							'wp_global_styles'
						)
						AND post_status <> 'auto-draft'
						AND post_title LIKE %s
					LIMIT %d OFFSET %d
					",
					$request['like'],
					$request['limit'],
					$request['offset']
				)
			);

			$post_records = array();

			if ( $rows ) {
				foreach ( $rows as &$post_data ) {
					if ( current_user_can( 'read_post', $post_data->ID ) ) {
						$post_data->post_title = html_entity_decode( $post_data->post_title, \ENT_HTML5 );
						$post_records[]        = $post_data;
					}
				}
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully retrieved matching posts.',
				'data'    => array( 'posts' => $post_records ),
			);
		} catch ( \Exception $err ) {
			$res = array(
				'status'  => 'error',
				'code'    => HTML_Builder::get_error_code( $err ),
				'message' => HTML_Builder::format_error_string( $err, 'Failed to find matching posts.' ),
				'data'    => null,
			);
		}

		return new \WP_REST_Response( $res, $res['code'] );
	}
}
