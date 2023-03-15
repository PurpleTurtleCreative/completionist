<?php
/**
 * Util class
 *
 * @since 3.4.0
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
		 * @since 3.4.0
		 *
		 * @param object|array $data An iterable object or array to modify.
		 * @param string       $prop The name of the property to remove.
		 */
		public static function deep_unset_prop( &$data, string $prop ) {

			if ( is_array( $data ) ) {
				unset( $data[ $prop ] );
			} elseif ( is_object( $data ) ) {
				unset( $data->{$prop} );
			}

			foreach ( $data as &$value ) {
				if ( is_array( $value ) || is_object( $value ) ) {
					self::deep_unset_prop( $value, $prop );
				}
			}
		}

		/**
		 * Modifies object properties and array keys with the given name.
		 *
		 * @since [unreleased]
		 *
		 * @param object|array $data An iterable object or array to modify.
		 * @param string       $prop The name of the property to remove.
		 * @param callable     $modify The modification function. It
		 * receives a reference to the object or array field to modify.
		 */
		public static function deep_modify_prop(
			&$data,
			string $prop,
			$modify
		) {

			if ( is_array( $data ) && isset( $data[ $prop ] ) ) {
				$modify( $data[ $prop ] );
			} elseif ( is_object( $data ) && isset( $data->{$prop} ) ) {
				$modify( $data->{$prop} );
			}

			foreach ( $data as &$value ) {
				if ( is_array( $value ) || is_object( $value ) ) {
					self::deep_modify_prop( $value, $prop, $modify );
				}
			}
		}
	}//end class
}//end if class exists
