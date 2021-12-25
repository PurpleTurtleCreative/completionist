<?php
/**
 * Pin an existing task to the provided post.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['task_link'] )
    && isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $site_tag_gid = Options::get( Options::ASANA_TAG_GID );
    if ( '' === $site_tag_gid ) {
      throw new \Exception( 'A site tag is required to pin tasks. Please set a site tag in Completionist\'s settings.', 409 );
    }

    $task_gid = Asana_Interface::get_task_gid_from_task_link( $_POST['task_link'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $task_gid === '' ) {
      throw new \Exception( 'Failed to get task from the submitted task link.', 400 );
    }

    $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $the_post_id < 1 ) {
      throw new \Exception( 'Invalid post identifier.', 400 );
    }

    if ( ! Asana_Interface::is_workspace_task( $task_gid ) ) {
      throw new \Exception( 'Task does not belong to this site\'s assigned workspace.', 409 );
    }

    if ( Options::postmeta_exists( Options::PINNED_TASK_GID, $task_gid, $the_post_id ) ) {
      throw new \Exception( "Task is already pinned to post $the_post_id.", 409 );
    }

    try {
      $did_pin_task = Options::save( Options::PINNED_TASK_GID, $task_gid, FALSE, $the_post_id );
    } catch ( \Exception $e ) {
      $did_pin_task = FALSE;
    }

    if ( $did_pin_task === FALSE ) {
      throw new \Exception( "Failed to pin the existing task to post $the_post_id.", 409 );
    }
    // @TODO - Else, update the cache on success.

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

      Asana_Interface::tag_and_comment( $task_gid, $site_tag_gid, $comment_text );

    } catch ( \Exception $e ) {
      error_log( HTML_Builder::format_error_string( $e, 'Failed to tag and comment pinned task.' ) );
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
