<?php
/**
 * Create a new task in Asana and pin to the provided post, if applicable.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../class-asana-interface.php';
require_once __DIR__ . '/../class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['name'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_create_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $task = Asana_Interface::create_task( $_POST );

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully created task {$task->gid}.";
    $res['data'] = HTML_Builder::format_task_row( $task );

    /* Leave comment in Asana with pin link */

    try {

      $asana = Asana_Interface::get_client();

      $comment_text = 'I just created this task using Completionist on the ';
      $comment_text .= get_bloginfo( 'name', 'display' );
      $comment_text .= ' WordPress website, here: ';

      if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
        $comment_text .= filter_var( wp_unslash( $_SERVER['HTTP_REFERER'] ), FILTER_SANITIZE_URL );
      } else {
        $comment_text .= get_site_url();
      }

      /** This filter is documented in src/automations/class-actions.php */
      $comment_text = apply_filters( 'ptc_cmp_create_task_comment', $comment_text, 'ajax' );

      $asana->tasks->addComment( $task->gid, [ 'text' => $comment_text ] );

    } catch ( \Exception $e ) {
      $error_code = $e->getCode();
      $error_msg = $e->getMessage();
      error_log( "Failed to add comment to new task. Error $error_code: $error_msg" );
    }

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = HTML_Builder::format_error_box( $e, 'Failed to create task. ' );
}

echo json_encode( $res );
wp_die();
