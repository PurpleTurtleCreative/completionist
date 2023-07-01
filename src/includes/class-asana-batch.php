<?php
/**
 * Asana Batch class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * An instanced class for batching Asana API requests.
 *
 * @link https://developers.asana.com/reference/batch-api
 * @link https://developers.asana.com/docs/batch-requests
 */
class Asana_Batch {

	private $asana;

	private $handle_success;

	private $handle_error;

	private $actions;

	private $action_response_handler_args;

	/**
	 * The maximum number of actions per batch request.
	 *
	 * Asana currently allows up to 10 actions per batch request.
	 *
	 * @link https://developers.asana.com/docs/batch-requests#making-a-batch-request
	 */
	private const MAX_BATCH_SIZE = 10;

	/**
	 * Instantiates an Asana_Batch object.
	 *
	 * @since [unreleased]
	 */
	public function __construct(
		\Asana\Client $asana,
		callable $handle_success,
		callable $handle_error
	) {
		$this->asana          = $asana;
		$this->handle_success = $handle_success;
		$this->handle_error   = $handle_error;
	}

	public function add_action(
		string $http_method,
		string $relative_path,
		$data = null,
		$options = null,
		array $action_response_handler_args = array()
	) {

		// Define the action given the provided arguments.

		$action = array(
			'method'        => $http_method,
			'relative_path' => $relative_path,
		);

		if ( $data ) {
			$action['data'] = $data;
		}

		if ( $options ) {
			$action['options'] = $options;
		}

		// Add batch entries.

		$this->actions[]                      = $action;
		$this->action_response_handler_args[] = $action_response_handler_args;

		// Check current batch size and process if necessary.

		if ( count( $this->actions ) === self::MAX_BATCH_SIZE ) {
			$this->process();
		}
	}

	public function reset() {
		$this->actions                      = array();
		$this->action_response_handler_args = array();
	}

	public function process() {

		$actions_count = count( $this->actions );
		if ( 0 === $actions_count ) {
			// No queued actions for processing.
			return;
		} elseif ( $actions_count > self::MAX_BATCH_SIZE ) {
			// This will result in an HTTP 400 response from Asana,
			// so let's not waste our API rate limit.
			trigger_error(
				'Refused to process Asana Batch API request with ' . $actions_count . ' actions (max: ' . self::MAX_BATCH_SIZE . ')',
				\E_USER_WARNING
			);
			$this->reset();
			return;
		}

		try {

			$responses = $this->asana->batchapi->createBatchRequest(
				array( 'actions' => $this->actions )
			);

			if ( ! empty( $responses ) && is_array( $responses ) ) {
				foreach ( $responses as $i => &$res ) {
					($this->handle_success)(
						$res,
						...$this->action_response_handler_args[ $i ]
					);
				}
			} else {
				throw new \Exception( 'Unknown error. Missing or invalid data from Asana Batch API response.' );
			}
		} catch ( \Exception $e ) {
			($this->handle_error)( $e );
		} finally {
			$this->reset();
		}
	}
}//end class
