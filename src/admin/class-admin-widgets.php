<?php
/**
 * Admin Widgets class
 *
 * Registers admin widgets and metaboxes.
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Registers admin widgets and metaboxes.
 */
class Admin_Widgets {

	/**
	 * Registers code.
	 *
	 * @since 3.0.0
	 */
	public static function register() {
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_boxes' ] );
		add_action( 'wp_dashboard_setup', [ __CLASS__, 'add_dashboard_widgets' ] );
	}

	/**
	 * Adds metaboxes.
	 *
	 * @since 3.0.0 Moved to Admin_Widgets class.
	 * @since 1.0.0
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'ptc-completionist_pinned-tasks',
			'Tasks',
			function () {
				include_once PLUGIN_PATH . 'src/admin/templates/html-metabox-pinned-tasks.php';
			},
			null,
			'side',
			'default',
			array( '__back_compat_meta_box' => true )
		);
		add_meta_box(
			'ptc-completionist-pinned-tasks',
			'Completionist',
			function () {
				?>
				<style type="text/css">
					#ptc-completionist-pinned-tasks:not(.closed) .postbox-header {
						border-bottom: none;
					}
					#ptc-completionist-pinned-tasks:not(.closed) .inside {
						margin: 0;
						padding: 0;
					}
				</style>
				<div id="ptc-PinnedTasksMetabox"></div>
				<?php
			},
			null,
			'side',
			'default',
			array( '__back_compat_meta_box' => true )
		);
	}

	/**
	 * Adds admin dashboard widgets.
	 *
	 * @since 3.0.0 Moved to Admin_Widgets class.
	 * @since 1.0.0
	 */
	public static function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'ptc-completionist_site-tasks',
			'Completionist Tasks',
			function () {
				include_once PLUGIN_PATH . 'src/admin/templates/html-dashboard-widget.php';
			}
		);
	}
}//end class
