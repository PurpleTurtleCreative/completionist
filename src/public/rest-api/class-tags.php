<?php
/**
 * REST API: Tags class
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

/**
 * Class to register and handle custom REST API endpoints
 * for managing Asana tags.
 *
 * @since 4.0.0
 */
class Tags {

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 4.0.0
	 */
	public static function register_routes() {

		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/tags',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( __CLASS__, 'handle_get_tags' ),
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'nonce'         => REST_Server::get_arg_def_nonce( 'ptc_completionist_get_tags' ),
						'workspace_gid' => REST_Server::get_arg_def_gid( true ),
					),
				),
			)
		);
	}//end register_routes()

	/**
	 * Handles a request to get Asana tags.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function handle_get_tags(
		\WP_REST_Request $request
	) {

		$res = array(
			'status'  => 'error',
			'code'    => 500,
			'message' => 'An unknown error occurred.',
			'data'    => null,
		);

		try {

			$asana = Asana_Interface::get_client(); // Use current user.

			try {

				$workspace_tags          = array();
				$workspace_tags_iterator = $asana->tags->findByWorkspace(
					$request['workspace_gid'],
					array( 'opt_fields' => 'gid,name' )
				);

				$html = '';

				$saved_tag_gid = Options::get( Options::ASANA_TAG_GID );
				$saved_tag     = null;

				foreach ( $workspace_tags_iterator as $i => $tag ) {

					$workspace_tags[ $i ] = $tag;

					if ( ! $saved_tag && $saved_tag_gid === $tag->gid ) {

						$saved_tag = $tag;

						$html .= sprintf(
							'<option value="%s" selected="selected">%s</option>',
							esc_attr( $tag->gid ),
							esc_html( $tag->name )
						);
					} else {
						$html .= sprintf(
							'<option value="%s">%s</option>',
							esc_attr( $tag->gid ),
							esc_html( $tag->name )
						);
					}
				}
			} catch ( \Asana\Errors\NotFoundError $e ) {
				throw new \Exception( 'Workspace not found.', 404 );
			} catch ( \Asana\Errors\InvalidRequestError $e ) {
				throw new \Exception( 'Workspace invalid.', 400 );
			}

			if ( ! $saved_tag && '' !== $saved_tag_gid ) {
				try {

					$saved_tag = $asana->tags->findById(
						$saved_tag_gid,
						array( 'opt_fields' => 'gid,name,workspace.gid' )
					);

					if (
						isset( $saved_tag->workspace->gid ) &&
						$saved_tag->workspace->gid === $request['workspace_gid'] &&
						isset( $saved_tag->gid ) &&
						isset( $saved_tag->name )
					) {

						$saved_tag_option_html = sprintf(
							'<option value="%s" selected="selected">%s</option>',
							esc_attr( $saved_tag->gid ),
							esc_html( $saved_tag->name )
						);

						$html = $saved_tag_option_html . $html;
					}

					unset( $saved_tag->workspace );
				} catch ( \Exception $e ) {
					trigger_error(
						esc_html( HTML_Builder::format_error_string( $e, 'Failed to list saved tag option.' ) ),
						\E_USER_NOTICE
					);
				}
			}

			$res = array(
				'status'  => 'success',
				'code'    => 200,
				'message' => 'Successfully retrieved tags in the workspace.',
				'data'    => array(
					'tags'         => $workspace_tags,
					'site_tag'     => $saved_tag,
					'html_options' => $html,
				),
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
}
