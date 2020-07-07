<?php
/**
 * Database Manager class
 *
 * Manages custom database tables.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Database_Manager' ) ) {
  /**
   * Manages custom database tables.
   */
  class Database_Manager {

    /**
     * The blog id currently being used.
     *
     * @since 1.1.0
     *
     * @var int $site_id
     */
    public static $site_id;

    /**
     * If the class has been initialized.
     *
     * @since 1.1.0
     *
     * @var bool $has_been_initialized
     */
    private static $has_been_initialized;

    /**
     * The current, up-to-date database version.
     *
     * @since 1.1.0
     *
     * @var int $db_version
     */
    public static $db_version;

    /**
     * The option name storing the currently installed database version.
     *
     * @since 1.1.0
     *
     * @var string $db_version_option
     */
    public static $db_version_option;

    /**
     * The full name of the automations table.
     *
     * @since 1.1.0
     *
     * @var string $automation_conditions_table
     */
    public static $automations_table;

    /**
     * The full name of the automation conditions table.
     *
     * @since 1.1.0
     *
     * @var string $automation_conditions_table
     */
    public static $automation_conditions_table;

    /**
     * The full name of the automation actions table.
     *
     * @since 1.1.0
     *
     * @var string $automation_actions_table
     */
    public static $automation_actions_table;

    /**
     * The full name of the automation actions meta table.
     *
     * @since 1.1.0
     *
     * @var string $automation_actions_meta_table
     */
    public static $automation_actions_meta_table;

    /**
     * Initializes table variables.
     *
     * @since 1.1.0
     *
     * @param int $site_id Optional. The blog id for which to manage tables.
     * Default -1 to use the current blog's id.
     */
    static function init( int $site_id = -1 ) {

      global $wpdb;

      if ( $site_id === -1 ) {
        $site_id = get_current_blog_id();
      }

      if ( function_exists( 'get_sites' ) && empty( get_sites( [ 'ID' => $site_id ] ) ) ) {
        $err_msg = "[PTC Completionist] FATAL: Cannot initialize Database Manager for site id $site_id";
        error_log( $err_msg );
        die( esc_html( $err_msg ) );
      }

      $wpdb_blog_prefix = $wpdb->get_blog_prefix( $site_id );
      $table_prefix = $wpdb_blog_prefix . 'ptc_completionist_';

      self::$automations_table = $table_prefix . 'automations';
      self::$automation_conditions_table = $table_prefix . 'automation_conditions';
      self::$automation_actions_table = $table_prefix . 'automation_actions';
      self::$automation_actions_meta_table = $table_prefix . 'automation_actions_meta';

      self::$db_version = 1;
      self::$db_version_option = '_ptc_completionist_db_version';

      self::$site_id = $site_id;
      self::$has_been_initialized = TRUE;

    }

    /**
     * Creates or updates all tables.
     *
     * @since 1.1.0
     *
     * @return bool If all tables were successfully created.
     */
    static function install_all_tables() : bool {

      self::require_initialiation();

      $success = TRUE;

      global $wpdb;
      $charset_collate = $wpdb->get_charset_collate();

      if ( function_exists( 'get_blog_option' ) ) {
        $installed_version = (int) get_blog_option( self::$site_id, self::$db_version_option, 0 );
      } else {
        $installed_version = (int) get_option( self::$db_version_option, 0 );
      }

      if ( $installed_version === self::$db_version ) {
        $success = FALSE;
        return $success;
      }

      $automations_table = self::$automations_table;
      $sql = "CREATE TABLE $automations_table (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
        title tinytext NOT NULL,
        description text NOT NULL,
        hook_name tinytext NOT NULL,
        last_modified datetime(0) DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (ID)
      ) $charset_collate;";

      if ( ! self::create_table( $sql ) ) {
        $success = FALSE;
      }

      $automation_conditions_table = self::$automation_conditions_table;
      $sql = "CREATE TABLE $automation_conditions_table (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
        automation_id bigint(20) UNSIGNED NOT NULL,
        property tinytext NOT NULL,
        comparison_method tinytext NOT NULL,
        value tinytext NOT NULL,
        PRIMARY KEY  (ID),
        FOREIGN KEY (automation_id) REFERENCES $automations_table(ID) ON DELETE CASCADE
      ) $charset_collate;";

      if ( ! self::create_table( $sql ) ) {
        $success = FALSE;
      }

      $automation_actions_table = self::$automation_actions_table;
      $sql = "CREATE TABLE $automation_actions_table (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
        automation_id bigint(20) UNSIGNED NOT NULL,
        action tinytext NOT NULL,
        triggered_count int(11) UNSIGNED DEFAULT 0 NOT NULL,
        last_triggered datetime(0) DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (ID),
        FOREIGN KEY (automation_id) REFERENCES $automations_table(ID) ON DELETE CASCADE
      ) $charset_collate;";

      if ( ! self::create_table( $sql ) ) {
        $success = FALSE;
      }

      $automation_actions_meta_table = self::$automation_actions_meta_table;
      $sql = "CREATE TABLE $automation_actions_meta_table (
        ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
        action_id bigint(20) UNSIGNED NOT NULL,
        meta_key varchar(255) NOT NULL,
        meta_value longtext NOT NULL,
        PRIMARY KEY  (ID),
        FOREIGN KEY (action_id) REFERENCES $automation_actions_table(ID) ON DELETE CASCADE
      ) $charset_collate;";

      if ( ! self::create_table( $sql ) ) {
        $success = FALSE;
      }

      if ( $success ) {
        if ( function_exists( 'update_blog_option' ) ) {
          update_blog_option( self::$site_id, self::$db_version_option, self::$db_version );
        } else {
          update_option( self::$db_version_option, self::$db_version );
        }
      }

      return $success;

    }

    /**
     * Drops all tables and deletes DB Version option.
     *
     * @since 1.1.0
     */
    static function drop_all_tables() {

      self::require_initialiation();

      /* Order matters due to foreign key constraints */
      self::drop_table( self::$automation_actions_meta_table );
      self::drop_table( self::$automation_actions_table );
      self::drop_table( self::$automation_conditions_table );
      self::drop_table( self::$automations_table );

      if ( function_exists( 'delete_blog_option' ) ) {
        delete_blog_option( self::$site_id, self::$db_version_option );
      } else {
        delete_option( self::$db_version_option );
      }

      error_log( '[PTC Completionist] Uninstalled database tables for site: ' . self::$site_id );

    }

    /**
     * Creates or updates an individual table using WordPress's dbDelta().
     *
     * @since 1.1.0
     *
     * @see https://codex.wordpress.org/Class_Reference/wpdb#Running_General_Queries
     * Used for checking if the table was successfully created. This is the only
     * WPBD function whose return differentiates MySQL errors from empty result
     * sets. If a MySQL error occurred, the table is determined to not have been
     * successfuly created.
     *
     * @see https://codex.wordpress.org/Creating_Tables_with_Plugins#Creating_or_Updating_the_Table
     * Describes the dbDelta() function's formatting needs with sample query.
     *
     * @param string $creation_sql The CREATE TABLE query, formatted properly
     * for use in dbDelta().
     *
     * @return bool If the table was successfully created.
     */
    static function create_table( string $creation_sql ) : bool {

      /* do not ignore case because dbDelta requires uppercase */
      preg_match( '/CREATE TABLE ([^ ]+)/', $creation_sql, $matches);

      if ( ! isset( $matches[1] ) || empty( $matches[1] ) ) {
        error_log( "Table name could not be matched for creation in query:\n$creation_sql" );
        return FALSE;
      }

      $table_name = $matches[1];

      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $creation_sql );

      global $wpdb;
      $res = $wpdb->query( "SELECT * FROM $table_name" );//phpcs:ignore

      if ( $res === FALSE ) {
        return FALSE;
      }

      return TRUE;

    }

    /**
     * Drops a table.
     *
     * @since 1.1.0
     *
     * @param string $table_name The name of the table to be deleted.
     *
     * @return bool If the table was successfully dropped.
     */
    static function drop_table( string $table_name ) : bool {

      global $wpdb;
      $res = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );//phpcs:ignore

      if ( $res === FALSE ) {
        return FALSE;
      }

      return TRUE;

    }

    /**
     * Dies if the class has not been initialized.
     *
     * @since 1.1.0
     *
     * @see self::init()
     */
    private static function require_initialiation() {
      if ( ! self::$has_been_initialized ) {
        $err_msg = '[PTC Completionist] FATAL: The Database Manager was not properly initialized before usage.';
        error_log( $err_msg );
        die( esc_html( $err_msg ) );
      }
    }

  }//end class

  Database_Manager::init();

}//end if class_exists
