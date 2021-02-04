<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette
 * @copyright         2020 Michelle Blanchette
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Completionist - Asana for WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Manage, pin, and automate Asana tasks in relevant areas of your WordPress admin.
 * Version:           2.0.1
 * Requires PHP:      7.1
 * Requires at least: 5.0.0
 * Tested up to:      5.6.0
 * Author:            Purple Turtle Creative
 * Author URI:        https://purpleturtlecreative.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

/*
This program is open-source software: you can redistribute it and/or modify
it UNDER THE TERMS of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
*/

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * The full file path to this plugin's main file.
 *
 * @since 2.0.1
 */
define( __NAMESPACE__ . '\PLUGIN_FILE', __FILE__ );

/**
 * The full file path to this plugin's directory ending with a slash.
 *
 * @since 2.0.1
 */
define( __NAMESPACE__ . '\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This plugin's current version.
 *
 * @since 2.0.1
 */
define( __NAMESPACE__ . '\PLUGIN_VERSION', get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' )['Version'] );

/**
 * This plugin's basename.
 *
 * @since 2.0.1
 */
define( __NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The full url to this plugin's directory.
 *
 * @since 2.0.1
 */
define( __NAMESPACE__ . '\PLUGIN_URL', plugins_url( '', __FILE__ ) );

/* REGISTER PLUGIN FUNCTIONS ---------------------- */

/* Activation Hook */
register_activation_hook( PLUGIN_FILE, function() {
	require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';
	Database_Manager::init();
	Database_Manager::install_all_tables();
});

/* Plugins Loaded */
add_action( 'plugins_loaded', function() {

	/* Ensure Database Tables are Installed */
	require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';
	Database_Manager::init();
	Database_Manager::install_all_tables();

	/* Enqueue Automation Actions */
	require_once PLUGIN_PATH . 'src/includes/automations/class-events.php';
	Automations\Events::add_actions();

	/* YahnisElsts/plugin-update-checker */
	require_once PLUGIN_PATH . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
	if ( class_exists( '\Puc_v4_Factory' ) ) {
		global $wp_version;
		$url = add_query_arg(
			'wp_version',
			$wp_version,
			'https://purpleturtlecreative.com/wp-json/ptc-resources/v1/plugins/completionist/latest'
		);
		\Puc_v4_Factory::buildUpdateChecker(
			$url,
			PLUGIN_FILE, // Full path to the main plugin file or functions.php.
			'completionist'
		);
	}
});

/* Register Admin Functionality */
if ( is_admin() ) {

	foreach ( glob( PLUGIN_PATH . 'src/admin/class-*.php' ) as $file ) {
		require_once $file;
	}

	Admin_Pages::register();
	Admin_Widgets::register();
}









if ( ! class_exists( '\PTC_Completionist' ) ) {
	/**
	 * Maintains data and functions related to plugin data and registration.
	 *
	 * @since 1.0.0
	 */
	class PTC_Completionist {

		/**
		 * Hooks code into WordPress.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function register() {

			add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );

			/* Task AJAX Handlers */
			add_action( 'wp_ajax_ptc_pin_task', [ $this, 'ajax_pin_task' ] );
			add_action( 'wp_ajax_ptc_unpin_task', [ $this, 'ajax_unpin_task' ] );
			add_action( 'wp_ajax_ptc_get_pins', [ $this, 'ajax_get_pins' ] );
			add_action( 'wp_ajax_ptc_list_task', [ $this, 'ajax_list_task' ] );
			add_action( 'wp_ajax_ptc_list_tasks', [ $this, 'ajax_list_tasks' ] );
			add_action( 'wp_ajax_ptc_create_task', [ $this, 'ajax_create_task' ] );
			add_action( 'wp_ajax_ptc_delete_task', [ $this, 'ajax_delete_task' ] );
			add_action( 'wp_ajax_ptc_update_task', [ $this, 'ajax_update_task' ] );
			/* Generic AJAX Handlers */
			add_action( 'wp_ajax_ptc_get_tag_options', [ $this, 'ajax_get_tag_options' ] );
			add_action( 'wp_ajax_ptc_get_post_options_by_title', [ $this, 'ajax_ptc_get_post_options_by_title' ] );
			add_action( 'wp_ajax_ptc_get_post_title_by_id', [ $this, 'ajax_ptc_get_post_title_by_id' ] );
			/* Automation AJAX Handlers */
			add_action( 'wp_ajax_ptc_save_automation', [ $this, 'ajax_ptc_save_automation' ] );
			add_action( 'wp_ajax_ptc_get_automation', [ $this, 'ajax_ptc_get_automation' ] );
			add_action( 'wp_ajax_ptc_get_automation_overviews', [ $this, 'ajax_ptc_get_automation_overviews' ] );
			add_action( 'wp_ajax_ptc_delete_automation', [ $this, 'ajax_ptc_delete_automation' ] );

		}

		/**
		 * AJAX handler to load tag options for a workspace.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_get_tag_options() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-tag-options.php';
		}

		/**
		 * AJAX handler to pin a task.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_pin_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-pin-task.php';
		}

		/**
		 * AJAX handler to unpin a task.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_unpin_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-unpin-task.php';
		}

		/**
		 * AJAX handler to load task HTML.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_get_pins() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-pins.php';
		}

		/**
		 * AJAX handler to load task HTML.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_list_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-list-task.php';
		}

		/**
		 * AJAX handler to load HTML for multiple tasks.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_list_tasks() {
			require_once $this->plugin_path . 'src/ajax/ajax-list-tasks.php';
		}

		/**
		 * AJAX handler to create and pin a new task.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_create_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-create-task.php';
		}

		/**
		 * AJAX handler to delete and unpin a task.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_delete_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-delete-task.php';
		}

		/**
		 * AJAX handler to update a task.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function ajax_update_task() {
			require_once $this->plugin_path . 'src/ajax/ajax-update-task.php';
		}

		/**
		 * AJAX handler to get post options by like title.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_get_post_options_by_title() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-post-options-by-title.php';
		}

		/**
		 * AJAX handler to get post title by ID.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_get_post_title_by_id() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-post-title-by-id.php';
		}

		/**
		 * AJAX handler to save automation data.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_save_automation() {
			require_once $this->plugin_path . 'src/ajax/ajax-save-automation.php';
		}

		/**
		 * AJAX handler to get automation by ID.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_get_automation() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-automation.php';
		}

		/**
		 * AJAX handler to get overview data for all automations.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_get_automation_overviews() {
			require_once $this->plugin_path . 'src/ajax/ajax-get-automation-overviews.php';
		}

		/**
		 * AJAX handler to delete an automation by ID.
		 *
		 * @since 1.1.0
		 *
		 * @ignore
		 */
		function ajax_ptc_delete_automation() {
			require_once $this->plugin_path . 'src/ajax/ajax-delete-automation.php';
		}

		/**
		 * Register and enqueue plugin CSS and JS.
		 *
		 * @since 1.0.0
		 *
		 * @ignore
		 */
		function register_scripts( $hook_suffix ) {

			wp_register_script(
				'fontawesome-5',
				'https://kit.fontawesome.com/02ab9ff442.js',
				[],
				'5.12.1'
			);

			wp_register_style(
				'ptc-completionist_admin-theme-css',
				plugins_url( 'assets/css/admin-theme.css', __FILE__ ),
				[],
				$this->plugin_version
			);

			switch ( $hook_suffix ) {

				case 'index.php':
					wp_enqueue_script(
						'ptc-completionist_dashboard-widget-js',
						plugins_url( 'assets/js/dashboard-widget.js', __FILE__ ),
						[ 'jquery', 'fontawesome-5' ],
						$this->plugin_version
					);
					wp_localize_script(
						'ptc-completionist_dashboard-widget-js',
						'ptc_completionist_dashboard_widget',
						[
							'nonce_pin' => wp_create_nonce( 'ptc_completionist_pin_task' ),
							'nonce_list' => wp_create_nonce( 'ptc_completionist_list_task' ),
							'nonce_create' => wp_create_nonce( 'ptc_completionist_create_task' ),
							'nonce_delete' => wp_create_nonce( 'ptc_completionist_delete_task' ),
							'nonce_update' => wp_create_nonce( 'ptc_completionist_update_task' ),
							'page_size' => 10,
							'current_category' => 'all-site-tasks',
							'current_page' => 1,
						]
					);
					wp_enqueue_style(
						'ptc-completionist_dashboard-widget-css',
						plugins_url( 'assets/css/dashboard-widget.css', __FILE__ ),
						[],
						$this->plugin_version
					);
					break;

				case 'toplevel_page_ptc-completionist':
					wp_enqueue_style(
						'ptc-completionist_connect-asana-css',
						plugins_url( 'assets/css/connect-asana.css', __FILE__ ),
						[ 'ptc-completionist_admin-theme-css' ],
						$this->plugin_version
					);
					wp_enqueue_style(
						'ptc-completionist_admin-dashboard-css',
						plugins_url( 'assets/css/admin-dashboard.css', __FILE__ ),
						[ 'ptc-completionist_admin-theme-css' ],
						$this->plugin_version
					);
					wp_enqueue_script(
						'ptc-completionist_admin-dashboard-js',
						plugins_url( 'assets/js/admin-dashboard.js', __FILE__ ),
						[ 'jquery', 'fontawesome-5' ],
						$this->plugin_version
					);
					require_once $this->plugin_path . 'src/class-options.php';
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

				case 'post.php':
				case 'post-new.php':
					require_once $this->plugin_path . 'src/class-options.php';
					wp_enqueue_script(
						'ptc-completionist_metabox-pinned-tasks-js',
						plugins_url( 'assets/js/metabox-pinned-tasks.js', __FILE__ ),
						[ 'jquery', 'fontawesome-5' ],
						$this->plugin_version
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
						plugins_url( 'assets/css/metabox-pinned-tasks.css', __FILE__ ),
						[],
						$this->plugin_version
					);
					break;

				case 'completionist_page_ptc-completionist-automations':
					require_once $this->plugin_path . 'src/class-asana-interface.php';
					try {
						Asana_Interface::require_settings();
						$has_required_settings = TRUE;
					} catch ( \Exception $e ) {
						$has_required_settings = FALSE;
					}
					if ( $has_required_settings && Asana_Interface::has_connected_asana() ) {
						$asset_file = require_once( $this->plugin_path . 'build/index.asset.php' );
						wp_enqueue_script(
							'ptc-completionist_build-index-js',
							plugins_url( 'build/index.js', __FILE__ ),
							$asset_file['dependencies'],
							$this->plugin_version
						);
						require_once $this->plugin_path . 'src/automations/class-events.php';
						require_once $this->plugin_path . 'src/automations/class-fields.php';
						require_once $this->plugin_path . 'src/automations/class-actions.php';
						require_once $this->plugin_path . 'src/automations/class-data.php';
						wp_localize_script(
							'ptc-completionist_build-index-js',
							'ptc_completionist_automations',
							[
								'automations' => Automations\Data::get_automation_overviews(),
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
						plugins_url( 'assets/css/admin-automations.css', __FILE__ ),
						[],
						$this->plugin_version
					);
					break;

			}//end switch hook suffix

		}//end register_scripts()

	}//end class

	$ptc_completionist = new PTC_Completionist();
	$ptc_completionist->register();

}//end if class_exists
