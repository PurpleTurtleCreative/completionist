<?php
/**
 * Unpin a task from the provided post.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';
require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['task_gid'] )
    && isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_pin_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $task_gid = Options::sanitize( 'gid', $_POST['task_gid'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $task_gid === '' ) {
      throw new \Exception( 'Invalid task gid.', 400 );
    }

    /* TODO: If no post_id isset, then request to untag the task */

    $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $the_post_id < 1 ) {
      throw new \Exception( 'Invalid post identifier.', 400 );
    }

    try {
      $did_unpin_task = Options::delete( Options::PINNED_TASK_GID, $the_post_id, $task_gid );
    } catch ( \Exception $e ) {
      $did_unpin_task = FALSE;
    }

    if ( $did_unpin_task === FALSE ) {
      throw new \Exception( "Failed to unpin the task $task_gid from post $the_post_id.", 409 );
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully unpinned task $task_gid from post $the_post_id.";
    $res['data'] = $task_gid;

    /* Remove site tag */

    try {
      $asana = Asana_Interface::get_client();
      $asana->tasks->removeTag( $task_gid, [ 'tag' => Options::get( Options::ASANA_TAG_GID ) ] );
    } catch ( \Exception $e ) {
      error_log( HTML_Builder::format_error_string( $e, 'Failed to untag an unpinned task.' ) );
    }

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = HTML_Builder::format_error_box( $e, 'Failed to unpin task. ' );
}

echo json_encode( $res );
wp_die();
