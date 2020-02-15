<?php
/**
 * Pin an existing task or a newly created task to the provided post.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

$data['status'] = 'error';
$data['data'] = 'Invalid submission.';

try {

  if (
    isset( $_POST['task_link'] )
    && isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_pinned_tasks' ) !== FALSE
  ) {

    /* PIN AN EXISTING TASK */

    $task_gid = Asana_Interface::get_task_gid_from_task_link( $_POST['task_link'] );

    if ( $task_gid === '' ) {
      throw new \Exception('Failed to get task from the submitted task link.');
    }

    $post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );

    if ( $post_id < 1 ) {
      throw new \Exception('Invalid post identifier.');
    }

    try {
      $did_pin_task = Options::save( Options::PINNED_TASK_GID, $task_gid, FALSE, $post_id );
    } catch ( \Exception $e ) {
      $did_pin_task = FALSE;
    }

    if ( $did_pin_task === FALSE ) {
      throw new \Exception("Failed to pin the existing task to post $post_id.");
    }

    $data['status'] = 'success';
    $data['data'] = "Successfully pinned task $task_gid to post $post_id.";

  }

} catch ( \Exception $e ) {
  $data['status'] = 'fail';
  $data['data'] = $e->getMessage();
}

echo json_encode( $data );
wp_die();
