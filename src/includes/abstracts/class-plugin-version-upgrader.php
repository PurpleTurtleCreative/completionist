<?php
/**
 * Upgrader class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist\Abstracts;

defined( 'ABSPATH' ) || die();

use \PTC_Completionist\Admin_Notices;

require_once \PTC_Completionist\PLUGIN_PATH . 'src/includes/class-util.php';
require_once \PTC_Completionist\PLUGIN_PATH . 'src/public/class-admin-notices.php';

/**
 * Static class to handle plugin version checks and migrations.
 *
 * @since [unreleased]
 */
abstract class Plugin_Version_Upgrader {

	/**
	 * The option name storing the last successfully upgraded
	 * plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @var string $upgraded_version_option
	 */
	protected static $upgraded_version_option;

	/**
	 * The current running plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @var string $current_plugin_version
	 */
	protected static $current_plugin_version;

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	final public static function register() {
		if ( ! defined( 'DOING_AJAX' ) || ! \DOING_AJAX ) {
			add_action(
				'plugins_loaded',
				function() {
					static::maybe_run();
				}
			);
		}
	}

	/**
	 * Checks the last successfully upgraded plugin version and
	 * runs the upgrade processes if needed.
	 *
	 * @since [unreleased]
	 */
	final public static function maybe_run() {

		if ( empty( static::$upgraded_version_option ) ) {
			// Doing it wrong.
		}

		if ( empty( static::$current_plugin_version ) ) {
			// Doing it wrong.
		}

		$last_upgraded_version = (string) get_option( static::$upgraded_version_option, '0.0.0' );

		if ( static::$current_plugin_version === $last_upgraded_version ) {
			// Plugin state is up-to-date.
			return;
		} elseif ( version_compare( static::$current_plugin_version, $last_upgraded_version, '>' ) ) {
			// Plugin state is old and needs upgrade.
			if ( static::upgrade_from_version( $last_upgraded_version, static::$current_plugin_version ) ) {
				// Update option on successful upgrade.
				update_option( static::$upgraded_version_option, static::$current_plugin_version, true );
				// Notify the user.
				Admin_Notices::add_notice(
					sprintf(
						'Completionist successfully upgraded from v%s to v%s!',
						$last_upgraded_version,
						static::$current_plugin_version
					),
					'success',
					false,
					'update_plugins'
				);
			} else {
				Admin_Notices::add_notice(
					sprintf(
						'Completionist failed to upgrade from v%s to v%s!',
						$last_upgraded_version,
						static::$current_plugin_version
					),
					'error',
					true,
					'update_plugins'
				);
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
			update_option(
				static::$upgraded_version_option,
				static::$current_plugin_version,
				true
			);
			// Offer assistance.
			Admin_Notices::add_notice(
				sprintf(
					'Completionist plugin rollback detected from v%s to v%s – Please email michelle@purpleturtlecreative.com if you\'re experiencing issues. We\'re happy to help!',
					$last_upgraded_version,
					static::$current_plugin_version
				),
				'warning',
				true,
				'update_plugins'
			);
		}
	}

	/**
	 * Resets the upgrader by deleting its data.
	 *
	 * @since [unreleased]
	 */
	final public static function reset() {
		delete_option( static::$upgraded_version_option );
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
	abstract protected static function upgrade_from_version( string $old_version ) : bool;
}//end class
