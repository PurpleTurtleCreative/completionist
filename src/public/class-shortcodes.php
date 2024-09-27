<?php
/**
 * Shortcodes class
 *
 * @since 3.4.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

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
	 * @since 3.7.0
	 *
	 * @var array $shortcodes_meta {
	 *
	 *   @type int $render_count The count of shortcode renders.
	 *
	 *   @type string $render_callback The callback function to
	 *                get the shortcode's rendered content.
	 *
	 *   @type string[] $default_atts The shortcode's default
	 *                  attributes.
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
			'render_count'    => 0,
			'render_callback' => __CLASS__ . '::get_ptc_asana_project',
			'default_atts'    => array(
				'src'                    => '',     // Required.
				'layout'                 => '',
				'auth_user'              => '',
				'include_sections'       => '',
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
				'sort_tasks_by'          => '',
			),
			'script_handles'  => array(
				'ptc-completionist-shortcode-asana-project',
			),
			'style_handles'   => array(
				'ptc-completionist-shortcode-asana-project',
			),
		),
		'ptc_asana_task'    => array(
			'render_count'    => 0,
			'render_callback' => __CLASS__ . '::get_ptc_asana_task',
			'default_atts'    => array(
				'src'              => '',     // Required.
				'auth_user'        => '',
				'show_description' => 'true',
				'show_assignee'    => 'true',
				'show_subtasks'    => 'true',
				'show_completed'   => 'true',
				'show_due'         => 'true',
				'show_attachments' => 'true',
				'show_tags'        => 'true',
				'sort_subtasks_by' => '',
			),
			'script_handles'  => array(
				'ptc-completionist-shortcode-asana-task',
			),
			'style_handles'   => array(
				'ptc-completionist-shortcode-asana-task',
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
	 * @since 3.7.0
	 */
	public static function process_collected_shortcodes() {

		foreach ( static::$shortcodes_meta as $shortcode_tag => &$metadata ) {
			if ( $metadata['render_count'] > 0 ) {

				// Enqueue included assets for rendered shortcodes.
				foreach ( $metadata['script_handles'] as &$script_handle ) {
					wp_enqueue_script( $script_handle );
				}
				foreach ( $metadata['style_handles'] as &$style_handle ) {
					wp_enqueue_style( $style_handle );
				}

				/**
				 * Enqueues custom scripts and styles for each shortcode tag.
				 *
				 * Note that this action only runs for shortcodes that
				 * have been executed. This ensures assets are enqueued
				 * only once per page load and only when they are needed.
				 *
				 * @since 3.7.0
				 *
				 * @param string $shortcode_tag The shortcode tag.
				 */
				do_action(
					'ptc_completionist_shortcode_enqueue_assets',
					$shortcode_tag
				);
			}
		}

		// End request token buffering.
		//
		// Again... it's not desirable that the buffer was started
		// within a different function.
		//
		// See Shortcodes::add_shortcodes().
		Request_Token::buffer_end_flush();
	}

	/**
	 * Adds shortcode definitions.
	 *
	 * @since 3.4.0
	 */
	public static function add_shortcodes() {

		// Start request token buffering.
		//
		// Preferably, the buffer would start and end within the
		// same function call to prevent overreaching context
		// with a tangled execution path.
		Request_Token::buffer_start();

		/**
		 * Filters the shortcode metadata for registering and
		 * tracking shortcodes managed by the Shortcodes class.
		 *
		 * @since 3.8.0
		 *
		 * @see Shortcodes::$shortcodes_meta
		 *
		 * @param array $shortcodes_meta The shortcode definitions.
		 */
		static::$shortcodes_meta = apply_filters(
			'ptc_completionist_shortcodes_meta_init',
			static::$shortcodes_meta
		);

		// Register all shortcodes.
		foreach ( static::$shortcodes_meta as $shortcode_tag => &$metadata ) {
			add_shortcode( $shortcode_tag, $metadata['render_callback'] );
		}
	}

	/**
	 * Registers frontend assets for enqueue.
	 *
	 * @since 3.4.0
	 */
	public static function register_assets() {

		// Asana project assets.

		$asset_file = require_once PLUGIN_PATH . 'build/index_ShortcodeAsanaProject.jsx.asset.php';

		$dependencies = apply_filters_deprecated(
			'ptc_completionist_shortcode_asana_project_script_deps',
			array( $asset_file['dependencies'] ),
			'3.7.0',
			'ptc_completionist_shortcode_enqueue_assets',
			'Filtering the dependency array of each asset for each shortcode is not a scalable approach. Please instead use the new, generic action hook for enqueueing custom assets.'
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

		// Asana task assets.

		$asset_file = require_once PLUGIN_PATH . 'build/index_ShortcodeAsanaTask.jsx.asset.php';

		wp_register_script(
			'ptc-completionist-shortcode-asana-task',
			PLUGIN_URL . '/build/index_ShortcodeAsanaTask.jsx.js',
			$dependencies,
			$asset_file['version'],
			true
		);

		wp_register_style(
			'ptc-completionist-shortcode-asana-task',
			PLUGIN_URL . '/build/index_ShortcodeAsanaTask.jsx.css',
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
	 * @since 3.7.0
	 *
	 * @param string $shortcode_tag The shortcode tag.
	 */
	public static function count_render( string $shortcode_tag ) {
		++static::$shortcodes_meta[ $shortcode_tag ]['render_count'];
	}

	/**
	 * Gets shortcode metadata.
	 *
	 * @since 3.8.0
	 *
	 * @see $shortcodes_meta
	 *
	 * @return array The metadata of each managed shortcode.
	 */
	public static function get_shortcodes_meta() : array {
		return static::$shortcodes_meta;
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
	) : string {

		// Collect shortcode attributes.

		$atts = shortcode_atts(
			static::$shortcodes_meta[ $shortcode_tag ]['default_atts'],
			$atts,
			$shortcode_tag
		);

		// Validate shortcode attributes.

		if (
			empty( $atts['auth_user'] ) &&
			Options::get( Options::FRONTEND_AUTH_USER_ID ) <= 0
		) {
			return '
				<div class="ptc-shortcode ptc-asana-project ptc-error">
					<p>Failed to load Asana project. Please specify <a href="https://docs.purpleturtlecreative.com/completionist/shortcodes/#ptc_asana_project" target="_blank">the auth_user ID</a> or <a href="https://docs.purpleturtlecreative.com/completionist/getting-started/#set-a-frontend-authentication-user" target="_blank">set a default authentication user</a>.</p>
				</div>
			';
		}

		// Sanitize shortcode attributes.

		$atts['src']              = (string) esc_url_raw( $atts['src'] );
		$atts['auth_user']        = (int) $atts['auth_user'];
		$atts['include_sections'] = html_entity_decode( $atts['include_sections'], ENT_QUOTES | ENT_HTML5 );
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

		// Specify request token key.
		$atts['_cache_key'] = 'shortcode_ptc_asana_project';

		// Generate request token for the frontend.
		$token = Request_Token::save( $atts );

		// Render frontend data.

		$layout = 'list';
		if ( ! empty( $atts['layout'] ) ) {
			$layout = $atts['layout'];
		} elseif ( ! empty( $parsed_asana_project['layout'] ) ) {
			$layout = $parsed_asana_project['layout'];
		}

		$request_url = add_query_arg(
			array( 'token' => $token ),
			rest_url( REST_API_NAMESPACE_V1 . '/projects' )
		);

		static::count_render( $shortcode_tag );
		return sprintf(
			'<div class="ptc-shortcode ptc-asana-project" data-src="%1$s" data-layout="%2$s"></div>',
			esc_url( $request_url ),
			esc_attr( $layout )
		);
	}

	/**
	 * Gets the [ptc_asana_task] shortcode content.
	 *
	 * @since 4.3.0
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
	public static function get_ptc_asana_task(
		$atts = array(),
		$content = null,
		$shortcode_tag = ''
	) : string {

		// Collect shortcode attributes.

		$atts = shortcode_atts(
			static::$shortcodes_meta[ $shortcode_tag ]['default_atts'],
			$atts,
			$shortcode_tag
		);

		// Validate shortcode attributes.

		if (
			empty( $atts['auth_user'] ) &&
			Options::get( Options::FRONTEND_AUTH_USER_ID ) <= 0
		) {
			return '
				<div class="ptc-shortcode ptc-asana-task ptc-error">
					<p>Failed to load Asana task. Please specify <a href="https://docs.purpleturtlecreative.com/completionist/shortcodes/#ptc_asana_project" target="_blank">the auth_user ID</a> or <a href="https://docs.purpleturtlecreative.com/completionist/getting-started/#set-a-frontend-authentication-user" target="_blank">set a default authentication user</a>.</p>
				</div>
			';
		}

		// Sanitize shortcode attributes.

		$atts['src']              = (string) esc_url_raw( $atts['src'] );
		$atts['auth_user']        = (int) $atts['auth_user'];
		$atts['sort_subtasks_by'] = (string) sanitize_text_field( $atts['sort_subtasks_by'] );

		foreach ( $atts as $key => &$value ) {
			if ( 0 === strpos( $key, 'show_', 0 ) ) {
				// Cast "show" flags to boolean value.
				$value = (bool) rest_sanitize_boolean( $value );
			}
		}

		// Prepare shortcode.

		$task_gid = Asana_Interface::get_task_gid_from_task_link( $atts['src'] );
		if ( empty( $task_gid ) ) {
			return '
				<div class="ptc-shortcode ptc-asana-task ptc-error">
					<p>Failed to load Asana task. Could not determine task GID from source URL.</p>
				</div>
			';
		}

		// Convert shortcode attributes into Asana opt_fields.
		$atts['opt_fields'] = 'name';
		if ( $atts['show_completed'] ) {
			$atts['opt_fields'] .= ',completed';
		}
		if ( $atts['show_description'] ) {
			$atts['opt_fields'] .= ',html_notes';
		}
		if ( $atts['show_assignee'] ) {
			$atts['opt_fields'] .= ',assignee,assignee.name,assignee.photo.image_36x36';
		}
		if ( $atts['show_due'] ) {
			$atts['opt_fields'] .= ',due_on';
		}
		if ( $atts['show_attachments'] ) {
			$atts['opt_fields'] .= ',attachments.name,attachments.host,attachments.download_url,attachments.view_url';
		}
		if ( $atts['show_tags'] ) {
			$atts['opt_fields'] .= ',tags,tags.name,tags.color';
		}

		// Always remove Asana object GIDs.
		$atts['show_gids'] = false;

		// Specify request token key.
		$atts['_cache_key'] = 'shortcode_ptc_asana_task';

		// Generate request token for the frontend.
		$token = Request_Token::save( $atts );

		// Render frontend data.

		$request_url = add_query_arg(
			array( 'token' => $token ),
			rest_url( REST_API_NAMESPACE_V1 . '/tasks/' . $task_gid )
		);

		static::count_render( $shortcode_tag );
		return sprintf(
			'<div class="ptc-shortcode ptc-asana-task" data-src="%1$s"></div>',
			esc_url( $request_url )
		);
	}
}
