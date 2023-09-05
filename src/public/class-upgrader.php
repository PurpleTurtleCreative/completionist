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
	 * @var string VERSION_OPTION_NAME
	 */
	const VERSION_OPTION_NAME = '_ptc_completionist_upgraded_version';

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action( 'plugins_loaded', __CLASS__ . '::maybe_run' );
	}

	/**
	 * Checks the last successfully upgraded plugin version and
	 * runs the upgrade processes if needed.
	 *
	 * @since [unreleased]
	 */
	public static function maybe_run() {

		$last_upgraded_version = (int) get_option( static::VERSION_OPTION_NAME, 0 );

		if ( PLUGIN_VERSION === $last_upgraded_version ) {
			// Plugin state is up-to-date.
			return;
		} elseif ( version_compare( PLUGIN_VERSION, $last_upgraded_version, '>' ) ) {
			// Plugin state is old and needs upgrade.
			if ( static::upgrade_from_version( $last_upgraded_version, PLUGIN_VERSION ) ) {
				// Update option on successful upgrade.
				update_option( static::VERSION_OPTION_NAME, PLUGIN_VERSION, true );
			}
		} else {
			/*
			The plugin was rolled back to a previous version. This is
			not recommended and usually signals that the user is
			experiencing issues with the latest release.

			This will likely cause old plugin data to be added back,
			so the upgrade process should run again if the plugin is
			updated again in the future.

			EDGE CASE: This will not work for users who roll back to
			versions prior to v4.0.0. That's because this class
			was first introduced in v4.0.0, so the database option
			will remain set as '4.0.0' because this code doesn't exist
			in prior versions to fix the option value. Using such an
			old version of the plugin will also cause the uninstall
			script to be insufficient.

			If this causes issues, the user should simply uninstall
			the plugin (from the latest installed version, if possible)
			to completely remove all data and reset the plugin's state.
			*/
			update_option( static::VERSION_OPTION_NAME, PLUGIN_VERSION, true );
		}
	}

	/**
	 * Resets the upgrader by clearing its data.
	 *
	 * @since [unreleased]
	 */
	public static function reset() {
		delete_option( static::VERSION_OPTION_NAME );
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
	private static function upgrade_from_version( string $old_version ) : bool {

		if ( version_compare( $old_version, '4.0.0', '<' ) ) {
			// First upgrade process, so the $old_version is always zero.
			// v4.0.0 is when plugin hosting changed from
			// <purpleturtlecreative.com> to <wordpress.org> which
			// removed the YahnisElsts/plugin-update-checker package.
			if (
				true === delete_site_option( 'external_updates-completionist' ) &&
				false !== wp_clear_scheduled_hook( 'puc_cron_check_updates-completionist' )
			) {
				return true;
			} else {
				return false;
			}
		}

		// No upgrade needed, so consider success.
		return true;
	}
}//end class
