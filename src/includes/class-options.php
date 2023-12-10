<?php
/**
 * Options class
 *
 * Manages data stored in various WordPress tables such as options, usermeta,
 * and postmeta.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Static class to manage blog-level options.
 */
class Options {

	/**
	 * The usermeta key name for the personal access token used to authorize API
	 * usage. This key's value should be of type string.
	 *
	 * @since 1.0.0
	 *
	 * @var string ASANA_PAT
	 */
	public const ASANA_PAT = '_ptc_asana_pat';

	/**
	 * The usermeta key name for the user's resource id in asana.
	 * This meta key's value should be of type string and contain only digits.
	 *
	 * @since 1.0.0
	 *
	 * @var string ASANA_USER_GID
	 */
	public const ASANA_USER_GID = '_ptc_asana_user_gid';

	/**
	 * The option key name for the Asana workspace's GID linking to this blog.
	 * This key's value should be of type string and contain only digits.
	 *
	 * @since 1.0.0
	 *
	 * @var string ASANA_WORKSPACE_GID
	 */
	public const ASANA_WORKSPACE_GID = '_ptc_asana_workspace_gid';

	/**
	 * The option key name for the blog's designated Asana tag's GID.
	 * This key's value should be of type string and contain only digits.
	 *
	 * @since 1.0.0
	 *
	 * @var string ASANA_WORKSPACE_GID
	 */
	public const ASANA_TAG_GID = '_ptc_asana_tag_gid';

	/**
	 * The postmeta key name for an Asana task GID pinned to the post. Multiple
	 * tasks may be pinned to a single post resulting in multiple rows per post.
	 * This key's value should be of type string and contain only digits.
	 *
	 * @since 1.0.0
	 *
	 * @var string PINNED_TASK_GID
	 */
	public const PINNED_TASK_GID = '_ptc_asana_task_gid';

	/**
	 * The option key name for the WordPress user's ID that will be used
	 * to authenticate Asana requests on the frontend.
	 *
	 * @since 3.4.0
	 *
	 * @var string FRONTEND_AUTH_USER_ID
	 */
	public const FRONTEND_AUTH_USER_ID = '_ptc_asana_frontend_auth_user_id';

	/**
	 * The option key name for the number of seconds that Asana
	 * data is cached.
	 *
	 * This key's value should be of type int and always
	 * greater than or equal to 0.
	 *
	 * @since 3.10.0
	 *
	 * @var string CACHE_TTL_SECONDS
	 */
	public const CACHE_TTL_SECONDS = '_ptc_asana_cache_ttl_seconds';

