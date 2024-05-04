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
	 * Builds HTML for an error note box.
	 *
	 * @since 3.1.1 Added optional parameter $show_dismiss_button.
	 * @since 1.0.0
	 *
	 * @param \Exception $e The exception object data to output.
	 * @param string     $context_message Optional. Text to output before the
	 *     exception's message. Default ''.
	 * @param bool       $show_dismiss_button Optional. If a dismiss button for the
	 *       note box should be displayed. Default true.
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
				<?php echo wp_kses_post( $context_message . $e->getMessage() ); ?>
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
	 * @param string     $context_message Optional. Text to output before the
	 *     exception's message. Default ''.
	 * @return string The formatted string containing the code and message.
	 */
	public static function format_error_string( \Exception $e, string $context_message = '' ) : string {

		$code = self::get_error_code( $e );
		$msg  = self::get_error_message( $e );

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
				$msg = wp_json_encode( $e->response->body->errors );
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

		$task_action_link = array(
			'href'    => '',
			'label'   => '',
			'target'  => '_self',
			'post_id' => 0,
		);

		// Get first pinned post, if applicable.
		$post_id = Options::get_task_pin_post_id( $task_gid );
		if ( $post_id > 0 ) {
			$post = get_post( $post_id );
			if ( isset( $post->post_type ) ) {
				$edit_post_link = get_edit_post_link( $post, 'raw' );
				if ( $edit_post_link ) {
					$task_action_link['href']    = $edit_post_link;
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
			$task_action_link = array(
				'href'   => self::get_asana_task_url( $task_gid ),
				'label'  => 'View in Asana',
				'target' => '_asana',
			);
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

		// TODO: just pass a string, don't require using an entire task object...

		$relative_due         = new \stdClass();
		$relative_due->label  = '';
		$relative_due->status = '';

		if ( isset( $task->due_on ) ) {
			$due_date = Options::sanitize( 'date', $task->due_on );
			if ( ! empty( $due_date ) ) {
				$dt_due = \DateTimeImmutable::createFromFormat( 'Y-m-d', $due_date );
				if ( false !== $dt_due && method_exists( $dt_due, 'setTime' ) ) {

					$dt_today = new \DateTimeImmutable( 'today' );
					$dt_due->setTime( 0, 0 );
					$dt_today->setTime( 0, 0 );
					$days_diff = $dt_today->diff( $dt_due )->days;

					if ( $dt_due < $dt_today && 0 !== $days_diff ) {

						if ( 1 === $days_diff ) {
							$dt_string = 'Yesterday';
						} else {
							$dt_string = human_time_diff( $dt_due->getTimestamp() ) . ' ago';
						}

						$relative_due->status = 'past';

					} elseif ( 0 === $days_diff ) {

							$dt_string = 'Today';
							$relative_due->status = 'today';

					} elseif ( $days_diff < 7 ) {

						if ( 1 === $days_diff ) {
							$dt_string = 'Tomorrow';
						} else {
							$dt_string = $dt_due->format( 'l' );
						}
						$relative_due->status = 'soon';

					} else {

						$dt_string = $dt_due->format( 'M j' );
						$relative_due->status = 'later';
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
			return array();
		}

		$success = usort(
			$tasks,
			function ( $a, $b ) {

				$a_unix = PHP_INT_MAX;

				if ( isset( $a->due_on ) ) {
					$a_due_date = Options::sanitize( 'date', $a->due_on );
					if ( ! empty( $a_due_date ) ) {
						$a_dt = \DateTimeImmutable::createFromFormat( 'Y-m-d', $a_due_date );
						if ( false !== $a_dt && method_exists( $a_dt, 'setTime' ) ) {
							$a_dt->setTime( 0, 0 );
							$a_unix = $a_dt->getTimestamp();
						}
					}
				}

				$b_unix = PHP_INT_MAX;

				if ( isset( $b->due_on ) ) {
					$b_due_date = Options::sanitize( 'date', $b->due_on );
					if ( ! empty( $b_due_date ) ) {
						$b_dt = \DateTimeImmutable::createFromFormat( 'Y-m-d', $b_due_date );
						if ( false !== $b_dt && method_exists( $b_dt, 'setTime' ) ) {
							$b_dt->setTime( 0, 0 );
							$b_unix = $b_dt->getTimestamp();
						}
					}
				}

				return ( $a_unix - $b_unix );
			}
		);

		if ( $success ) {
			return $tasks;
		}

		error_log( 'Failed to sort tasks by due date.' );
		return array();
	}

	/**
	 * Replaces inline Asana attachment URLs with local API
	 * endpoints for retrieval.
	 *
	 * @since 3.7.0 Deprecated $post_id parameter.
	 * @since 3.5.0
	 *
	 * @param string   $html The HTML content to search and replace.
	 * @param int      $deprecated Deprecated.
	 * @param int      $auth_user Optional. The WordPress user to
	 * authenticate the attachment endpoint request.
	 * @param string[] $replacements Optional. A variable
	 * for capturing the replacement local attachment urls.
	 * @return string The modified HTML content.
	 */
	public static function localize_attachment_urls(
		string $html,
		int $deprecated,
		int $auth_user = 0,
		array &$replacements = array()
	) : string {

		if ( -1 !== $deprecated ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.7.0',
				'The $post_id parameter is deprecated. Pass -1 to silence this notice.'
			);
		}

		// Find and replace all inline Asana attachment images.
		// Note that we can't already regex capture the attributes
		// because we don't know what sequence they'll be in.
		return preg_replace_callback(
			'/<img .*?data-asana-type="attachment".*?>/m',
			function ( $img_attachment_matches ) use ( &$auth_user, &$replacements ) {

				// Find the Asana attachment's data attributes.

				preg_match_all(
					'/(data-asana-gid|data-src-width|data-src-height|alt)="(.*?)"/m',
					$img_attachment_matches[0],
					$asana_data_attr_matches,
					PREG_SET_ORDER
				);

				$data_attrs = array(
					'data-asana-gid'  => '',
					'data-src-width'  => '',
					'data-src-height' => '',
					'alt'             => '',
				);

				foreach ( $asana_data_attr_matches as &$capture_group ) {
					$data_attrs[ $capture_group[1] ] = $capture_group[2];
				}

				// Replace the image, using a local src URL.
				if ( ! empty( $data_attrs['data-asana-gid'] ) ) {
					$local_attachment_url = self::get_local_attachment_view_url(
						$data_attrs['data-asana-gid'],
						-1,
						$auth_user
					);
					$replacements[] = $local_attachment_url;
					return sprintf(
						'<img src="%s" width="%s" height="%s" alt="%s" style="%s" draggable="false" />',
						esc_url( $local_attachment_url ),
						esc_attr( $data_attrs['data-src-width'] ),
						esc_attr( $data_attrs['data-src-height'] ),
						esc_attr( $data_attrs['alt'] ),
						esc_attr( "aspect-ratio:{$data_attrs['data-src-width']}/{$data_attrs['data-src-height']};max-width:100%;height:auto;" )
					);
				}

				return $img_attachment_matches[0];
			},
			$html
		);
	}

	/**
	 * Replaces inline video objects with oEmbed HTML.
	 *
	 * @since 3.6.0
	 *
	 * @param string   $html The HTML content to search and replace.
	 * @param string[] $replacements Optional. A variable
	 * for capturing the replaced urls.
	 * @return string The modified HTML content.
	 */
	public static function replace_urls_with_oembeds(
		string $html,
		array &$replacements = array()
	) : string {
		// Find and replace all inline objects.
		return preg_replace_callback(
			'/<object.*>.*(https?:\/\/[^<"]+).*<\/object>/m',
			function ( $object_tag_matches ) use ( &$replacements ) {
				// Replace the object with oEmbed HTML.
				if ( ! empty( $object_tag_matches[1] ) ) {
					$oembed_html = static::get_oembed_for_url( $object_tag_matches[1] );
					if ( ! empty( $oembed_html ) ) {
						$oembed_url     = html_entity_decode( $object_tag_matches[1] );
						$replacements[] = $oembed_url;
						return $oembed_html;
					}
				}

				return $object_tag_matches[0];
			},
			$html
		);
	}

	/**
	 * Gets the oEmbed HTML for the given URL.
	 *
	 * @since 4.1.0
	 *
	 * @param string $url The URL.
	 * @return string The HTML. Empty string on failure.
	 */
	public static function get_oembed_for_url( string $url ) : string {

		if ( ! empty( $url ) ) {

			$oembed_html = wp_oembed_get(
				html_entity_decode( $url ),
				array(
					'width'  => 1280,
					'height' => 720,
				)
			);

			if ( $oembed_html && is_string( $oembed_html ) ) {
				return '<div class="ptc-responsive-embed">' . $oembed_html . '</div>';
			}
		}

		return '';
	}

	/**
	 * Gets the local API endpoint for retrieving an attachment's content
	 * for viewing.
	 *
	 * @since 3.7.0 Deprecated $post_id parameter.
	 * @since 3.5.0
	 *
	 * @param string $attachment_gid The Asana attachment's GID.
	 * @param int    $deprecated Deprecated.
	 * @param int    $auth_user Optional. The WordPress user to
	 * authenticate the attachment endpoint request.
	 * @return string The local API endpoint URL.
	 */
	public static function get_local_attachment_view_url(
		string $attachment_gid,
		int $deprecated,
		int $auth_user = 0
	) : string {

		if ( -1 !== $deprecated ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.7.0',
				'The $post_id parameter is deprecated. Pass -1 to silence this notice.'
			);
		}

		$request_args = array(
			'_cache_key'     => 'get_local_attachment_view_url',
			'attachment_gid' => $attachment_gid,
			'auth_user'      => $auth_user,
			'proxy_field'    => 'view_url',
		);

		$token = Request_Token::save( $request_args );

		return add_query_arg(
			array( 'token' => $token ),
			rest_url( REST_API_NAMESPACE_V1 . '/attachments' )
		);
	}

	/**
	 * Gets the local API endpoint for retrieving an attachment with
	 * the provided arguments.
	 *
	 * @see PTC_Completionist\REST_API\Attachments::handle_get_attachment()
	 *
	 * @since 4.3.0
	 *
	 * @param string $attachment_gid The Asana attachment's GID.
	 * @param array  $args Optional. Additional arguments for the
	 * API endpoint which retrieves the attachment's data.
	 * @return string The local API endpoint URL.
	 */
	public static function get_local_attachment_url(
		string $attachment_gid,
		array $args = array()
	) : string {

		$args['_cache_key']     = 'get_local_attachment_url';
		$args['attachment_gid'] = $attachment_gid;

		$token = Request_Token::save( $args );

		return add_query_arg(
			array( 'token' => $token ),
			rest_url( REST_API_NAMESPACE_V1 . '/attachments' )
		);
	}

	/**
	 * Sanitizes content for allowed HTML tags for post content.
	 *
	 * This is an extension on WordPress's wp_kses_post() for
	 * backwards compatibility and compatibility with Asana's
	 * returned markup.
	 *
	 * @since 3.9.0
	 *
	 * @param string $content The content.
	 *
	 * @return string The sanitized content.
	 */
	public static function kses_post( string $content ) : string {

		$allowed_html = wp_kses_allowed_html( 'post' );

		if ( ! isset( $allowed_html['object'] ) ) {
			// Asana denotes third-party embeds as <object> elements.
			//
			// See replace_urls_with_oembeds().
			//
			// WordPress allowed <object> tags in this commit https://github.com/WordPress/wordpress-develop/commit/9ca3e8f36b07c41e9298c545135a451718f5d805
			// of v5.9.0 but with attributes stripped if it doesn't
			// have the required [data] and [type] attributes.
			$allowed_html['object'] = array();
		}

		return wp_kses( $content, $allowed_html );
	}
}//end class
