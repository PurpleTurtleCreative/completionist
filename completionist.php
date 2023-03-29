<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette
 * @copyright         2023 Purple Turtle Creative, LLC
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Completionist - Asana for WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Manage, pin, automate, and display Asana tasks in relevant areas of your WordPress admin and website frontend.
 * Version:           3.6.0-beta
 * Requires PHP:      7.1
 * Requires at least: 5.0.0
 * Tested up to:      6.1.1
 * Author:            Purple Turtle Creative
 * Author URI:        https://purpleturtlecreative.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

/*
This program is open-source software: you can redistribute it and/or modify
it UNDER THE TERMS of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see https://www.gnu.org/licenses/gpl-3.0.txt.
*/

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * The full file path to this plugin's main file.
 *
 * @since 3.0.0
 */
define( __NAMESPACE__ . '\PLUGIN_FILE', __FILE__ );

/**
 * The full file path to this plugin's directory ending with a slash.
 *
 * @since 3.0.0
 */
define( __NAMESPACE__ . '\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This plugin's current version.
 *
 * @since 3.0.0
 */
define( __NAMESPACE__ . '\PLUGIN_VERSION', get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' )['Version'] );

/**
 * This plugin's basename.
 *
 * @since 3.0.0
 */
define( __NAMESPACE__ . '\PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * This plugin's directory basename.
 *
 * @since 3.2.0
 */
define( __NAMESPACE__ . '\PLUGIN_SLUG', dirname( PLUGIN_BASENAME ) );

/**
 * The full url to this plugin's directory, NOT ending with a slash.
 *
 * @since 3.0.0
 */
define( __NAMESPACE__ . '\PLUGIN_URL', plugins_url( '', __FILE__ ) );

/**
 * The namespace for all v1 REST API routes registered by this plugin.
 *
 * @since 3.4.0
 *
 * @var string REST_API_NAMESPACE_V1
 */
define( __NAMESPACE__ . '\REST_API_NAMESPACE_V1', PLUGIN_SLUG . '/v1' );

/* REGISTER PLUGIN FUNCTIONS ---------------------- */

/* Activation Hook */
register_activation_hook(
	PLUGIN_FILE,
	function() {
		require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';
		Database_Manager::init();
		Database_Manager::install_all_tables();
	}
);

/* Plugins Loaded */
add_action(
	'plugins_loaded',
	function() {
		/* Ensure Database Tables are Installed */
		require_once PLUGIN_PATH . 'src/includes/class-database-manager.php';
		Database_Manager::init();
		Database_Manager::install_all_tables();
		/* Enqueue Automation Actions */
		require_once PLUGIN_PATH . 'src/includes/automations/class-events.php';
		Automations\Events::add_actions();
	}
);

/**
 * Registers remote plugin updates.
 *
 * @since 3.5.1
 */
function register_remote_plugin_updates() {
	/* YahnisElsts/plugin-update-checker */
	require_once PLUGIN_PATH . 'vendor/yahnis-elsts/plugin-update-checker/plugin-update-checker.php';
	if ( class_exists( '\Puc_v4_Factory' ) ) {
		$plugin_server_endpoint = add_query_arg(
			array(
				'wp_version' => $GLOBALS['wp_version'],
				'site_domain' => rawurlencode( get_site_url( null, '', 'https' ) ),
			),
			'https://purpleturtlecreative.com/wp-json/ptc-resources/v1/plugins/completionist/latest'
		);
		\Puc_v4_Factory::buildUpdateChecker(
			$plugin_server_endpoint,
			PLUGIN_FILE,
			PLUGIN_SLUG
		);
	}
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\register_remote_plugin_updates' );

// Register public functionality.
foreach ( glob( PLUGIN_PATH . 'src/public/class-*.php' ) as $file ) {
	require_once $file;
}

Request_Tokens::register();
REST_Server::register();
Shortcodes::register();

// Register admin functionality.
if ( is_admin() ) {

	foreach ( glob( PLUGIN_PATH . 'src/admin/class-*.php' ) as $file ) {
		require_once $file;
	}

	Admin_Pages::register();
	Admin_Widgets::register();
	Admin_Ajax::register();
}
