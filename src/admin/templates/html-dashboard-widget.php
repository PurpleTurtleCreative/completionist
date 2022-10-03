<?php
/**
 * The content of the Site Tasks admin dashboard widget.
 *
 * @since 3.1.0 Now using ReactJS to render.
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

// Libraries.
require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';

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
} catch ( Errors\NoAuthorization $e ) {
	/* User is not authenticated for API usage. */
	require_once PLUGIN_PATH . 'src/admin/class-admin-pages.php';
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
	require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
	echo HTML_Builder::format_error_box( $e, 'Feature unavailable. ', false );
}//end try catch asana client
