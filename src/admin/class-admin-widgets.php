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
		add_action( 'add_meta_boxes', __CLASS__ . '::add_meta_boxes' );
		add_action( 'wp_dashboard_setup', __CLASS__ . '::add_dashboard_widgets' );
	}

	/**
	 * Adds metaboxes.
	 *
	 * @since 3.0.0 Moved to Admin_Widgets class.
	 * @since 1.0.0
	 */
	public static function add_meta_boxes() {
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
			__CLASS__ . '::display_tasks_dashboard_widget'
		);
	}

	/**
	 * Displays the Tasks admin dashboard widget.
	 *
	 * @since 4.0.0 Moved to Admin_Widgets class.
	 * @since 3.1.0 Now using ReactJS to render.
	 * @since 1.0.0
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function display_tasks_dashboard_widget() {

		try {

			Asana_Interface::require_settings();

			if ( ! Asana_Interface::is_workspace_member() ) {
				throw new \Exception( 'You are not a member of the assigned Asana Workspace.', 403 );
			}

			/* Display */
			?>
			<div id="ptc-DashboardWidget">
				<p class="ptc-loading"><i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>Loading...</p>
			</div>
			<?php
		} catch ( Errors\No_Authorization $e ) {
			/* User is not authenticated for API usage. */
			?>
			<div class="note-box note-box-error">
				<p>
					<strong>Not authorized.</strong>
					<br>
					Please connect your Asana account to use Completionist.
					<br>
					<a class="note-box-cta" href="<?php echo esc_url( Admin_Pages::get_settings_url() ); ?>">Go to Settings<i class="fas fa-long-arrow-alt-right"></i></a>
				</p>
			</div>
			<?php
		} catch ( \Exception $e ) {
			echo wp_kses_post( HTML_Builder::format_error_box( $e, 'Feature unavailable. ', false ) );
		}//end try catch asana client
	}
}//end class
