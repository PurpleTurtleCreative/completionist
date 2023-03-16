<?php
/**
 * Request_Tokens class
 *
 * @since 3.4.0
 */

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';

/**
 * Class to manage frontend request tokens.
 *
 * Request tokens ensure private data cannot be accessed. The server
 * creates a request token to obscure request parameters on the frontend.
 * This ensures generic requests cannot be made from the frontend to
 * access data or perform actions that have not been explicitly published.
 * Request tokens also maintain a cache of the respective response data.
 *
 * @since 3.4.0
 */
class Request_Tokens {

	/**
	 * The post ID of which the request token applies.
	 *
	 * @since 3.4.0
	 *
	 * @var int $post_id
	 */
	private $post_id;

	// **************************** //
	// **    Static Functions    ** //
	// **************************** //

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 3.4.0
	 */
	public static function register() {
		add_action( 'edit_post', array( __CLASS__, 'purge_for_post' ), 10, 1 );
	}

	/**
	 * Deletes all request token data for a given post.
	 *
	 * @since 3.4.0
	 *
	 * @param int $post_id The post to purge all request tokens.
	 */
	public static function purge_for_post( int $post_id ) {
		$request_tokens = new static( $post_id );
		$request_tokens->purge();
	}

	/**
	 * Deletes all request token data.
	 *
	 * @since 3.4.0
	 */
	public static function purge_all() {
		Options::delete( Options::REQUEST_TOKENS, -1 );
	}

	// ************************** //
	// **    Public Methods    ** //
	// ************************** //

	/**
	 * Instantiates a request token management context for the given post.
	 *
	 * @since 3.4.0
	 *
	 * @param int $post_id The post for request token management.
	 */
	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}

	/**
	 * Returns the request token's post ID.
	 *
	 * @since [unreleased]
	 *
	 * @param int The request token's post ID.
	 */
	public function get_post_id() : int {
		return $this->post_id;
	}

	/**
	 * Saves a new request token for the given arguments if it does
	 * not already exist.
	 *
	 * @since 3.4.0
	 *
	 * @param array $request_args The arguments that the request
	 * token represents.
	 * @return string The request token.
	 */
	public function save( array $request_args ) : string {

		// Get existing request tokens.
		$request_tokens = $this->get();

		// Calculate request token.
		$token = $this->generate_token( $request_args );

		// Abort if request token already exists.
		if ( isset( $request_tokens[ $token ] ) ) {
			return $token;
		}

		// Add new request token data.
		$request_tokens[ $token ] = array(
			'request_args' => $request_args,
			'cached_response' => array(
				'data' => '',
				'created_at' => 0,
			),
		);

		// Save record.
		Options::save(
			Options::REQUEST_TOKENS,
			$request_tokens,
			true,
			$this->post_id
		);

		// Return saved request token.
		return $token;
	}

	/**
	 * Gets the request arguments for the given request token.
	 *
	 * @since 3.4.0
	 *
	 * @param string $request_token The request token to retrieve.
	 * @return array The request arguments. Default empty if invalid.
	 */
	public function get_request_args( string $request_token ) : array {
		return $this->get()[ $request_token ]['request_args'] ?? array();
	}

	/**
	 * Gets the cached response, if available.
	 *
	 * @since 3.4.0
	 *
	 * @param string $request_token The request token to retrieve.
	 * @param mixed  $default The default value to return if the cache entry
	 * is expired.
	 * @return mixed The cached response if valid, or the specified $default
	 * value.
	 */
	public function get_cached_response(
		string $request_token,
		$default
	) {

		// Get cached response data.
		$cached_response = $this->get()[ $request_token ]['cached_response'];

		// Check if expired.
		$cache_expires_at = $cached_response['created_at'] + $this->get_cache_ttl();
		if ( time() > $cache_expires_at ) {
			// Return default since cache is invalid.
			return $default;
		}

		// Return valid cached response data.
		return $cached_response['data'];
	}

	/**
	 * Updates the response data cache entry for the given request token.
	 *
	 * Note that the request token must already exist.
	 *
	 * @since 3.4.0
	 *
	 * @param string $request_token The request token.
	 * @param mixed  $response The response data to cache.
	 */
	public function save_response(
		string $request_token,
		$response
	) {

		// Get existing request tokens.
		$request_tokens = $this->get();

		// Abort if invalid request token.
		if ( ! isset( $request_tokens[ $request_token ] ) ) {
			return;
		}

		// Update request token cached response data.
		$request_tokens[ $request_token ]['cached_response'] = array(
			'data' => $response,
			'created_at' => time(),
		);

		// Save record.
		Options::save(
			Options::REQUEST_TOKENS,
			$request_tokens,
			true,
			$this->post_id
		);
	}

	/**
	 * Deletes all request tokens for the current post context.
	 *
	 * @since 3.4.0
	 */
	public function purge() {
		Options::delete( Options::REQUEST_TOKENS, $this->post_id );
	}

	/**
	 * Checks if the request token exists for the current post context.
	 *
	 * @since 3.4.0
	 *
	 * @return bool True if the request token exists.
	 */
	public function exists( string $request_token ) : bool {
		return isset( $this->get()[ $request_token ] );
	}

	// *************************** //
	// **    Private Methods    ** //
	// *************************** //

	/**
	 * Gets all request token data for the current post context.
	 *
	 * @since 3.4.0
	 *
	 * @return array The request token data. Empty if none.
	 */
	private function get() : array {
		return Options::get( Options::REQUEST_TOKENS, $this->post_id );
	}

	/**
	 * Generates the request token for the provided arguments.
	 *
	 * Note this should always return the same token string for the
	 * same arguments (irrespective of sort order). It is a pure function.
	 *
	 * @since 3.4.0
	 *
	 * @param array $request_args The request arguments to represent.
	 * @return string A token representing the provided arguments.
	 */
	private function generate_token( array $request_args ) : string {
		asort( $request_args );
		return md5( wp_salt( 'nonce' ) . serialize( $request_args ) );
	}

	/**
	 * Gets the cache entry TTL in seconds.
	 *
	 * @since 3.4.0
	 *
	 * @return int The cache TTL in seconds.
	 */
	private function get_cache_ttl() : int {
		/**
		 * Filters the duration of Asana response cache entries.
		 *
		 * @since 3.4.0
		 *
		 * @param int $ttl Duration in seconds. Default 900 (15 minutes).
		 */
		return apply_filters( 'ptc_completionist_request_tokens_ttl', 0 * MINUTE_IN_SECONDS );
	}
}
