<?php
/**
 * Pin an existing task to the provided post.
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
    isset( $_POST['task_link'] )
    && isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_pin_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $task_gid = Asana_Interface::get_task_gid_from_task_link( $_POST['task_link'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $task_gid === '' ) {
      throw new \Exception('Failed to get task from the submitted task link.');
    }

    $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $the_post_id < 1 ) {
      throw new \Exception('Invalid post identifier.');
    }

    if ( Options::postmeta_exists( Options::PINNED_TASK_GID, $task_gid, $the_post_id ) ) {
      throw new \Exception( "Task $task_gid is already pinned to post $the_post_id.", 409 );
    }

    try {
      $did_pin_task = Options::save( Options::PINNED_TASK_GID, $task_gid, FALSE, $the_post_id );
    } catch ( \Exception $e ) {
      $did_pin_task = FALSE;
    }

    if ( $did_pin_task === FALSE ) {
      throw new \Exception( "Failed to pin the existing task to post $the_post_id.", 409 );
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully pinned task $task_gid to post $the_post_id.";
    $res['data'] = $task_gid;

    /* Leave comment in Asana with pin link */

    try {

      $comment_text = 'I just pinned this task using Completionist on the ';
      $comment_text .= get_bloginfo( 'name', 'display' );
      $comment_text .= ' WordPress website, here: ';

      if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
        $comment_text .= filter_var( wp_unslash( $_SERVER['HTTP_REFERER'] ), FILTER_SANITIZE_URL );
      } else {
        $comment_text .= get_site_url();
      }

      $asana = Asana_Interface::get_client();
      $asana->tasks->addComment( $task_gid, [ 'text' => $comment_text ] );

    } catch ( \Exception $e ) {
      $error_code = $e->getCode();
      $error_msg = $e->getMessage();
      error_log( "Failed to add comment to pinned task. Error $error_code: $error_msg" );
    }

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = HTML_Builder::format_error_box( $e, 'Failed to pin task. ' );
}

echo json_encode( $res );
wp_die();
