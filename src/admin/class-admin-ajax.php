<?php
/**
 * Admin Ajax class
 *
 * Registers AJAX endpoints requiring privileges.
 *
 * @since 2.0.1
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( '\Admin_Ajax' ) ) {
	/**
	 * Registers AJAX endpoints requiring privileges.
	 */
	class Admin_Ajax {

		/**
		 * Registers code.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function register() {

			/* Task AJAX Handlers */
			add_action( 'wp_ajax_ptc_pin_task', [ __CLASS__, 'ajax_pin_task' ] );
			add_action( 'wp_ajax_ptc_unpin_task', [ __CLASS__, 'ajax_unpin_task' ] );
			add_action( 'wp_ajax_ptc_get_pins', [ __CLASS__, 'ajax_get_pins' ] );
			add_action( 'wp_ajax_ptc_list_task', [ __CLASS__, 'ajax_list_task' ] );
			add_action( 'wp_ajax_ptc_list_tasks', [ __CLASS__, 'ajax_list_tasks' ] );
			add_action( 'wp_ajax_ptc_create_task', [ __CLASS__, 'ajax_create_task' ] );
			add_action( 'wp_ajax_ptc_delete_task', [ __CLASS__, 'ajax_delete_task' ] );
			add_action( 'wp_ajax_ptc_update_task', [ __CLASS__, 'ajax_update_task' ] );
			/* Generic AJAX Handlers */
			add_action( 'wp_ajax_ptc_get_tag_options', [ __CLASS__, 'ajax_get_tag_options' ] );
			add_action( 'wp_ajax_ptc_get_post_options_by_title', [ __CLASS__, 'ajax_ptc_get_post_options_by_title' ] );
			add_action( 'wp_ajax_ptc_get_post_title_by_id', [ __CLASS__, 'ajax_ptc_get_post_title_by_id' ] );
			/* Automation AJAX Handlers */
			add_action( 'wp_ajax_ptc_save_automation', [ __CLASS__, 'ajax_ptc_save_automation' ] );
			add_action( 'wp_ajax_ptc_get_automation', [ __CLASS__, 'ajax_ptc_get_automation' ] );
			add_action( 'wp_ajax_ptc_get_automation_overviews', [ __CLASS__, 'ajax_ptc_get_automation_overviews' ] );
			add_action( 'wp_ajax_ptc_delete_automation', [ __CLASS__, 'ajax_ptc_delete_automation' ] );
		}

		/**
		 * AJAX handler to load tag options for a workspace.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_get_tag_options() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-tag-options.php';
		}

		/**
		 * AJAX handler to pin a task.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_pin_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-pin-task.php';
		}

		/**
		 * AJAX handler to unpin a task.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_unpin_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-unpin-task.php';
		}

		/**
		 * AJAX handler to load task HTML.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_get_pins() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-pins.php';
		}

		/**
		 * AJAX handler to load task HTML.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_list_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-list-task.php';
		}

		/**
		 * AJAX handler to load HTML for multiple tasks.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_list_tasks() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-list-tasks.php';
		}

		/**
		 * AJAX handler to create and pin a new task.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_create_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-create-task.php';
		}

		/**
		 * AJAX handler to delete and unpin a task.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_delete_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-delete-task.php';
		}

		/**
		 * AJAX handler to update a task.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.0.0
		 */
		public static function ajax_update_task() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-update-task.php';
		}

		/**
		 * AJAX handler to get post options by like title.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_get_post_options_by_title() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-post-options-by-title.php';
		}

		/**
		 * AJAX handler to get post title by ID.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_get_post_title_by_id() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-post-title-by-id.php';
		}

		/**
		 * AJAX handler to save automation data.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_save_automation() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-save-automation.php';
		}

		/**
		 * AJAX handler to get automation by ID.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_get_automation() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-automation.php';
		}

		/**
		 * AJAX handler to get overview data for all automations.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_get_automation_overviews() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-automation-overviews.php';
		}

		/**
		 * AJAX handler to delete an automation by ID.
		 *
		 * @since 2.0.1 Moved to Admin_Ajax class.
		 * @since 1.1.0
		 */
		public static function ajax_ptc_delete_automation() {
			require_once PLUGIN_PATH . 'src/admin/ajax/ajax-delete-automation.php';
		}
	}//end class
}//end if class_exists
