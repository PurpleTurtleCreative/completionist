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
        $task_gid = Options::sanitize( 'gid', $task->gid );
        if ( empty( $task_gid ) ) {
          error_log( 'Refused to format task row for invalid task.' );
          return '';
        }
      } else {
        error_log( 'Refused to format task row for unidentified task.' );
        return '';
      }

      /* Task URL */
      $task_url = self::get_asana_task_url( $task_gid );

      /* Assignee */
      if ( ! isset( $task->assignee->gid ) ) {
        $assignee_id = 0;
        $assignee_name = '';
        $assignee_gravatar = '';
      } else {
        $assignee_id = Asana_Interface::get_user_id_by_gid( $task->assignee->gid );
        $assignee_gravatar = get_avatar( $assignee_id, 20 );
        $user_info = get_userdata( $assignee_id );
        if (
          FALSE === $user_info
          || ! ( $user_info instanceof \WP_User )
          || empty( $user_info->display_name )
        ) {
          try {
            $asana = Asana_Interface::get_client();
            $assignee = $asana->users->findById( $task->assignee->gid, [ 'opt_fields' => 'name,photo' ] );
            if ( isset( $assignee->name ) ) {
              $assignee_name = $assignee->name;
            } else {
              $assignee_name = 'Unknown';
            }
            if ( isset( $assignee->photo->image_21x21 ) ) {
              $assignee_gravatar = '<img src="' . esc_url( $assignee->photo->image_21x21 ) . '">';
            }
          } catch ( \Exception $e ) {
            error_log( 'Failed to fetch assignee user name for display: ' . $e->getMessage() );
            $assignee_name = 'Unknown';
          }
        } else {
          $assignee_name = $user_info->display_name;
        }
      }

      /* Mark Completed */
      if ( isset( $task->completed ) && $task->completed === TRUE ) {
        $is_completed_val = 'true';
      } else {
        $is_completed_val = 'false';
      }

      /* Task Title */
      if ( isset( $task->name ) ) {
        $task_name = Options::sanitize( 'string', $task->name );
      } else {
        $task_name = '';
      }

      /* Task Description */
      if ( isset( $task->notes ) ) {
        $task_notes = Options::sanitize( 'string', $task->notes );
      } else {
        $task_notes = '';
      }

      /* Due Date */
      $due_status = 'none';
      if ( isset( $task->due_on ) ) {
        $due_date = Options::sanitize( 'date', $task->due_on );
        if ( ! empty( $due_date ) ) {
          $dt = \DateTime::createFromFormat( 'Y-m-d', $due_date );
          if ( $dt !== FALSE && array_sum( $dt::getLastErrors() ) === 0 ) {

            $dt_today = new \DateTime( date( 'Y-m-d' ) );
            $days_diff = $dt_today->diff( $dt )->days;

            if ( $dt < $dt_today && $days_diff !== 0 ) {
              $dt_string = "$days_diff days ago";
              $due_status = 'past';
            } else {
              if ( $days_diff === 0 ) {
                $dt_string = 'Today';
                $due_status = 'today';
              } elseif ( $days_diff < 7 ) {
                $dt_string = $dt->format('l');
                $due_status = 'soon';
              } else {
                $dt_string = $dt->format('M j');
                $due_status = 'later';
              }
            }

            $due_date = ( $dt_string !== FALSE ) ? $dt_string : $due_date;

          } else {
            $due_date = '';
          }
        }
      } else {
        $due_date = '';
      }

      ob_start();
      ?>
      <section class="ptc-completionist-task" data-gid="<?php echo esc_attr( $task_gid ); ?>" data-completed="<?php echo esc_attr( $is_completed_val ); ?>" data-due-status="<?php echo esc_attr( $due_status ); ?>">

        <button title="Mark Complete" class="mark-complete" type="button">
          <i class="fas fa-check"></i>
        </button>

        <div class="name">
          <?php echo esc_html( $task_name ); ?>
          <a href="<?php echo esc_url( $task_url ); ?>" target="_asana" title="View in Asana" class="view-task">
            <i class="fas fa-external-link-alt"></i>
          </a>
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

        <div class="task-actions">
          <?php if ( ! empty( $task_notes ) ) { ?>
            <button title="Notes" class="view-task-notes" type="button">
              <i class="fas fa-sticky-note"></i>
            </button>
          <?php }//end if not empty task notes ?>
          <button title="Unpin" class="unpin-task" type="button">
            <i class="fas fa-thumbtack"></i>
          </button>
          <button title="Delete" class="delete-task" type="button">
            <i class="fas fa-trash-alt"></i>
          </button>
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

    /**
     * Returns an Asana task URL.
     *
     * @since 1.0.0
     *
     * @param string $task_gid The GID of the task to link.
     *
     * @return string The URL to the task in Asana. Default ''.
     */
    static function get_asana_task_url( string $task_gid ) : string {

      $task_gid = Options::sanitize( 'gid', $task_gid );

      if ( ! empty( $task_gid ) ) {
        return "https://app.asana.com/0/0/{$task_gid}/f";
      }

      return '';

    }

  }//end class

}//end if class exists
