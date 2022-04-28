<?php
/**
 * Asana User class
 *
 * Loads the Asana API client for a given user.
 *
 * @since [UNRELEASED]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/errors.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';

/**
 * Instantiated class to perform Asana API requests on an authenticated
 * user's behalf. All Asana API requests require a user identity, so all
 * Asana API requests should be handled via this class.
 */
class Asana_User {

	/**
	 * The task ?opt_fields csv for Asana API requests.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var string TASK_OPT_FIELDS
	 */
	private const TASK_OPT_FIELDS = 'name,completed,notes,due_on,assignee,workspace,tags';

	/**
	 * The currently authenticated WordPress user's ID.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var int $wp_user_id
	 */
	private $wp_user_id;

	/**
	 * The authenticated Asana API client object.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var \Asana\Client $asana
	 */
	private $asana;

	/**
	 * The currently authenticated Asana API user's metadata from Asana.
	 *
	 * @since [UNRELEASED]
	 *
	 * @var \stdClass $me
	 */
	private $me;

	/**
	 * Attempts to instantiate an authenticated Asana API client instance
	 * for the given WordPress user.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param int $wp_user_id The WordPress user ID.
	 *
	 * @throws \PTC_Completionist\Errors\NoAuthorization If the user does not
	 * have a valid Asana PAT saved.
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public function __construct( int $wp_user_id ) {

		$this->wp_user_id = $wp_user_id;
		$asana_personal_access_token = Options::get( Options::ASANA_PAT, $this->wp_user_id );
		if (
			false === $asana_personal_access_token
			|| '' === $asana_personal_access_token
		) {
			throw new Errors\NoAuthorization( 'No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.', 401 );
		}

		require_once PLUGIN_PATH . '/vendor/autoload.php';
		$asana = \Asana\Client::accessToken(
			$asana_personal_access_token,
			[
				'headers' => [
					'asana-enable' => 'new_user_task_lists',
				],
			]
		);

		try {
			$this->me = $asana->users->me();
		} catch ( \Asana\Errors\NoAuthorizationError $e ) {
			// Asana responded with disapproval of the provided PAT.
			Options::delete( Options::ASANA_PAT );
			throw new Errors\NoAuthorization( 'Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.', $e->getCode() );
		} catch ( \Exception $e ) {
			// Don't delete option here because could be server error or API limit.
			$error_code = esc_html( $e->getCode() );
			$error_msg = esc_html( $e->getMessage() );
			throw new \Exception( "Asana authorization failure {$error_code}: {$error_msg}", $e->getCode() );
		}

		// If all was well, we can safely use this authenticated client instance.
		$this->asana = $asana;
	}

	// .. Validation .. //

	/**
	 * Checks if the current Asana user is a member of the saved workspace.
	 *
	 * @since [UNRELEASED]
	 *
	 * @return bool
	 */
	public function is_workspace_member() : bool {

		$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		if ( empty( $workspace_gid ) ) {
			return false;
		}

		foreach ( $this->me->workspaces as $workspace ) {
			if ( $workspace->gid === $workspace_gid ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if an Asana task is visible to the currently authenticated user.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The Asana task GID to check.
	 * @return bool
	 */
	public function is_task_visible( string $task_gid ) : bool {
		return in_array( $task_gid, $this->get_task_gids() );
	}

	// .. Getters .. //

	public function get_task( string $task_gid ) {}
	public function get_task_gids() {}
	public function get_all_tasks() {}
	public function get_all_tasks_by_post( int $post_id ) {}
	public function get_workspace_projects() {}

	// .. Modifiers .. //

	public function tag_task( string $task_gid ) {}
	public function untag_task( string $task_gid ) {}

	/**
	 * Deletes a task in Asana.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task gid to delete.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public function delete_task( string $task_gid ) {
		// Request the deletion in Asana.
		$this->asana->tasks->delete( $task_gid );
		// @TODO - Update the cache.
	}

	/**
	 * Creates a task in Asana and optionally pins it to a WordPress post.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param array $args The unsanitized task params. Accepted keys are:
	 * * 'name' => (string) Required. The task title.
	 * * 'post_id' => (int) The WordPress post ID on which to pin the new task.
	 * * 'assignee' => (gid) The assignee's Asana gid.
	 * * 'due_on' => (date string) The task due date.
	 * * 'project' => (gid) The Asana project gid to house the task.
	 * * 'notes' => (string) The task description.
	 * @return \stdClass The created task object response from Asana.
	 *
	 * @throws \Exception Required settings may be missing or the request to
	 * Asana may fail.
	 */
	public function create_task( array $args ) : \stdClass {

		if ( ! isset( $args['name'] ) ) {
			throw new \Exception( 'A task name is required.', 409 );
		}

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'A site tag is required to create a task. Please set a site tag in Completionist\'s settings.', 409 );
		}

		if ( isset( $args['post_id'] ) ) {
			/* Validate for pinning */
			$the_post_id = (int) Options::sanitize( 'gid', $args['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$the_post = get_post( $the_post_id );
			if ( null === $the_post ) {
				throw new \Exception( 'The provided post id is invalid.', 400 );
			}
		}

		/* Gather Input Data */

		$params['tags'] = $site_tag_gid;

		$name = sanitize_text_field( wp_unslash( $args['name'] ) );
		if ( ! empty( $name ) ) {
			$params['name'] = $name;
		} else {
			throw new \Exception( 'A task name is required.', 400 );
		}

		if ( isset( $args['assignee'] ) ) {
			$assignee_gid = Options::sanitize( 'gid', $args['assignee'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! empty( $assignee_gid ) ) {
				$params['assignee'] = $assignee_gid;
			}
		}

		if ( isset( $args['due_on'] ) ) {
			$due_on = Options::sanitize( 'date', $args['due_on'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! empty( $due_on ) ) {
				$params['due_on'] = $due_on;
			}
		}

		if ( isset( $args['project'] ) ) {
			$project_gid = Options::sanitize( 'gid', $args['project'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( ! empty( $project_gid ) ) {
				$params['projects'] = $project_gid;
			}
		}

		if ( ! isset( $params['projects'] ) ) {
			/* A workspace is required if a project hasn't been provided. */
			$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
			if ( ! empty( $workspace_gid ) ) {
				$params['workspace'] = $workspace_gid;
			} else {
				throw new \Exception( 'Please set a Workspace in Completionist\'s settings before creating a task.', 400 );
			}
		}

		if ( isset( $args['notes'] ) ) {
			$notes = sanitize_textarea_field( wp_unslash( $args['notes'] ) );
			if ( ! empty( $notes ) ) {
				$params['notes'] = $notes;
			}
		}

		/* Create the task */

		$task = $this->asana->tasks->create( $params );

		if ( ! isset( $task->gid ) ) {
			throw new \Exception( 'Unrecognized API response to create task.', 409 );
		}

		if ( isset( $the_post_id ) ) {

			/* Pin the task */

			try {
				$did_pin_task = Options::save( Options::PINNED_TASK_GID, $task->gid, false, $the_post_id );
			} catch ( \Exception $e ) {
				$did_pin_task = false;
			}

			if ( false === $did_pin_task ) {
				error_log( "Failed to pin new task {$task->gid} to post {$the_post_id}." );
			}
		}

		// @TODO - Refresh cache to contain new task.
		return $task;
	}//end create_task()

	// .. Cache Updating (Asana API) .. //

	private function refresh_task( string $task_gid ) {}
	private function refresh_tasks( string[] $task_gids ) {}
	private function refresh_task_visibility_gids() {}
	private function refresh_workspace_projects() {}







	/*========== ORIGINAL Asana_Interface CLASS BELOW ==========*/

	/**
	 * Gets WordPress users that match Asana users by email.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $workspace_gid Optional. The gid of the workspace to get
	 * Asana users to match by email. Default '' to use the chosen workspace.
	 * @return \WP_User[] The matching WordPress users keyed by their Asana gid.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function get_connected_workspace_users( string $workspace_gid = '' ) : array {

		if ( '' === $workspace_gid ) {
			$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		} else {
			$workspace_gid = Options::sanitize( 'gid', $workspace_gid );
		}

		if ( '' === $workspace_gid ) {
			return [];
		}

		$users_with_pat = get_users( [ 'meta_key' => Options::ASANA_PAT ] );
		$wp_users = [];

		require_once PLUGIN_PATH . '/vendor/autoload.php';
		foreach ( $users_with_pat as $wp_user ) {

			$asana_personal_access_token = Options::get( Options::ASANA_PAT, $wp_user->ID );
			if (
				false === $asana_personal_access_token
				|| '' === $asana_personal_access_token
			) {
				continue;
			}

			$asana = \Asana\Client::accessToken( $asana_personal_access_token );

			try {
				$me = $asana->users->me();
				foreach ( $me->workspaces as $workspace ) {
					if ( $workspace->gid == $workspace_gid ) {
						$wp_users[ $me->gid ] = $wp_user;
						break;
					}
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}//end foreach $users_with_pat

		return $wp_users;
	}

	/**
	 * Gets an array of WordPress user display names and emails keyed by their
	 * Asana gid.
	 *
	 * @see get_connected_workspace_users() For how users are selected.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $workspace_gid Optional. The gid of the workspace to get
	 * Asana users to match by email. Default '' to use the chosen workspace.
	 * @return string[] Strings of WordPress user display names and emails keyed
	 * by their Asana gid.
	 */
	public static function get_connected_workspace_user_options( string $workspace_gid = '' ) : array {

		$wp_users = [];

		try {
			$wp_users = self::get_connected_workspace_users( $workspace_gid );
			foreach ( $wp_users as $gid => $wp_user ) {
				$wp_users[ $gid ] = "{$wp_user->display_name} ({$wp_user->user_email})";
			}
		} catch ( \Exception $e ) {
			error_log( HTML_Builder::format_error_string( $e, 'Failed to get_connected_workspace_user_options().' ) );
			$wp_users = [ 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) ];
		}

		return $wp_users;
	}

	/**
	 * Gets an array of Asana project names keyed by their gid.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $workspace_gid Optional. The gid of the workspace to get
	 * Asana users to match by email. Default '' to use the chosen workspace.
	 * @return string[] Asana project names keyed by their gid.
	 */
	public static function get_workspace_project_options( string $workspace_gid = '' ) : array {

		$project_options = [];

		try {
			$params = [
				'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
				'archived' => false,
				'opt_fields' => 'gid,name',
			];
			$projects = self::get_client()->projects->findAll( $params );
			foreach ( $projects as $project ) {
				$project_options[ $project->gid ] = $project->name;
			}
		} catch ( \Exception $e ) {
			error_log( HTML_Builder::format_error_string( $e, 'Failed to get_workspace_project_options().' ) );
			$project_options = [ 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) ];
		}

		return $project_options;
	}

	/**
	 * Gets a WordPress user ID by Asana user GID. Note that a user's GID is
	 * stored as long as the user is successfully authorized.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $user_gid The Asana user GID for searching.
	 * @return int The WordPress user's ID. Default 0.
	 */
	public static function get_user_id_by_gid( string $user_gid ) : int {

		$user_gid = Options::sanitize( 'gid', $user_gid );

		$query_args = [
			'meta_key' => Options::ASANA_USER_GID,
			'meta_value' => $user_gid,
			'fields' => 'ID',
		];

		$users = get_users( $query_args );
		$users_count = count( $users );

		if (
			1 === $users_count
			&& isset( $users[0] )
			&& $users[0] > 0
		) {
			return (int) $users[0];
		} elseif ( $users_count > 1 ) {
			// TODO: This error state should not be allowed.
			// Check PATs and User GIDs for dups when saving them.
			error_log( 'Warning: Multiple users have the same Asana User GID saved. Their WordPress User IDs are:' + print_r( $users, true ) );
		}

		return 0;
	}

	/**
	 * Gets the external link to a user's task list in Asana for the chosen
	 * workspace.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param int $user_id Optional. The WordPress user's ID. Default 0 to use
	 * current user's ID.
	 * @return string The link to the task list on Asana. Default ''.
	 */
	public static function get_task_list_external_link( int $user_id = 0 ) : string {

		$user_gid = Options::get( Options::ASANA_USER_GID, $user_id );
		$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		if ( '' === $workspace_gid || '' === $user_gid ) {
			return '';
		}

		try {

			$asana = self::get_client();
			$params = [
				'workspace' => $workspace_gid,
				'opt_fields' => 'gid',
			];

			$user_task_list = $asana->user_task_lists->findByUser( $user_gid, $params );
			$user_task_list_gid = Options::sanitize( 'gid', $user_task_list->gid );
			if ( empty( $user_task_list_gid ) ) {
				return '';
			}

			return 'https://app.asana.com/0/' . $user_task_list_gid . '/list';
		} catch ( \Exception $e ) {
			error_log( 'Failed to retrieve user\'s task list link. Error ' . $e->getCode() . ': ' . $e->getMessage() );
		}

		return '';
	}

	/**
	 * Extracts a task gid from a copied task link.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_link The task link provided by clicking the chainlink
	 * icon, "Copy task link", on a task in Asana.
	 *
	 * @return string The task gid. Default '' on failure.
	 */
	public static function get_task_gid_from_task_link( string $task_link ) : string {

		$task_link = filter_var( wp_unslash( $task_link ), FILTER_SANITIZE_URL );

		if (
			preg_match( '/\/([0-9]+)\/.$/', $task_link, $matches ) === 1
			&& isset( $matches[1] )
			&& ! empty( $matches[1] )
		) {
			return Options::sanitize( 'gid', $matches[1] );
		}

		return '';
	}

	/**
	 * Attempts to retrieve task data. Providing the post id of the provided
	 * pinned task gid will also attempt data self-healing.
	 *
	 * @since [UNRELEASED]
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The gid of the task to retrieve.
	 * @param string $opt_fields_deprecated Deprecated.
	 * @param int $post_id Optional. The post ID on which the task belongs to
	 * attempt self-healing on certain error responses. Default 0 to take no
	 * action on failure.
	 * @return \stdClass The task data returned from Asana.
	 *
	 * @throws \Exception The Asana client may not be authenticated or the API
	 * request may fail. Additional custom exceptions are:
	 * * 400: Invalid task gid - The provided task gid is invalid.
	 * * 410: Invalid task - The task is no longer available or relevent.
	 */
	public static function maybe_get_task_data( string $task_gid, string $opt_fields_deprecated = '', int $post_id = 0 ) : \stdClass {

		if ( ! empty( $opt_fields_deprecated ) ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.1.0',
				'$opt_fields is now a member constant, ' . __CLASS__ . '::TASK_OPT_FIELDS'
			);
		}

		$task_gid = Options::sanitize( 'gid', $task_gid );
		if ( empty( $task_gid ) ) {
			throw new \Exception( 'Invalid task gid', 400 );
		}

		try {

			$asana = self::get_client();
			$task = $asana->tasks->findById( $task_gid, [ 'opt_fields' => self::TASK_OPT_FIELDS ] );

			if (
				isset( $task->workspace->gid )
				&& $task->workspace->gid != Options::get( Options::ASANA_WORKSPACE_GID )
				&& $post_id > 0
			) {
				if ( '' != $task_gid && Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
					error_log( "Unpinned foreign task from post $post_id." );
					throw new \Exception( 'Unpinned Foreign Task', 410 );
				}
			}

			if (
				isset( $task->tags )
				&& is_array( $task->tags )
				&& ! self::has_tag( $task, Options::get( Options::ASANA_TAG_GID ) )
				&& $post_id > 0
			) {
				if ( '' != $task_gid && Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
					error_log( "Unpinned task missing site tag from post $post_id." );
					throw new \Exception( 'Unpinned Foreign Task', 410 );
				}
			}

			return $task;
		} catch ( \Exception $e ) {

			$error_code = $e->getCode();
			if (
				0 === $error_code
				&& isset( $e->status )
				&& $e->status > 0
			) {
				$error_code = $e->status;
			}

			$error_msg = $e->getMessage();

			if (
				404 == $error_code
				&& $post_id > 0
			) {
				if ( '' != $task_gid && Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
					error_log( "Unpinned [404: Not Found] task from post $post_id." );
					throw new \Exception( 'Unpinned Task', 410 );
				}
			} elseif (
				403 != $error_code
				&& 410 != $error_code
			) {
				error_log( "Failed to fetch task data, error $error_code: $error_msg" );
			}

			throw $e;
		}

		throw new \Exception( 'Failed to get task data.', 0 );
	}

	/**
	 * Determines if a task belongs to a workspace.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task to check.
	 * @param string $workspace_gid Optional. Default '' to use saved workspace.
	 * @return bool If the task belongs to the workspace. Note that any API
	 * errors will cause false to be returned.
	 *
	 * @throws \Exception If a parameter is invalid, a 400 error will be thrown.
	 */
	public static function is_workspace_task( string $task_gid, string $workspace_gid = '' ) : bool {

		$task_gid = Options::sanitize( 'gid', $task_gid );
		if ( empty( $task_gid ) ) {
			throw new \Exception( 'Invalid task gid', 400 );
		}

		if ( '' === $workspace_gid ) {
			$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		} else {
			$workspace_gid = Options::sanitize( 'gid', $workspace_gid );
		}

		if ( empty( $workspace_gid ) ) {
			throw new \Exception( 'Invalid workspace gid', 400 );
		}

		try {
			$asana = self::get_client();
			$task = $asana->tasks->findById( $task_gid, [ 'opt_fields' => 'workspace' ] );
			if (
				isset( $task->workspace->gid )
				&& $task->workspace->gid === $workspace_gid
			) {
				return true;
			}
		} catch ( \Exception $e ) {
			return false;
		}

		return false;
	}

	/**
	 * Attempts to retrieve task data for all site tasks.
	 *
	 * @since [UNRELEASED]
	 * @since [UNRELEASED]
	 *
	 * @param string $opt_fields_deprecated Deprecated.
	 * @return \stdClass[] Task data objects.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function maybe_get_all_site_tasks( string $opt_fields_deprecated = '' ) : array {

		if ( ! empty( $opt_fields_deprecated ) ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.1.0',
				'$opt_fields is now a member constant, ' . __CLASS__ . '::TASK_OPT_FIELDS'
			);
		}

		// Load client to ensure current user is authenticated and set.
		$asana = self::get_client();

		$tasks = [];

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'Unable to retrieve site tasks when no site tag has been set.', 409 );
		}

		$params = [
			'opt_fields' => self::TASK_OPT_FIELDS,
		];

		$options = [
			'page_size' => 100, // Max page_size = 100.
			'item_limit' => 15000,
		];

		/*
		** An Asana Collection (Iterator) is returned. To actually perform the
		** API requests to get all the tasks, we must use the Iterator.
		*/
		$site_tasks = $asana->tasks->findByTag( $site_tag_gid, $params, $options );
		$all_tasks = [];
		foreach ( $site_tasks as $task ) {
			$task->action_link = HTML_Builder::get_task_action_link( $task->gid );
			$all_tasks[ $task->gid ] = $task;
		}

		return $all_tasks;
	}

	/**
	 * Sends a batch request to tag and comment on a task in Asana.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string $task_gid The task to act on.
	 * @param string $tag_gid The tag to add.
	 * @param string $comment The comment text.
	 * @param string $opt_fields_deprecated Deprecated.
	 * @return \stdClass[] An array of response objects. Instance members
	 * include 'body', 'status_code', and 'headers'.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function tag_and_comment( string $task_gid, string $tag_gid, string $comment, string $opt_fields_deprecated = '' ) : array {

		if ( ! empty( $opt_fields_deprecated ) ) {
			_deprecated_argument(
				__FUNCTION__,
				'3.1.0',
				'$opt_fields is now a member constant, ' . __CLASS__ . '::TASK_OPT_FIELDS'
			);
		}

		$asana = self::get_client();

		$task_gid = Options::sanitize( 'gid', $task_gid );
		$tag_gid = Options::sanitize( 'gid', $tag_gid );
		$comment = Options::sanitize( 'string', $comment );

		$opt_fields = explode( ',', self::TASK_OPT_FIELDS );

		$data = [
			'actions' => [
				[
					'method' => 'POST',
					'relative_path' => sprintf( '/tasks/%s/addTag', $task_gid ),
					'data' => [
						'tag' => $tag_gid,
					],
					'options' => [
						'fields' => $opt_fields,
					],
				],
				[
					'method' => 'POST',
					'relative_path' => sprintf( '/tasks/%s/stories', $task_gid ),
					'data' => [
						'text' => $comment,
					],
					'options' => [
						'fields' => $opt_fields,
					],
				],
			],
		];

		return $asana->post( '/batch', $data );
	}

	/**
	 * Sends batch requests to tag multiple tasks.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param string[] $task_gids The tasks to tag.
	 * @param string $tag_gid The tag.
	 * @return int Count of successfully tagged tasks.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function tag_all( array $task_gids, string $tag_gid ) : int {

		$asana = self::get_client();

		$data = [];
		$success_count = 0;

		$tag_gid = Options::sanitize( 'gid', $tag_gid );
		if ( '' === $tag_gid ) {
			return $success_count;
		}

		foreach ( $task_gids as $i => $task_gid ) {

			$task_gid = Options::sanitize( 'gid', $task_gid );
			if ( '' === $task_gid ) {
				continue;
			}

			$data['actions'][] = [
				'method' => 'POST',
				'relative_path' => sprintf( '/tasks/%s/addTag', $task_gid ),
				'data' => [
					'tag' => $tag_gid,
				],
			];

			if ( 10 === count( $data['actions'] ) ) {

				try {
					$res = $asana->post( '/batch', $data );
					foreach ( $res as $obj ) {
						if ( $obj->status_code >= 200 && $obj->status_code < 300 ) {
							++$success_count;
						}
					}
				} catch ( \Exception $e ) {
					$err_code = $e->getCode();
					$err_msg = $e->getMessage();
					error_log( "Batch request failed, tag_all(). Error {$err_code}: {$err_msg}" );
				}

				$data = [];

			}//end if 10 actions
		}//end foreach task gid

		if ( ! empty( $data ) ) {
			try {
				$res = $asana->post( '/batch', $data );
				foreach ( $res as $obj ) {
					if ( $obj->status_code >= 200 && $obj->status_code < 300 ) {
						++$success_count;
					}
				}
			} catch ( \Exception $e ) {
				$err_code = $e->getCode();
				$err_msg = $e->getMessage();
				error_log( "Batch request failed, tag_all(). Error {$err_code}: {$err_msg}" );
			}
			$data = [];
		}

		return $success_count;
	}

	/**
	 * Determines if a task has a tag.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param \stdClass $task The task object.
	 * @param string $tag_gid The tag gid.
	 * @return bool If the task has the tag.
	 */
	public static function has_tag( \stdClass $task, string $tag_gid ) : bool {

		$has_tag = false;

		if ( isset( $task->tags ) && is_array( $task->tags ) ) {
			foreach ( $task->tags as $tag ) {
				if ( isset( $tag->gid ) && $tag->gid === $tag_gid ) {
					$has_tag = true;
				}
			}
		}

		return $has_tag;
	}

	/**
	 * Confirms that required plugin settings are set and valid.
	 *
	 * @since [UNRELEASED]
	 *
	 * @throws \Exception If settings were found to be invalid.
	 */
	public static function require_settings() {

		$asana = self::get_client();

		$saved_workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
		$saved_tag_gid = Options::get( Options::ASANA_TAG_GID );

		if ( '' === $saved_workspace_gid || '' === $saved_tag_gid ) {
			throw new \Exception( 'Missing required settings. Please save an Asana workspace and tag.', 403 );
		}

		try {
			$tag = $asana->tags->findById( $saved_tag_gid, [ 'opt_fields' => 'workspace' ] );
			if ( isset( $tag->workspace->gid ) && $tag->workspace->gid !== $saved_workspace_gid ) {
				throw new \Exception( 'Invalid workspace and tag settings. The site tag is part of a different workspace.', 409 );
			}
		} catch ( \Asana\Errors\NotFoundError $e ) {
			throw new \Exception( 'Invalid site tag set.', 404 );
		} catch ( \Asana\Errors\InvalidRequestError $e ) {
			throw new \Exception( 'Invalid site tag or workspace set.', 400 );
		}
	}

	/**
	 * Counts how many tasks are completed.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param \stdClass[] $tasks An array of task objects with the "completed"
	 * boolean member set.
	 * @return int The count.
	 */
	public static function count_completed_tasks( array $tasks ) : int {

		$count = 0;

		foreach ( $tasks as $task ) {
			if ( isset( $task->completed ) && is_bool( $task->completed ) ) {
				if ( true === $task->completed ) {
					++$count;
				}
			}
		}

		return $count;
	}

	/**
	 * Gets an array of task gids from an array of task objects.
	 *
	 * @since [UNRELEASED]
	 *
	 * @param \stdClass[] $tasks The task objects.
	 * @return string[] The task gids.
	 */
	public static function get_tasks_gid_array( array $tasks ) : array {

		$arr = [];

		foreach ( $tasks as $task ) {
			if ( isset( $task->gid ) ) {
				$arr[] = $task->gid;
			}
		}

		return $arr;
	}
}//end class
