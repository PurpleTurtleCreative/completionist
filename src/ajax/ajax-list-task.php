<?php
/**
 * Provide task HTML in response to an AJAX request.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../class-asana-interface.php';
require_once __DIR__ . '/../class-options.php';
require_once __DIR__ . '/../class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['task_gid'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_list_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $task_gid = Options::sanitize( 'gid', $_POST['task_gid'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput

    if ( isset( $_POST['post_id'] ) ) {
      $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
      if ( $the_post_id < 1 ) {
        throw new \Exception( 'Invalid post identifier.', 400 );
      }
    } else {
      $the_post_id = Options::get_task_pin_post_id( $task_gid );
    }

    if ( isset( $_POST['detailed'] ) ) {
      $detailed_view = filter_var( wp_unslash( $_POST['detailed'] ), FILTER_VALIDATE_BOOLEAN );
    } else {
      $detailed_view = FALSE;
    }

    $task = Asana_Interface::maybe_get_task_data( $task_gid, HTML_Builder::TASK_OPT_FIELDS, $the_post_id );
    $html = HTML_Builder::format_task_row( $task, $detailed_view );

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = 'Task data was retrieved';
    $res['data'] = $html;

  } else {
    throw new \Exception( 'Invalid submission', 400 );
  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $html = '';

  if (
    $e->getCode() > 400
    && $e->getCode() != 403
    && $e->getCode() != 410
  ) {
    // 400 Bad Request is the developer's fault, not the user's
    // 403 Forbidden is expected when a private task is pinned
    // 410 Unpinned is expected when a task was automatically unpinned
    $html = HTML_Builder::format_error_box( $e, 'Failed to load task. ' );
  }

  $res['data'] = $html;
}

echo json_encode( $res );
wp_die();
