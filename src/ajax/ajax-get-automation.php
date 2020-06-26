<?php
/**
 * Gets an automation record object from the database.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../automations/class-automation.php';
require_once __DIR__ . '/../class-asana-interface.php';
require_once __DIR__ . '/../class-options.php';
require_once __DIR__ . '/../class-html-builder.php';

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
    && Asana_Interface::require_license()
  ) {

    $automation_id = (int) Options::sanitize( 'gid', $_POST['ID'] );
    $automation = ( new Automation( $automation_id ) )->to_stdClass();

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully retrieved automation {$automation->ID}.";
    $res['data'] = $automation;

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = HTML_Builder::get_error_code( $e );
  $res['message'] = HTML_Builder::get_error_message( $e );
  $res['data'] = '';
}

echo json_encode( $res );
wp_die();
