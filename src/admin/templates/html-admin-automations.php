<?php
/**
 * The main administrative dashboard for a user to view and manage plugin and
 * API settings.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';

try {

	Asana_Interface::require_settings();

	if ( Asana_Interface::has_connected_asana() ) {
		?>
		<div id="ptc-PTCCompletionistAutomations">
			<p class="ptc-loading"><i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>Loading...</p>
		</div>
		<?php
	} else {
		throw new \Exception( 'Not Authorized. Please connect your Asana account to use Completionist.', 401 );
	}
} catch ( \Exception $e ) {
	require_once PLUGIN_PATH . 'src/admin/class-admin-pages.php';
	require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
	?>
	<div class="ptc-error-screen">
		<p>
			<strong><?php echo esc_html( 'Error ' . HTML_Builder::get_error_code( $e ) . '.' ); ?></strong>
			<br />
			<?php echo esc_html( HTML_Builder::get_error_message( $e ) ); ?>
			<br />
			<a href="<?php echo esc_url( Admin_Pages::get_settings_url() ); ?>">
				Go to Settings
				<i class="fas fa-long-arrow-alt-right"></i>
			</a>
		</p>
	</div>
	<?php
}
