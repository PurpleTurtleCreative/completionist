<?php
/**
 * Gets overview records for all automations in the database.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../automations/class-data.php';
require_once __DIR__ . '/../class-asana-interface.php';
require_once __DIR__ . '/../class-options.php';
require_once __DIR__ . '/../class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_automations' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
    && Asana_Interface::require_license()
  ) {

    if ( isset( $_POST['order_by'] ) ) {
      $order_by = Options::sanitize( 'string', $_POST['order_by'] );
      $automation_overviews = Automations\Data::get_automation_overviews( $order_by );
    } else {
      $automation_overviews = Automations\Data::get_automation_overviews();
    }

    $records_count = count( $automation_overviews );

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully retrieved {$records_count} automation overviews.";
    $res['data'] = $automation_overviews;

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = HTML_Builder::get_error_code( $e );
  $res['message'] = HTML_Builder::get_error_message( $e );
  $res['data'] = '';
}

echo json_encode( $res );
wp_die();
