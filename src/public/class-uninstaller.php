<?php
/**
 * Uninstaller class
 *
 * @since 4.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Static class to handle plugin uninstallation.
 *
 * @since 4.0.0
 */
class Uninstaller {

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 4.0.0
	 */
	public static function register() {
		register_uninstall_hook( PLUGIN_FILE, __CLASS__ . '::uninstall_all' );
	}

	/**
	 * Removes this plugin's data for the entire WordPress site.
	 *
	 * @since 4.0.0
	 */
	public static function uninstall_all() {
		if ( function_exists( 'get_sites' ) ) {
			// Uninstall across entire multisite network.
			$site_ids = get_sites( array( 'fields' => 'ids' ) );
			foreach ( $site_ids as $site_id ) {
				switch_to_blog( $site_id );
				static::uninstall_current_blog();
				restore_current_blog();
			}//end foreach $site_ids
		} else {
			// Uninstall for single site.
			static::uninstall_current_blog();
		}
	}

	/**
	 * Removes this plugin's data for the current blog.
	 *
	 * @since 4.0.0
	 */
	public static function uninstall_current_blog() {

		/**
		 * Filters whether all plugin data should be removed during
		 * uninstallation.
		 *
		 * @since 4.0.0
		 *
		 * @param bool $uninstall_all_data If all plugin data should
		 * be uninstalled. Default true.
		 */
		$uninstall_all_data = apply_filters( 'ptc_completionist_uninstall_all_data', true );
		if ( ! $uninstall_all_data ) {
			return;
		}

		// Remove all plugin data.

		if ( class_exists( __NAMESPACE__ . '\Options' ) ) {
			if ( method_exists( __NAMESPACE__ . '\Options', 'delete_all' ) ) {
				Options::delete_all();
			}
		}

		if ( class_exists( __NAMESPACE__ . '\Database_Manager' ) ) {
			if (
				method_exists( __NAMESPACE__ . '\Database_Manager', 'init' )
				&& method_exists( __NAMESPACE__ . '\Database_Manager', 'drop_all_tables' )
			) {
				Database_Manager::init();
				Database_Manager::drop_all_tables();
			}
		}

		if ( class_exists( __NAMESPACE__ . '\Admin_Notices' ) ) {
			if ( method_exists( __NAMESPACE__ . '\Admin_Notices', 'delete_all' ) ) {
				Admin_Notices::delete_all();
			}
		}

		if ( class_exists( __NAMESPACE__ . '\Upgrader' ) ) {
			if ( method_exists( __NAMESPACE__ . '\Upgrader', 'delete_data' ) ) {
				Upgrader::delete_data();
			}
		}

		/**
		 * Runs after uninstalling plugin data for the current blog.
		 *
		 * Note that this runs for each blog if Completionist is
		 * uninstalled on a WordPress multisite.
		 *
		 * @since 4.0.0
		 */
		do_action( 'ptc_completionist_after_uninstall_current_blog' );
	}
}//end class
