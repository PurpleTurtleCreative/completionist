<?php
/**
 * Create a new task in Asana and pin to the provided post, if applicable.
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
    isset( $_POST['name'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_create_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    if ( isset( $_POST['post_id'] ) ) {
      /* Validate for pinning */
      $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
      $the_post = get_post( $the_post_id );
      if ( NULL === $the_post ) {
        throw new \Exception( 'The provided post id is invalid.', 400 );
      }
    }

    /* Gather Input Data */

    $asana = Asana_Interface::get_client();

    $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
    if ( ! empty( $name ) ) {
      $params['name'] = $name;
    } else {
      throw new \Exception( 'A task name is required.', 400 );
    }

    if ( isset( $_POST['assignee'] ) ) {
      $assignee_gid = Options::sanitize( 'gid', $_POST['assignee'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
      if ( ! empty( $assignee_gid ) ) {
        $params['assignee'] = $assignee_gid;
      }
    }

    if ( isset( $_POST['due_on'] ) ) {
      $due_on = Options::sanitize( 'date', $_POST['due_on'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
      if ( ! empty( $due_on ) ) {
        $params['due_on'] = $due_on;
      }
    }

    if ( isset( $_POST['project'] ) ) {
      $project_gid = Options::sanitize( 'gid', $_POST['project'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
      if ( ! empty( $project_gid ) ) {
        $params['projects'] = $project_gid;
      }
    }

    if ( ! isset( $params['projects'] ) ) {
      /* A workspace is required if a project hasn't been provided. */
      $workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
      if ( ! empty( $workspace_gid ) ) {
        $params['workspace'] = $workspace_gid;
      } else {
        throw new \Exception( 'Please set a Workspace in Completionist\'s settings before creating a task.', 400 );
      }
    }

    if ( isset( $_POST['notes'] ) ) {
      $notes = sanitize_textarea_field( wp_unslash( $_POST['notes'] ) );
      if ( ! empty( $notes ) ) {
        $params['notes'] = $notes;
      }
    }

    /* Create the task */

    $task = $asana->tasks->create( $params );

    if ( ! isset( $task->gid ) ) {
      throw new Exception( 'Unrecognized API response to create task.', 409 );
    }

    if ( isset( $the_post_id ) ) {

      /* Pin the task */

      try {
        $did_pin_task = Options::save( Options::PINNED_TASK_GID, $task->gid, FALSE, $the_post_id );
      } catch ( \Exception $e ) {
        $did_pin_task = FALSE;
      }

      if ( $did_pin_task === FALSE ) {
        throw new \Exception( "Failed to pin the new task to post $the_post_id.", 201 );
      }

      $res['status'] = 'success';
      $res['code'] = 200;
      $res['message'] = "Successfully created task {$task->gid} and pinned to post $the_post_id.";
      $res['data'] = HTML_Builder::format_task_row( $task );

    } else {
      $res['status'] = 'success';
      $res['code'] = 201;
      $res['message'] = "Successfully created task: {$task->name}";
      $res['data'] = $task->gid;
    }

    /* Leave comment in Asana with pin link */

    try {

      $comment_text = 'I just created this task using Completionist on the ';
      $comment_text .= get_bloginfo( 'name', 'display' );
      $comment_text .= ' WordPress website, here: ';

      if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
        $comment_text .= filter_var( wp_unslash( $_SERVER['HTTP_REFERER'] ), FILTER_SANITIZE_URL );
      } else {
        $comment_text .= get_site_url();
      }

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
