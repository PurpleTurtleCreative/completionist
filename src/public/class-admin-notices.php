<?php
/**
 * Admin Notices class
 *
 * Despite the name, this class is registered publicly so that
 * admin notices can be added under any context. It's simply
 * that the notices themselves are only ever displayed in the
 * admin context—meaning they are "notices for the admin".
 *
 * @todo Remove dismissed notices via AJAX from frontend. Otherwise,
 * they never go away! Be mindful that you must prevent race conditions
 * by making these calls atomic... If that's even possible by using
 * a single database option_name and option_value...
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
	 * 'admin_init' action. Unset until loaded.
	 *
	 * @since [unreleased]
	 *
	 * @var array $notices
	 */
	private static $notices = array();

	/**
	 * If the admin notices have been updated in memory after
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

	public static function register() {
		add_action( 'plugins_loaded', __CLASS__ . '::load', \PHP_INT_MIN );
		add_action( 'admin_notices', __CLASS__ . '::display', \PHP_INT_MAX );
		add_action( 'shutdown', __CLASS__ . '::save' );
	}

	public static function load() {
		static::$notices = get_option( static::ADMIN_NOTICES_OPTION_NAME, array() );
	}

	public static function save() {
		if ( static::$has_updates ) {
			update_option( static::ADMIN_NOTICES_OPTION_NAME, static::$notices, true );
		}
	}

	public static function display() {
		if ( is_array( static::$notices ) && count( static::$notices ) > 0 ) {
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
			}
		}
	}

	public static function add( array $args = array() ) : string {

		$notice = wp_parse_args(
			$args,
			array(
				'id'          => uniqid(),
				'timestamp'   => time(),
				'message'     => '',
				'type'        => 'info',
				'dismissible' => false,
				'capability'  => 'edit_posts',
			)
		);

		static::$notices[ $args['id'] ] = $notice;

		static::$has_updates = true;

		return $args['id'];
	}

	public static function remove( string $id ) {
		if ( isset( static::$notices[ $id ] ) ) {
			unset( static::$notices[ $id ] );
			static::$has_updates = true;
		}
	}

	public static function delete_all() {
		delete_option( static::ADMIN_NOTICES_OPTION_NAME );
	}
}//end class
