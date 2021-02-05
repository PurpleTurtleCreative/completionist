<?php
/**
 * Admin Widgets class
 *
 * Registers admin widgets and metaboxes.
 *
 * @since 2.0.1
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Admin_Widgets' ) ) {
	/**
	 * Registers admin widgets and metaboxes.
	 */
	class Admin_Widgets {

		/**
		 * Registers code.
		 *
		 * @since 2.0.1
		 */
		public static function register() {
			add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
			add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_dashboard_widgets' ] );
		}

		/**
		 * Adds metaboxes.
		 *
		 * @since 2.0.1 Moved to Admin_Widgets class.
		 * @since 1.0.0
		 */
		public static function add_meta_boxes() {
			add_meta_box(
				'ptc-completionist_pinned-tasks',
				'Tasks',
				function() {
					include_once PLUGIN_PATH . 'src/admin/templates/html-metabox-pinned-tasks.php';
				},
				null,
				'side'
			);
		}

		/**
		 * Adds admin dashboard widgets.
		 *
		 * @since 2.0.1 Moved to Admin_Widgets class.
		 * @since 1.0.0
		 */
		public static function add_dashboard_widgets() {
			wp_add_dashboard_widget(
				'ptc-completionist_site-tasks',
				'Completionist Tasks',
				function() {
					include_once PLUGIN_PATH . 'src/admin/templates/html-dashboard-widget.php';
				}
			);
		}
	}//end class
}
