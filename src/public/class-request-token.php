<?php
/**
 * Request_Token class
 *
 * @since 3.7.0
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Class to manage frontend request tokens.
 *
 * Request tokens ensure private data cannot be accessed. The server
 * creates a request token to obscure request parameters on the frontend.
 * This ensures generic requests cannot be made from the frontend to
 * access data or perform actions that have not been explicitly published.
 * Request tokens can also maintain a cache of the respective response data.
 *
 * WordPress nonces are specific to a user's identity and eventually
 * expire per the "nonce tick" duration. Instead, request tokens
 * are made to be pure per the arguments they represent. There
 * is no need to cyclically expire them or tie them to each user.
 *
 * WordPress transients are more effort to uninstall and may
 * behave differently depending on WordPress's caching system.
 * Transients are also likely to be less efficient since they
 * are stored as multiple records within the generic wp_options
 * database table, which can get to be quite large.
 *
 * @since 3.7.0
 */
class Request_Token {

	/**
	 * The buffer for database transactions.
	 *
	 * @see Request_Token::buffer_init()
	 *
	 * @since 3.7.0
	 *
	 * @var array $buffer
	 */
	private static $buffer;

	/**
	 * If buffering database transactions. Default false.
	 *
	 * @since 3.7.0
	 *
	 * @var bool $buffer_enabled
	 */
	private static $buffer_enabled = false;

	/**
	 * The request token's database record data.
	 *
	 * @since 3.7.0
	 *
	 * @var array $data
	 */
	private $data;

	// **************************** //
	// **    Static Functions    ** //
	// **************************** //

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 3.7.0
	 */
	public static function register() {
		add_action( 'delete_expired_transients', __CLASS__ . '::delete_stale_tokens', 10, 0 );
	}

