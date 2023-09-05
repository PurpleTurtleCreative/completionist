<?php
/**
 * Uninstaller class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';

/**
 * Static class to handle plugin uninstallation.
 *
 * @since [unreleased]
 */
class Uninstaller {

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action(
			'ptc_completionist_freemius_loaded',
			function( $freemius ) {
				$freemius->add_action(
					'after_uninstall',
					__CLASS__ . '::uninstall_all'
				);
			}
		);
	}

	/**
	 * Removes this plugin's data for the entire WordPress site.
	 *
	 * @since [unreleased]
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
	 * @since [unreleased]
	 */
	public static function uninstall_current_blog() {

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
	}
}//end class
