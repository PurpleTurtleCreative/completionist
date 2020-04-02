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
     * The option key name for the blog's designated Asana tag's GID.
     * This key's value should be of type string and contain only digits.
     *
     * @since 1.0.0
     *
     * @var string ASANA_WORKSPACE_GID
     */
    const ASANA_TAG_GID = '_ptc_asana_tag_gid';

    /**
     * The postmeta key name for an Asana task GID pinned to the post. Multiple
     * tasks may be pinned to a single post resulting in multiple rows per post.
     * This key's value should be of type string and contain only digits.
     *
     * @since 1.0.0
     *
     * @var string PINNED_TASK_GID
     */
    const PINNED_TASK_GID = '_ptc_asana_task_gid';

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
            error_log( "ALERT: Sanitization occurred. Saved meta is corrupt for user $object_id: $key" );
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

        case self::PINNED_TASK_GID:
          if ( $object_id === 0 ) {
            $object_id = get_the_ID();
            if ( $object_id === 0 || $object_id === FALSE ) {
              error_log( 'Could not identify post to get value for: ' . $key );
              return [];
            }
          }
          $pinned_task_gids = get_post_meta( $object_id, $key, FALSE );
          foreach ( $pinned_task_gids as $i => $task_gid ) {
            $sanitized_task_gid = self::sanitize( $key, $task_gid );
            if ( $task_gid != $sanitized_task_gid ) {
              error_log( "ALERT: Sanitization occurred. Saved meta is corrupt for post $object_id: $key" );
            }
            $pinned_task_gids[ $i ] = $sanitized_task_gid;
          }
          return $pinned_task_gids;

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
            throw new \Exception( 'ERROR: Refused to save invalid Asana PAT value.' );
          }
          return self::maybe_update_usermeta( $key, self::crypt( $sanitized_asana_pat, 'e' ), $object_id );

        case self::ASANA_USER_GID:
          $user_meta = $value;
          $sanitized_user_meta = self::sanitize( $key, $user_meta );
          if ( ! $force && $user_meta != $sanitized_user_meta ) {
            throw new \Exception( 'ERROR: Refused to save different value for usermeta: ' . $key );
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

        case self::PINNED_TASK_GID:
          $post_meta = $value;
          $sanitized_post_meta = self::sanitize( $key, $post_meta );
          if ( ! $force && $post_meta != $sanitized_post_meta ) {
            throw new \Exception( 'ERROR: Refused to save different value for postmeta: ' . $key );
          }
          return self::maybe_add_postmeta( $key, $sanitized_post_meta, $object_id );

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
     * Inserts the postmeta value only if it does not already exist.
     *
     * @since 1.0.0
     *
     * @param string $key The meta key name.
     *
     * @param string $value The value to be saved.
     *
     * @param int $post_id Optional. The post's id. Default 0 for current post.
     *
     * @return bool Returns TRUE if the post's meta value was inserted.
     */
    private static function maybe_add_postmeta( string $key, string $value, int $post_id = 0 ) : bool {

      if ( ! self::postmeta_exists( $key, $value, $post_id ) ) {
        return add_post_meta( $post_id, $key, $value ) ? TRUE : FALSE;
      }

      return FALSE;

    }

    /**
     * Checks if a postmeta key-value pair exists.
     *
     * @since 1.0.0
     *
     * @param string $key The meta key name.
     *
     * @param string $value The value to search.
     *
     * @param int $post_id Optional. The post's id. Set to 0 to use current
     * post. Default -1 for any post.
     *
     * @return bool Returns TRUE if the postmeta key-value pair exists.
     */
    static function postmeta_exists( string $key, string $value, int $post_id = -1 ) : bool {

      if ( $post_id === 0 ) {
        $post_id = get_the_ID();
        if ( $post_id === 0 || $post_id === FALSE ) {
          return FALSE;
        }
      }

      global $wpdb;

      if ( $post_id < 0 ) {
        $res = $wpdb->get_row( $wpdb->prepare(
            "
            SELECT meta_id
            FROM $wpdb->postmeta
            WHERE meta_key = %s
              AND meta_value = %s
            ",
            $key,
            $value
          ) );
      } else {
        $res = $wpdb->get_row( $wpdb->prepare(
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
          ) );
      }

      if ( $res === NULL || empty( $res ) ) {
        return FALSE;
      }

      return TRUE;

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
     * @param string $value Optional. The meta value to be deleted. If provided,
     * only metadata entries matching the key and value will be deleted.
     * Default '' to delete all key entries, regardless of value. TAKE CAUTION
     * WHEN PASSING A VARIABLE. FIRST CHECK IF EMPTY TO AVOID UNEXPECTED
     * DELETION OF ALL KEY INSTANCES.
     *
     * @return bool If the option was deleted. FALSE if key is invalid.
     */
    static function delete( string $key, int $object_id = 0, string $value = '' ) : bool {

      switch ( $key ) {
        case self::ASANA_PAT:
        case self::ASANA_USER_GID:
          if ( $object_id === -1 ) {
            return delete_metadata( 'user', 0, $key, '', TRUE );
          } elseif ( $object_id === 0 && get_current_user_id() !== 0 ) {
            return delete_user_meta( get_current_user_id(), $key );
          } else {
            return delete_user_meta( $object_id, $key );
          }
        case self::PINNED_TASK_GID:
          if ( $object_id === -1 ) {
            return delete_metadata( 'post', 0, $key, '', TRUE );
          } elseif ( $object_id === 0 && get_the_ID() !== FALSE ) {
            return delete_post_meta( get_the_ID(), $key, $value );
          } else {
            return delete_post_meta( $object_id, $key, $value );
          }
        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
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
          $sanitized_asana_pat = preg_replace( '/[^a-z0-9\/:]+/', '', $filtered_asana_pat );
          return (string) $sanitized_asana_pat;

        case 'gid':
        case self::ASANA_USER_GID:
        case self::ASANA_WORKSPACE_GID:
        case self::ASANA_TAG_GID:
        case self::PINNED_TASK_GID:
          $filtered_integer_string = filter_var(
            $value,
            FILTER_SANITIZE_NUMBER_INT
          );
          $sanitized_integer_string = preg_replace( '/[^0-9]+/', '', $filtered_integer_string );
          return (string) $sanitized_integer_string;

        case 'datetime':
          $filtered_datetime = filter_var(
            $value,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
          );
          $sanitized_datetime = preg_replace( '/[^0-9:\- ]+/', '', $filtered_datetime );
          /* should be string in format Y-m-d H:i:s */
          $dt = \DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
          if ( $dt !== FALSE && array_sum( $dt::getLastErrors() ) === 0 ) {
            $dt_string = $dt->format('Y-m-d H:i:s');
            return ( $dt_string !== FALSE ) ? $dt_string : '';
          } else {
            return '';
          }

        case 'date':
          $filtered_date = filter_var(
            $value,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
          );
          $sanitized_date = preg_replace( '/[^0-9\-]+/', '', $filtered_date );
          /* should be string in format yyyy-mm-dd */
          $dt = \DateTime::createFromFormat( 'Y-m-d', $sanitized_date );
          if ( $dt !== FALSE && array_sum( $dt::getLastErrors() ) === 0 ) {
            $dt_string = $dt->format('Y-m-d');
            return ( $dt_string !== FALSE ) ? $dt_string : '';
          } else {
            return '';
          }

        case 'string':
          $filtered_value = filter_var(
            $value,
            FILTER_SANITIZE_STRING,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
          );
          return ( $filtered_value !== FALSE ) ? $filtered_value : '';

        case 'html':
          $sanitized_value = wp_kses(
            $value,
            [
              'a' => [
                'href' => [],
                'title' => [],
                'target' => [],
                'id' => [],
                'class' => [],
              ],
              'br' => [],
              'em' => [],
              'strong' => [],
              'i' => [
                'class' => [],
              ],
              'b' => [],
            ],
            [ 'http', 'https', 'mailto' ]
          );
          return $sanitized_value;

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

    /**
     * Get all pinned task entries as post id and task gid pairs.
     *
     * @since 1.0.0
     *
     * @return \stdClass[] An array of objects with post_id and task_gid.
     */
    static function get_all_task_pins() : array {

      global $wpdb;
      $sql = $wpdb->prepare(
        "
        SELECT post_id,meta_value AS 'task_gid'
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        ",
        self::PINNED_TASK_GID
      );

      return $wpdb->get_results( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    }

    /**
     * Get all task gids that have been pinned.
     *
     * @since 1.0.0
     *
     * @return string[] The task gids.
     */
    static function get_all_pinned_tasks() : array {

      global $wpdb;
      $sql = $wpdb->prepare(
        "
        SELECT meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        GROUP BY meta_value
        ",
        self::PINNED_TASK_GID
      );

      return $wpdb->get_col( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    }

    /**
     * Get the first post id for a pinned task gid.
     *
     * @since 1.0.0
     *
     * @param string $task_gid The task gid.
     *
     * @return int The post id if found. Default 0.
     */
    static function get_task_pin_post_id( string $task_gid ) : int {

      $value = self::sanitize( self::PINNED_TASK_GID, $task_gid );
      if ( '' === $value ) {
        return 0;
      }

      $key = self::PINNED_TASK_GID;

      global $wpdb;
      $res = $wpdb->get_var( $wpdb->prepare(
          "
          SELECT post_id
          FROM $wpdb->postmeta
          WHERE meta_key = %s
            AND meta_value = %s
          ",
          $key,
          $value
        ) );

      if ( NULL === $res ) {
        $res = 0;
      }
      return (int) $res;

    }

    /**
     * Count the number of task pins.
     *
     * @since 1.0.0
     *
     * @return int The count.
     */
    static function count_all_task_pins() : int {

      global $wpdb;
      $sql = $wpdb->prepare(
        "
        SELECT COUNT(meta_value)
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        ",
        self::PINNED_TASK_GID
      );

      $res = $wpdb->get_var( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
      if ( NULL === $res ) {
        $res = 0;
      }
      return (int) $res;

    }

    /**
     * Count the number of pinned tasks.
     *
     * @since 1.0.0
     *
     * @return int The count.
     */
    static function count_all_pinned_tasks() : int {

      global $wpdb;
      $sql = $wpdb->prepare(
        "
        SELECT COUNT( DISTINCT meta_value )
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        ",
        self::PINNED_TASK_GID
      );

      $res = $wpdb->get_var( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
      if ( NULL === $res ) {
        $res = 0;
      }
      return (int) $res;

    }

    /**
     * Count the number of pinned tasks per post id.
     *
     * @since 1.0.0
     *
     * @return \stdClass[] An array of objects with post_id and task_count.
     */
    static function count_all_pins_by_post() : array {

      global $wpdb;
      $sql = $wpdb->prepare(
        "
        SELECT post_id, COUNT(meta_value) AS 'task_count'
        FROM wp_postmeta
        WHERE meta_key = %s
        GROUP BY post_id
        ",
        self::PINNED_TASK_GID
      );

      return $wpdb->get_results( $sql );//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

    }

  }//end class
}//end if class_exists
