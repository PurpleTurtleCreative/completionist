<?php
/**
 * Task Categorizer abstract class
 *
 * An abstract class for easily and efficiently iterating a set of task data
 * by custom categorization. Also provides basic category collection stats.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Task_Categorizer' ) ) {
  /**
   * An abstract class for easily and efficiently iterating a set of task data
   * by custom categorization. Also provides basic category collection stats.
   *
   * Subclasses are expected to perform categorization during construction which
   * will collect indices from the original array and keep track of counts.
   */
  abstract class Task_Categorizer implements \Iterator {

    protected $position = 0;
    protected $tasks = [];
    protected $categorized_indices = [];

    protected $completed_count = 0;
    protected $incomplete_count = 0;

    protected function __construct( array $tasks ) {
      $this->rewind();
      $this->tasks = $tasks;
    }

    final public function rewind() {
      $this->position = 0;
    }

    final public function current() {
      return $this->tasks[ $this->categorized_indices[ $this->position ] ];
    }

    final public function key() {
      return $this->position;
    }

    final public function next() {
      ++$this->position;
    }

    final public function valid() {
      return (
        isset( $this->categorized_indices[ $this->position ] )
        && isset( $this->tasks[ $this->categorized_indices[ $this->position ] ] )
      );
    }

    final public function get_total_count() : int {
      return count( $this->categorized_indices );
    }

    final public function get_completed_count() : int {
      return $this->completed_count;
    }

    final public function get_incomplete_count() : int {
      return $this->incomplete_count;
    }

    final public function get_tasks_gid_array() : array {
      $arr = [];
      foreach ( $this as $task ) {
        if ( isset( $task->gid ) ) {
          $arr[] = $task->gid;
        }
      }
      return $arr;
    }

  }//end class
}//end if class_exists
