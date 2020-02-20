<?php
/**
 * HTML Builder class
 *
 * Formats and returns common HTML template parts.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

if ( ! class_exists( __NAMESPACE__ . '\HTML_Builder' ) ) {
  /**
   * A static class to format and return common HTML template parts.
   */
  class HTML_Builder {

    /**
     * The ?opt_fields csv for Asana API requests.
     *
     * @since 1.0.0
     *
     * @var string TASK_OPT_FIELDS
     */
    const TASK_OPT_FIELDS = 'name,completed,notes,due_on,assignee';

    /**
     * Builds HTML for a task row section to be output.
     *
     * @since 1.0.0
     *
     * @param \stdClass $task An Asana task object.
     *
     * @return string The HTML. Default ''.
     */
    static function format_task_row( \stdClass $task ) : string {

      /* Task GID */
      if ( isset( $task->gid ) ) {
        $task_gid = $task->gid;
      } else {
        error_log( 'Refused to format task row for unidentified task.' );
        return '';
      }

      /* Assignee */
      if ( ! isset( $task->assignee->gid ) ) {
        $assignee_id = 0;
        $assignee_name = '';
        $assignee_gravatar = '';
      } else {
        $assignee_id = Asana_Interface::get_user_id_by_gid( $task->assignee->gid );
        $assignee_gravatar = get_avatar( $assignee_id, 20, 'mystery' );
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

      /* Mark Completed */
      if ( isset( $task->completed ) && $task->completed == 'true' ) {
        $is_completed = TRUE;
      } else {
        $is_completed = FALSE;
      }

      /* Task Title */
      if ( isset( $task->name ) ) {
        $task_name = $task->name;
      } else {
        $task_name = '';
      }

      /* Task Description */
      if ( isset( $task->notes ) ) {
        $task_notes = $task->notes;
      } else {
        $task_notes = '';
      }

      /* Due Date */
      if ( isset( $task->due_on ) ) {
        $due_date = $task->due_on;
      } else {
        $due_date = '';
      }

      ob_start();
      ?>
      <section class="ptc-completionist-task" data-task-gid="<?php echo esc_attr( $task_gid ); ?>">

        <div class="mark-complete" data-task-completed="<?php echo esc_attr( $task->completed ); ?>">
          <?php echo ( $is_completed ) ? '<i class="fas fa-check"></i>' : ''; ?>
        </div>

        <div class="name">
          <?php echo esc_html( $task_name ); ?>
          <div class="task-actions">
            <?php if ( ! empty( $task_notes ) ) { ?>
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
            <?php if ( ! empty( $due_date ) ) { ?>
              <i class="fas fa-clock"></i>
              <?php echo esc_html( $due_date ); ?>
            <?php }//end if not empty task due ?>
          </div>
        </div>

        <div class="description" style="display:none;">
          <?php echo esc_html( $task_notes ); ?>
        </div>

      </section>
      <?php
      $html = ob_get_clean();
      return ( $html !== FALSE && is_string( $html ) ) ? $html : '';
    }//end format_task_row()

    /**
     * Builds HTML for an error note box.
     *
     * @since 1.0.0
     *
     * @param \Exception $e The exception object data to output.
     *
     * @param string $context_message Optional. Text to output before the
     * exception's message. Default ''.
     *
     * @return string The HTML. Default ''.
     */
    static function format_error_box( \Exception $e, string $context_message = '' ) : string {
      ob_start();
      ?>
      <div class="note-box note-box-error">
        <i class="fas fa-times"></i>
        <p>
          <strong>Error <?php echo esc_html( $e->getCode() ); ?>.</strong>
          <br><?php echo esc_html( $context_message . $e->getMessage() ); ?>
        </p>
      </div>
      <?php
      $html = ob_get_clean();
      return ( $html !== FALSE && is_string( $html ) ) ? $html : '';
    }

  }//end class

}//end if class exists
