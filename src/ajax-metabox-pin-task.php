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
  ) {

    $task_gid = Asana_Interface::get_task_gid_from_task_link( $_POST['task_link'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $task_gid === '' ) {
      throw new \Exception('Failed to get task from the submitted task link.');
    }

    $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $the_post_id < 1 ) {
      throw new \Exception('Invalid post identifier.');
    }

    try {
      $did_pin_task = Options::save( Options::PINNED_TASK_GID, $task_gid, FALSE, $the_post_id );
    } catch ( \Exception $e ) {
      $did_pin_task = FALSE;
    }

    if ( $did_pin_task === FALSE ) {
      throw new \Exception("Failed to pin the existing task to post $the_post_id.");
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully pinned task $task_gid to post $the_post_id.";
    $res['data'] = $task_gid;

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = HTML_Builder::format_error_box( $e, 'Failed to pin task. ' );
}

echo json_encode( $res );
wp_die();
