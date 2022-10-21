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
require_once PLUGIN_PATH . 'src/includes/class-options.php';

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
			// add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
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

			wp_register_script(
				'ptc-asana-project-script',
				plugins_url( '/assets/scripts/shortcode-asana-project.js', PLUGIN_FILE ),
				array(),
				PLUGIN_VERSION,
				true
			);

			wp_register_style(
				'ptc-asana-project-style',
				plugins_url( '/assets/styles/shortcode-asana-project.css', PLUGIN_FILE ),
				array(),
				PLUGIN_VERSION,
				'all'
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
					'src' => '',
					'auth_user' => Options::get( Options::FRONTEND_AUTH_USER_ID ),
					// 'layout' => 'list',
					// 'show_title' => 'true',
					// 'show_description' => 'true',
					// 'show_status' => 'true',
					// 'show_modified' => 'true',
					// 'show_due' => 'true',
					// 'show_tasks_assignee' => 'true',
					// 'show_tasks_subtasks' => 'true',
					// 'show_tasks_completed' => 'true',
					// 'show_tasks_due' => 'true',
					// 'include_tag' => '',
					// 'exclude_tag' => '',
				),
				$atts,
				$shortcode_tag
			);

			// Sanitize shortcode attributes.

			$atts['auth_user'] = (int) $atts['auth_user'];
			$atts['src'] = (string) esc_url_raw( $atts['src'] );

			try {

				// Load authentication user.
				$asana = Asana_Interface::get_client( $atts['auth_user'] );

				// Get Asana project data.
				$parsed_asana_project = Asana_Interface::parse_project_link( $atts['src'] );
				if ( ! empty( $parsed_asana_project['gid'] ) ) {
					$asana_project = Asana_Interface::get_project_data( $parsed_asana_project['gid'] );

					// Load HTML template and assets.

					// wp_enqueue_script( 'ptc-asana-project-script' );
					// wp_enqueue_style( 'ptc-asana-project-style' );

					return '<pre style="max-width:100%;white-space:pre-wrap;">' . print_r( $asana_project, true ) . '</pre>';
				}
			} catch ( \Exception $e ) {
				require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
				return HTML_Builder::format_error_box( $e, 'Failed to embed Asana project. ', false );
			}
		}
	}
}
