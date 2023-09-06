<?php
/**
 * Upgrader class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/abstracts/class-plugin-version-upgrader.php';

/**
 * Static class to handle plugin upgrades.
 *
 * @since [unreleased]
 */
class Upgrader extends Abstracts\Plugin_Version_Upgrader {

	/**
	 * The option name storing the last successfully upgraded
	 * plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @var string $upgraded_version_option
	 */
	protected static $upgraded_version_option = '_ptc_completionist_upgraded_version';

	/**
	 * The current running plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @var string $current_plugin_version
	 */
	protected static $current_plugin_version = PLUGIN_VERSION;

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

		if ( version_compare( $old_version, '4.0.0', '<' ) ) {
			// First upgrade process, so the $old_version is always zero.
			// v4.0.0 is when plugin hosting changed from
			// <purpleturtlecreative.com> to <wordpress.org> which
			// removed the YahnisElsts/plugin-update-checker package.
			delete_site_option( 'external_updates-completionist' );
			wp_clear_scheduled_hook( 'puc_cron_check_updates-completionist' );
			return true; // Assume success.
		}

		// No upgrade needed, so consider success.
		return true;
	}
}//end class
