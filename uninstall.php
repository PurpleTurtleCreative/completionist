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

include_once __DIR__ . '/src/class-options.php';
include_once __DIR__ . '/src/class-database-manager.php';

if ( ! class_exists( '\WC_AM_Client_2_7' ) ) {
  include_once( plugin_dir_path( __FILE__ ) . 'wc-am-client.php' );
}

if ( class_exists( '\WC_AM_Client_2_7' ) ) {
  $wcam = new \WC_AM_Client_2_7( __FILE__, '', '1.1.0', 'plugin', 'https://www.purpleturtlecreative.com/', 'Completionist' );
}

$wc_am_product_id_key = '';
$wc_am_data_key = '';

if ( isset( $wcam ) && is_a( $wcam, '\WC_AM_Client_2_7' ) ) {
  $wcam->uninstall();
  $wc_am_product_id_key = isset( $wcam->wc_am_product_id ) ? $wcam->wc_am_product_id : '';
  $wc_am_data_key = isset( $wcam->data_key ) ? $wcam->data_key : '';
}

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

/* HELPERS */

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

  if (
    function_exists( 'delete_option' )
    && $wc_am_product_id_key != ''
    && $wc_am_data_key != ''
  ) {
    delete_option( $wc_am_product_id_key );
    delete_option( $wc_am_data_key );
  }
}
