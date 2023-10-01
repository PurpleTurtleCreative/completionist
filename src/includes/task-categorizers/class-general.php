<?php
/**
 * Task Categorizer: General class
 *
 * Task Categorizer for tasks that are NOT pinned on the site.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Task_Categorizer;

defined( 'ABSPATH' ) || die();

use PTC_Completionist\Task_Categorizer;
use PTC_Completionist\Options;

/**
 * Task Categorizer for tasks that are NOT pinned on the site.
 */
class General extends Task_Categorizer {

	public function __construct( array $tasks ) {

		parent::__construct( $tasks );

		foreach ( $tasks as $i => $task ) {
			if (
				isset( $task->gid )
				&& ! Options::postmeta_exists( Options::PINNED_TASK_GID, $task->gid )
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
	}//end __construct()
}//end class
