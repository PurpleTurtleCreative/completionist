<?php
/**
 * Asana Batch class
 *
 * @since 3.9.0
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

	/**
	 * The authenticated Asana API client object.
	 *
	 * @since 3.9.0
	 *
	 * @var \Asana\Client $asana
	 */
	private $asana;

	/**
	 * The callback handler for processing each action response of
	 * a successfully retrieved batch API response.
	 *
	 * It receives the batch API response object as the first
	 * argument and any additional action response handler args
	 * from when the batch action was added.
	 *
	 * @see add_action() For registering additional callback args
	 * for processing the batch action.
	 *
	 * @since 3.9.0
	 *
	 * @var callable $handle_success
	 */
	private $handle_success;

	/**
	 * The callback handler for processing batch API request errors.
	 *
	 * This callback is called whenever the batch API request
	 * itself fails. It receives the batch API request error
	 * exception as its first and only argument since it is
	 * unrelated to the processing of any individual action.
	 *
	 * @since 3.9.0
	 *
	 * @var callable $handle_error
	 */
	private $handle_error;

	/**
	 * The actions to be included in the batch request.
	 *
	 * @link https://developers.asana.com/reference/createbatchrequest
	 *
	 * @since 3.9.0
	 *
	 * @var array[] $actions
	 */
	private $actions;

	/**
	 * The additional arguments passed to the success handler
	 * for each action's response included in the batch response.
	 *
	 * Note that the index of this array corresponds to the same
	 * index of the actions array, so they should be kept in sync.
	 *
	 * @see $handle_success For processing action responses.
	 *
	 * @since 3.9.0
	 *
	 * @var array[] $actions
	 */
	private $action_response_handler_args;

	/**
	 * The maximum number of actions per batch request.
	 *
	 * The batch request will automatically be sent once the
	 * current count of actions reaches this limit.
	 *
	 * Asana currently allows up to 10 actions per batch request.
	 *
	 * @link https://developers.asana.com/docs/batch-requests#making-a-batch-request
	 */
	private const MAX_BATCH_SIZE = 10;

	/**
	 * Instantiates an Asana_Batch object.
	 *
	 * @since 3.9.0
	 *
	 * @param \Asana\Client $asana The Asana client.
	 * @param callable      $handle_success The success handler.
	 * @param callable      $handle_error The error handler.
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

	/**
	 * Adds an action to be included in the batch request.
	 *
	 * If the added action results in the MAX_BATCH_SIZE, then the
	 * batch request will also be sent and processed.
	 *
	 * @link https://developers.asana.com/reference/createbatchrequest
	 *
	 * @since 3.9.0
	 *
	 * @param string $http_method The HTTP method.
	 * @param string $relative_path The Asana API relative path.
	 * @param array  $data Optional. The request arguments.
	 * Default null to not include request data.
	 * @param array  $options Optional. The request options such
	 * as 'limit' or 'fields'. Default null to not include options.
	 * @param array  $action_response_handler_args Optional. The
	 * additional arguments for the success handler when processing
	 * this action's response. Default empty array.
	 */
	public function add_action(
		string $http_method,
		string $relative_path,
		?array $data = null,
		?array $options = null,
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

	/**
	 * Resets the batch data to empty.
	 *
	 * @since 3.9.0
	 */
	public function reset() {
		$this->actions                      = array();
		$this->action_response_handler_args = array();
	}

	/**
	 * Processes the current batch of actions.
	 *
	 * @since 3.9.0
	 *
	 * @throws \Exception If the provided error handler throws
	 * an exception that occurs from API requests to Asana.
	 */
	public function process() {

		$actions_count = count( $this->actions );
		if ( 0 === $actions_count ) {
			// No queued actions for processing.
			return;
		} elseif ( $actions_count > self::MAX_BATCH_SIZE ) {
			// This will result in an HTTP 400 response from Asana,
			// so let's not waste our API rate limit.
			trigger_error(
				'Refused to process Asana Batch API request with ' . intval( $actions_count ) . ' actions (max: ' . intval( self::MAX_BATCH_SIZE ) . ')',
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
					( $this->handle_success )(
						$res,
						...$this->action_response_handler_args[ $i ]
					);
				}
			} else {
				throw new \Exception( 'Unknown error. Missing or invalid data from Asana Batch API response.' );
			}
		} catch ( \Exception $e ) {
			( $this->handle_error )( $e );
		} finally {
			$this->reset();
		}
	}
}//end class
