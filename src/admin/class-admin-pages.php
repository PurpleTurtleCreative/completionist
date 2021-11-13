<?php
/**
 * Admin Pages class
 *
 * Registers admin pages.
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Admin_Pages' ) ) {
	/**
	 * Registers admin pages.
	 */
	class Admin_Pages {

		/**
		 * The name of the plugin menu's main parent page.
		 *
		 * @since 3.0.0
		 */
		public const PARENT_PAGE_SLUG = 'ptc-completionist';

		/**
		 * Registers code.
		 *
		 * @since 3.0.0
		 */
		public static function register() {
			add_action( 'admin_menu', [ __CLASS__, 'add_admin_pages' ] );
			add_filter( 'plugin_action_links_' . PLUGIN_BASENAME, [ __CLASS__, 'filter_plugin_action_links' ] );
			add_action( 'admin_enqueue_scripts', [ __CLASS__, 'register_scripts' ] );
		}

		/**
		 * Gets the settings admin page URL.
		 *
		 * @since 3.0.0
		 */
		public static function get_settings_url() {
			return admin_url( 'admin.php?page=' . static::PARENT_PAGE_SLUG );
		}

		/**
		 * Adds the admin pages.
		 *
		 * @since 3.0.0 Moved to Admin_Pages class.
		 * @since 1.0.0
		 */
		public static function add_admin_pages() {

			add_menu_page(
				'Completionist &ndash; Settings',
				'Completionist',
				'edit_posts',
				static::PARENT_PAGE_SLUG,
				function() {
					if ( current_user_can( 'edit_posts' ) ) {
						include_once PLUGIN_PATH . 'src/admin/templates/html-admin-dashboard.php';
					} else {
						wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
					}
				},
				'data:image/svg+xml;base64,' . base64_encode( '<svg width="20" height="20" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-check" class="svg-inline--fa fa-clipboard-check fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="white" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm121.2 231.8l-143 141.8c-4.7 4.7-12.3 4.6-17-.1l-82.6-83.3c-4.7-4.7-4.6-12.3.1-17L99.1 285c4.7-4.7 12.3-4.6 17 .1l46 46.4 106-105.2c4.7-4.7 12.3-4.6 17 .1l28.2 28.4c4.7 4.8 4.6 12.3-.1 17z"></path></svg>' ),
				100 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
			);

			add_submenu_page(
				static::PARENT_PAGE_SLUG,
				'Completionist &ndash; Automations',
				'Automations',
				'edit_posts',
				static::PARENT_PAGE_SLUG . '-automations',
				function() {
					if ( current_user_can( 'edit_posts' ) ) {
						include_once PLUGIN_PATH . 'src/admin/templates/html-admin-automations.php';
					} else {
						wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
					}
				},
				null
			);
		}//end add_admin_pages()

		/**
		 * Edits the plugin row's action links.
		 *
		 * @since 3.0.0 Moved to Admin_Pages class.
		 * @since 1.0.0
		 *
		 * @param string[] $links The plugin action link HTML items.
		 */
		public static function filter_plugin_action_links( $links ) {
			$links[] = '<a href="https://docs.purpleturtlecreative.com/completionist/" target="_blank">Docs</a>';
			$links[] = '<a href="' . esc_url( static::get_settings_url() ) . '">Settings</a>';
			return $links;
		}

		/**
		 * Registers and enqueues admin CSS and JS.
		 *
		 * @since 3.0.0 Moved to Admin_Pages class.
		 * @since 1.0.0
		 *
		 * @param string $hook_suffix The current admin page.
		 */
		public static function register_scripts( $hook_suffix ) {

			wp_register_script(
				'fontawesome-5',
				'https://kit.fontawesome.com/02ab9ff442.js',
				[],
				'5.12.1'
			);

			wp_register_style(
				'ptc-completionist_admin-theme-css',
				PLUGIN_URL . '/assets/styles/admin-theme.css',
				[],
				PLUGIN_VERSION
			);

			switch ( $hook_suffix ) {

				case 'index.php':
					$asset_file = require_once( PLUGIN_PATH . 'build/index.asset.php' );
					wp_enqueue_script(
						'ptc-completionist_build-index-js',
						PLUGIN_URL . '/build/index.js',
						$asset_file['dependencies'],
						PLUGIN_VERSION
					);
					// @TODO: Also get map of Asana user GIDs => WordPress users.
					// This is for displaying assignee information like name and gravatar.
					$js_data = [
						'tasks' => Asana_Interface::maybe_get_all_site_tasks( HTML_Builder::TASK_OPT_FIELDS )
					];
					$js_data = json_encode( $js_data );
					wp_add_inline_script(
						'ptc-completionist_build-index-js',
						"var PTC = {$js_data};",
						'before'
					);
					// wp_enqueue_script(
					// 	'ptc-completionist_dashboard-widget-js',
					// 	PLUGIN_URL . '/assets/scripts/dashboard-widget.js',
					// 	[ 'jquery', 'fontawesome-5' ],
					// 	PLUGIN_VERSION
					// );
					// wp_localize_script(
					// 	'ptc-completionist_dashboard-widget-js',
					// 	'ptc_completionist_dashboard_widget',
					// 	[
					// 		'nonce_pin' => wp_create_nonce( 'ptc_completionist_pin_task' ),
					// 		'nonce_list' => wp_create_nonce( 'ptc_completionist_list_task' ),
					// 		'nonce_create' => wp_create_nonce( 'ptc_completionist_create_task' ),
					// 		'nonce_delete' => wp_create_nonce( 'ptc_completionist_delete_task' ),
					// 		'nonce_update' => wp_create_nonce( 'ptc_completionist_update_task' ),
					// 		'page_size' => 10,
					// 		'current_category' => 'all-site-tasks',
					// 		'current_page' => 1,
					// 	]
					// );
					wp_enqueue_style(
						'ptc-completionist_dashboard-widget-css',
						PLUGIN_URL . '/assets/styles/dashboard-widget.css',
						[],
						PLUGIN_VERSION
					);
					break;

				case 'post.php':
				case 'post-new.php':
					require_once PLUGIN_PATH . 'src/includes/class-options.php';
					wp_enqueue_script(
						'ptc-completionist_metabox-pinned-tasks-js',
						PLUGIN_URL . '/assets/scripts/metabox-pinned-tasks.js',
						[ 'jquery', 'fontawesome-5' ],
						PLUGIN_VERSION
					);
					wp_localize_script(
						'ptc-completionist_metabox-pinned-tasks-js',
						'ptc_completionist_pinned_tasks',
						[
							'post_id' => get_the_ID(),
							'pinned_task_gids' => Options::get( Options::PINNED_TASK_GID, get_the_ID() ),
							'nonce_pin' => wp_create_nonce( 'ptc_completionist_pin_task' ),
							'nonce_list' => wp_create_nonce( 'ptc_completionist_list_task' ),
							'nonce_create' => wp_create_nonce( 'ptc_completionist_create_task' ),
							'nonce_delete' => wp_create_nonce( 'ptc_completionist_delete_task' ),
							'nonce_update' => wp_create_nonce( 'ptc_completionist_update_task' ),
						]
					);
					wp_enqueue_style(
						'ptc-completionist_metabox-pinned-tasks-css',
						PLUGIN_URL . '/assets/styles/metabox-pinned-tasks.css',
						[],
						PLUGIN_VERSION
					);
					break;

				case 'toplevel_page_ptc-completionist':
					wp_enqueue_style(
						'ptc-completionist_connect-asana-css',
						PLUGIN_URL . '/assets/styles/connect-asana.css',
						[ 'ptc-completionist_admin-theme-css' ],
						PLUGIN_VERSION
					);
					wp_enqueue_style(
						'ptc-completionist_admin-dashboard-css',
						PLUGIN_URL . '/assets/styles/admin-dashboard.css',
						[ 'ptc-completionist_admin-theme-css' ],
						PLUGIN_VERSION
					);
					wp_enqueue_script(
						'ptc-completionist_admin-dashboard-js',
						PLUGIN_URL . '/assets/scripts/admin-dashboard.js',
						[ 'jquery', 'fontawesome-5' ],
						PLUGIN_VERSION
					);
					require_once PLUGIN_PATH . 'src/includes/class-options.php';
					wp_localize_script(
						'ptc-completionist_admin-dashboard-js',
						'ptc_completionist_dashboard',
						[
							'saved_workspace_gid' => Options::get( Options::ASANA_WORKSPACE_GID ),
							'saved_tag_gid' => Options::get( Options::ASANA_TAG_GID ),
							'nonce' => wp_create_nonce( 'ptc_completionist_dashboard' ),
						]
					);
					break;

				case 'completionist_page_ptc-completionist-automations':
					require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
					try {
						Asana_Interface::require_settings();
						$has_required_settings = true;
					} catch ( \Exception $e ) {
						$has_required_settings = false;
					}
					if ( $has_required_settings && Asana_Interface::has_connected_asana() ) {
						$asset_file = require_once( PLUGIN_PATH . 'build/index.asset.php' );
						wp_enqueue_script(
							'ptc-completionist_build-index-js',
							PLUGIN_URL . '/build/index.js',
							$asset_file['dependencies'],
							PLUGIN_VERSION
						);
						require_once PLUGIN_PATH . 'src/includes/automations/class-events.php';
						require_once PLUGIN_PATH . 'src/includes/automations/class-fields.php';
						require_once PLUGIN_PATH . 'src/includes/automations/class-actions.php';
						require_once PLUGIN_PATH . 'src/includes/automations/class-data.php';
						wp_localize_script(
							'ptc-completionist_build-index-js',
							'ptc_completionist_automations',
							[
								'automations' => Automations\Data::get_automation_overviews( null, true ),
								'event_user_options' => Automations\Events::USER_OPTIONS,
								'event_post_options' => Automations\Events::POST_OPTIONS,
								'field_user_options' => Automations\Fields::USER_OPTIONS,
								'field_post_options' => Automations\Fields::POST_OPTIONS,
								'field_comparison_methods' => Automations\Fields::COMPARISON_METHODS,
								'action_options' => Automations\Actions::ACTION_OPTIONS,
								'workspace_users' => Asana_Interface::get_workspace_user_options(),
								'connected_workspace_users' => Asana_Interface::get_connected_workspace_user_options(),
								'workspace_projects' => Asana_Interface::get_workspace_project_options(),
								'nonce' => wp_create_nonce( 'ptc_completionist_automations' ),
							]
						);
					}
					wp_enqueue_script( 'fontawesome-5' );
					wp_enqueue_style(
						'ptc-completionist_admin-automations-css',
						PLUGIN_URL . '/assets/styles/admin-automations.css',
						[],
						PLUGIN_VERSION
					);
					break;
			}//end switch hook suffix
		}//end register_scripts()
	}//end class
}
