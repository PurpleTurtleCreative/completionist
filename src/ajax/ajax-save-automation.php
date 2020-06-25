<?php
/**
 *
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../automations/class-data.php';
require_once __DIR__ . '/../class-asana-interface.php';
require_once __DIR__ . '/../class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['automation'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_automations' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
    && Asana_Interface::require_license()
  ) {

    if ( is_array( $_POST['automation'] ) ) {
      $automation = json_decode( json_encode( $_POST['automation'] ), FALSE );
    } elseif ( is_string( $_POST['automation'] ) ) {
      $automation = json_decode( $_POST['automation'], FALSE );
    } else {
      throw new \Exception( 'Received invalid automation data object for saving.', 500 );
    }

    if ( ! is_a( $automation, '\stdClass' ) || ! isset( $automation->ID ) ) {
      throw new \Exception( 'Invalid automation data JSON for saving.', 500 );
    }

    $saved_automation = Automations\Data::save_automation( $automation );

    if (
      $automation->ID == 0
      && isset( $saved_automation->ID )
      && $saved_automation->ID > 0
    ) {
      $res['status'] = 'success';
      $res['code'] = 201;
      $res['message'] = "Successfully created new automation {$saved_automation->ID}.";
      $res['data'] = $saved_automation;
    } elseif (
      $automation->ID > 0
      && isset( $saved_automation->ID )
      && $saved_automation->ID == $automation->ID
    ) {
      $res['status'] = 'success';
      $res['code'] = 200;
      $res['message'] = "Successfully updated automation {$saved_automation->ID}.";
      $res['data'] = $saved_automation;
    } else {
      $res['status'] = 'error';
      $res['code'] = 500;
      $res['message'] = 'Something went wrong when saving the automation.';
      $res['data'] = $saved_automation;
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
