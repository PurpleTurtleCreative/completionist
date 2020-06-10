<?php
/**
 * Provide HTML for multiple tasks in response to an AJAX request.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '../class-asana-interface.php';
require_once __DIR__ . '../class-options.php';
require_once __DIR__ . '../class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['task_gids'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_list_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
    && Asana_Interface::require_license()
  ) {

    $task_gids = json_decode( stripslashes( $_POST['task_gids'] ), FALSE, 2, JSON_BIGINT_AS_STRING );

    if ( ! is_array( $task_gids ) || empty( $task_gids ) ) {
      throw new \Exception( 'Invalid task_gids array.', 400 );
    }

    if ( isset( $_POST['detailed'] ) ) {
      $detailed_view = filter_var( wp_unslash( $_POST['detailed'] ), FILTER_VALIDATE_BOOLEAN );
    } else {
      $detailed_view = FALSE;
    }

    $all_tasks = Asana_Interface::maybe_get_all_site_tasks( HTML_Builder::TASK_OPT_FIELDS );

    $matched_tasks = [];
    foreach ( $all_tasks as $task ) {
      if ( isset( $task->gid ) && in_array( $task->gid, $task_gids ) ) {
        $matched_tasks[ $task->gid ] = $task;
      }
    }

    $html = '';
    foreach ( $task_gids as $t_gid ) {
      if ( isset( $matched_tasks[ $t_gid ] ) && is_a( $matched_tasks[ $t_gid ], 'stdClass' ) ) {
        $html .= HTML_Builder::format_task_row( $matched_tasks[ $t_gid ], $detailed_view );
      }
    }

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