	/**
	 * Gets a sanitized value for an option of this class returned in the key's
	 * format as documented on this class's constants.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key name. Use this class's constant members.
	 * @param int    $object_id Optional. The relevant user or post id for which to
	 * retrieve. Default 0 to use current object, if available.
	 * @return mixed The value returned from the database, sanitized and
	 * formatted as documented on option key constant members in this class.
	 * Default ''.
	 */
	public static function get( string $key, int $object_id = 0 ) {

		switch ( $key ) {

			case self::ASANA_PAT:
				if ( $object_id < 0 ) {
					// If -1 was passed an error value, for example,
					// then get_user_meta() will actually absint() and
					// interpret that value as user with ID 1. Aye yi yi!
					trigger_error( 'Cannot use negative user ID to get value for: ' . esc_html( $key ), \E_USER_WARNING );
					return '';
				} elseif ( 0 === $object_id ) {
					$object_id = get_current_user_id();
					if ( 0 === $object_id ) {
						trigger_error( 'Could not identify user to get value for: ' . esc_html( $key ), \E_USER_WARNING );
						return '';
					}
				}
				$asana_pat           = self::crypt(
					get_user_meta( $object_id, $key, true ),
					'd'
				);
				$sanitized_asana_pat = self::sanitize( $key, $asana_pat );
				if ( $asana_pat !== $sanitized_asana_pat ) {
					trigger_error( 'Sanitization occurred. Saved meta is corrupt for: ' . esc_html( $key ), \E_USER_WARNING );
				}
				return (string) $sanitized_asana_pat;

			case self::ASANA_USER_GID:
				if ( 0 === $object_id ) {
					$object_id = get_current_user_id();
					if ( 0 === $object_id ) {
						trigger_error( 'Could not identify user to get value for: ' . esc_html( $key ), \E_USER_WARNING );
						return '';
					}
				}
				$user_meta           = (string) get_user_meta( $object_id, $key, true );
				$sanitized_user_meta = self::sanitize( $key, $user_meta );
				if ( $user_meta !== $sanitized_user_meta ) {
					trigger_error(
						sprintf(
							'Sanitization occurred. Saved meta is corrupt for user %s: %s',
							esc_html( $object_id ),
							esc_html( $key )
						),
						\E_USER_WARNING
					);
				}
				return (string) $sanitized_user_meta;

			case self::ASANA_WORKSPACE_GID:
			case self::ASANA_TAG_GID:
				$asana_gid           = (string) get_option( $key, '' );
				$sanitized_asana_gid = self::sanitize( $key, $asana_gid );
				if ( $asana_gid !== $sanitized_asana_gid ) {
					trigger_error( 'Sanitization occurred. Saved option is corrupt for: ' . esc_html( $key ), \E_USER_WARNING );
				}
				return (string) $sanitized_asana_gid;

			case self::CACHE_TTL_SECONDS:
				$value = get_option( $key, 15 * \MINUTE_IN_SECONDS );
				if ( $value < 0 ) {
					trigger_error( 'Invalid value. Saved option is corrupt for: ' . esc_html( $key ), \E_USER_WARNING );
				}
				return (int) self::sanitize( $key, (string) $value );

			case self::PINNED_TASK_GID:
				if ( 0 === $object_id ) {
					$object_id = get_the_ID();
					if ( 0 === $object_id || false === $object_id ) {
						trigger_error( 'Could not identify post to get value for: ' . esc_html( $key ), \E_USER_WARNING );
						return array();
					}
				}
				$pinned_task_gids = get_post_meta( $object_id, $key, false );
				foreach ( $pinned_task_gids as $i => $task_gid ) {
					$sanitized_task_gid = self::sanitize( $key, $task_gid );
					if ( $task_gid !== $sanitized_task_gid ) {
						trigger_error(
							sprintf(
								'Sanitization occurred. Saved meta is corrupt for post %s: %s',
								esc_html( $object_id ),
								esc_html( $key )
							),
							\E_USER_WARNING
						);
					}
					$pinned_task_gids[ $i ] = $sanitized_task_gid;
				}
				return $pinned_task_gids;

			case self::FRONTEND_AUTH_USER_ID:
				$frontend_auth_user_id = get_option( $key, false );
				if ( false === $frontend_auth_user_id ) {
					return -1;
				} else {
					$frontend_auth_user_id = self::sanitize( $key, $frontend_auth_user_id );
					$wp_user               = new \WP_User( $frontend_auth_user_id );
					if ( ! $wp_user->exists() ) {
						trigger_error(
							'WordPress user (ID: ' . esc_html( $frontend_auth_user_id ) . ') to authenticate frontend Asana requests does not exist! Please save a new frontend authentication user in Completionist\'s settings.',
							\E_USER_WARNING
						);
						self::delete( $key );
						return -1;
					}
				}
				return (int) $frontend_auth_user_id;
		}

		trigger_error(
			'Invalid key to get option value: ' . esc_html( $key ),
			\E_USER_WARNING
		);

		return '';
	}

