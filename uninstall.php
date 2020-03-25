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

if ( ! class_exists( '\WC_AM_Client_2_7' ) ) {
  include_once( plugin_dir_path( __FILE__ ) . 'wc-am-client.php' );
}

if ( class_exists( '\WC_AM_Client_2_7' ) ) {
  $wcam = new \WC_AM_Client_2_7( __FILE__, '', '1.0.0', 'plugin', 'https://www.purpleturtlecreative.com/', 'Completionist' );
}

if ( isset( $wcam ) && is_object( $wcam ) ) {
  $wcam->uninstall();
  $wc_am_product_id_key = isset( $wcam->wc_am_product_id ) ? $wcam->wc_am_product_id : '';
  $wc_am_data_key = isset( $wcam->data_key ) ? $wcam->data_key : '';
} else {
  $wc_am_product_id_key = '';
  $wc_am_data_key = '';
}

if ( is_multisite() ) {
  $site_ids = get_sites( [ 'fields' => 'ids' ] );
  foreach ( $site_ids as $site_id ) {
    switch_to_blog( $site_id );
    Options::delete_all();
    delete_option( $wc_am_product_id_key );
    delete_option( $wc_am_data_key );
    restore_current_blog();
  }
} else {
  Options::delete_all();
  delete_option( $wc_am_product_id_key );
  delete_option( $wc_am_data_key );
}

