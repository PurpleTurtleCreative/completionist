<?php
/**
 * Critical Tasks Categorizer class
 *
 * Task Categorizer for tasks that are due in the past, today, or within 7 days.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Task_Categorizer;

use PTC_Completionist\Task_Categorizer;
use PTC_Completionist\HTML_Builder;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-task-categorizer.php';
require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';

if ( ! class_exists( __NAMESPACE__ . '\Critical' ) ) {
  /**
   * Task Categorizer for tasks that are due in the past, today, or within 7
   * days.
   */
  class Critical extends Task_Categorizer {

    public function __construct( array $tasks ) {

      parent::__construct( $tasks );

      foreach ( $tasks as $i => $task ) {
        $due_status = ( HTML_Builder::get_relative_due( $task ) )->status;
        if (
          $due_status !== ''
          && $due_status !== 'later'
        ) {
          $this->categorized_indices[] = $i;
          if ( isset( $task->completed ) && is_bool( $task->completed ) ) {
            if ( TRUE === $task->completed ) {
              ++$this->completed_count;
            } else {
              ++$this->incomplete_count;
            }
          }
        }
      }

    }

  }//end class
}//end if class_exists
