<?php
/**
 * Update a task in Asana using provided parameters.
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
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_update_task' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
    && Asana_Interface::require_license()
  ) {

    $task_gid = Options::sanitize( 'gid', $_POST['task_gid'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( $task_gid === '' ) {
      throw new \Exception( 'Invalid task gid.', 400 );
    }

    $asana = Asana_Interface::get_client();

    /* Gather Input Data */

    $params = [];

    if ( isset( $_POST['completed'] ) ) {
      $completed = filter_var( wp_unslash( $_POST['completed'] ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
      if ( ! is_null( $completed ) ) {
        $params['completed'] = $completed;
      }
    }

    if ( isset( $_POST['name'] ) ) {
      $name = sanitize_text_field( wp_unslash( $_POST['name'] ) );
      if ( ! empty( $name ) ) {
        $params['name'] = $name;
      }
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

    if ( isset( $_POST['notes'] ) ) {
      $notes = sanitize_textarea_field( wp_unslash( $_POST['notes'] ) );
      if ( ! empty( $notes ) ) {
        $params['notes'] = $notes;
      }
    }

    /* Update the task */

    if ( empty( $params ) ) {
      throw new \Exception( 'No valid parameters were supplied for task update.', 400 );
    }

    $task = $asana->tasks->update( $task_gid, $params );

    if ( ! isset( $task->gid ) ) {
      throw new Exception( 'Unrecognized API response to task update request.', 409 );
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Successfully updated task {$task->gid}.";
    $res['data'] = HTML_Builder::format_task_row( $task );

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = HTML_Builder::format_error_box( $e, 'Failed to update task. ' );
}

echo json_encode( $res );
wp_die();
