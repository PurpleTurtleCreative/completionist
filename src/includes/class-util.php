<?php
/**
 * Util class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Util' ) ) {
	/**
	 * A static class to provide generic utility functions.
	 */
	class Util {

		/**
		 * Unsets object properties and array keys with the given name.
		 *
		 * @since [unreleased]
		 *
		 * @param object|array $data An iterable object or array to modify.
		 * @param string       $prop The name of the property to remove.
		 */
		public static function deep_unset_prop( &$data, string $prop ) {
			foreach ( $data as $key => $value ) {
				if ( is_object( $value ) || is_array( $value ) ) {
					self::deep_unset_prop( $value, $prop );
				} elseif ( $prop === $key ) {
					if ( is_array( $data ) ) {
						unset( $data[ $key ] );
					} elseif ( is_object( $data ) ) {
						unset( $data->{$key} );
					}
				}
			}
		}
	}//end class
}//end if class exists
