<?php
/**
 * Upgrader class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/abstracts/class-plugin-version-checker.php';
require_once PLUGIN_PATH . 'src/public/class-freemius.php';

/**
 * Static class to handle plugin upgrades.
 *
 * @since [unreleased]
 */
class Upgrader extends Abstracts\Plugin_Version_Checker {

	/**
	 * Gets the current running plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	protected static function get_current_plugin_version() : string {
		return PLUGIN_VERSION;
	}

	/**
	 * Gets the option name storing the last successfully upgraded
	 * plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	protected static function get_upgraded_version_option_name() : string {
		return '_ptc_completionist_upgraded_version';
	}

	/**
	 * Gets the plugin name to display in alerts and notices.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	protected static function get_plugin_name() : string {
		return 'Completionist';
	}

	/**
	 * Gets the URL for the plugin's support page.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	protected static function get_support_link() : string {
		return Freemius::instance()->contact_url();
	}

	/**
	 * Runs migration processes to upgrade the plugin's state from
	 * the specified version.
	 *
	 * @since [unreleased]
	 *
	 * @param string $old_version The plugin version string from
	 * which to upgrade.
	 *
	 * @return bool If upgraded successfully.
	 */
	protected static function upgrade_from_version( string $old_version ) : bool {

		if ( version_compare( $old_version, '3.7.0', '<' ) ) {
			// Check installed database version.
			require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';
			Database_Manager::init();
			$db_version = Database_Manager::get_installed_version();
			if ( $db_version >= 2 ) {
				// Now that the custom request tokens database table
				// is successfully installed, all deprecated Request
				// Tokens (stored within postmeta) should be purged.
				require_once PLUGIN_PATH . 'src/public/class-request-tokens.php';
				Request_Tokens::purge_all();
			}
		}

		if ( version_compare( $old_version, '4.0.0', '<' ) ) {
			// First upgrade process, so the $old_version is always zero.
			// v4.0.0 is when plugin hosting changed from
			// <purpleturtlecreative.com> to <wordpress.org> which
			// removed the YahnisElsts/plugin-update-checker package.
			delete_site_option( 'external_updates-completionist' );
			wp_clear_scheduled_hook( 'puc_cron_check_updates-completionist' );
			// Assume success because they could've been deleted already.
			// It also isn't problematic if they actually failed.
		}

		// No upgrade needed, so consider success.
		return true;
	}
}//end class
