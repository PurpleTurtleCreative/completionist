<?php
/**
 * Asana Cache Manager class
 *
 * Manages local caching of Asana data.
 *
 * @since [UNRELEASED]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';

/**
 * Static class to manage Asana data cache keys, expiration times, etc.
 */
class Asana_Cache_Manager {

	/**
	 * The default cache entry TTL in seconds.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var int DEFAULT_EXPIRATION_SECONDS
	 */
	private const DEFAULT_EXPIRATION_SECONDS = 3 * \MINUTE_IN_SECONDS;

	/**
	 * The prefix for all transient keys managed by this class.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var string TRANSIENT_PREFIX
	 */
	private const TRANSIENT_PREFIX = 'ptc-asana-cache_';

	// .. Settings .. //

	/**
	 * Gets the TTL seconds for task object caching.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return int
	 */
	private static function get_task_expiration_seconds() : int {
		return self::DEFAULT_EXPIRATION_SECONDS;
	}

	/**
	 * Gets the TTL seconds for users' task visibility caching.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return int
	 */
	private static function get_task_visibility_expiration_seconds() {
		return self::DEFAULT_EXPIRATION_SECONDS;
	}

	// .. Key Generators .. //

	/**
	 * Gets the transient key name for an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The Asana task's GID.
	 * @return string The transient name.
	 */
	private static function get_task_key( string $task_gid ) : string {
		return self::TRANSIENT_PREFIX . "task_{$task_gid}";
	}

	/**
	 * Gets the transient key name for a user's visible Asana tasks.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param int $user_id The WordPress user's ID.
	 * @return string The transient name.
	 *
	 * @throws \Exception If a site tag has not been set
	 * in the plugin's settings.
	 */
	private static function get_user_task_visibility_key( int $user_id ) : string {

		$tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $tag_gid ) {
			throw new \Exception( 'A site tag is required to retrieve your Asana tasks. Please set a site tag in Completionist\'s settings.', 409 );
		}

		return self::TRANSIENT_PREFIX . "visible_tasks_{$user_id}_{$tag_gid}";
	}

	// .. Key Queries .. //

	/**
	 * Gets an array of transient names currently in the WordPress database that
	 * match the given pattern. Note that only transients with a set expiration
	 * time will be searched.
	 *
	 * This function searches the WordPress database which may actually be
	 * bypassed by the Transient API functions if \wp_using_ext_object_cache()
	 * returns true. Installations where the WordPress cache has been extended
	 * will likely have unexpected results.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $query_name_like The transient name SQL LIKE pattern.
	 * Underscores and dashes are escaped as literals instead of single
	 * character wildcards due to common usage of kabob-casing and snake_casing.
	 * @return string[] The transient names.
	 */
	private static function get_transient_names_like( string $query_name_like ) : array {
		// Ensure quotes and other characters are properly escaped.
		// Use longest transient prefix in case name begins with a wildcard.
		// This means only transients with a set expiration will be searched.
		$query_name_like = esc_sql( "_transient_timeout_{$query_name_like}" );
		// Ensure underscores and dashes are taken literally.
		$query_name_like = str_replace( '_', '\\_', $query_name_like );
		$query_name_like = str_replace( '-', '\\-', $query_name_like );
		// Search the database.
		$transient_keys = $GLOBALS['wpdb']->get_col(
			$GLOBALS['wpdb']->prepare(
				"
					SELECT option_name
					FROM {$GLOBALS['wpdb']->options}
					WHERE option_name LIKE %s ESCAPE '\\';
				",
				$query_name_like
			)
		);
		// Extract the transient key names.
		foreach ( $transient_keys as &$key ) {
			$key = str_replace( '_transient_timeout_', '', $key );
		}
		// Done.
		return $transient_keys;
	}

	/**
	 * Gets all Asana task object cache keys that are currently in the WordPress
	 * database.
	 *
	 * @since [UNRELEASED]
	 */
	private static function get_all_task_keys() : array {
		return self::get_transient_names_like( self::TRANSIENT_PREFIX . 'task_%'; );
	}

	/**
	 * Gets all users' Asana task visibility cache keys that are currently in the
	 * WordPress database.
	 *
	 * @since [UNRELEASED]
	 */
	private static function get_all_user_task_visibility_keys() : array {
		return self::get_transient_names_like( self::TRANSIENT_PREFIX . 'visible_tasks_%_%' );
	}

	/**
	 * Gets all Asana cache keys that are currently in the WordPress database.
	 *
	 * @since [UNRELEASED]
	 */
	private static function get_all_keys() : array {
		return self::get_transient_names_like( self::TRANSIENT_PREFIX . '%' );
	}

	// .. Modifiers .. //

	/**
	 * Caches an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param \stdClass $task The task object.
	 */
	public static function save_task( \stdClass $task ) {
		\set_transient(
			self::get_task_key( $task->gid ),
			$task,
			self::get_task_expiration_seconds()
		);
	}

	/**
	 * Caches a user's visible Asana task GIDs.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string[] $task_gids The tasks visible to the user.
	 * @param int      $user_id The WordPress user's ID.
	 */
	public static function save_visible_tasks_for_user( array $task_gids, int $user_id ) {
		\set_transient(
			self::get_user_task_visibility_key( $user_id ),
			$task_gids,
			self::get_task_expiration_seconds()
		);
	}

	// .. Purging .. //

	/**
	 * Removes the task GID from all users' task visibility caches and purges
	 * the task object cache entry.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task's GID.
	 */
	public static function forget_task( string $task_gid ) {
		// @TODO - write this function's body.
	}

	/**
	 * Deletes the cache entry of an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task's GID.
	 */
	public static function purge_task( string $task_gid ) {
		\delete_transient(
			self::get_task_key( $task_gid )
		);
	}

	/**
	 * Deletes the cache entry of a user's visible Asana task GIDs.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param int $user_id The WordPress user's ID.
	 */
	public static function purge_visible_tasks_for_user( int $user_id ) {
		\delete_transient(
			self::get_user_task_visibility_key( $user_id )
		);
	}

	/**
	 * Deletes all users' Asana task visibility cache entries.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param int $user_id The WordPress user's ID.
	 */
	public static function purge_visible_tasks_for_all_users() {
		foreach ( self::get_all_user_task_visibility_keys() as &$key ) {
			\delete_transient( $key );
		}
	}

	/**
	 * Deletes all cache entries managed by this plugin.
	 *
	 * @since [UNRELEASED]
	 */
	public static function purge_all() {
		foreach ( self::get_all_keys() as &$key ) {
			\delete_transient( $key );
		}
	}
}
