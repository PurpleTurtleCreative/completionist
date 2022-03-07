<?php
/**
 * Automations Actions class
 *
 * Performs Automation actions.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Automations;

defined( 'ABSPATH' ) || die();

require_once 'class-fields.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-html-builder.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-options.php';

use \PTC_Completionist\Asana_Interface;
use \PTC_Completionist\HTML_Builder;
use \PTC_Completionist\Options;

if ( ! class_exists( __NAMESPACE__ . '\Actions' ) ) {
	/**
	 * Static class for executing automation actions.
	 */
	class Actions {

		public const ACTION_OPTIONS = [
			'create_task' => 'Create Asana Task',
		];

		public const ACTION_META_OPTIONS = [
			'create_task' => [
				'task_author' => 'Creator',
				'name' => 'Title',
				'post_id' => 'Post Pin',
				'assignee' => 'Assignee',
				'due_on' => 'Due Date',
				'project' => 'Project',
				'notes' => 'Description',
			],
		];

		/**
		 * Evaluates an automation condition entry using the provided objects.
		 *
		 * @since 1.1.0
		 *
		 * @see \PTC_Completionist\Automation::get_actions() To get an automation
		 * action object with meta.
		 *
		 * @param \stdClass $action_with_meta The action object to perform.
		 * @param object[] $translation_objects An array of objects to translate
		 * field values. The object type must match the object key as follows:
		 * * 'post' => \WP_Post
		 * * 'user' => \WP_User
		 */
		public static function run_action( \stdClass $action_with_meta, array $translation_objects ) : bool {

			switch ( $action_with_meta->action ) {
				case 'create_task':
					return self::create_task( $action_with_meta, $translation_objects );
					break;
				default:
					error_log( 'Failed to run automation action with invalid action key.' );
					return false;
					break;
			}

			return false;
		}

		/**
		 * Creates a new task in Asana with an automation comment.
		 *
		 * @since 1.1.0
		 *
		 * @param \stdClass $action_with_meta The action object with meta data for
		 * task creation. Accepted meta keys are:
		 * * 'task_author' => (int) Required. WordPress user ID that will authorize
		 * the task to be created in Asana.
		 * * 'name' => (string) Required. The task title.
		 * * 'post_id' => (int) The WordPress post ID on which to pin the new task.
		 * * 'assignee' => (gid) The assignee's Asana gid.
		 * * 'due_on' => (date string) The task due date.
		 * * 'project' => (gid) The Asana project gid to house the task.
		 * * 'notes' => (string) The task description.
		 * @param object[] $translation_objects An array of objects to translate
		 * field values. The object type must match the object key as follows:
		 * * 'post' => \WP_Post
		 * * 'user' => \WP_User
		 * @return bool If the task was successfully created.
		 */
		private static function create_task( \stdClass $action_with_meta, array $translation_objects ) : bool {

			try {

				if (
					! isset( $action_with_meta->meta['task_author'] )
					|| ! isset( $action_with_meta->meta['name'] )
				) {
					throw new \Exception( 'A task author and title are required. Please review Completionist Automation {$action_with_meta->ID}.', 409 );
				}

				$action_with_meta->meta['name'] = Fields::translate_templates( $action_with_meta->meta['name'] ?? '', $translation_objects );
				$action_with_meta->meta['notes'] = Fields::translate_templates( $action_with_meta->meta['notes'] ?? '', $translation_objects );

				$task = Asana_Interface::create_task( $action_with_meta->meta, (string) $action_with_meta->meta['task_author'] );

				/* Leave comment */

				try {

					$asana = Asana_Interface::get_client( (string) $action_with_meta->meta['task_author'] );

					$comment_text = sprintf(
						'This task was automatically created using Completionist on the %s WordPress website, %s',
						get_bloginfo( 'name', 'display' ),
						get_site_url()
					);

					/**
					 * Filters the comment added to new Asana tasks.
					 *
					 * @since 1.1.0
					 *
					 * @param string $comment_text The plain text string to comment on the new task.
					 * @param string $context The context of this filter, 'automation' or 'ajax'.
					 */
					$comment_text = apply_filters( 'ptc_cmp_create_task_comment', $comment_text, 'automation' );
					if ( $comment_text ) {
						$asana->tasks->addComment( $task->gid, [ 'text' => $comment_text ] );
					}
				} catch ( \Exception $e ) {
					error_log( HTML_Builder::format_error_string( $e, 'Failed to add comment to new task.' ) );
				}
			} catch ( \Exception $e ) {
				error_log( HTML_Builder::format_error_string( $e, "Failed to run create_task Automation Action {$action_with_meta->ID}." ) );
				return false;
			}

			return true;
		}//end create_task()
	}//end class
}//end if class_exists
