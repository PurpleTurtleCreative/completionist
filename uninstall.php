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

include_once __DIR__ . '/src/class-options.php';

if ( is_multisite() ) {
  $site_ids = get_sites( [ 'fields' => 'ids' ] );
  foreach ( $site_ids as $site_id ) {
    switch_to_blog( $site_id );
    Options::delete_all();
    restore_current_blog();
  }
} else {
  Options::delete_all();
}
