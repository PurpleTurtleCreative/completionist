<?php
/**
 * Request_Tokens class
 *
 * @since [unreleased]
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
 * access data or perform actions that have not been explicitly used.
 * Request tokens also maintain a cache of the respective response data.
 *
 * @since [unreleased]
 */
class Request_Tokens {

	private $post_id;

	// **************************** //
	// **    Static Functions    ** //
	// **************************** //

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action( 'edit_post', array( __CLASS__, 'purge_for_post' ), 10, 1 );
	}

	public static function purge_for_post( int $post_id ) {
		$request_tokens = new static( $post_id );
		$request_tokens->purge();
	}

	public static function purge_all() {
		Options::delete( Options::REQUEST_TOKENS, -1 );
	}

	// ************************** //
	// **    Public Methods    ** //
	// ************************** //

	public function __construct( int $post_id ) {
		$this->post_id = $post_id;
	}

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

	public function get_request_args( string $request_token ) : array {
		return $this->get()[ $request_token ]['request_args'] ?? array();
	}

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

	public function purge() {
		Options::delete( Options::REQUEST_TOKENS, $this->post_id );
	}

	public function exists( string $request_token ) {
		return isset( $this->get()[ $request_token ] );
	}

	// *************************** //
	// **    Private Methods    ** //
	// *************************** //

	private function get() {
		return Options::get( Options::REQUEST_TOKENS, $this->post_id );
	}

	private function generate_token( array $request_args ) : string {
		asort( $request_args );
		return md5( wp_salt( 'nonce' ) . serialize( $request_args ) );
	}

	private function get_cache_ttl() {
		/**
		 * Filters the duration of Asana response cache entries.
		 *
		 * @since [unreleased]
		 *
		 * @param int $ttl Duration in seconds. Default 30 minutes.
		 */
		return apply_filters( 'ptc_completionist_request_tokens_ttl', 30 * MINUTE_IN_SECONDS );
	}
}
