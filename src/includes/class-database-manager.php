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
	 * The full name of the request tokens table.
	 *
	 * @since 3.7.0
	 *
	 * @var string $request_tokens_table
	 */
	public static $request_tokens_table;

	/**
	 * An array of all the database table names that this class
	 * manages.
	 *
	 * @since 3.7.0
	 *
	 * @var string[] $table_names
	 */
	public static $table_names;

	/**
	 * Initializes table variables.
	 *
	 * @since 1.1.0
	 *
	 * @param int $site_id Optional. The blog id for which to manage tables.
	 * Default -1 to use the current blog's id.
	 */
	public static function init( int $site_id = -1 ) {

		if ( -1 === $site_id ) {
			$site_id = get_current_blog_id();
		}

		if (
			true === self::$has_been_initialized &&
			$site_id === self::$site_id
		) {
			// Already initialized for the specified site.
			return;
		}

		if (
			function_exists( 'get_sites' ) &&
			empty( get_sites( array( 'ID' => $site_id ) ) )
		) {
			$err_msg = "Cannot initialize Database Manager for unrecognized site id {$site_id}.";
			trigger_error( esc_html( $err_msg ), \E_USER_ERROR );
			wp_die( esc_html( $err_msg ) );
		}

		global $wpdb;

		$wpdb_blog_prefix = $wpdb->get_blog_prefix( $site_id );
		$table_prefix     = $wpdb_blog_prefix . 'ptc_completionist_';

		self::$automations_table             = $table_prefix . 'automations';
		self::$automation_conditions_table   = $table_prefix . 'automation_conditions';
		self::$automation_actions_table      = $table_prefix . 'automation_actions';
		self::$automation_actions_meta_table = $table_prefix . 'automation_actions_meta';
		self::$request_tokens_table          = $table_prefix . 'request_tokens';

		self::$table_names = array(
			self::$automations_table,
			self::$automation_conditions_table,
			self::$automation_actions_table,
			self::$automation_actions_meta_table,
			self::$request_tokens_table,
		);

		self::$db_version        = 3;
		self::$db_version_option = '_ptc_completionist_db_version';

		self::$site_id              = $site_id;
		self::$has_been_initialized = true;
	}

	/**
	 * Gets the currently installed database version.
	 *
	 * @since 4.0.0
	 *
	 * @return int The database version.
	 */
	public static function get_installed_version() : int {

		self::require_initialiation();

		$installed_version = 0;

		if ( function_exists( 'get_blog_option' ) ) {
			$installed_version = (int) get_blog_option( self::$site_id, self::$db_version_option, 0 );
		} else {
			$installed_version = (int) get_option( self::$db_version_option, 0 );
		}

		return $installed_version;
	}

	/**
	 * Creates or updates all tables.
	 *
	 * @link https://dev.mysql.com/doc/refman/5.7/en/data-types.html
	 *
	 * @since 1.1.0
	 *
	 * @return bool If all tables were successfully created.
	 */
	public static function install_all_tables() : bool {
		global $wpdb;

		self::require_initialiation();

		if ( self::get_installed_version() === self::$db_version ) {
			// Currently up-to-date! No installation needed.
			return false;
		}

		// !! BEGIN !! Fix some dbDelta() incompatibilities.
		add_filter( 'query', __CLASS__ . '::filter_install_query', PHP_INT_MAX, 1 );

		$success = true;

		$charset_collate = $wpdb->get_charset_collate();

		$automations_table = self::$automations_table;
		$sql = "CREATE TABLE {$automations_table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT UNIQUE,
			title tinytext NOT NULL,
			description text NOT NULL,
			hook_name tinytext NOT NULL,
			last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (ID)
		) {$charset_collate};";

		if ( ! self::create_table( $sql ) ) {
			$success = false;
		}

		$automation_conditions_table = self::$automation_conditions_table;
		$sql = "CREATE TABLE {$automation_conditions_table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT UNIQUE,
			automation_id bigint(20) unsigned NOT NULL,
			property tinytext NOT NULL,
			comparison_method tinytext NOT NULL,
			value tinytext NOT NULL,
			PRIMARY KEY  (ID),
			FOREIGN KEY (automation_id) REFERENCES $automations_table(ID) ON DELETE CASCADE
		) {$charset_collate};";

		if ( ! self::create_table( $sql ) ) {
			$success = false;
		}

		$automation_actions_table = self::$automation_actions_table;
		$sql = "CREATE TABLE {$automation_actions_table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT UNIQUE,
			automation_id bigint(20) unsigned NOT NULL,
			action tinytext NOT NULL,
			triggered_count int(11) unsigned DEFAULT 0 NOT NULL,
			last_triggered datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (ID),
			FOREIGN KEY (automation_id) REFERENCES $automations_table(ID) ON DELETE CASCADE
		) {$charset_collate};";

		if ( ! self::create_table( $sql ) ) {
			$success = false;
		}

		$automation_actions_meta_table = self::$automation_actions_meta_table;
		$sql = "CREATE TABLE {$automation_actions_meta_table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT UNIQUE,
			action_id bigint(20) unsigned NOT NULL,
			meta_key varchar(255) NOT NULL,
			meta_value longtext NOT NULL,
			PRIMARY KEY  (ID),
			FOREIGN KEY (action_id) REFERENCES $automation_actions_table(ID) ON DELETE CASCADE
		) {$charset_collate};";

		if ( ! self::create_table( $sql ) ) {
			$success = false;
		}

		$request_tokens_table = self::$request_tokens_table;
		$sql = "CREATE TABLE {$request_tokens_table} (
			token char(32) NOT NULL UNIQUE,
			args text NOT NULL,
			cache_data longtext NOT NULL,
			cached_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			last_accessed datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (token)
		) {$charset_collate};";

		if ( ! self::create_table( $sql ) ) {
			$success = false;
		}

		// !! END !! Done altering database queries.
		remove_filter( 'query', __CLASS__ . '::filter_install_query', PHP_INT_MAX );

		if ( $success ) {

			if ( function_exists( 'update_blog_option' ) ) {
				update_blog_option( self::$site_id, self::$db_version_option, self::$db_version );
			} else {
				update_option( self::$db_version_option, self::$db_version );
			}
		} else {
			// Warn if unsuccessful.
			trigger_error(
				'Failed to install database tables for Completionist. SQL error encountered: ' . esc_html( $wpdb->last_error ),
				\E_USER_WARNING
			);
		}

		return $success;
	}

	/**
	 * Filters the database table installation queries.
	 *
	 * @link https://developer.wordpress.org/reference/functions/dbdelta/#comment-4027
	 * @link https://core.trac.wordpress.org/ticket/19207
	 *
	 * @since 3.7.0
	 *
	 * @param string $query The database query statement.
	 * @return string The altered database query statement.
	 */
	public static function filter_install_query( $query ) {
		if ( 1 === preg_match( '/ALTER TABLE .+ ADD COLUMN FOREIGN KEY/i', $query ) ) {
			// dbDelta() doesn't understand FOREIGN KEY declarations
			// and instead thinks they are column definitions.
			return '';
		}
		return $query;
	}

	/**
	 * Drops all tables and deletes DB Version option.
	 *
	 * @since 1.1.0
	 */
	public static function drop_all_tables() {

		self::require_initialiation();

		// Order matters due to foreign key constraints!
		self::drop_table( self::$automation_actions_meta_table );
		self::drop_table( self::$automation_actions_table );
		self::drop_table( self::$automation_conditions_table );
		self::drop_table( self::$automations_table );
		self::drop_table( self::$request_tokens_table );

		if ( function_exists( 'delete_blog_option' ) ) {
			delete_blog_option( self::$site_id, self::$db_version_option );
		} else {
			delete_option( self::$db_version_option );
		}
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
	public static function create_table( string $creation_sql ) : bool {

		self::require_initialiation();

		// Do not ignore case because dbDelta() requires uppercase.
		preg_match( '/CREATE TABLE ([^ ]+)/', $creation_sql, $matches );

		if ( ! isset( $matches[1] ) || empty( $matches[1] ) ) {
			error_log( "Table name could not be matched for creation in query:\n{$creation_sql}" );
			return false;
		}

		$table_name = $matches[1];

		if ( ! self::is_permitted_table_name( $table_name ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $creation_sql );

		global $wpdb;
		$res = $wpdb->get_var(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$table_name
			)
		);

		return ( $table_name === $res );
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
	public static function drop_table( string $table_name ) : bool {

		if ( ! self::is_permitted_table_name( $table_name ) ) {
			return false;
		}

		global $wpdb;
		return ( false !== $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ) );//phpcs:ignore
	}

	/**
	 * Truncates a table.
	 *
	 * @since 3.7.0
	 *
	 * @param string $table_name The name of the table.
	 *
	 * @return bool If the table was successfully truncated.
	 */
	public static function truncate_table( string $table_name ) : bool {

		if ( ! self::is_permitted_table_name( $table_name ) ) {
			return false;
		}

		global $wpdb;
		return ( false !== $wpdb->query( "TRUNCATE TABLE {$table_name}" ) );//phpcs:ignore
	}

	/**
	 * Checks if the provided table name is permitted by this class.
	 *
	 * @since 3.7.0
	 *
	 * @param string $table_name The table name to check.
	 *
	 * @return bool If the table name is permitted.
	 */
	public static function is_permitted_table_name( string $table_name ) : bool {

		self::require_initialiation();

		if ( ! in_array( $table_name, self::$table_names, true ) ) {
			trigger_error(
				"Table name '" . esc_html( $table_name ) . "' is not in the allowlist:\n" . esc_html( print_r( self::$table_names, true ) ),
				\E_USER_WARNING
			);
			return false;
		}

		return true;
	}

	/**
	 * Gets the SQL DateTime string of a Unix timestamp.
	 *
	 * @since 3.7.0
	 *
	 * @param int|null $unix_timestamp Optional. The number of
	 * seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).
	 * Defaults to the current Unix timestamp.
	 *
	 * @return string The SQL DateTime timestamp string.
	 */
	public static function unix_as_sql_timestamp(
		?int $unix_timestamp = null
	) : string {
		if ( null === $unix_timestamp ) {
			$unix_timestamp = time();
		}
		return gmdate( 'Y-m-d H:i:s', $unix_timestamp );
	}

	/**
	 * Gets the Unix timestamp of a SQL DateTime string.
	 *
	 * @since 3.7.0
	 *
	 * @param string $sql_timestamp The SQL DateTime timestamp
	 * string.
	 *
	 * @return int The SQL DateTime timestamp string.
	 */
	public static function sql_timestamp_as_unix( string $sql_timestamp ) : int {
		return \DateTimeImmutable::createFromFormat(
			'Y-m-d H:i:s',
			$sql_timestamp,
			new \DateTimeZone( 'UTC' )
		)->getTimestamp();
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
			$err_msg = 'The Database Manager must be initialized before usage.';
			trigger_error( esc_html( $err_msg ), \E_USER_ERROR );
			wp_die( esc_html( $err_msg ) );
		}
	}
}//end class
