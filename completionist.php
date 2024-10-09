<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette <michelle@purpleturtlecreative.com>
 * @copyright         2024 Purple Turtle Creative, LLC
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Completionist – Asana for WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Manage, pin, automate, and display Asana tasks in relevant areas of your WordPress admin and website frontend.
 * Version:           4.4.1
 * Requires PHP:      8.1
 * Requires at least: 5.0.0
 * Tested up to:      6.6.2
 * Author:            Purple Turtle Creative
 * Author URI:        https://purpleturtlecreative.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 */

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/gpl-3.0.txt>.
*/

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * The full file path to this plugin's main file.
 *
 * @since 3.0.0
 */
define( 'PTC_Completionist\PLUGIN_FILE', __FILE__ );

/**
 * The full file path to this plugin's directory ending with a slash.
 *
 * @since 3.0.0
 */
define( 'PTC_Completionist\PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/**
 * This plugin's current version.
 *
 * @since 3.0.0
 */
define( 'PTC_Completionist\PLUGIN_VERSION', get_file_data( __FILE__, array( 'Version' => 'Version' ), 'plugin' )['Version'] );

/**
 * This plugin's basename.
 *
 * @since 3.0.0
 */
define( 'PTC_Completionist\PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * This plugin's directory basename.
 *
 * @since 3.2.0
 */
define( 'PTC_Completionist\PLUGIN_SLUG', dirname( PLUGIN_BASENAME ) );

/**
 * The full url to this plugin's directory, NOT ending with a slash.
 *
 * @since 3.0.0
 */
define( 'PTC_Completionist\PLUGIN_URL', plugins_url( '', __FILE__ ) );

/**
 * The namespace for all v1 REST API routes registered by this plugin.
 *
 * @since 3.4.0
 *
 * @var string REST_API_NAMESPACE_V1
 */
define( 'PTC_Completionist\REST_API_NAMESPACE_V1', PLUGIN_SLUG . '/v1' );

/* REGISTER PLUGIN FUNCTIONS ---------------------- */

/**
 * Initializes the plugin's code.
 *
 * This ensures all variables are contained within the declared
 * namespace to not contaminate the global namespace.
 *
 * @since 4.0.0
 */
function init() {

	// Register class autoloading.
	require_once PLUGIN_PATH . 'src/includes/class-autoloader.php';
	Autoloader::register();

	// Plugins loaded.
	add_action(
		'plugins_loaded',
		function () {
			// Ensure database tables are installed.
			Database_Manager::init();
			Database_Manager::install_all_tables();
			// Enqueue automation actions.
			Automations\Events::add_actions();
		}
	);

	// Register public functionality.
	Admin_Notices::register();
	Request_Token::register();
	REST_Server::register();
	Shortcodes::register();
	Uninstaller::register();
	Upgrader::register();

	// Register admin functionality.
	if ( is_admin() ) {
		Admin_Pages::register();
		Admin_Widgets::register();
	}
}

// Load the plugin code.
init();
