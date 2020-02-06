<?php
/**
 * Processes settings form submissions and displays process notices.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-options.php';
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';

if (
  isset( $_POST['asana_connect'] )
  && isset( $_POST['asana_pat'] )
  && isset( $_POST['asana_connect_nonce'] )
  && wp_verify_nonce( $_POST['asana_connect_nonce'], 'connect_asana' ) !== FALSE
) {

  try {
    $did_save_pat = Options::save( Options::ASANA_PAT, $_POST['asana_pat'] );
  } catch ( \Exception $e ) {
    echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
    $did_save_pat = FALSE;
  }

  if ( $did_save_pat === TRUE ) {

    try {
      $asana = Asana_Interface::get_client();
      $me = Asana_Interface::get_me();
      $did_save_gid = Options::save( Options::ASANA_USER_GID, $me->gid );
      $did_delete_pat = FALSE;
      $did_delete_gid = FALSE;
    } catch ( \Exception $e ) {
      echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
      $did_save_gid = FALSE;
      $did_delete_pat = Options::delete( Options::ASANA_PAT );
      $did_delete_gid = Options::delete( Options::ASANA_USER_GID );
    }

    if ( $did_delete_pat === TRUE ) {
      echo '<p class="notice notice-error">An error occurred, causing your Personal Access Token to not be saved.</p>';
    } elseif ( $did_save_gid === TRUE ) {
      echo '<p class="notice notice-success">Your Asana account was successfully connected!</p>';
    }

  }//end if did_save_pat

}//end if asana_connect

if (
  isset( $_POST['asana_disconnect'] )
  && isset( $_POST['asana_disconnect_nonce'] )
  && wp_verify_nonce( $_POST['asana_disconnect_nonce'], 'disconnect_asana' ) !== FALSE
) {

  $did_delete_pat = Options::delete( Options::ASANA_PAT );
  $did_delete_gid = Options::delete( Options::ASANA_USER_GID );
  if (
    $did_delete_pat === TRUE
    && $did_delete_gid === TRUE
  ) {
    echo '<p class="notice notice-success">Your Asana account was successfully forgotten!</p>';
  } elseif ( $did_delete_pat === TRUE ) {
    echo '<p class="notice notice-success">Your Asana account was successfully disconnected.</p>';
  } else {
    echo '<p class="notice notice-error">Your Asana account could not be disconnected.</p>';
  }

}//end if asana_disconnect

if (
  isset( $_POST['asana_workspace_save'] )
  && isset( $_POST['asana_workspace'] )
  && isset( $_POST['asana_workspace_save_nonce'] )
  && wp_verify_nonce( $_POST['asana_workspace_save_nonce'], 'asana_workspace_save' ) !== FALSE
  && current_user_can( 'manage_options' )
) {

  try {
    $did_save_workspace = Options::save( Options::ASANA_WORKSPACE_GID, $_POST['asana_workspace'] );
  } catch ( \Exception $e ) {
    echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
    $did_save_workspace = FALSE;
  }

  if ( $did_save_workspace ) {
    echo '<p class="notice notice-success">This site\'s workspace was updated successfully.</p>';
  }

}//end if asana_workspace_save