	/**
	 * Saves a sanitized value for an option of this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key name. Use this class's constant members.
	 * @param mixed  $value The value to attempt to save.
	 * @param bool   $force Optional. If to force saving when
	 * sanitization occurs. Default false to throw \Exceptions.
	 * @param int    $object_id Optional. The relevant user or post
	 * id for which to save. Default 0 to use current object, if available.
	 * @return bool If the option was updated.
	 *
	 * @throws \Exception If $force is false, throws when sanitized value to
	 * save is different than passed value. If $force is true, only throws when
	 * an invalid value would be saved.
	 */
	public static function save( string $key, $value, bool $force = false, int $object_id = 0 ) : bool {

		switch ( $key ) {

			case self::ASANA_PAT:
				$asana_pat           = (string) $value;
				$sanitized_asana_pat = self::sanitize( $key, $asana_pat );
				if ( ! $force && $asana_pat !== $sanitized_asana_pat ) {
					throw new \Exception( 'ERROR: Refused to save invalid Asana PAT value.' );
				}
				$encrypted_asana_pat = self::crypt( $sanitized_asana_pat, 'e' );
				return self::maybe_update_usermeta( $key, $encrypted_asana_pat, $object_id );

			case self::ASANA_USER_GID:
				$user_meta           = (string) $value;
				$sanitized_user_meta = self::sanitize( $key, $user_meta );
				if ( ! $force && $user_meta !== $sanitized_user_meta ) {
					throw new \Exception( 'ERROR: Refused to save different value for usermeta: ' . esc_html( $key ) );
				}
				return self::maybe_update_usermeta( $key, $sanitized_user_meta, $object_id );

			case self::ASANA_WORKSPACE_GID:
			case self::ASANA_TAG_GID:
				$asana_gid           = (string) $value;
				$sanitized_asana_gid = self::sanitize( $key, $asana_gid );
				if ( ! $force && $asana_gid !== $sanitized_asana_gid ) {
					throw new \Exception( 'ERROR: Refused to save different value for option: ' . esc_html( $key ) );
				}
				return self::maybe_update_option( $key, $sanitized_asana_gid, true );

			case self::CACHE_TTL_SECONDS:
				$cache_ttl_seconds = (string) $value;
				$sanitized_value   = self::sanitize( $key, $value );
				if ( ! $force && $cache_ttl_seconds !== $sanitized_value ) {
					throw new \Exception( 'ERROR: Refused to save different value for option: ' . esc_html( $key ) );
				}
				return self::maybe_update_option( $key, $sanitized_value, true );

			case self::PINNED_TASK_GID:
				$post_meta           = (string) $value;
				$sanitized_post_meta = self::sanitize( $key, $post_meta );
				if ( ! $force && $post_meta !== $sanitized_post_meta ) {
					throw new \Exception( 'ERROR: Refused to save different value for postmeta: ' . esc_html( $key ) );
				}
				return self::maybe_add_postmeta( $key, $sanitized_post_meta, $object_id );

			case self::FRONTEND_AUTH_USER_ID:
				$user_id           = (string) $value;
				$sanitized_user_id = self::sanitize( $key, $user_id );
				if ( ! $force && $user_id !== $sanitized_user_id ) {
					throw new \Exception( 'ERROR: Refused to save different value for option: ' . esc_html( $key ) );
				}
				return self::maybe_update_option( $key, $sanitized_user_id, true );
		}

		trigger_error(
			'Invalid key to save option value: ' . esc_html( $key ),
			\E_USER_WARNING
		);

		return false;
	}

	/**
	 * Updates option value only if different.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The option key name.
	 * @param string $value The value to be saved.
	 * @param bool   $autoload Optional. If the option should be
	 * loaded when WordPress starts up. Default false.
	 * @return bool If the option value was updated.
	 */
	private static function maybe_update_option( string $key, string $value, bool $autoload = false ) : bool {

		if ( get_option( $key, '' ) === $value ) {
			return false;
		}

		return update_option( $key, $value, $autoload );
	}

	/**
	 * Updates the usermeta value only if different.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The meta key name.
	 * @param string $value The value to be saved.
	 * @param int    $user_id Optional. The user's id. Default 0
	 * for the current user.
	 * @return bool If the user's meta value was updated.
	 */
	private static function maybe_update_usermeta( string $key, string $value, int $user_id = 0 ) : bool {

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		if (
			0 === $user_id
			|| get_user_meta( $user_id, $key, true ) === $value
		) {
			return false;
		}

		return update_user_meta( $user_id, $key, $value ) ? true : false;
	}

	/**
	 * Inserts the postmeta value only if it does not already exist.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The meta key name.
	 * @param string $value The value to be saved.
	 * @param int    $post_id Optional. The post's id. Default 0
	 * for the current post.
	 * @return bool Returns true if the post's meta value was inserted.
	 */
	private static function maybe_add_postmeta( string $key, string $value, int $post_id = 0 ) : bool {

		if ( $post_id < 0 ) {
			trigger_error( 'Cannot add postmeta for negative post ID value.', \E_USER_WARNING );
			return false;
		}

		if ( ! self::postmeta_exists( $key, $value, $post_id ) ) {
			return add_post_meta( $post_id, $key, $value ) ? true : false;
		}

		return false;
	}

