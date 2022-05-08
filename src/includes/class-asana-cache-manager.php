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
	private const TRANSIENT_PREFIX = 'ptc-asana_';

	// .. Settings .. //

	/**
	 * Gets the TTL seconds for task object caching.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return int
	 */
	private function get_task_expiration_seconds() : int {
		return self::DEFAULT_EXPIRATION_SECONDS;
	}

	/**
	 * Gets the TTL seconds for users' task visibility caching.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return int
	 */
	private function get_task_visibility_expiration_seconds() {
		return self::DEFAULT_EXPIRATION_SECONDS;
	}

	/**
	 * Gets the TTL seconds for workspace projects object caching.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return int
	 */
	private function get_workspace_projects_expiration_seconds() {
		return 2 * self::DEFAULT_EXPIRATION_SECONDS;
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
	private function get_task_key( string $task_gid ) : string {
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
	private function get_user_task_visibility_key( int $user_id ) : string {

		$tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $tag_gid ) {
			throw new \Exception( 'A site tag is required to retrieve your Asana tasks. Please set a site tag in Completionist\'s settings.', 409 );
		}

		return self::TRANSIENT_PREFIX . "visible_tasks_{$user_id}_{$tag_gid}";
	}

	/**
	 * Gets the transient key name for the Asana workspace's projects.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return string The transient name.
	 *
	 * @throws \Exception If a workspace has not been set
	 * in the plugin's settings.
	 */
	private function get_workspace_projects_key() : string {

		$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		if ( '' === $workspace_gid ) {
			throw new \Exception( 'An Asana workspace is required to retrieve Asana project data. Please set a workspace in Completionist\'s settings.', 400 );
		}

		return self::TRANSIENT_PREFIX . "projects_{$workspace_gid}";
	}

	// .. Modifiers .. //

	/**
	 * Caches an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param \stdClass $task The task object.
	 */
	public function save_task( \stdClass $task ) {
		\set_transient(
			self::get_task_key( $task->gid ),
			$task,
			self::get_task_expiration_seconds()
		);
	}

	/**
	 * Caches an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string[] $task_gids The tasks visible to the user.
	 * @param int      $user_id The WordPress user's ID.
	 */
	public function save_visible_tasks_for_user( array $task_gids, int $user_id ) {
		\set_transient(
			self::get_user_task_visibility_key( $user_id ),
			$task_gids,
			self::get_task_expiration_seconds()
		);
	}

	// .. Purging .. //

	/**
	 * Deletes the cache entry of an Asana task object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task's GID.
	 */
	public function forget_task( string $task_gid ) {
		\delete_transient(
			self::get_task_key( $task_gid )
		);
	}

	public function delete_all() {}
}
