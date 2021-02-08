<?php
/**
 * Deletes an automation from the database.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/automations/class-data.php';
require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['ID'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_automations' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $automation_id = (int) Options::sanitize( 'gid', $_POST['ID'] );

    if ( Automations\Data::delete_automation( $automation_id ) ) {
      $res['status'] = 'success';
      $res['code'] = 200;
      $res['message'] = "Successfully deleted automation {$automation_id}.";
      $res['data'] = $automation_id;
    } else {
      throw new \Exception( "Failed to delete automation {$automation_id}.", 409 );
    }

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = HTML_Builder::get_error_code( $e );
  $res['message'] = HTML_Builder::get_error_message( $e );
  $res['data'] = '';
}

echo json_encode( $res );
wp_die();
