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

require_once 'class-asana-interface.php';
require_once 'class-options.php';

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
		public const TASK_OPT_FIELDS = 'name,completed,notes,due_on,assignee';

		/**
		 * Builds HTML for a task row section to be output.
		 *
		 * @since 1.0.0
		 *
		 * @param \stdClass $task An Asana task object.
		 * @param bool $detailed_view Optional. If to display additional elements.
		 * Default false for basic view.
		 * @return string The HTML. Default ''.
		 */
		public static function format_task_row( \stdClass $task, bool $detailed_view = false ) : string {

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
					false === $user_info
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
			if ( isset( $task->completed ) && true === $task->completed ) {
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
			$relative_due = self::get_relative_due( $task );
			$due_status = $relative_due->status;
			$due_date = $relative_due->label;

			$task_action_link = self::get_task_action_link( $task_gid );
			$cta_button_link = $task_action_link['href'];
			$cta_button_label = $task_action_link['label'];
			$cta_button_target = $task_action_link['target'];

			ob_start();
			?>
			<section class="ptc-completionist-task" data-gid="<?php echo esc_attr( $task_gid ); ?>" data-completed="<?php echo esc_attr( $is_completed_val ); ?>" data-due-status="<?php echo esc_attr( $due_status ); ?>">

				<button title="Mark Complete" class="mark-complete" type="button">
					<i class="fas fa-check"></i>
				</button>

				<div class="body">

					<div class="name">
						<?php echo esc_html( $task_name ); ?>
						<?php if ( ! empty( $task_notes ) ) { ?>
							<i class="far fa-sticky-note"></i>
						<?php }//end if not empty task notes ?>
					</div>

					<div class="details">
						<?php if ( ! empty( $assignee_name ) ) { ?>
						<div class="assignee">
							<?php echo $assignee_gravatar;//phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $assignee_name ); ?>
						</div>
						<?php }//end if not empty assignee ?>
						<?php if ( ! empty( $due_date ) ) { ?>
						<div class="due">
								<i class="fas fa-clock"></i>
								<?php echo esc_html( $due_date ); ?>
						</div>
						<?php }//end if not empty task due ?>
					</div>

					<?php if ( ! empty( $task_notes ) ) { ?>
					<div class="description">
						<?php echo esc_html( $task_notes ); ?>
					</div>
					<?php }//end if not empty task notes ?>

					<div class="task-actions">
						<a href="<?php echo esc_url( $task_url ); ?>" target="_asana">
							<button title="View in Asana" class="view-task" type="button">
								<i class="fas fa-link"></i>
							</button>
						</a>
						<button title="Unpin" class="unpin-task" type="button">
							<i class="fas fa-thumbtack"></i>
						</button>
						<button title="Delete" class="delete-task" type="button">
							<i class="fas fa-minus"></i>
						</button>
					</div>

				</div>

				<?php if ( $detailed_view ) { ?>
				<div class="cta-button">
					<a href="<?php echo esc_url( $cta_button_link ); ?>" target="<?php echo esc_attr( $cta_button_target ); ?>">
						<?php echo esc_html( $cta_button_label ); ?>
						<i class="fas fa-long-arrow-alt-right"></i>
					</a>
				</div>
				<?php }//end if detailed view ?>

			</section>
			<?php
			$html = ob_get_clean();
			return ( false !== $html && is_string( $html ) ) ? $html : '';
		}//end format_task_row()

		/**
		 * Builds HTML for an error note box.
		 *
		 * @since 3.1.1 Added optional parameter $show_dismiss_button.
		 * @since 1.0.0
		 *
		 * @param \Exception $e The exception object data to output.
		 * @param string $context_message Optional. Text to output before the
		 * exception's message. Default ''.
		 * @param bool $show_dismiss_button Optional. If a dismiss button for the
		 * note box should be displayed. Default true.
		 * @return string The HTML. Default ''.
		 */
		public static function format_error_box( \Exception $e, string $context_message = '', bool $show_dismiss_button = true ) : string {

			$code = $e->getCode();
			if (
				0 === $code
				&& isset( $e->status )
				&& $e->status > 0
			) {
				$code = $e->status;
			}

			ob_start();
			?>
			<div class="note-box note-box-error">
				<p>
					<strong>Error <?php echo esc_html( $code ); ?>.</strong>
					<br>
					<?php echo Options::sanitize( 'html', $context_message . $e->getMessage() ); ?>
				</p>
				<?php if ( true === $show_dismiss_button ) : ?>
				<div class="note-box-dismiss">
					<i class="fas fa-times"></i>
				</div>
				<?php endif; ?>
			</div>
			<?php
			$html = ob_get_clean();
			return ( false !== $html && is_string( $html ) ) ? $html : '';
		}

		/**
		 * Builds HTML for a note-box cta link.
		 *
		 * @since 1.0.0
		 *
		 * @param string $cta_link The link's [href].
		 * @param string $cta_text The link's text.
		 * @return string The HTML, an anchor tag element.
		 */
		public static function format_note_box_cta_button( string $cta_link, string $cta_text ) : string {
			return sprintf(
				'<a class="note-box-cta" href="%s">%s<i class="fas fa-long-arrow-alt-right"></i></a>',
				esc_url( $cta_link ),
				esc_html( $cta_text )
			);
		}

		/**
		 * Formats Exception data into a plain-text string.
		 *
		 * @since 1.0.0
		 *
		 * @param \Exception $e The Exception object.
		 * @param string $context_message Optional. Text to output before the
		 * exception's message. Default ''.
		 * @return string The formatted string containing the code and message.
		 */
		public static function format_error_string( \Exception $e, string $context_message = '' ) : string {

			$code = self::get_error_code( $e );
			$msg = self::get_error_message( $e );

			if ( '' === $context_message ) {
				return "Error $code: $msg";
			}

			return "Error $code: $context_message $msg";
		}

		/**
		 * Gets the HTTP error code from an Exception.
		 *
		 * @since 1.0.0
		 *
		 * @param \Exception $e The Exception object.
		 * @return int The HTTP code if there is one, else the Exception's code.
		 */
		public static function get_error_code( \Exception $e ) : int {

			$code = $e->getCode();

			if (
				0 === $code
				&& isset( $e->status )
				&& $e->status > 0
			) {
				$code = $e->status;
			}

			return (int) $code;
		}

		/**
		 * Gets the full error message from an Exception.
		 *
		 * @since 1.0.0
		 *
		 * @param \Exception $e The Exception object.
		 * @return string The full error message.
		 */
		public static function get_error_message( \Exception $e ) : string {

			$msg = $e->getMessage();

			if (
				isset( $e->response->body->errors )
				&& ! empty( $e->response->body->errors )
				&& is_array( $e->response->body->errors )
			) {
				if ( count( $e->response->body->errors ) > 1 ) {
					$msg = json_encode( $e->response->body->errors );
				} elseif ( isset( $e->response->body->errors[0]->message ) ) {
					$msg = $e->response->body->errors[0]->message;
				}
			}

			return $msg;
		}

		/**
		 * Gets an Asana task URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $task_gid The GID of the task to link.
		 * @return string The URL to the task in Asana. Default ''.
		 */
		public static function get_asana_task_url( string $task_gid ) : string {

			$task_gid = Options::sanitize( 'gid', $task_gid );

			if ( ! empty( $task_gid ) ) {
				return "https://app.asana.com/0/0/{$task_gid}/f";
			}

			return '';
		}

		/**
		 * Gets the task action link information.
		 *
		 * @since 3.1.0
		 *
		 * @param string $task_gid The Asana task GID.
		 */
		public static function get_task_action_link( string $task_gid ) : array {

			$task_action_link = [
				'href' => '',
				'label' => '',
				'target' => '_self',
				'post_id' => 0,
			];

			// Get first pinned post, if applicable.
			$post_id = Options::get_task_pin_post_id( $task_gid );
			if ( $post_id > 0 ) {
				$post = get_post( $post_id );
				if ( isset( $post->post_type ) ) {
					$edit_post_link = get_edit_post_link( $post, 'raw' );
					if ( $edit_post_link ) {
						$task_action_link['href'] = $edit_post_link;
						$task_action_link['post_id'] = $post_id;
						$post_type_obj = get_post_type_object( $post->post_type );
						if (
							$post_type_obj
							&& isset( $post_type_obj->labels->singular_name )
							&& ! empty( $post_type_obj->labels->singular_name )
						) {
							$task_action_link['label'] = "Edit {$post_type_obj->labels->singular_name}";
						} else {
							$task_action_link['label'] = "Edit {$post->post_type}";
						}
					}
				}
			}

			// Use Asana task link if no pinned post.
			if ( empty( $task_action_link['href'] ) || empty( $task_action_link['label'] ) ) {
				$task_action_link = [
					'href' => self::get_asana_task_url( $task_gid ),
					'label' => 'View in Asana',
					'target' => '_asana',
				];
			}

			return $task_action_link;
		}

		/**
		 * Gets an Asana tag list URL.
		 *
		 * @since 1.0.0
		 *
		 * @param string $tag_gid Optional. The Asana tag gid. Default '' to use
		 * the site tag.
		 * @return string The Asana link. Default ''.
		 */
		public static function get_asana_tag_url( string $tag_gid = '' ) : string {

			if ( '' === $tag_gid ) {
				$tag_gid = Options::get( Options::ASANA_TAG_GID );
			} else {
				$tag_gid = Options::sanitize( 'gid', $tag_gid );
			}

			if ( ! empty( $tag_gid ) ) {
				return "https://app.asana.com/0/{$tag_gid}/list";
			}

			return '';
		}

		/**
		 * Gets various data for a task's 'due_on' relativity to today's date.
		 *
		 * @since 1.1.0 Removed returned object member 'days'.
		 * @since 1.0.0
		 *
		 * @param \stdClass $task A task object with set 'due_on' member value.
		 * @return \stdClass A standard object containing a label and status.
		 * Object members are:
		 * * `label`: A human-readable string for the relative time
		 * * `status`: {'past','today','soon','later'}
		 */
		public static function get_relative_due( \stdClass $task ) : \stdClass {

			// TODO: just pass a string, don't require using a task object...

			$relative_due = new \stdClass();
			$relative_due->label = '';
			$relative_due->status = '';

			if ( isset( $task->due_on ) ) {
				$due_date = Options::sanitize( 'date', $task->due_on );
				if ( ! empty( $due_date ) ) {
					$dt = \DateTime::createFromFormat( 'Y-m-d', $due_date );
					if ( false !== $dt && 0 === array_sum( $dt::getLastErrors() ) ) {

						$dt_today = new \DateTime( 'today' );
						$dt->setTime( 0, 0 );
						$dt_today->setTime( 0, 0 );
						$days_diff = $dt_today->diff( $dt )->days;

						if ( $dt < $dt_today && 0 !== $days_diff ) {

							if ( 1 === $days_diff ) {
								$dt_string = 'Yesterday';
							} else {
								$dt_string = human_time_diff( $dt->getTimestamp() ) . ' ago';
							}

							$relative_due->status = 'past';

						} else {

							if ( 0 === $days_diff ) {

								$dt_string = 'Today';
								$relative_due->status = 'today';

							} elseif ( $days_diff < 7 ) {

								if ( 1 === $days_diff ) {
									$dt_string = 'Tomorrow';
								} else {
									$dt_string = $dt->format( 'l' );
								}
								$relative_due->status = 'soon';

							} else {

								$dt_string = $dt->format( 'M j' );
								$relative_due->status = 'later';

							}
						}

						$due_date = ( false !== $dt_string ) ? $dt_string : $due_date;

					} else {
						$due_date = '';
					}
				}
			} else {
				$due_date = '';
			}

			$relative_due->label = $due_date;

			return $relative_due;
		}

		/**
		 * Sorts task objects from soonest to latest date due.
		 *
		 * @since 1.0.0
		 *
		 * @param array $tasks The task objects to sort by 'due_on'.
		 * @return array The sorted task objects on success. Default empty array.
		 */
		public static function sort_tasks_by_due( array $tasks ) : array {

			if ( empty( $tasks ) ) {
				error_log( 'Failed to sort tasks by due date: no tasks provided.' );
				return [];
			}

			$success = usort( $tasks, function( $a, $b ) {

				$a_unix = PHP_INT_MAX;

				if ( isset( $a->due_on ) ) {
					$a_due_date = Options::sanitize( 'date', $a->due_on );
					if ( ! empty( $a_due_date ) ) {
						$a_dt = \DateTime::createFromFormat( 'Y-m-d', $a_due_date );
						if ( false !== $a_dt || 0 === array_sum( $a_dt::getLastErrors() ) ) {
							$a_dt->setTime( 0, 0 );
							$a_unix = $a_dt->getTimestamp();
						}
					}
				}

				$b_unix = PHP_INT_MAX;

				if ( isset( $b->due_on ) ) {
					$b_due_date = Options::sanitize( 'date', $b->due_on );
					if ( ! empty( $b_due_date ) ) {
						$b_dt = \DateTime::createFromFormat( 'Y-m-d', $b_due_date );
						if ( false !== $b_dt || 0 === array_sum( $b_dt::getLastErrors() ) ) {
							$b_dt->setTime( 0, 0 );
							$b_unix = $b_dt->getTimestamp();
						}
					}
				}

				return ( $a_unix - $b_unix );
			} );

			if ( $success ) {
				return $tasks;
			}

			error_log( 'Failed to sort tasks by due date.' );
			return [];
		}

		/**
		 * Replaces inline Asana attachment URLs with local API
		 * endpoints for retrieval.
		 *
		 * @since [unreleased]
		 *
		 * @param string   $html The HTML content to search and replace.
		 * @param int      $post_id The post ID to associate the API
		 * request token.
		 * @param int      $auth_user Optional. The WordPress user to
		 * authenticate the attachment endpoint request.
		 * @param string[] $replacements Optional. A variable
		 * for capturing the replacement local attachment urls.
		 * @return string The modified HTML content.
		 */
		public static function localize_attachment_urls(
			string $html,
			int $post_id,
			int $auth_user = 0,
			array &$replacements = array()
		) : string {
			// Find and replace all inline Asana attachment images.
			return preg_replace_callback(
				'/<img .*?data-asana-type="attachment".*?>/m',
				function ( $img_attachment_matches ) use ( &$post_id, &$auth_user, &$replacements ) {

					// Find the Asana attachment's GID.
					preg_match(
						'/ data-asana-gid="([0-9]+)"[\s\/>]/',
						$img_attachment_matches[0],
						$asana_gid_matches
					);

					// Replace the image, using a local src URL.
					if ( ! empty( $asana_gid_matches[1] ) ) {
						$local_attachment_url = self::get_local_attachment_view_url(
							$asana_gid_matches[1],
							$post_id,
							$auth_user
						);
						$replacements[] = $local_attachment_url;
						return '<img src="' . esc_url( $local_attachment_url ) . '" />';
					}

					return $img_attachment_matches[0];
				},
				$html
			);
		}

		/**
		 * Gets the local API endpoint for retrieving an attachment.
		 *
		 * @since [unreleased]
		 *
		 * @param string $attachment_gid The Asana attachment's GID.
		 * @param int    $post_id The post ID to associate the API
		 * request token.
		 * @param int    $auth_user Optional. The WordPress user to
		 * authenticate the attachment endpoint request.
		 * @return string The local API endpoint URL.
		 */
		public static function get_local_attachment_view_url(
			string $attachment_gid,
			int $post_id,
			int $auth_user = 0
		) : string {

			$request_tokens = new Request_Tokens( $post_id );

			return add_query_arg(
				array(
					'token' => $request_tokens->save(
						array(
							'attachment_gid' => $attachment_gid,
							'auth_user' => $auth_user,
							'proxy_field' => 'view_url',
						)
					),
					'post_id' => $request_tokens->get_post_id(),
				),
				rest_url( REST_API_NAMESPACE_V1 . '/attachments' )
			);
		}
	}//end class
}//end if class exists
