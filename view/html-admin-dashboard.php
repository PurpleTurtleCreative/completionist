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

echo '<h1>Completionist &ndash; Settings</h1>';
require_once $ptc_completionist->plugin_path . 'src/script-save-authorization.php';

try {

  $asana = Asana_Interface::get_client();

  /* User is authenticated for API usage. */

  $me = $asana->users->me();

  ?>
  <img src="<?php echo esc_url( $me->photo->image_128x128 ); ?>" alt="<?php echo esc_attr( $me->name ); ?> on Asana">
  <h2>You're connected, <?php echo esc_html( $me->name ); ?>!</h2>
  <p>Your Asana account is successfully connected. Completionist is able to help you get stuff done on <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?>.</p>

  <form class="ptc-asana-disconnect" method="POST">
    <div class="field-group">
      <input type="hidden" name="asana_disconnect_nonce" value="">
      <input type="submit" name="asana_disconnect" value="Deauthorize">
      <p class="disconnect-notice"><i class="fas fa-ban"></i>This will remove your Personal Access Token from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features or be recognized on tasks.</p>
    </div>
  </form>
  <?php

} catch ( \Exception $e ) {

  /* User is not authenticated for API usage. */

  ?>
  <img src="<?php echo esc_url( $ptc_completionist->plugin_url . '/assets/images/asana_logo-horizontal-color.png' ); ?>" alt="Asana Logo" title="Asana">
  <h2>Connect Asana</h2>
  <p>To use Completionist, you must first connect your Asana account.</p>

  <form class="ptc-asana-connect" method="POST">
    <div class="field-group">
      <label for="asana-pat"><i class="fas fa-key"></i>Personal Access Token:</label>
      <input id="asana-pat" name="asana_pat" type="password">
      <p class="asana-pat-info"><i class="fas fa-question"></i>You may generate an access token from <a href="https://app.asana.com/0/developer-console" target="_blank">your Asana developer console</a>. Be sure to name it something identifiable like <em>My <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?> WordPress Site</em>.</p>
    </div>
    <div class="field-group">
      <input type="hidden" name="asana_connect_nonce" value="">
      <input type="submit" name="asana_connect" value="Authorize">
    </div>
  </form>

  <div class="additional-info">
    <p class="security"><i class="fas fa-lock"></i>Personal Access Tokens authenticate access to your Asana account just like a username and password, so we encrypt it when saving.</p>
    <p class="privacy"><i class="fas fa-mask"></i>Your personal data will not be stored. Completionist only works on your behalf via Asana's API when you are logged into this site.</p>
  </div>
  <?php

}//end try catch asana client

// global $ptc_completionist;
// require_once $ptc_completionist->plugin_path . '/vendor/autoload.php';
// $asana = \Asana\Client::accessToken( '0/97990fc271a5faa778f65acacdff75dc' );

// $var = $asana->tasks->findById( '1160214767009228', [ 'opt_fields' => 'name' ] );
// echo '<pre>';
// foreach ( $var as $v ) {
//   var_dump( $v );
// }
// echo '</pre>';
