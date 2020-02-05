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

if ( ! class_exists( __NAMESPACE__ . '\Options' ) ) {
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
    const ASANA_PAT = '_ptc_asana_pat';

    /**
     * The usermeta key name for the user's resource id in asana.
     * This meta key's value should be of type string and contain only digits.
     *
     * @since 1.0.0
     *
     * @var string ASANA_USER_GID
     */
    const ASANA_USER_GID = '_ptc_asana_user_gid';

    /**
     * The option key name for the Asana workspace's GID linking to this blog.
     * This key's value should be of type string and contain only digits.
     *
     * @since 1.0.0
     *
     * @var string ASANA_WORKSPACE_GID
     */
    const ASANA_WORKSPACE_GID = '_ptc_asana_workspace_gid';

    /**
     * The option key name for the Asana tag's GID linking tasks to this blog.
     * This key's value should be of type string and contain only digits.
     *
     * @since 1.0.0
     *
     * @var string ASANA_TAG_GID
     */
    const ASANA_TAG_GID = '_ptc_asana_tag_gid';

    /**
     * The option key name for number of seconds to consider local data to be
     * expired. This key's value should be of type integer.
     *
     * @since 1.0.0
     *
     * @var string LOCAL_EXPIRY
     */
    const LOCAL_EXPIRY = '_ptc_asana_local_expiry';

    /**
     * The option key name for the SQL DateTime that local data was loaded.
     * This key's value should be of type \DateTime in 'Y-m-d H:i:s' formatting.
     *
     * @since 1.0.0
     *
     * @var string LOCAL_LAST_UPDATED
     */
    const LOCAL_LAST_UPDATED = '_ptc_asana_local_last_updated';

    /**
     * Gets a sanitized value for an option of this class returned in the key's
     * format as documented on this class's constants.
     *
     * @since 1.0.0
     *
     * @param string $key The key name. Use this class's constant members.
     *
     * @param int $object_id Optional. The relevant user or post id for which to
     * retrieve. Default 0 to use current object, if available.
     *
     * @return mixed The value returned from the database, sanitized and
     * formatted as documented on option key constant members in this class.
     * Default ''.
     */
    static function get( string $key, int $object_id = 0 ) {

      switch ( $key ) {

        case self::ASANA_PAT:
          if ( $object_id === 0 ) {
            $object_id = get_current_user_id();
            if ( $object_id === 0 ) {
              error_log( 'Could not identify user to get value for: ' . $key );
              return '';
            }
          }
          $asana_pat = self::crypt( get_user_meta( $object_id, $key, TRUE ), 'd' );
          $sanitized_asana_pat = self::sanitize( $key, $asana_pat );
          if ( $asana_pat != $sanitized_asana_pat ) {
            error_log( 'ALERT: Sanitization occurred. Saved meta is corrupt for: ' . $key );
          }
          return (string) $sanitized_asana_pat;

        case self::ASANA_USER_GID:
          if ( $object_id === 0 ) {
            $object_id = get_current_user_id();
            if ( $object_id === 0 ) {
              error_log( 'Could not identify user to get value for: ' . $key );
              return '';
            }
          }
          $user_meta = get_user_meta( $object_id, $key, TRUE );
          $sanitized_user_meta = self::sanitize( $key, $user_meta );
          if ( $user_meta != $sanitized_user_meta ) {
            error_log( 'ALERT: Sanitization occurred. Saved meta is corrupt for: ' . $key );
          }
          return (string) $sanitized_user_meta;

        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
          $asana_gid = get_option( $key, '' );
          $sanitized_asana_gid = self::sanitize( $key, $asana_gid );
          if ( $asana_gid != $sanitized_asana_gid ) {
            error_log( 'ALERT: Sanitization occurred. Saved option is corrupt for: ' . $key );
          }
          return (string) $sanitized_asana_gid;

        case self::LOCAL_EXPIRY:
          $local_expiry = get_option( $key, '' );
          $sanitized_local_expiry = self::sanitize( $key, $local_expiry );
          if ( $local_expiry != $sanitized_local_expiry ) {
            error_log( 'ALERT: Sanitization occurred. Saved option is corrupt for: ' . $key );
          }
          return (int) $sanitized_local_expiry;

        case self::LOCAL_LAST_UPDATED:
          $local_last_updated = get_option( $key, '' );
          $sanitized_local_last_updated = self::sanitize( $key, $local_last_updated );
          if ( $local_last_updated != $sanitized_local_last_updated ) {
            error_log( 'ALERT: Sanitization occurred. Saved option is corrupt for: ' . $key );
          }
          $local_last_updated_datetime = date_create_from_format( 'Y-m-d H:i:s', $sanitized_local_last_updated);
          if (
            FALSE === $local_last_updated_datetime
            || ! is_object( $local_last_updated_datetime )
            || ! ( $local_last_updated_datetime instanceof \DateTime )
          ) {
            error_log( 'ERROR: Failed to retrieve DateTime object for:' . $key );
            return '';
          }
          return $local_last_updated_datetime;

      }

      error_log( 'Invalid key to get value: ' . $key );
      return '';

    }

    /**
     * Saves a sanitized value for an option of this class.
     *
     * @since 1.0.0
     *
     * @param string $key The key name. Use this class's constant members.
     *
     * @param string $value The value to attempt to save.
     *
     * @param bool $force Optional. If to force saving when sanitization occurs.
     * Default FALSE to throw \Exceptions.
     *
     * @param int $object_id Optional. The relevant user or post id for which to
     * save. Default 0 to use current object, if available.
     *
     * @return bool If the option was updated.
     *
     * @throws \Exception If $force is FALSE, throws when sanitized value to
     * save is different than passed value. If $force is TRUE, only throws when
     * an invalid value would be saved.
     */
    static function save( string $key, string $value, bool $force = FALSE, int $object_id = 0 ) : bool {

      switch ( $key ) {

        case self::ASANA_PAT:
          $asana_pat = $value;
          $sanitized_asana_pat = self::sanitize( $key, $asana_pat );
          if ( ! $force && $asana_pat != $sanitized_asana_pat ) {
            throw new \Exception( 'ERROR: Refused to save different value for option: ' . $key );
          }
          return self::maybe_update_usermeta( $key, self::crypt( $sanitized_asana_pat, 'e' ), $object_id );

        case self::ASANA_USER_GID:
          $user_meta = $value;
          $sanitized_user_meta = self::sanitize( $key, $user_meta );
          if ( ! $force && $user_meta != $sanitized_user_meta ) {
            throw new \Exception( 'ERROR: Refused to save different value for option: ' . $key );
          }
          return self::maybe_update_usermeta( $key, $sanitized_user_meta, $object_id );

        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
          $asana_gid = $value;
          $sanitized_asana_gid = self::sanitize( $key, $asana_gid );
          if ( ! $force && $asana_gid != $sanitized_asana_gid ) {
            throw new \Exception( 'ERROR: Refused to save different value for option: ' . $key );
          }
          return self::maybe_update_option( $key, $sanitized_asana_gid, TRUE );

        case self::LOCAL_EXPIRY:
          $local_expiry = $value;
          $sanitized_local_expiry = self::sanitize( $key, $local_expiry );
          if ( ! $force && $local_expiry != $sanitized_local_expiry ) {
            throw new \Exception( 'ERROR: Refused to save different value for option: ' . $key );
          }
          return self::maybe_update_option( $key, $sanitized_local_expiry, TRUE );

        case self::LOCAL_LAST_UPDATED:
          $local_last_updated = $value;
          $sanitized_local_last_updated = self::sanitize( $key, $local_last_updated );
          if ( ! $force && $local_last_updated != $sanitized_local_last_updated ) {
            throw new \Exception( 'ERROR: Refused to save different value for option: ' . $key );
          }
          $local_last_updated_datetime = date_create_from_format( 'Y-m-d H:i:s', $sanitized_local_last_updated);
          if (
            FALSE === $local_last_updated_datetime
            || ! is_object( $local_last_updated_datetime )
            || ! ( $local_last_updated_datetime instanceof \DateTime )
          ) {
            throw new \Exception( 'ERROR: Refused to save invalid DateTime for:' . $key );
          }
          return self::maybe_update_option( $key, $sanitized_local_last_updated, TRUE );

      }

      error_log( 'Invalid key to save value: ' . $key );
      return FALSE;

    }

    /**
     * Updates option value only if different.
     *
     * @since 1.0.0
     *
     * @param string $key The option key name.
     *
     * @param string $value The value to be saved.
     *
     * @param bool $autoload Optional. If the option should be loaded when
     * WordPress starts up. Default FALSE.
     *
     * @return bool If the option value was updated.
     */
    private static function maybe_update_option( string $key, string $value, bool $autoload = FALSE ) : bool {

      if ( get_option( $key, '' ) === $value ) {
        return FALSE;
      }

      return update_option( $key, $value, $autoload );

    }

    /**
     * Updates the usermeta value only if different.
     *
     * @since 1.0.0
     *
     * @param string $key The meta key name.
     *
     * @param string $value The value to be saved.
     *
     * @param int $user_id Optional. The user's id. Default 0 for current user.
     *
     * @return bool If the user's meta value was updated.
     */
    private static function maybe_update_usermeta( string $key, string $value, int $user_id = 0 ) : bool {

      if ( $user_id === 0 ) {
        $user_id = get_current_user_id();
      }

      if (
        $user_id === 0
        || get_user_meta( $user_id, $key, TRUE ) === $value
      ) {
        return FALSE;
      }

      return update_user_meta( $user_id, $key, $value ) ? TRUE : FALSE;

    }

    /**
     * Deletes an option of this class.
     *
     * @since 1.0.0
     *
     * @param string $key The key name. Use this class's
     * constant members when specifying the desired key to delete.
     *
     * @param int $object_id Optional. The relevant user or post id for which to
     * delete the key. Set to -1 to delete for all objects. Default 0 to delete
     * for current object, if available.
     *
     * @return bool If the option was deleted. FALSE if key is not a
     * member of this class.
     */
    static function delete( string $key, int $object_id = 0 ) : bool {

      switch ( $key ) {
        case self::ASANA_PAT:
        case self::ASANA_USER_GID:
          if ( $object_id === -1 ) {
            /* If user id not specified, delete for all users */
            return delete_metadata( 'user', 0, $key, '', TRUE );
          } elseif ( $object_id === 0 && get_current_user_id() !== 0 ) {
            return delete_user_meta( get_current_user_id(), $key );
          }
        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
        case self::LOCAL_EXPIRY:
        case self::LOCAL_LAST_UPDATED:
          return delete_option( $key );
      }

      return FALSE;

    }

    /**
     * Deletes all options.
     *
     * @since 1.0.0
     */
    static function delete_all() {
      $constants_reflection = new \ReflectionClass( __CLASS__ );
      $constants = $constants_reflection->getConstants();
      foreach ( $constants as $name => $value ) {
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
     *
     * @param string $value The value to sanitize.
     *
     * @return string The sanitized string. Default ''.
     */
    static function sanitize( string $context, string $value ) : string {

      $value = trim( $value );

      switch ( $context ) {

        case self::ASANA_PAT:
          $filtered_asana_pat = filter_var(
            $value,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
          );
          $sanitized_asana_pat = preg_replace( '/[^a-z0-9\/]+/', '', $filtered_asana_pat );
          return (string) $sanitized_asana_pat;

        case 'gid':
        case self::ASANA_USER_GID:
        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
        case self::LOCAL_EXPIRY:
          $filtered_integer_string = filter_var(
            $value,
            FILTER_SANITIZE_NUMBER_INT
          );
          $sanitized_integer_string = preg_replace( '/[^0-9]+/', '', $filtered_integer_string );
          return (string) $sanitized_integer_string;

        case 'datetime':
        case self::LOCAL_LAST_UPDATED:
          $filtered_local_last_updated = filter_var(
            $value,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
          );
          $sanitized_local_last_updated = preg_replace( '/[^0-9:\- ]+/', '', $filtered_local_last_updated );
          /* should be string in format Y-m-d H:i:s */
          $matched = preg_match( '/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', $sanitized_local_last_updated, $matches );
          if ( $matched === 1 ) {
            return (string) $sanitized_local_last_updated;
          } elseif ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {
            error_log( "ALERT: Using matched substring from sanitized date for $context context with passed value: $value" );
            return (string) $matches[0];
          } else {
            error_log( "ERROR: No proper date format matched from sanitized date for $context context with passed value: $value" );
            return '';
          }

      }

      error_log( 'Invalid sanitization context: ' . $context );
      return '';

    }

    /**
     * Encrypts or decrypts a string.
     *
     * @since 1.0.0
     *
     * @param string $value The value to encrypt or decrypt.
     *
     * @param string $mode Optional. The action to take on the provided value.
     * 'e' to encrypt or 'd' to decrypt. Default 'e'.
     *
     * @return string The encrypted or decrypted result. Default '' if failure.
     */
    static function crypt( string $value, string $mode = 'e' ) : string {

      $key = AUTH_SALT;
      $iv = NONCE_SALT;
      $method = 'aes-256-ctr';

      $iv = substr( $iv, 0, openssl_cipher_iv_length( $method ) );

      if ( $mode === 'e' ) {
        $encrypted = openssl_encrypt( $value, $method, $key, 0, $iv );
        if ( FALSE === $encrypted ) {
          error_log( 'OpenSSL encryption failed.' );
          return '';
        }
        return base64_encode( $encrypted );
      } elseif ( $mode === 'd' ) {
        $decrypted = openssl_decrypt( base64_decode( $value ), $method, $key, 0, $iv );
        if ( FALSE === $decrypted ) {
          error_log( 'OpenSSL decryption failed.' );
          return '';
        }
        return $decrypted;
      }

      error_log( "Invalid crypt mode '$mode'. Accepted values are 'e' and 'd'." );
      return '';

    }

  }//end class
}//end if class_exists
