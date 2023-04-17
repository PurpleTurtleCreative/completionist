<?php
/**
 * Shortcodes class
 *
 * @since 3.4.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/public/class-request-token.php';

if ( ! class_exists( __NAMESPACE__ . '\Shortcodes' ) ) {
	/**
	 * Class to register shortcodes and associated assets.
	 *
	 * @since 3.4.0
	 */
	class Shortcodes {

		/**
		 * A map of shortcode tag keys to metadata map values.
		 *
		 * This map is used to register all shortcode tags and track
		 * various metadata about them during execution. It helps
		 * with performance optimization and debugging.
		 *
		 * @since [unreleased]
		 *
		 * @var array $shortcodes_meta {
		 *
		 *   @type int $render_count The count of shortcode renders.
		 *
		 *   @type array[] $request_tokens A map of request token
		 *                 keys to their request argument arrays.
		 *
		 *   @type string[] $script_handles The script handles
		 *                  that should be enqueued for this tag.
		 *
		 *   @type string[] $style_handles The stylesheet handles
		 *                  that should be enqueued for this tag.
		 *
		 * }
		 */
		private static $shortcodes_meta = array(
			'ptc_asana_project' => array(
				'render_count'   => 0,
				'request_tokens' => array(),
				'script_handles' => array(
					'ptc-completionist-shortcode-asana-project',
				),
				'style_handles'  => array(
					'ptc-completionist-shortcode-asana-project',
				),
			),
		);

		// *************************** //
		// **   Code Registration   ** //
		// *************************** //

		/**
		 * Hooks functionality into the WordPress execution flow.
		 *
		 * @since 3.4.0
		 */
		public static function register() {
			add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
			add_action( 'wp_footer', __CLASS__ . '::process_collected_shortcodes' );
		}

		/**
		 * Finalizes shortcode renders by processing the collected
		 * metadata for each shortcode tag.
		 *
		 * @since [unreleased]
		 */
		public static function process_collected_shortcodes() {
			foreach ( static::$shortcodes_meta as $shortcode_tag => &$metadata ) {

				if ( $metadata['render_count'] > 0 ) {
					// Enqueue assets for rendered shortcodes.
					foreach ( $metadata['script_handles'] as &$script_handle ) {
						wp_enqueue_script( $script_handle );
					}
					foreach ( $metadata['style_handles'] as &$style_handle ) {
						wp_enqueue_style( $style_handle );
					}
				}

				if ( count( $metadata['request_tokens'] ) > 0 ) {
					// Process request tokens.
					foreach ( $metadata['request_tokens'] as $shortcode_token => &$request_args ) {
						$actual_token = Request_Token::save( $request_args );
						if ( $shortcode_token !== $actual_token ) {
							trigger_error(
								"Shortcode [{$shortcode_tag}] is using a request token ({$shortcode_token}) that doesn't match the token generated from its request arguments ({$actual_token}). The shortcode will likely fail to properly function.",
								E_USER_WARNING
							);
						}
					}
				}
			}
		}

		/**
		 * Adds shortcode definitions.
		 *
		 * @since 3.4.0
		 */
		public static function add_shortcodes() {
			foreach ( static::$shortcodes_meta as $shortcode_tag => &$metadata ) {
				add_shortcode(
					$shortcode_tag,
					__CLASS__ . "::get_{$shortcode_tag}"
				);
			}
		}

		/**
		 * Registers frontend assets for enqueue.
		 *
		 * @since 3.4.0
		 */
		public static function register_assets() {

			// Asana project assets.

			$asset_file = require_once( PLUGIN_PATH . 'build/index_ShortcodeAsanaProject.jsx.asset.php' );
			$dependencies = apply_filters(
				'ptc_completionist_shortcode_asana_project_script_deps',
				$asset_file['dependencies']
			);
			wp_register_script(
				'ptc-completionist-shortcode-asana-project',
				PLUGIN_URL . '/build/index_ShortcodeAsanaProject.jsx.js',
				$dependencies,
				$asset_file['version'],
				true
			);

			wp_register_style(
				'ptc-completionist-shortcode-asana-project',
				PLUGIN_URL . '/build/index_ShortcodeAsanaProject.jsx.css',
				array(),
				$asset_file['version']
			);
		}

		// **************************** //
		// **   Shortcode Tracking   ** //
		// **************************** //

		/**
		 * Increments the render count for the given shortcode tag.
		 *
		 * @since [unreleased]
		 *
		 * @param string $shortcode_tag The shortcode tag.
		 */
		public static function count_render( string $shortcode_tag ) {
			++static::$shortcodes_meta[ $shortcode_tag ]['render_count'];
		}

		/**
		 * Adds request token information for the given shortcode tag.
		 *
		 * @since [unreleased]
		 *
		 * @param string $shortcode_tag The shortcode tag.
		 * @param array  $request_args The request arguments to
		 * generate the request token.
		 *
		 * @return string The generated request token.
		 */
		public static function push_request_token(
			string $shortcode_tag,
			array &$request_args
		) : string {
			$token = Request_Token::generate_token( $request_args );
			if ( empty( static::$shortcodes_meta[ $shortcode_tag ]['request_tokens'][ $token ] ) ) {
				static::$shortcodes_meta[ $shortcode_tag ]['request_tokens'][ $token ] = $request_args;
			}
			return $token;
		}

		// *************************** //
		// **   Shortcode Renders   ** //
		// *************************** //

		/**
		 * Gets the [ptc_asana_project] shortcode content.
		 *
		 * @since 3.4.0
		 *
		 * @see \add_shortcode()
		 *
		 * @param array       $atts Optional. The shortcode attribute values.
		 * Default empty array.
		 * @param string|null $content Optional. The shortcode contents.
		 * Default null.
		 * @param string      $shortcode_tag Optional. The shortcode tag for
		 * processing default attributes. Default empty string.
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
					'exclude_sections'       => '',
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
					'show_tasks_attachments' => 'true',
					'show_tasks_tags'        => 'true',
				),
				$atts,
				$shortcode_tag
			);

			// Sanitize shortcode attributes.

			$atts['src']              = (string) esc_url_raw( $atts['src'] );
			$atts['auth_user']        = (int) $atts['auth_user'];
			$atts['exclude_sections'] = html_entity_decode( $atts['exclude_sections'], ENT_QUOTES | ENT_HTML5 );

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

			// Always remove Asana object GIDs.
			$atts['show_gids'] = 'false';

			// Generate request token for the frontend.
			$atts['_cache_key'] = 'shortcode_ptc_asana_project';
			$token = static::push_request_token( $shortcode_tag, $atts );

			// Render frontend data.

			$request_url = add_query_arg(
				array( 'token' => $token ),
				rest_url( REST_API_NAMESPACE_V1 . '/projects' )
			);

			static::count_render( $shortcode_tag );
			return sprintf(
				'<div class="ptc-shortcode ptc-asana-project" data-src="%1$s" data-layout="%2$s"></div>',
				esc_url( $request_url ),
				esc_attr( $parsed_asana_project['layout'] ?? 'list' )
			);
		}
	}
}
