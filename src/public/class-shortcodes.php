<?php
/**
 * Shortcodes class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/public/class-request-tokens.php';

if ( ! class_exists( __NAMESPACE__ . '\Shortcodes' ) ) {
	/**
	 * Class to register shortcodes and associated assets.
	 *
	 * @since [unreleased]
	 */
	class Shortcodes {

		/**
		 * Registers code.
		 *
		 * @since [unreleased]
		 */
		public static function register() {
			add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		}

		/**
		 * Adds shortcode definitions.
		 *
		 * @since [unreleased]
		 */
		public static function add_shortcodes() {
			add_shortcode(
				'ptc_asana_project',
				array( __CLASS__, 'get_ptc_asana_project' )
			);
		}

		/**
		 * Registers frontend assets for enqueue.
		 *
		 * @since [unreleased]
		 */
		public static function register_assets() {

			// Asana project assets.

			$asset_file = require_once( PLUGIN_PATH . 'build/index_ShortcodeAsanaProject.jsx.asset.php' );
			wp_register_script(
				'ptc-completionist-shortcode-asana-project',
				PLUGIN_URL . '/build/index_ShortcodeAsanaProject.jsx.js',
				$asset_file['dependencies'],
				PLUGIN_VERSION,
				true
			);
		}

		/**
		 * Gets the Asana project template and enqueues the related frontend
		 * assets.
		 *
		 * @since [unreleased]
		 *
		 * @see \add_shortcode()
		 *
		 * @param array       $atts          Optional. The shortcode attribute
		 *                                   values. Default empty array.
		 * @param string|null $content       Optional. The shortcode contents.
		 *                                   Default null.
		 * @param string      $shortcode_tag Optional. The shortcode tag for
		 *                                   processing default attributes.
		 *                                   Default empty string.
		 *
		 * @return string The resulting HTML.
		 */
		public static function get_ptc_asana_project(
			$atts = array(),
			$content = null,
			$shortcode_tag = ''
		) {

			// Collect shortcode attributes.

			$atts = shortcode_atts(
				array(
					'src'                    => '', // Required.
					'auth_user'              => '',
					'show_name'              => 'true',
					'show_description'       => 'true',
					'show_status'            => 'true',
					'show_modified'          => 'true',
					'show_due'               => 'true',
					'show_tasks_description' => 'true',
					'show_tasks_assignee'    => 'true',
					'show_tasks_subtasks'    => 'true',
					'show_tasks_completed'   => 'true',
					'show_tasks_due'         => 'true',
					// 'include_tag'          => '',
					// 'exclude_tag'          => '',
				),
				$atts,
				$shortcode_tag
			);

			// Sanitize shortcode attributes.

			$atts['src'] = (string) esc_url_raw( $atts['src'] );
			$atts['auth_user'] = (int) $atts['auth_user'];

			// Prepare shortcode.

			$parsed_asana_project = Asana_Interface::parse_project_link( $atts['src'] );
			if ( empty( $parsed_asana_project['gid'] ) ) {
				return '
					<div class="ptc-shortcode ptc-asana-project ptc-error">
						<p>Failed to load Asana project. Could not determine project GID from source URL.</p>
					</div>
				';
			}

			$atts['project_gid'] = $parsed_asana_project['gid'];
			$atts['show_gids'] = 'false'; // Always remove Asana object GIDs.

			// Generate request token.
			$post_id = get_the_ID();
			$request_tokens = new Request_Tokens( $post_id );
			$token = $request_tokens->save( $atts );

			// Render frontend data.

			wp_enqueue_script( 'ptc-completionist-shortcode-asana-project' );

			$request_url = esc_url_raw(
				rest_url( REST_API_NAMESPACE_V1 . "/projects?token={$token}&post_id={$post_id}" )
			);

			return sprintf(
				'<div class="ptc-shortcode ptc-asana-project" data-src="%1$s" data-layout="%2$s"></div>',
				esc_attr( $request_url ),
				esc_attr( $parsed_asana_project['layout'] ?? 'list' )
			);
		}
	}
}