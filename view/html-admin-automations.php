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

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';

try {

  Asana_Interface::require_settings();

  if ( \PTC_Completionist\Asana_Interface::has_connected_asana() ) {
    ?>
    <div id="ptc-completionist-automations-root"></div>;
    <?php
  } else {
    throw new \Exception( 'Not Authorized. Please connect your Asana account to use Completionist.', 401 );
  }

} catch ( \Exception $e ) {
  require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';
  ?>
  <div class="ptc-error-screen">
    <p>
      <strong><?php echo esc_html( 'Error ' . HTML_Builder::get_error_code( $e ) . '.' ); ?></strong>
      <br />
      <?php echo esc_html( HTML_Builder::get_error_message( $e ) ); ?>
      <br />
      <a href="<?php echo esc_url( $this->settings_url ); ?>">
        Go to Settings
        <i class="fas fa-long-arrow-alt-right"></i>
      </a>
    </p>
  </div>
  <?php
}
