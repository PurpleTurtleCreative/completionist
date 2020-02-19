<?php
/**
 * Display task listing in response to an AJAX request.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

try {
  if (
    isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_list_tasks' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput

    $pinned_task_gids = Options::get( Options::PINNED_TASK_GID, $the_post_id );

    if ( is_array( $pinned_task_gids ) && ! empty( $pinned_task_gids ) ) {
      foreach ( $pinned_task_gids as $task_gid ) {
        maybe_display_task_row( $task_gid, $the_post_id );
      }
    } else {
      echo '<p><i class="fas fa-clipboard-check"></i>There are no pinned tasks!</p>';
    }

  } else {
    throw new \Exception( 'Invalid submission.' );
  }
} catch ( \Exception $e ) {
  echo '<p>Error: ' . esc_html( $e->getMessage() ) . '</p>';
}

wp_die();

/* HELPERS */

function maybe_display_task_row( string $task_gid, int $post_id ) : void {

  $task_gid = Options::sanitize( 'gid', $task_gid );
  if ( empty( $task_gid ) ) {
    return;
  }

  /* Get the task */
  try {
    $asana = Asana_Interface::get_client();
    $task = $asana->tasks->findById( $task_gid, [ 'opt_fields' => 'name,completed,notes,due_on,assignee' ] );
  } catch ( \Exception $e ) {
    $error_code = $e->getCode();
    $error_msg = $e->getMessage();
    if (
      'Not Found' === $error_msg
      || 404 == $error_code
    ) {
      if ( Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
        error_log( "Deleted [404: Not Found] pinned task on post $post_id." );
      }
    } elseif (
      'Forbidden' !== $error_msg
    ) {
      error_log( "Failed to fetch task data for display with error $error_code: $error_msg" );
    }
    return;
  }

  /* Get the assignee, if applicable */
  if ( NULL === $task->assignee ) {
    $assignee_id = 0;
    $assignee_name = '';
    $assignee_gravatar = '';
  } else {
    $assignee_id = Asana_Interface::get_user_id_by_gid( $task->assignee->gid );
    $assignee_gravatar = get_avatar( $assignee_id, 30, 'mystery' );
    $user_info = get_userdata( $assignee_id );
    if (
      FALSE === $user_info
      || ! ( $user_info instanceof \WP_User )
      || empty( $user_info->display_name )
    ) {
      try {
        $asana = Asana_Interface::get_client();
        $assignee = $asana->users->findById( $task->assignee->gid, [ 'opt_fields' => 'name' ] );
        $assignee_name = $assignee->name;
      } catch ( \Exception $e ) {
        error_log( 'Failed to fetch assignee user name for display: ' . $e->getMessage() );
        $assignee_name = 'Unknown';
      }
    } else {
      $assignee_name = $user_info->display_name;
    }
  }

  ?>
  <div class="ptc-completionist-task" data-task-gid="<?php echo esc_attr( $task_gid ); ?>">

    <div class="mark-complete" data-task-completed="<?php echo esc_attr( $task->completed ); ?>">
      <?php echo ( $task->completed == 'true' ) ? '<i class="fas fa-check"></i>' : ''; ?>
    </div>

    <div class="name">
      <?php echo esc_html( $task->name ); ?>
      <div class="task-actions">
        <?php if ( ! empty( $task->notes ) ) { ?>
          <button title="View task description" class="view-task-notes" type="button">
            <i class="fas fa-sticky-note"></i>
          </button>
        <?php }//end if not empty task notes ?>
        <button title="Unpin this task" class="unpin-task" type="button" data-task-gid="<?php echo esc_attr( $task_gid ); ?>">
          <i class="fas fa-thumbtack"></i>
        </button>
        <button title="Unpin this task and delete in Asana" class="delete-task" type="button" data-task-gid="<?php echo esc_attr( $task_gid ); ?>">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    </div>

    <div class="details">
      <div class="assignee">
        <?php echo $assignee_gravatar;//phpcs:ignore WordPress.Security.EscapeOutput ?>
        <?php echo esc_html( $assignee_name ); ?>
      </div>
      <div class="due">
        <?php if ( ! empty( $task->due_on ) ) { ?>
          <i class="fas fa-clock"></i>
          <?php echo esc_html( $task->due_on ); ?>
        <?php }//end if not empty task due ?>
      </div>
    </div>

    <div class="description">
      <?php
      if ( ! empty( $task->notes ) ) {
        echo esc_html( $task->notes );
      }//end if not empty task notes
      ?>
    </div>

  </div>
  <?php
}