	/**
	 * Deletes request tokens that have not been recently accessed.
	 *
	 * @see Request_Token::get_staleness_duration()
	 *
	 * @since 3.7.0
	 */
	public static function delete_stale_tokens() {

		Database_Manager::init();
		$table = Database_Manager::$request_tokens_table;

		$staleness_timestamp = Database_Manager::unix_as_sql_timestamp(
			time() - static::get_staleness_duration()
		);

		global $wpdb;
		$rows_affected = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM `' . esc_sql( $table ) . '`
					WHERE last_accessed <= %s',
				$staleness_timestamp
			)
		);

		if ( false === $rows_affected ) {
			trigger_error(
				'Failed to delete stale request tokens. SQL error encountered: ' . esc_html( $wpdb->last_error ),
				E_USER_NOTICE
			);
		} else {
			/**
			 * Fires after stale request tokens were successfully deleted.
			 *
			 * @since 3.8.0
			 *
			 * @param int $count The number of request tokens that were
			 * deleted. Note this may be 0 if no stale tokens were found.
			 */
			do_action(
				'ptc_completionist_deleted_stale_request_tokens',
				$rows_affected
			);
		}
	}

	/**
	 * Deletes all request tokens and their data.
	 *
	 * @since 3.10.0 Returns boolean instead of void.
	 * @since 3.7.0
	 *
	 * @return bool If successful.
	 */
	public static function delete_all() : bool {

		Database_Manager::init();
		$success = Database_Manager::truncate_table(
			Database_Manager::$request_tokens_table
		);

		if ( true === $success ) {
			/**
			 * Fires after all request tokens were successfully deleted.
			 *
			 * @since 3.8.0
			 */
			do_action( 'ptc_completionist_deleted_all_request_tokens' );
		}

		return $success;
	}

	/**
	 * Clears all request tokens' cached data.
	 *
	 * @since 3.10.2
	 *
	 * @return int The number of rows affected.
	 */
	public static function clear_cache_data() : int {

		Database_Manager::init();
		$table = Database_Manager::$request_tokens_table;

		global $wpdb;
		$rows_affected = $wpdb->query(
			'UPDATE `' . esc_sql( $table ) . "`
				SET cache_data = '', cached_at = '0000-00-00 00:00:00'"
		);

		if ( false === $rows_affected ) {
			trigger_error(
				'Failed to clear request tokens\' cache data. SQL error encountered:' . esc_html( $wpdb->last_error ),
				E_USER_NOTICE
			);
			$rows_affected = 0;
		} else {
			/**
			 * Fires after all request tokens' cache data was cleared.
			 *
			 * @since 3.10.2
			 *
			 * @param int $count The number of cache records that
			 * were cleared.
			 */
			do_action(
				'ptc_completionist_cleared_request_tokens_cache_data',
				$rows_affected
			);
		}

		return $rows_affected;
	}

	/**
	 * Generates the request token for the provided arguments.
	 *
	 * This should always return the same token string for the
	 * same arguments (irrespective of sort order). It is a pure function.
	 *
	 * @since 3.7.0
	 *
	 * @param array  $request_args The request arguments to represent.
	 * Note that they may change due to validation and standardization.
	 * @param string $args_as_json Optional. Variable reference which
	 * will be set to the JSON-encoded arguments on success. Default ''.
	 *
	 * @return string A token representing the provided arguments.
	 */
	public static function generate_token(
		array &$request_args,
		?string &$args_as_json = ''
	) : string {

		if ( empty( $request_args['auth_user'] ) ) {
			// An 'auth_user' argument is required to prevent data
			// security and privacy mixups. Default to current setting.
			$request_args['auth_user'] = (int) Options::get( Options::FRONTEND_AUTH_USER_ID );
			if (
				empty( $request_args['auth_user'] ) ||
				$request_args['auth_user'] <= 0
			) {
				// You shouldn't be generating tokens without
				// a specified frontend authentication user!
				trigger_error(
					'Failed to generate a request token. The default frontend authentication user has not been set.',
					E_USER_NOTICE
				);
				return '';
			}
		}

		if ( empty( $request_args['_cache_key'] ) ) {
			trigger_error(
				'A "_cache_key" argument is required to prevent cache data collisions by requests that have the same arguments.',
				E_USER_WARNING
			);
			return '';
		}

		ksort( $request_args );

		$args_as_json = wp_json_encode( $request_args );
		if ( false === $args_as_json ) {
			trigger_error(
				'Failed to JSON encode arguments array to generate a request token.',
				E_USER_WARNING
			);
			$args_as_json = '';
			return '';
		}

		return md5( wp_salt( 'nonce' ) . $args_as_json );
	}

	/**
	 * Saves a request token for the given arguments.
	 *
	 * If the request token already exists in the database, its
	 * `last_accessed` datetime will still be updated.
	 *
	 * Saving a request token authorizes the frontend to make use
	 * of the request token, so perform this action only in
	 * permitted and secure contexts. For example, post content
	 * requires certain user permissions to update, so it can be
	 * trusted. In contrast, an anonymous submission of a public
	 * form should generally not be trusted.
	 *
	 * @since 3.7.0
	 *
	 * @param array $request_args The request arguments to represent.
	 * Note that they may change due to validation and standardization.
	 *
	 * @return string The request token or an empty string if
	 * the data could not be saved.
	 */
	public static function save( array &$request_args ) : string {

		// Generate the token.
		$args_as_json = '';
		$token = static::generate_token( $request_args, $args_as_json );
		if ( empty( $token ) ) {
			trigger_error(
				'Failed to save invalid request token for request arguments: ' . esc_html( print_r( $request_args, true ) ),
				E_USER_WARNING
			);
			return '';
		}

		// Add new or update existing request token data.

		if ( true === static::$buffer_enabled ) {
			// Add request token data to buffer, if necessary.
			if ( empty( static::$buffer['save'][ $token ] ) ) {
				static::$buffer['save'][ $token ] = $args_as_json;
			}
		} else {
			// Immediately write to the database.

			Database_Manager::init();
			$table = Database_Manager::$request_tokens_table;

			global $wpdb;
			$rows_affected = $wpdb->query(
				$wpdb->prepare(
					'INSERT INTO `' . esc_sql( $table ) . '` (token,args) VALUES (%s,%s)
						ON DUPLICATE KEY UPDATE last_accessed=CURRENT_TIMESTAMP',
					$token,
					$args_as_json
				)
			);

			if ( false === $rows_affected ) {
				trigger_error(
					'Failed to save request token. SQL error encountered: ' . esc_html( $wpdb->last_error ),
					E_USER_WARNING
				);
				return '';
			}
		}

		// Return saved request token.
		return $token;
	}

	/**
	 * Initializes and enables buffering.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $force_init Optional. If the buffer should be
	 * cleared even when buffering is already enabled. Default false.
	 */
	public static function buffer_start( bool $force_init = false ) {
		if ( $force_init || ! static::$buffer_enabled ) {
			static::buffer_init();
			static::$buffer_enabled = true;
		}
	}

	/**
	 * Commits and cleans the buffer's contents and disables buffering.
	 *
	 * Note that the buffer is disabled before actually flushing
	 * the buffer's contents. This is for thread safety since
	 * disabling the buffer means any further transactions will be
	 * immediately written to the database. Theoretically, the
	 * buffer could be committed and cleaned (aka flushed), then
	 * a write request happens which sees that the buffer is
	 * currently enabled so it does nothing, and then the buffer
	 * actually gets disabled. That buffered request would then
	 * not be committed (aka written).
	 *
	 * @since 3.7.0
	 */
	public static function buffer_end_flush() {
		static::$buffer_enabled = false;
		static::buffer_flush();
	}

	/**
	 * Initializes (or resets) the buffer.
	 *
	 * @since 3.7.0
	 */
	public static function buffer_init() {
		static::$buffer = array(
			'save' => array(),
		);
	}

	/**
	 * Commits the buffer's content and resets the buffer.
	 *
	 * @since 3.7.0
	 */
	public static function buffer_flush() {
		static::buffer_commit();
		static::buffer_init();
	}

	/**
	 * Commits the buffered database writes.
	 *
	 * You REALLY shouldn't support buffering for instance methods.
	 * Database queries may unpredictably fail which means
	 * you can't know if updating the Request_Token instance's
	 * $data in memory will actually reflect what's in the database.
	 * You can't just assume the buffered queries will succeed.
	 * For this reason, only support buffering on static functions.
	 *
	 * @see Request_Token::save()
	 *
	 * @since 3.7.0
	 */
	public static function buffer_commit() {

		// Prepare database connection information.
		global $wpdb;
		Database_Manager::init();
		$table = Database_Manager::$request_tokens_table;

		// Add new or update existing request token data.
		if ( ! empty( static::$buffer['save'] ) ) {

			// Build database query statement.
			$insertion_query = 'INSERT INTO `' . esc_sql( $table ) . '` (token,args) VALUES ';

			// Append each VALUES set.
			foreach ( static::$buffer['save'] as $token => &$args_as_json ) {
				$insertion_query .= $wpdb->prepare(
					'(%s,%s), ',
					$token,
					$args_as_json
				);
			}

			// Remove last ending ', ' (comma space).
			$insertion_query = substr( $insertion_query, 0, -2 );

			// Update the last_accessed datetime if record exists.
			$insertion_query .= ' ON DUPLICATE KEY UPDATE last_accessed=CURRENT_TIMESTAMP';

			// Execute statement.
			$rows_affected = $wpdb->query( $insertion_query );//phpcs:ignore

			// Check for errors.
			if ( false === $rows_affected ) {
				trigger_error(
					'Failed to buffer_commit save request tokens. SQL error encountered: ' . esc_html( $wpdb->last_error ),
					E_USER_WARNING
				);
			}
		}
	}

	/**
	 * Gets the cache entry TTL in seconds.
	 *
	 * @since 3.7.0
	 *
	 * @return int The cache TTL in seconds.
	 */
	private static function get_cache_ttl() : int {
		/**
		 * Filters the duration of Asana response cache entries.
		 *
		 * @since 3.7.0
		 *
		 * @param int $ttl Duration in seconds. Default 900 (15 minutes).
		 */
		return apply_filters(
			'ptc_completionist_request_tokens_ttl',
			Options::get( Options::CACHE_TTL_SECONDS )
		);
	}

	/**
	 * Gets the duration in seconds for when a request token is
	 * considered to be stale.
	 *
	 * Request tokens are routinely deleted after they haven't
	 * been accessed for at least the "staleness duration".
	 * This is because request tokens authorize which API
	 * requests may be made. Note that they do not authenticate
	 * the API requests.
	 *
	 * When a request token is no longer present and does not
	 * get recreated on the server-side, the frontend requests
	 * are then not permitted and the request token will no
	 * longer work.
	 *
	 * Using a "staleness" expiry is the most reliable way to
	 * recognize when a request token is no longer applicable.
	 * While deauthorization is not immediate (due to waiting
	 * for the staleness duration to pass), it is more reliable
	 * than detecting each case where a request token is no
	 * longer needed as WordPress systems can vary dramatically.
	 *
	 * @since 3.7.0
	 *
	 * @return int The staleness duration in seconds.
	 */
	private static function get_staleness_duration() : int {
		/**
		 * Filters the duration in seconds for when a request token
		 * is considered to be stale.
		 *
		 * A proper "stalenss duration" depends on the regularity
		 * of traffic and activity on the WordPress system. Cache
		 * durations and strategies may also play a role.
		 *
		 * For stronger security, a shorter staleness duration is
		 * preferred so that request tokens are deauthorized as
		 * soon as possible. However, note that the interval for
		 * deleting stale request tokens may be longer. Using a 0
		 * (zero) staleness duration would cause all request tokens
		 * to be deleted during each scan.
		 *
		 * If a request token is needed but has already been deleted
		 * by staleness checks, it will simply be created again as
		 * it is needed again. The only lapse in service would be if
		 * caching is preventing the server-side from recording the
		 * token before the frontend tries to use the token.
		 *
		 * @since 3.7.0
		 *
		 * @param int $duration Duration in seconds. Default 43200 (12 hours).
		 */
		return apply_filters(
			'ptc_completionist_request_tokens_staleness_duration',
			12 * HOUR_IN_SECONDS
		);
	}

	// ************************** //
	// **    Public Methods    ** //
	// ************************** //

	/**
	 * Instantiates a request token management context.
	 *
	 * The request token should already exist in the database, but
	 * execution won't break here if not. Be sure to confirm with
	 * `$request_token->exists()` after instantiation.
	 *
	 * @see Request_Token::exists()
	 *
	 * @since 3.7.0
	 *
	 * @param string $token The request token.
	 */
	public function __construct( string $token ) {
		$this->data = array( 'token' => $token );
		$this->load_from_database();
	}

	/**
	 * Checks if the request token exists in the database.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $force_read Optional. If the value should be
	 * retrieved fresh from the database. Default false to use
	 * the current value in memory.
	 *
	 * @return bool If the request token exists.
	 */
	public function exists( bool $force_read = false ) : bool {

		if ( $force_read ) {
			$this->load_from_database();
		}

		// Request arguments are required to be stored for a
		// request token, as well as the `last_accessed` datetime.
		//
		// The current token in question is always stored in memory
		// for this management object instance, so the 'token' field
		// does not reflect its actual validity.
		return (
			! empty( $this->data['args'] ) &&
			! empty( $this->data['last_accessed'] )
		);
	}

	/**
	 * Loads the request token's data from the database.
	 *
	 * @since 3.7.0
	 */
	public function load_from_database() {

		// Clear memory cache. This is also the error state.
		$this->data = array( 'token' => $this->data['token'] );

		// Attempt to read data from the database.

		Database_Manager::init();
		$table = Database_Manager::$request_tokens_table;

		global $wpdb;
		$res = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM `' . esc_sql( $table ) . '`
					WHERE token = %s LIMIT 1',
				$this->data['token']
			),
			\ARRAY_A
		);

		// Check if valid.

		if ( null === $res || ! is_array( $res ) ) {
			// The request token simply doesn't exist.
			return;
		}

		if ( $res['token'] !== $this->data['token'] ) {
			trigger_error(
				"Retrieved token '" . esc_html( $res['token'] ) . "' does not match requested token '" . esc_html( $this->data['token'] ) . "'. The request token's data in memory was not updated.",
				\E_USER_WARNING
			);
			return;
		}

		// Format data for usage.
		foreach ( $res as $field => &$value ) {
			switch ( $field ) {

				// Decode JSON value into array.
				case 'args':
				case 'cache_data':
					if ( '' === $value ) {
						// There is no value to decode, so use an empty array.
						$value = array();
					} else {
						// Decode JSON value for usage.
						$decoded = json_decode( $value, true );
						if ( null === $decoded || ! is_array( $decoded ) ) {
							trigger_error(
								"Failed to JSON decode '" . esc_html( $field ) . "' data: " . esc_html( print_r( $value, true ) ),
								E_USER_WARNING
							);
							// Abort loading this dataset since it is invalid.
							return;
						} else {
							// Successfully decoded JSON value to an array.
							$value = $decoded;
						}
					}
					break;

				// @NOTE - You may want to eventually load 'last_accessed'
				// as an actual PHP DateTime object. I'm just not doing
				// that right now because it's unnecessary at this time.

				default:
					break;
			}
		}

		// Successful retrieval; Data is ready to use.
		$this->data = $res;
	}

	/**
	 * Updates the `last_accessed` timestamp in the database.
	 *
	 * @since 3.7.0
	 *
	 * @return bool If successfully updated.
	 */
	public function touch() : bool {

		global $wpdb;
		Database_Manager::init();

		$current_timestamp = Database_Manager::unix_as_sql_timestamp();

		$rows_affected = $wpdb->update(
			Database_Manager::$request_tokens_table,
			array( 'last_accessed' => $current_timestamp ),
			array( 'token' => $this->data['token'] ),
			'%s',
			'%s'
		);

		if ( 1 !== $rows_affected ) {
			return false;
		}

		$this->data['last_accessed'] = $current_timestamp;
		return true;
	}

	/**
	 * Updates the request token's cache_data value in the database.
	 *
	 * Note that an array, associative array, or object may be
	 * cached; however, it will only be retrieved as an array or
	 * associative array.
	 *
	 * @see Request_Token::get_cache_data()
	 *
	 * @since 3.7.0
	 *
	 * @param array|object $data The raw data. It should not be
	 * encoded, serialized, or escaped.
	 *
	 * @return bool If successfully updated.
	 */
	public function update_cache_data( $data ) : bool {

		if ( ! ( is_array( $data ) || is_object( $data ) ) ) {
			// Union types aren't supported until PHP 8.0,
			// so this is a backwards-compatible typecheck.
			$data_type = gettype( $data );
			trigger_error(
				"Refused to cache non-array, non-object data of type '" . esc_html( $data_type ) . "':" . esc_html( print_r( $data, true ) ),
				E_USER_WARNING
			);
			return '';
		}

		$data_as_json = wp_json_encode( $data );
		if ( false === $data_as_json ) {
			trigger_error(
				'Failed to JSON encode cache data: ' . esc_html( print_r( $data, true ) ),
				E_USER_WARNING
			);
			return '';
		}

		global $wpdb;
		Database_Manager::init();

		$current_timestamp = Database_Manager::unix_as_sql_timestamp();

		$rows_affected = $wpdb->update(
			Database_Manager::$request_tokens_table,
			array(
				'cache_data'    => $data_as_json,
				'cached_at'     => $current_timestamp,
				'last_accessed' => $current_timestamp,
			),
			array( 'token' => $this->data['token'] ),
			'%s',
			'%s'
		);

		if ( 1 !== $rows_affected ) {
			return false;
		}

		$this->data['cache_data'] = $data;
		$this->data['cached_at']  = $current_timestamp;
		return true;
	}

	/**
	 * Gets the request token's cached data.
	 *
	 * Note that an array, associative array, or object may be
	 * cached; however, it will only be retrieved as an array or
	 * associative array.
	 *
	 * @see Request_Token::update_cache_data()
	 * @see Request_Token::get_cache_ttl()
	 *
	 * @since 3.7.0
	 *
	 * @param bool $force_read Optional. If the value should be
	 * retrieved fresh from the database. Default false to use
	 * the current value in memory.
	 *
	 * @return array The cache data. An empty array is returned
	 * when there is no cache data or if the cache has expired.
	 */
	public function get_cache_data( bool $force_read = false ) : array {

		if ( $force_read ) {
			$this->load_from_database();
		}

		// Check if cache is expired.

		$cached_at_unix = Database_Manager::sql_timestamp_as_unix(
			$this->data['cached_at']
		);

		$expire_unix = $cached_at_unix + static::get_cache_ttl();
		if ( time() > $expire_unix ) {
			// The cached data has expired.
			return array();
		}

		// The cached data is valid.
		return $this->data['cache_data'];
	}

	/**
	 * Gets the arguments represented by the request token.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $force_read Optional. If the value should be
	 * retrieved fresh from the database. Default false to use
	 * the current value in memory.
	 *
	 * @return mixed The cache data.
	 */
	public function get_args( bool $force_read = false ) {

		if ( $force_read ) {
			$this->load_from_database();
		}

		return $this->data['args'];
	}
}
