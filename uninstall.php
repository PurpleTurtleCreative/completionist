<?php
/**
 * Uninstall script
 *
 * Uninstalls all custom plugin data for a single site or multisite network.
 *
 * @since 1.0.0
 */

namespace PTC_Completionist;

defined( 'WP_UNINSTALL_PLUGIN' ) || die();

/* Delete all plugin options */

include_once __DIR__ . '/src/includes/class-options.php';
include_once __DIR__ . '/src/includes/class-database-manager.php';

if ( function_exists( 'get_sites' ) ) {
	$site_ids = get_sites( [ 'fields' => 'ids' ] );
	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		uninstall_for_current_blog();
		restore_current_blog();
	}//end foreach $site_ids
} else {
	uninstall_for_current_blog();
}

/**
 * Attempts to uninstall this plugin's data for the current blog.
 */
function uninstall_for_current_blog() {

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

	// Remove PUC's data.
	delete_site_option( 'external_updates-completionist' );
}
