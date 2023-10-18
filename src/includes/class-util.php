<?php
/**
 * Util class
 *
 * @since 3.4.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

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
	 * @since 3.5.0
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

	/**
	 * Asserts whether a given string starts with any of the
	 * provided prefixes.
	 *
	 * @since 3.5.0
	 *
	 * @param string   $subject The string to check.
	 * @param string[] $prefixes The prefix strings.
	 * @param bool     $case_sensitive Optional. If to use
	 * case-sensitive comparisons. Default true.
	 * @return bool If the string starts with any of the prefixes.
	 */
	public static function str_starts_with_any(
		string $subject,
		array $prefixes,
		bool $case_sensitive = true
	) : bool {

		if ( true === $case_sensitive ) {
			// Case-sensitive comparison.
			foreach ( $prefixes as &$prefix ) {
				if ( substr( $subject, 0, strlen( $prefix ) ) === $prefix ) {
					return true;
				}
			}
		} else {
			// Case-insensitive comparison.
			$subject = strtolower( $subject );
			foreach ( $prefixes as $prefix ) {
				$prefix = strtolower( $prefix );
				if ( substr( $subject, 0, strlen( $prefix ) ) === $prefix ) {
					return true;
				}
			}
		}

		// No matches.
		return false;
	}

	/**
	 * Removes empty values from an array.
	 *
	 * Note that indexes are maintained which means you may want
	 * to use `array_values()` on indexed arrays after filtering.
	 *
	 * @since [unreleased]
	 *
	 * @param array $arr An indexed or associative array.
	 * @return array The filtered array.
	 */
	public static function remove_empty_values( array $arr ) : array {
		return array_filter(
			$arr,
			function ( $value ) {
				return ! empty( $value );
			}
		);
	}
}//end class
