<?php
/**
 * Cache Manager class
 *
 * Manages transient and object cache data.
 *
 * @since 3.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Cache_Manager' ) ) {
	/**
	 * Manages transient and object cache data.
	 */
	class Cache_Manager {

		/**
		 * The transient prefix.
		 *
		 * Note that this value should NEVER be hashed or manipulated when
		 * creating cache keys because it is used for general querying.
		 *
		 * @since 3.1.0
		 *
		 * @var string TRANSIENT_PREFIX
		 */
		private const TRANSIENT_PREFIX = 'ptc-completionist_';

		/**
		 * Gets the full cache key name for the given key.
		 *
		 * @since 3.1.0
		 *
		 * @param string $key The cache key name after the plugin prefix. Should not
		 * exceed 150 characters in length.
		 * @return string The full cache key name.
		 */
		public static function get_cache_key( string $key ) : string {
			return self::TRANSIENT_PREFIX . $key;
		}

		/**
		 * Gets the lifespan to use for transients.
		 *
		 * @since 3.1.0
		 */
		public static function get_transient_lifespan() {
			// @TODO - Eventually create a plugin setting for this.
			// @TODO - IMPORTANT: Cache lifespan must be changed to 5 minutes before release.
			return 30 * MINUTE_IN_SECONDS;
		}

		/**
		 * Gets all active transient keys for this plugin.
		 *
		 * @since 3.1.0
		 *
		 * @return string[] The transient key names.
		 */
		public static function get_all_transient_keys() : array {
			// @TODO - Query all transient keys with our prefix.
			return [];
		}

		/**
		 * Deletes all transients from the database.
		 *
		 * @since 3.1.0
		 */
		public static function purge_transients() {
			// @TODO - IMPORTANT: Purge the cache.
		}
	}//end class
}//end if class_exists
