<?php
/**
 * Pinned Tasks Categorizer class
 *
 * Task Categorizer for tasks that are pinned on the site.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Task_Categorizer;

use PTC_Completionist\Task_Categorizer;
use PTC_Completionist\Options;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-task-categorizer.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

if ( ! class_exists( __NAMESPACE__ . '\Pinned' ) ) {
  /**
   * Task Categorizer for tasks that are pinned on the site.
   */
  class Pinned extends Task_Categorizer {

    public function __construct( array $tasks ) {

      parent::__construct( $tasks );

      foreach ( $tasks as $i => $task ) {
        if (
          isset( $task->gid )
          && Options::postmeta_exists( Options::PINNED_TASK_GID, $task->gid )
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
