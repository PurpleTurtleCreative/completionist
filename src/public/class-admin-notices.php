<?php
/**
 * Admin Notices class
 *
 * Despite the name, this class is registered publicly so that
 * admin notices can be added under any context. It's simply
 * that the notices themselves are only ever displayed in the
 * admin context which means they are "notices for the admin".
 *
 * @since 4.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * A static class to manage and display admin notices.
 */
class Admin_Notices {

	/**
	 * The active admin notices for display.
	 *
	 * These are persisted in the database and loaded during the
	 * 'plugins_loaded' action.
	 *
	 * @since 4.0.0
	 *
	 * @var array $notices
	 */
	private static $notices;

	/**
	 * If the admin notices have been updated in memory since
	 * being loaded from the database.
	 *
	 * @since 4.0.0
	 *
	 * @var bool $has_updates
	 */
	private static $has_updates = false;

	/**
	 * The option name for storing the active admin notices.
	 *
	 * @since 4.0.0
	 *
	 * @var string ADMIN_NOTICES_OPTION_NAME
	 */
	private const ADMIN_NOTICES_OPTION_NAME = '_ptc_completionist_admin_notices';

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 4.0.0
	 */
	public static function register() {
		add_action( 'plugins_loaded', __CLASS__ . '::load', 5 );
		add_action( 'admin_notices', __CLASS__ . '::display', 10 );
		add_action( 'shutdown', __CLASS__ . '::save', 10 );
	}

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since 4.0.0
	 */
	public static function register_routes() {
		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/admin-notices/dismiss',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => __CLASS__ . '::handle_notice_dismiss',
					'permission_callback' => 'is_user_logged_in',
					'args'                => array(
						'id' => array(
							'type'              => 'string',
							'required'          => true,
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}

	/**
	 * Handles a POST request to dismiss an admin notice.
	 *
	 * @since 4.0.0
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_notice_dismiss(
		\WP_REST_Request $request
	) {
		static::remove( $request['id'] );
		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Loads the admin notices from the database.
	 *
	 * @since 4.0.0
	 */
	public static function load() {
		if ( ! isset( static::$notices ) ) {
			static::$notices = get_option( static::ADMIN_NOTICES_OPTION_NAME, array() );
		}
	}

	/**
	 * Saves the admin notices to the database.
	 *
	 * @since 4.0.0
	 */
	public static function save() {
		if ( static::$has_updates ) {
			update_option( static::ADMIN_NOTICES_OPTION_NAME, static::$notices, true );
		}
	}

	/**
	 * Displays all admin notices per the current user's capabilities.
	 *
	 * @since 4.0.0
	 */
	public static function display() {
		if ( is_array( static::$notices ) && count( static::$notices ) > 0 ) {

			$is_displaying_dismissible = false;

			// Custom admin notice capability check for specific user ID.
			$is_user_cap = '_is_user_' . get_current_user_id();

			foreach ( static::$notices as $id => $notice ) {

				if (
					! current_user_can( $notice['capability'] ) &&
					$is_user_cap !== $notice['capability']
				) {
					// User does not have permission to read
					// or dismiss this notice.
					continue;
				}

				$class = "notice notice-{$notice['type']} ptc-completionist-admin-notice";

				$maybe_formatted_timestamp = '';
				if ( ! empty( $notice['timestamp'] ) ) {
					$maybe_formatted_timestamp = date_i18n(
						get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
						$notice['timestamp']
					);
					$maybe_formatted_timestamp = '[' . $maybe_formatted_timestamp . '] ';
				}

				$message = $notice['message'];

				if ( $notice['dismissible'] ) {
					$class .= ' is-dismissible';
					$is_displaying_dismissible = true;
				} else {
					// Non-dismissible notices display once.
					static::remove( $id );
				}

				printf(
					'<div class="%1$s" data-notice-id="%2$s"><p>%3$s</p></div>',
					esc_attr( $class ),
					esc_attr( $id ),
					wp_kses_post( "{$maybe_formatted_timestamp}{$message}" )
				);
			}//end foreach.

			if ( $is_displaying_dismissible ) {
				// AJAX handler for dismissible notices.
				?>
				<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', () => {
					document
						.querySelectorAll('.ptc-completionist-admin-notice.is-dismissible[data-notice-id]')
						.forEach(notice => {
							if ( notice.dataset.noticeId ) {
								notice.addEventListener('click', event => {
									if ( event.target.classList.contains('notice-dismiss') ) {
										window.fetch(
											'<?php echo esc_url( rest_url( REST_API_NAMESPACE_V1 . '/admin-notices/dismiss' ) ); ?>',
											{
												method: 'POST',
												headers: {
													'Content-Type': 'application/x-www-form-urlencoded',
													'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>',
												},
												body: `id=${notice.dataset.noticeId}`,
												credentials: 'same-origin', // Include cookies.
											}
										);
									}
								});
							}
						});
				});
				</script>
				<?php
			}
		}
	}

	/**
	 * Adds an admin notice.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *     The admin notice's data.
	 *
	 *     @type string $id          The data storage key. Default
	 *                               randomly generated unique ID.
	 *
	 *     @type int    $timestamp   The unix timestamp. Set to false
	 *                               or 0 if not important.
	 *                               Default current time.
	 *
	 *     @type string $message     The notice message.
	 *
	 *     @type string $type        The notice type like 'info',
	 *                               'warning', 'success', or 'error'.
	 *
	 *     @type bool   $dismissible If the notice is dismissible
	 *                               and should persist until
	 *                               dismissed on the frontend.
	 *
	 *     @type string $capability  The user capability for
	 *                               displaying the notice. Can also
	 *                               use the custom format
	 *                               "_is_user_{$wp_user->id}" to
	 *                               limit the notice to a particular
	 *                               individual user by ID.
	 * }
	 *
	 * @return string The notice's ID. Empty string on failure.
	 */
	public static function add( array $args = array() ) : string {

		$notice = wp_parse_args(
			$args,
			array(
				'id'          => uniqid( 'notice_' ),
				'timestamp'   => time(),
				'message'     => '',
				'type'        => 'info',
				'dismissible' => false,
				'capability'  => 'edit_posts',
			)
		);

		if (
			empty( $notice['id'] ) ||
			empty( $notice['message'] ) ||
			empty( $notice['type'] )
		) {
			trigger_error(
				'Refused to add an admin notice with missing data. The keys id, message, and type are all required: ' . esc_html( print_r( $notice, true ) ),
				\E_USER_WARNING
			);
			return '';
		}

		static::$notices[ $notice['id'] ] = $notice;

		static::$has_updates = true;

		return $notice['id'];
	}

	/**
	 * Removes an admin notice.
	 *
	 * @since 4.0.0
	 *
	 * @param string $id The notice's ID.
	 */
	public static function remove( string $id ) {
		if ( isset( static::$notices[ $id ] ) ) {
			unset( static::$notices[ $id ] );
			static::$has_updates = true;
		}
	}

	/**
	 * Deletes all admin notices data.
	 *
	 * @since 4.0.0
	 */
	public static function delete_all() {
		delete_option( static::ADMIN_NOTICES_OPTION_NAME );
	}
}//end class
