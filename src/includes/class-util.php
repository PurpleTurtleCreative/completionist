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
	 * Unsets all array keys and object properties that are not
	 * of the given names.
	 *
	 * @since 4.0.0
	 *
	 * @param array|object $data   An iterable object or array to modify.
	 * @param string[]     $fields The names of the array keys or
	 *                             object properties to keep.
	 */
	public static function deep_unset_except( &$data, array $fields ) {

		if ( is_array( $data ) ) {
			foreach ( $data as $key => &$_ ) {
				if ( is_string( $key ) && ! in_array( $key, $fields, true ) ) {
					unset( $data[ $key ] );
				}
			}
		} elseif ( is_object( $data ) ) {
			foreach ( $data as $prop => &$_ ) {
				if ( ! in_array( $prop, $fields, true ) ) {
					unset( $data->{$prop} );
				}
			}
		}

		foreach ( $data as &$value ) {
			if ( is_array( $value ) || is_object( $value ) ) {
				static::deep_unset_except( $value, $fields );
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
}//end class
