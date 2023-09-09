<?php
/**
 * Plugin Version Checker class
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
abstract class Plugin_Version_Checker {

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

		$upgraded_version_option = static::get_upgraded_version_option_name();
		$last_upgraded_version   = (string) get_option( $upgraded_version_option, '0.0.0' );

		$current_plugin_version = static::get_current_plugin_version();

		if ( $current_plugin_version === $last_upgraded_version ) {
			// Plugin state is up-to-date.
			return;
		} elseif ( version_compare( $current_plugin_version, $last_upgraded_version, '>' ) ) {
			// Plugin state is old and needs upgrade.
			if ( static::upgrade_from_version( $last_upgraded_version, $current_plugin_version ) ) {
				// Update option on successful upgrade.
				update_option(
					$upgraded_version_option,
					$current_plugin_version,
					true
				);
				// Notify success.
				Admin_Notices::add(
					array(
						'id'          => 'plugin_version_check',
						'message'     => sprintf(
							'<strong>%s successfully upgraded from v%s to v%s!</strong> We greatly appreciate your continued support!',
							static::get_plugin_name(),
							$last_upgraded_version,
							$current_plugin_version
						),
						'type'        => 'success',
						'dismissible' => false,
						'capability'  => 'update_plugins',
					)
				);
			} else {
				// Notify error.
				Admin_Notices::add(
					array(
						'id'          => 'plugin_version_check',
						'message'     => sprintf(
							'<strong>%s failed to upgrade from v%s to v%s!</strong> If you\'re experiencing issues, please do not hesitate to <a href="%s">contact support</a>! We\'re always happy to help!',
							static::get_plugin_name(),
							$last_upgraded_version,
							$current_plugin_version,
							esc_url( static::get_support_link() )
						),
						'type'        => 'error',
						'dismissible' => true,
						'capability'  => 'update_plugins',
					)
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
				$upgraded_version_option,
				$current_plugin_version,
				true
			);
			// Offer assistance.
			Admin_Notices::add(
				array(
					'id'          => 'plugin_version_check',
					'message'     => sprintf(
						'<strong>%s plugin rollback detected from v%s to v%s</strong> – If you\'re experiencing issues, please do not hesitate to <a href="%s">contact support</a>! We\'re always happy to help!',
						static::get_plugin_name(),
						$last_upgraded_version,
						$current_plugin_version,
						esc_url( static::get_support_link() )
					),
					'type'        => 'warning',
					'dismissible' => true,
					'capability'  => 'update_plugins',
				)
			);
		}
	}

	/**
	 * Resets the upgrader by deleting its data.
	 *
	 * @since [unreleased]
	 */
	final public static function reset() {
		delete_option( static::get_upgraded_version_option_name() );
	}

	/**
	 * Gets the current running plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	abstract protected static function get_current_plugin_version() : string;

	/**
	 * Gets the option name storing the last successfully upgraded
	 * plugin version.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	abstract protected static function get_upgraded_version_option_name() : string;

	/**
	 * Gets the plugin name to display in alerts and notices.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	abstract protected static function get_plugin_name() : string;

	/**
	 * Gets the URL for the plugin's support page.
	 *
	 * @since [unreleased]
	 *
	 * @return string
	 */
	abstract protected static function get_support_link() : string;

	/**
	 * Runs migration processes to upgrade the plugin's state from
	 * the specified version.
	 *
	 * @since [unreleased]
	 *
	 * @param string $old_version The plugin version string from
	 * which to upgrade. (eg. '1.2.3')
	 *
	 * @return bool If upgraded successfully.
	 */
	abstract protected static function upgrade_from_version( string $old_version ) : bool;
}//end class
