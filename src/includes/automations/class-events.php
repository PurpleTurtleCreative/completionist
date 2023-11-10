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

use PTC_Completionist\Automation;

/**
 * Static class for hooking in automations.
 */
class Events {

	public const USER_OPTIONS = array(
		'user_register' => 'User is Created',
		'profile_update' => 'User is Updated',
		'delete_user' => 'User is Deleted',
	);

	public const POST_OPTIONS = array(
		'wp_insert_post' => 'Post is Created',
		'post_updated' => 'Post is Updated',
		'trash_post' => 'Post is Trashed',
	);

	public const CUSTOM_OPTIONS = array(
		'custom_action__' => 'Custom Action Hook',
		'custom_filter__' => 'Custom Filter Hook',
	);

	/**
	 * Hook all automations into WordPress execution.
	 *
	 * @since 1.1.0
	 */
	public static function add_actions() {

		/* User Events */

		$user_hook_names = array_keys( self::USER_OPTIONS );
		foreach ( $user_hook_names as $hook_name ) {
			if ( Data::actions_exist_for( $hook_name ) ) {
				add_action(
					$hook_name,
					function ( $user_id ) use ( $hook_name ) {
						$user = new \WP_User( $user_id );
						$automation_ids = Data::get_all_automation_ids_for( $hook_name );
						foreach ( $automation_ids as $id ) {
							( new Automation( $id, array( 'user' => $user ) ) )->maybe_run_actions();
						}
					},
					10,
					1
				);
			}
		}

		/* Post Events */

		if ( Data::actions_exist_for( 'wp_insert_post' ) ) {
			add_action(
				'transition_post_status',
				function ( $new_status, $old_status, $the_post ) {
					if (
					$old_status !== $new_status &&
					in_array( $old_status, array( 'new', 'auto-draft' ), true ) &&
					! in_array( $new_status, array( 'new', 'auto-draft' ), true ) &&
					false === wp_is_post_revision( $the_post )
					) {
						/**
						 * Note that the original hook for 'Post is Created'
						 * was 'wp_insert_post' but this proved to cause
						 * duplicate or missed executions. This now makes
						 * no sense in the database, but it's more semantic
						 * than 'transition_post_status' so I still prefer it.
						 *
						 * @since 3.10.0
						 * @ignore
						 */
						$automation_ids = Data::get_all_automation_ids_for( 'wp_insert_post' );
						if ( count( $automation_ids ) > 0 ) {
							foreach ( $automation_ids as $id ) {
								( new Automation( $id, array( 'post' => $the_post ) ) )->maybe_run_actions();
							}
						}
					}
				},
				10,
				3
			);
		}

		if ( Data::actions_exist_for( 'post_updated' ) ) {
			add_action(
				'post_updated',
				function ( $post_id, $post_after, $post_before ) {
					$automation_ids = Data::get_all_automation_ids_for( 'post_updated' );
					if ( count( $automation_ids ) > 0 ) {
						$has_changes = false;
						foreach ( $post_after as $field => $val ) {
							if (
							$val != $post_before->{$field}
							&& 'post_modified' != $field
							&& 'post_modified_gmt' != $field
							) {
								$has_changes = true;
								break;
							}
						}
						if ( $has_changes ) {
							foreach ( $automation_ids as $id ) {
								( new Automation( $id, array( 'post' => $post_after ) ) )->maybe_run_actions();
							}
						}
					}
				},
				10,
				3
			);
		}

		if ( Data::actions_exist_for( 'trash_post' ) ) {
			add_action(
				'trash_post',
				function ( $post_id ) {
					$automation_ids = Data::get_all_automation_ids_for( 'trash_post' );
					if ( count( $automation_ids ) > 0 ) {
						$the_post = get_post( $post_id );
						foreach ( $automation_ids as $id ) {
							( new Automation( $id, array( 'post' => $the_post ) ) )->maybe_run_actions();
						}
					}
				},
				10,
				1
			);
		}

		/* Custom Events */

		$automation_ids = Data::get_all_automation_ids_for( 'custom_action__%' );
		foreach ( $automation_ids as $id ) {
			$automation = new Automation( $id );
			add_action(
				str_replace( 'custom_action__', '', $automation->hook_name ),
				function () use ( $automation ) {
					$automation->maybe_run_actions();
				},
				10,
				0
			);
		}

		$automation_ids = Data::get_all_automation_ids_for( 'custom_filter__%' );
		foreach ( $automation_ids as $id ) {
			$automation = new Automation( $id );
			add_filter(
				str_replace( 'custom_filter__', '', $automation->hook_name ),
				function ( $filtered_value ) use ( $automation ) {
					$automation->maybe_run_actions();
					return $filtered_value;
				},
				10,
				1
			);
		}
	}//end add_actions()
}//end class
