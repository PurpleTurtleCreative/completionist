<?php
/**
 * My_Tasks Tasks Categorizer class
 *
 * Task Categorizer for tasks that are assigned to the current user.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Task_Categorizer;

defined( 'ABSPATH' ) || die();

require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/task-categorizers/class-task-categorizer.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-options.php';

use PTC_Completionist\Task_Categorizer;
use PTC_Completionist\Options;

if ( ! class_exists( __NAMESPACE__ . '\My_Tasks' ) ) {
	/**
	 * Task Categorizer for tasks that are assigned to the current user.
	 */
	class My_Tasks extends Task_Categorizer {

		public function __construct( array $tasks ) {

			parent::__construct( $tasks );

			$my_gid = Options::get( Options::ASANA_USER_GID );

			foreach ( $tasks as $i => $task ) {
				if (
					isset( $task->assignee->gid )
					&& $task->assignee->gid === $my_gid
				) {
					$this->categorized_indices[] = $i;
					if ( isset( $task->completed ) && is_bool( $task->completed ) ) {
						if ( true === $task->completed ) {
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
