<?php
/**
 * Admin Notices class
 *
 * Despite the name, this class is registered publicly so that
 * admin notices can be added under any context. It's simply
 * that the notices themselves are only ever displayed in the
 * admin context which means they are "notices for the admin".
 *
 * @since [unreleased]
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
	 * @since [unreleased]
	 *
	 * @var array $notices
	 */
	private static $notices = array();

	/**
	 * If the admin notices have been updated in memory since
	 * being loaded from the database.
	 *
	 * @since [unreleased]
	 *
	 * @var bool $has_updates
	 */
	private static $has_updates = false;

	/**
	 * The option name for storing the active admin notices.
	 *
	 * @since [unreleased]
	 *
	 * @var string ADMIN_NOTICES_OPTION_NAME
	 */
	private const ADMIN_NOTICES_OPTION_NAME = '_ptc_completionist_admin_notices';

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action( 'plugins_loaded', __CLASS__ . '::load', \PHP_INT_MIN );
		add_action( 'admin_notices', __CLASS__ . '::display', \PHP_INT_MAX );
		add_action( 'shutdown', __CLASS__ . '::save' );
	}

	/**
	 * Registers the custom REST API endpoints.
	 *
	 * @since [unreleased]
	 */
	public static function register_routes() {
		register_rest_route(
			REST_API_NAMESPACE_V1,
			'/admin-notices/dismiss',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => __CLASS__ . '::handle_post_dismiss',
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
	 * @since [unreleased]
	 *
	 * @param \WP_REST_Request $request The API request.
	 *
	 * @return \WP_REST_Response|\WP_Error The API response.
	 */
	public static function handle_post_dismiss(
		\WP_REST_Request $request
	) {
		static::remove( $request['id'] );
		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * Loads the admin notices from the database.
	 *
	 * @since [unreleased]
	 */
	public static function load() {
		static::$notices = get_option( static::ADMIN_NOTICES_OPTION_NAME, array() );
	}

	/**
	 * Saves the admin notices to the database.
	 *
	 * @since [unreleased]
	 */
	public static function save() {
		if ( static::$has_updates ) {
			update_option( static::ADMIN_NOTICES_OPTION_NAME, static::$notices, true );
		}
	}

	/**
	 * Displays all admin notices per the current user's capabilities.
	 *
	 * @since [unreleased]
	 */
	public static function display() {
		if ( is_array( static::$notices ) && count( static::$notices ) > 0 ) {

			$is_displaying_dismissible = false;

			foreach ( static::$notices as $id => $notice ) {

				if ( ! current_user_can( $notice['capability'] ) ) {
					// User does not have permission to read
					// or dismiss this notice.
					continue;
				}

				$class = "notice notice-{$notice['type']} ptc-completionist-admin-notice";

				$formatted_timestamp = date_i18n(
					get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
					$notice['timestamp']
				);

				$message = $notice['message'];

				if ( $notice['dismissible'] ) {
					$class .= ' is-dismissible';
					$is_displaying_dismissible = true;
				} else {
					// Non-dismissible notices display once.
					unset( static::$notices[ $id ] );
					static::$has_updates = true;
				}

				printf(
					'<div class="%1$s" data-notice-id="%2$s"><p>%3$s</p></div>',
					esc_attr( $class ),
					esc_attr( $id ),
					wp_kses_post( "[{$formatted_timestamp}] $message" )
				);
			}//end foreach.

			if ( $is_displaying_dismissible ) {
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
	 * @since [unreleased]
	 *
	 * @param array $args {
	 *     The admin notice's data.
	 *
	 *     @type string $id          The data storage key.
	 *     @type int    $timestamp   The unix timestamp.
	 *     @type string $message     The notice message.
	 *     @type string $type        The notice type like
	 *                               'success' or 'error'.
	 *     @type bool   $dismissible If the notice is dismissible
	 *                               and should persist until
	 *                               dismissed on the frontend.
	 *     @type string $capability  The user capability for
	 *                               displaying the notice.
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
			empty( $notice['timestamp'] ) ||
			empty( $notice['message'] ) ||
			empty( $notice['type'] )
		) {
			trigger_error(
				'Refused to add an admin notice with missing data. The keys id, timestamp, message, and type are all required: ' . print_r( $notice, true ),
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
	 * @since [unreleased]
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
	 * @since [unreleased]
	 */
	public static function delete_all() {
		delete_option( static::ADMIN_NOTICES_OPTION_NAME );
	}
}//end class
