<?php
/**
 * Automations Events class
 *
 * Provides event field information and processing for automation jobs.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Automations;

defined( 'ABSPATH' ) || die();

require_once 'class-data.php';
require_once 'class-automation.php';

use \PTC_Completionist\Automation;

if ( ! class_exists( __NAMESPACE__ . '\Events' ) ) {
  /**
   * Static class for hooking in automations.
   */
  class Events {

    const USER_OPTIONS = [
      'user_register' => 'User is Created',
      'profile_update' => 'User is Updated',
      'delete_user' => 'User is Deleted',
    ];

    const POST_OPTIONS = [
      'wp_insert_post' => 'Post is Created',
      'post_updated' => 'Post is Updated',
      'trash_post' => 'Post is Trashed',
    ];

    /**
     * Hook all automations into WordPress execution.
     *
     * @since 1.1.0
     */
    static function add_actions() {

      /* User Events */

      $user_hook_names = array_keys( self::USER_OPTIONS );
      foreach ( $user_hook_names as $hook_name ) {
        if ( Data::actions_exist_for( $hook_name ) ) {
          add_action( $hook_name, function( $user_id ) use ( $hook_name ) {
            $user = new \WP_User( $user_id );
            $automation_ids = Data::get_all_automation_ids_for( $hook_name );
            foreach ( $automation_ids as $id ) {
              ( new Automation( $id, [ 'user' => $user ] ) )->maybe_run_actions();
            }
          }, 10, 1 );
        }
      }

      /* Post Events */

      if ( Data::actions_exist_for( 'wp_insert_post' ) ) {
        add_action( 'wp_insert_post', function( $post_id, $the_post, $update ) {
          if ( ! $update ) {
            $automation_ids = Data::get_all_automation_ids_for( 'wp_insert_post' );
            if ( count( $automation_ids ) > 0 ) {
              foreach ( $automation_ids as $id ) {
                ( new Automation( $id, [ 'post' => $the_post ] ) )->maybe_run_actions();
              }
            }
          }
        }, 10, 3 );
      }

      if ( Data::actions_exist_for( 'post_updated' ) ) {
        add_action( 'post_updated', function( $post_id, $post_after, $post_before ) {
          $automation_ids = Data::get_all_automation_ids_for( 'post_updated' );
          if ( count( $automation_ids ) > 0 ) {
            foreach ( $automation_ids as $id ) {
              ( new Automation( $id, [ 'post' => $post_after ] ) )->maybe_run_actions();
            }
          }
        }, 10, 3 );
      }

      if ( Data::actions_exist_for( 'trash_post' ) ) {
        add_action( 'trash_post', function( $post_id ) {
          $automation_ids = Data::get_all_automation_ids_for( 'trash_post' );
          if ( count( $automation_ids ) > 0 ) {
            $the_post = get_post( $post_id );
            foreach ( $automation_ids as $id ) {
              ( new Automation( $id, [ 'post' => $the_post ] ) )->maybe_run_actions();
            }
          }
        }, 10, 1 );
      }

    }//end enqueue_automations()

  }//end class
}//end if class_exists