	/**
	 * Inserts the usermeta value only if it does not already exist.
	 *
	 * @since 3.8.0
	 *
	 * @param string $key The meta key name.
	 * @param string $value The value to be saved.
	 * @param int    $user_id Optional. The user's id. Default 0
	 * for the current user.
	 * @return bool Returns true if the user's meta value was inserted.
	 */
	public static function maybe_add_usermeta( string $key, string $value, int $user_id = 0 ) : bool {

		if ( $user_id < 0 ) {
			trigger_error( 'Cannot add usermeta for negative user ID value.', \E_USER_WARNING );
			return false;
		}

		if ( ! self::usermeta_exists( $key, $value, $user_id ) ) {
			return add_user_meta( $user_id, $key, $value ) ? true : false;
		}

		return false;
	}

	/**
	 * Checks if a postmeta key-value pair exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The meta key name.
	 * @param string $value The value to search.
	 * @param int    $post_id Optional. The post's id. Set to 0
	 * to use the current post. Default -1 for any post.
	 * @return bool Returns true if the postmeta key-value pair exists.
	 */
	public static function postmeta_exists( string $key, string $value, int $post_id = -1 ) : bool {

		if ( 0 === $post_id ) {
			$post_id = get_the_ID();
			if ( 0 === $post_id || false === $post_id ) {
				return false;
			}
		}

		global $wpdb;

		if ( $post_id < 0 ) {
			$res = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT meta_id
					FROM $wpdb->postmeta
					WHERE meta_key = %s
						AND meta_value = %s
					",
					$key,
					$value
				)
			);
		} else {
			$res = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT meta_id
					FROM $wpdb->postmeta
					WHERE post_id = %d
						AND meta_key = %s
						AND meta_value = %s
					",
					$post_id,
					$key,
					$value
				)
			);
		}

		if ( null === $res || empty( $res ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if a usermeta key-value pair exists.
	 *
	 * @since 3.8.0
	 *
	 * @param string $key The meta key name.
	 * @param string $value The value to search.
	 * @param int    $user_id Optional. The user's id. Set to 0
	 * to use the current user. Default -1 for any user.
	 * @return bool Returns true if the user meta key-value pair exists.
	 */
	public static function usermeta_exists( string $key, string $value, int $user_id = -1 ) : bool {

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
			if ( 0 === $user_id ) {
				trigger_error(
					sprintf(
						'Could not determine current user to check if user meta key (%s) exists with value (%s).',
						esc_html( $key ),
						esc_html( $value )
					),
					\E_USER_WARNING
				);
				return false;
			}
		}

		global $wpdb;

		if ( $user_id < 0 ) {
			$res = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT umeta_id
					FROM $wpdb->usermeta
					WHERE meta_key = %s
						AND meta_value = %s
					",
					$key,
					$value
				)
			);
		} else {
			$res = $wpdb->get_row(
				$wpdb->prepare(
					"
					SELECT umeta_id
					FROM $wpdb->usermeta
					WHERE user_id = %d
						AND meta_key = %s
						AND meta_value = %s
					",
					$user_id,
					$key,
					$value
				)
			);
		}

		if ( null === $res || empty( $res ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Deletes an option of this class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The key name. Use this class's
	 * constant members when specifying the desired key to delete.
	 * @param int    $object_id Optional. The relevant user or post id for which to
	 * delete the key. Set to -1 to delete for all objects. Default 0 to delete
	 * for current object, if available.
	 * @param string $value Optional. The meta value to be deleted. If provided,
	 * only metadata entries matching the key and value will be deleted.
	 * Default '' to delete all key entries, regardless of value. TAKE CAUTION
	 * WHEN PASSING A VARIABLE. FIRST CHECK IF EMPTY TO AVOID UNEXPECTED
	 * DELETION OF ALL KEY INSTANCES.
	 * @return bool If the option was deleted. false if key is invalid.
	 */
	public static function delete( string $key, int $object_id = 0, string $value = '' ) : bool {

		switch ( $key ) {

			case self::ASANA_PAT:
			case self::ASANA_USER_GID:
				if ( -1 === $object_id ) {
					return delete_metadata( 'user', 0, $key, $value, true );
				} elseif ( 0 === $object_id && 0 !== get_current_user_id() ) {
					return delete_user_meta( get_current_user_id(), $key );
				}
				return delete_user_meta( $object_id, $key );

			case self::PINNED_TASK_GID:
				if ( -1 === $object_id ) {
					return delete_metadata( 'post', 0, $key, $value, true );
				} elseif ( 0 === $object_id && get_the_ID() !== false ) {
					return delete_post_meta( get_the_ID(), $key, $value );
				}
				return delete_post_meta( $object_id, $key, $value );

			case self::ASANA_WORKSPACE_GID:
			case self::ASANA_TAG_GID:
			case self::FRONTEND_AUTH_USER_ID:
			case self::CACHE_TTL_SECONDS:
				return delete_option( $key );
		}

		trigger_error(
			'Invalid deletion key: ' . esc_html( $key ),
			\E_USER_WARNING
		);

		return false;
	}

	/**
	 * Deletes all options.
	 *
	 * @since 1.0.0
	 */
	public static function delete_all() {
		$constants_reflection = new \ReflectionClass( __CLASS__ );
		foreach ( $constants_reflection->getConstants() as $value ) {
			self::delete( $value, -1 );
		}
	}

	/**
	 * Sanitizes a value based on the given context.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context The data context for sanitizing. Use this class's
	 * constant members when specifying the desired option context to use. Other
	 * possible values are 'gid' (for an Asana resource GID) and 'datetime' (for
	 * an SQL Y-m-d H:i:s datetime string).
	 * @param string $value The value to sanitize.
	 * @return string The sanitized string. Default ''.
	 */
	public static function sanitize( string $context, string $value ) : string {

		switch ( $context ) {

			case self::ASANA_PAT:
				return (string) trim(
					preg_replace(
						'/[^a-z0-9\/:]+/',
						'',
						sanitize_text_field( $value )
					)
				);

			case 'gid':
			case self::ASANA_USER_GID:
			case self::ASANA_WORKSPACE_GID:
			case self::ASANA_TAG_GID:
			case self::PINNED_TASK_GID:
				// Asana GIDs are numeric strings that may exceed
				// the maximum allowed integer value and therefore
				// should not be converted to an integer in memory.
				return (string) trim(
					preg_replace(
						'/[^0-9]+/',
						'',
						sanitize_text_field( $value )
					)
				);

			case 'absint':
			case self::FRONTEND_AUTH_USER_ID:
			case self::CACHE_TTL_SECONDS:
				return (string) absint( $value );

			case 'datetime':
				$sanitized_datetime = trim(
					preg_replace(
						'/[^0-9:\- ]+/',
						'',
						sanitize_text_field( $value )
					)
				);
				// Should be string in format Y-m-d H:i:s of a valid date/time.
				$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $sanitized_datetime );
				if ( false !== $dt && method_exists( $dt, 'format' ) ) {
					$dt_string = $dt->format( 'Y-m-d H:i:s' );
					return ( false !== $dt_string ) ? $dt_string : '';
				}
				return '';

			case 'date':
				$sanitized_date = trim(
					preg_replace(
						'/[^0-9\-]+/',
						'',
						sanitize_text_field( $value )
					)
				);
				// Should be string in format yyyy-mm-dd.
				// Use DateTime to validate if the string represents
				// a valid date.
				$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d', $sanitized_date );
				if ( false !== $dt && method_exists( $dt, 'format' ) ) {
					$dt_string = $dt->format( 'Y-m-d' );
					return ( false !== $dt_string ) ? $dt_string : '';
				}
				return '';

			case 'string':
				return sanitize_textarea_field( $value );

			case 'html':
				return wp_kses_post( $value );
		}

		trigger_error(
			'Invalid sanitization context: ' . esc_html( $context ),
			\E_USER_WARNING
		);

		return '';
	}

	/**
	 * Encrypts or decrypts a string.
	 *
	 * @since 1.0.0
	 *
	 * @param string $value The value to encrypt or decrypt.
	 * @param string $mode Optional. The action to take on the provided value.
	 * 'e' to encrypt or 'd' to decrypt. Default 'e'.
	 * @return string The encrypted or decrypted result. Default '' if failure.
	 */
	public static function crypt( string $value, string $mode = 'e' ) : string {

		$key    = \AUTH_SALT;
		$method = 'aes-256-ctr';
		$iv     = substr( \NONCE_SALT, 0, openssl_cipher_iv_length( $method ) );

		if ( 'e' === $mode ) {
			$encrypted = openssl_encrypt( $value, $method, $key, 0, $iv );
			if ( false === $encrypted ) {
				trigger_error( 'OpenSSL encryption failed.', \E_USER_WARNING );
				return '';
			}
			return base64_encode( $encrypted );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		} elseif ( 'd' === $mode ) {
			$decrypted = openssl_decrypt( base64_decode( $value ), $method, $key, 0, $iv );// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			if ( false === $decrypted ) {
				trigger_error( 'OpenSSL decryption failed.', \E_USER_WARNING );
				return '';
			}
			return $decrypted;
		}

		trigger_error( 'Invalid crypt mode. Accepted values are "e" and "d".', \E_USER_WARNING );
		return '';
	}

	/**
	 * Gets all pinned task entries as post id and task gid pairs.
	 *
	 * @since 1.0.0
	 *
	 * @return \stdClass[] An array of objects with post_id and task_gid.
	 */
	public static function get_all_task_pins() : array {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT post_id,meta_value AS 'task_gid'
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				",
				self::PINNED_TASK_GID
			)
		);
	}

	/**
	 * Gets all task gids that have been pinned.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] The task gids.
	 */
	public static function get_all_pinned_tasks() : array {
		global $wpdb;
		return $wpdb->get_col(
			$wpdb->prepare(
				"
				SELECT meta_value
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				GROUP BY meta_value
				",
				self::PINNED_TASK_GID
			)
		);
	}

	/**
	 * Gets the first post id for a pinned task gid.
	 *
	 * @since 1.0.0
	 *
	 * @param string $task_gid The task gid.
	 * @return int The post id if found. Default 0.
	 */
	public static function get_task_pin_post_id( string $task_gid ) : int {

		$value = self::sanitize( self::PINNED_TASK_GID, $task_gid );
		if ( '' === $value ) {
			return 0;
		}

		$key = self::PINNED_TASK_GID;

		global $wpdb;
		$res = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT post_id
				FROM $wpdb->postmeta
				WHERE meta_key = %s
					AND meta_value = %s
				LIMIT 1
				",
				$key,
				$value
			)
		);

		if ( null === $res ) {
			$res = 0;
		}

		return (int) $res;
	}

	/**
	 * Counts the number of task pins.
	 *
	 * @since 1.0.0
	 *
	 * @return int The count.
	 */
	public static function count_all_task_pins() : int {

		global $wpdb;
		$res = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT(meta_value)
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				",
				self::PINNED_TASK_GID
			)
		);

		if ( null === $res ) {
			$res = 0;
		}

		return (int) $res;
	}

	/**
	 * Counts the number of pinned tasks.
	 *
	 * @since 1.0.0
	 *
	 * @return int The count.
	 */
	public static function count_all_pinned_tasks() : int {

		global $wpdb;
		$res = $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT COUNT( DISTINCT meta_value )
				FROM {$wpdb->postmeta}
				WHERE meta_key = %s
				",
				self::PINNED_TASK_GID
			)
		);

		if ( null === $res ) {
			$res = 0;
		}

		return (int) $res;
	}

	/**
	 * Counts the number of pinned tasks per post id.
	 *
	 * @since 1.0.0
	 *
	 * @return \stdClass[] An array of objects with post_id and task_count.
	 */
	public static function count_all_pins_by_post() : array {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT post_id, COUNT(meta_value) AS 'task_count'
				FROM wp_postmeta
				WHERE meta_key = %s
				GROUP BY post_id
				",
				self::PINNED_TASK_GID
			)
		);
	}
}//end class
