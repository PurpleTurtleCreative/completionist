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
		 */
		public static function get_cache_key( string $key ) {
			return self::TRANSIENT_PREFIX . $key;
		}

		/**
		 * Gets the lifespan to use for transients.
		 *
		 * @since 3.1.0
		 */
		public static function get_transient_lifespan() {
			// @TODO - Eventually create a plugin setting for this.
			return 30 * MINUTE_IN_SECONDS;
		}

		/**
		 * Deletes all transients from the database.
		 *
		 * @since 3.1.0
		 */
		public static function purge_transients() {
			// @TODO.
		}
	}//end class
}//end if class_exists
