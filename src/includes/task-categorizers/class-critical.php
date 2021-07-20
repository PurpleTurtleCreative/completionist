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

defined( 'ABSPATH' ) || die();

require_once 'class-task-categorizer.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-html-builder.php';

use PTC_Completionist\Task_Categorizer;
use PTC_Completionist\HTML_Builder;

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
					'' !== $due_status
					&& 'later' !== $due_status
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
