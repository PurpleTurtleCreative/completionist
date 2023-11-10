<?php
/**
 * Automations Fields class
 *
 * Provides object field information and processing for automation conditions
 * and action templating (aka merge fields).
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Automations;

defined( 'ABSPATH' ) || die();

/**
 * Static class processing object fields.
 */
class Fields {

	/**
	 * A map of user object translations to their readable labels.
	 *
	 * @var string[] USER_OPTIONS
	 */
	public const USER_OPTIONS = array(
		'user.ID'           => 'User ID',
		'user.user_login'   => 'Username',
		'user.user_email'   => 'Email',
		'user.display_name' => 'Display Name',
		'user.roles'        => 'Roles',
		'user.first_name'   => 'First Name',
		'user.last_name'    => 'Last Name',
	);

	/**
	 * A map of post object translations to their readable labels.
	 *
	 * @var string[] POST_OPTIONS
	 */
	public const POST_OPTIONS = array(
		'post.ID'          => 'Post ID',
		'post.post_author' => 'Author (User ID)',
		'post.post_title'  => 'Title',
		'post.post_status' => 'Status',
		'post.post_type'   => 'Type',
	);

	/**
	 * The comparison method labels.
	 *
	 * @var string[] COMPARISON_METHODS
	 */
	public const COMPARISON_METHODS = array(
		'equals',
		'does not equal',
		'less than',
		'greater than',
		'is empty',
		'is filled',
		'is in (csv)',
		'starts with',
		'ends with',
		'contains',
	);

	/**
	 * Evaluates an automation condition entry using the provided objects.
	 *
	 * @since 1.1.0
	 *
	 * @param \stdClass $condition The condition record object for evaluation.
	 * @param object[]  $translation_objects {
	 *     An array of objects to translate field values.
	 *
	 *     @type \WP_Post $post A post object for translation.
	 *     @type \WP_User $user A user object for translation.
	 * }
	 * @return bool If the provided object met the condition.
	 */
	public static function evaluate_condition( \stdClass $condition, array $translation_objects ) : bool {

		if ( ! in_array( $condition->comparison_method, self::COMPARISON_METHODS ) ) {
			error_log( 'Failed to evaluate automation condition with unrecognized comparison method: ' . esc_html( $condition->comparison_method ) );
			return false;
		}

		$property_value = self::get_template_value( $condition->property, $translation_objects );
		if ( $property_value == $condition->property ) {
			error_log( 'Failed to evaluate automation condition with property error.' );
			return false;
		}

		switch ( $condition->comparison_method ) {
			case 'is empty':
				return ( trim( $property_value ) == '' );
			case 'is filled':
				return ( trim( $property_value ) != '' );
			case 'is in (csv)':
				$csv_values = explode( ',', $condition->value );
				return in_array( $property_value, $csv_values );
			case 'starts with':
				return ( strpos( $property_value, $condition->value, 0 ) === 0 );
			case 'ends with':
				$offset = strlen( $property_value ) - strlen( $condition->value );
				if ( $offset < 0 ) {
					return false;
				}
				return ( strpos( $property_value, $condition->value, $offset ) === $offset );
			case 'contains':
				return ( strpos( $property_value, $condition->value, 0 ) !== false );
		}

		if ( is_numeric( $property_value ) && is_numeric( $condition->value ) ) {
			switch ( $condition->comparison_method ) {
				case 'equals':
					return ( $property_value == $condition->value );
				case 'does not equal':
					return ( $property_value != $condition->value );
				case 'less than':
					return ( $property_value < $condition->value );
				case 'greater than':
					return ( $property_value > $condition->value );
			}
		} else {
			switch ( $condition->comparison_method ) {
				case 'equals':
					return ( strcasecmp( $property_value, $condition->value ) === 0 );
				case 'does not equal':
					return ( strcasecmp( $property_value, $condition->value ) !== 0 );
				case 'less than':
					return ( strcasecmp( $property_value, $condition->value ) < 0 );
				case 'greater than':
					return ( strcasecmp( $property_value, $condition->value ) > 0 );
			}
		}

		error_log( 'Failed to evaluate automation condition with unrecognized comparison method: ' . esc_html( $condition->comparison_method ) );
		return false;
	}

	/**
	 * Translates all merge fields using the provided objects. Merge fields use
	 * the pattern: {object_key.property_key}
	 *
	 * @since 1.1.0
	 *
	 * @param string   $string_with_fields The string containing merge fields.
	 * @param object[] $translation_objects {
	 *     An array of objects to translate field values.
	 *
	 *     @type \WP_Post $post A post object for translation.
	 *     @type \WP_User $user A user object for translation.
	 * }
	 * @return string The translated string.
	 */
	public static function translate_templates( string $string_with_fields, array $translation_objects ) : string {

		$translated_string = preg_replace_callback(
			'/{([a-zA-Z_]+?\.[a-zA-Z_]+?)}/',
			function ( $matches ) use ( $translation_objects ) {
				return self::get_template_value( $matches[1], $translation_objects );
			},
			$string_with_fields
		);

		return ( null === $translated_string ) ? $string_with_fields : $translated_string;
	}

	/**
	 * Gets the object property value described by a merge field string.
	 *
	 * @since 1.1.0
	 *
	 * @param string   $field_accessor The merge field string to translate.
	 * @param object[] $translation_objects {
	 *     An array of objects to translate field values.
	 *
	 *     @type \WP_Post $post A post object for translation.
	 *     @type \WP_User $user A user object for translation.
	 * }
	 * @return mixed The translated object property value. If an error occurred,
	 * the original field accessor string will be returned.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function get_template_value( string $field_accessor, array $translation_objects ) {

		try {

			if ( empty( $field_accessor ) || strpos( $field_accessor, '.' ) === false ) {
				throw new \Exception( 'Invalid field accessor format. Should follow form: object_key.property_key' );
			}

			$field = explode( '.', $field_accessor );

			if ( ! isset( $translation_objects[ $field[0] ] ) ) {
				throw new \Exception( "Missing object param key, {$field[0]}" );
			}

			$object = $translation_objects[ $field[0] ];

			if ( 'user' == $field[0] ) {
				if ( ! is_a( $object, '\WP_User' ) ) {
					throw new \Exception( 'Provided object was not \WP_User instance' );
				}
			} elseif ( 'post' == $field[0] ) {
				if ( ! is_a( $object, '\WP_Post' ) ) {
					throw new \Exception( 'Provided object was not \WP_User instance' );
				}
				$actual_post_id = wp_is_post_revision( $object );
				if ( $actual_post_id ) {
					$object = get_post( $actual_post_id );
				}
			} else {
				throw new \Exception( "Invalid field object key, {$field[0]}" );
			}

			$value = $object->{$field[1]};
			return ( null === $value ) ? $field_accessor : $value;

		} catch ( \Exception $e ) {
			error_log( "[PTC Completionist] Failed to process automation template field: {$field_accessor} <-- " . $e->getMessage() );
			return $field_accessor;
		}
	}//end get_template_value()
}//end class
