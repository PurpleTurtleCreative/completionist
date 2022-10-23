<?php
/**
 * Asana Interface class
 *
 * Loads the Asana API client and translates common interactions between Asana
 * and WordPress.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once 'class-options.php';
require_once 'errors.php';
require_once 'class-html-builder.php';

if ( ! class_exists( __NAMESPACE__ . '\Asana_Interface' ) ) {
	/**
	 * A static class that translates interactions between the loaded Asana API
	 * instance and WordPress. Note that this is a singleton class to reduce API
	 * calls. Therefore, the only client used by this class is the one
	 * authenticated by the current user. The current WordPress user should never
	 * use the API through someone else's authentication!
	 */
	class Asana_Interface {

		/**
		 * The ?opt_fields csv for Asana API requests.
		 *
		 * @since 3.1.0
		 *
		 * @var string TASK_OPT_FIELDS
		 */
		public const TASK_OPT_FIELDS = 'name,completed,notes,due_on,assignee,workspace,tags';

		/**
		 * The currently authenticated WordPress user's ID.
		 *
		 * @since 1.1.0
		 *
		 * @var int $wp_user_id
		 */
		private static $wp_user_id;

		/**
		 * The authenticated Asana API client object.
		 *
		 * @see get_client() To get the loaded client.
		 *
		 * @since 1.0.0
		 *
		 * @var \Asana\Client $asana
		 */
		private static $asana;

		/**
		 * The currently authenticated Asana API user.
		 *
		 * @since 1.0.0
		 *
		 * @var \stdClass $me
		 */
		private static $me;

		/**
		 * Gets the authenticated Asana API client.
		 *
		 * @since 1.1.0 Added optional parameter $user_id.
		 * @since 1.0.0
		 *
		 * @param string|int $user_id_or_gid Optional. The user to authenticate.
		 * Pass an Asana user GID string or the WordPress user ID integer.
		 * Default 0 to use the current WordPress user.
		 * @return \Asana\Client The authenticated Asana API client.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function get_client( $user_id_or_gid = 0 ) : \Asana\Client {

			/*
			 * @TODO - This needs to NEVER interpret the user's ID.
			 * Allowing a default value here can confuse frontend requests
			 * where the user ID (anonymous user) is ACTUALLY 0.
			 *
			 * Authentication should NEVER be left to interpretation.
			 * Always be explicit when loading the current Asana client.
			 */

			if ( is_string( $user_id_or_gid ) ) {
				$user_id = self::get_user_id_by_gid( $user_id_or_gid );
			} elseif ( is_int( $user_id_or_gid ) ) {
				$user_id = $user_id_or_gid;
			} else {
				throw new \Exception( 'Failed to get Asana client for invalid user identifier. Must be string for Asana PAT or integer for WordPress User ID.', 400 );
			}

			if ( 0 === $user_id ) {
				$user_id = get_current_user_id();
			}

			if (
				! isset( self::$asana )
				|| ! isset( self::$wp_user_id )
				|| self::$wp_user_id !== $user_id
			) {
				self::$asana = self::maybe_load_client( $user_id );
			}

			return self::$asana;
		}

		/**
		 * Gets the currently authenticated Asana API user. Using this function
		 * instead of the \Asana\Client\Users::me() will help reduce API calls.
		 *
		 * @since 1.0.0
		 *
		 * @return \Asana\Client The authenticated Asana API client.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function get_me() : \stdClass {

			if ( ! isset( self::$me ) || ! is_a( self::$me, '\stdClass' ) ) {
				self::$me = self::get_client()->users->me();
			}

			if ( isset( self::$me ) && is_a( self::$me, '\stdClass' ) ) {
				return self::$me;
			}

			throw new \Exception( 'Could not retrieve the current Asana user\'s identity. The Asana API may be experiencing issues, see <a href="https://status.asana.com/" target="_blank">https://status.asana.com/</a>', 500 );
		}

		/**
		 * Loads an authenticated Asana API client.
		 *
		 * @see get_client() To get the loaded client.
		 *
		 * @since 1.1.0 Added optional parameter $user_id.
		 * @since 1.0.0
		 *
		 * @param int $user_id Optional. The WordPress user to authenticate. Default
		 * 0 to use the current user.
		 * @return \Asana\Client The authenticated Asana API client.
		 *
		 * @throws \PTC_Completionist\Errors\NoAuthorization If the user does not
		 * have a valid Asana PAT saved.
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		private static function maybe_load_client( int $user_id = 0 ) : \Asana\Client {

			error_log( "Maybe loading client for user ID: {$user_id}" );
			error_log( 'Currently loaded client is for user ID: ' . self::$wp_user_id );

			if ( 0 === $user_id ) {
				$user_id = get_current_user_id();
			}

			$asana_personal_access_token = Options::get( Options::ASANA_PAT, $user_id );
			if (
				false === $asana_personal_access_token
				|| '' === $asana_personal_access_token
			) {
				throw new Errors\NoAuthorization( 'No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.', 401 );
			} else {
				self::$wp_user_id = $user_id;
			}

			require_once PLUGIN_PATH . '/vendor/autoload.php';
			$asana = \Asana\Client::accessToken(
				$asana_personal_access_token,
				[
					'headers' => [
						'asana-enable' => 'new_user_task_lists,new_project_templates',
					],
				]
			);

			try {
				self::$me = $asana->users->me();
			} catch ( \Asana\Errors\NoAuthorizationError $e ) {
				Options::delete( Options::ASANA_PAT );
				throw new Errors\NoAuthorization( 'Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.', $e->getCode() );
			} catch ( \Exception $e ) {
				/* Don't delete option here because could be server error or API limit... */
				$error_code = esc_html( $e->getCode() );
				$error_msg = esc_html( $e->getMessage() );
				throw new \Exception( "Asana authorization failure {$error_code}: {$error_msg}", $e->getCode() );
			}

			return $asana;
		}

		/**
		 * Determines if the current Asana user is a member of a workspace.
		 *
		 * @since 1.0.0
		 *
		 * @param string $workspace_gid Optional. The gid of the workspace. Default
		 * '' to use the chosen workspace.
		 * @return bool If the current Asana user is a member of the workspace.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function is_workspace_member( string $workspace_gid = '' ) : bool {

			if ( '' === $workspace_gid ) {
				$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
				if ( empty( $workspace_gid ) ) {
					return false;
				}
			}

			$me = self::get_me();

			foreach ( $me->workspaces as $workspace ) {
				if ( $workspace->gid === $workspace_gid ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Gets WordPress users that match Asana users by email.
		 *
		 * @since 1.0.0
		 *
		 * @param string $workspace_gid Optional. The gid of the workspace to get
		 * Asana users to match by email. Default '' to use the chosen workspace.
		 *
		 * @return \WP_User[] The matching WordPress users keyed by their Asana gid.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function find_workspace_users( string $workspace_gid = '' ) : array {

			if ( '' === $workspace_gid ) {
				$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
			} else {
				$workspace_gid = Options::sanitize( 'gid', $workspace_gid );
			}

			if ( '' === $workspace_gid ) {
				return [];
			}

			$params = [ 'opt_fields' => 'email' ];
			$asana_users = self::get_client()->users->findByWorkspace( $workspace_gid, $params );

			$wp_users = [];

			foreach ( $asana_users as $user ) {
				$wp_user = get_user_by( 'email', $user->email );
				if (
					false !== $wp_user
					&& $wp_user instanceof \WP_User
				) {
					$wp_users[ $user->gid ] = $wp_user;
				}
			}

			return $wp_users;

		}

		/**
		 * Gets WordPress users that match Asana users by email.
		 *
		 * @since 1.0.0
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
		 * @see find_workspace_users() For how users are selected.
		 *
		 * @since 1.1.0
		 *
		 * @param string $workspace_gid Optional. The gid of the workspace to get
		 * Asana users to match by email. Default '' to use the chosen workspace.
		 * @return string[] Strings of WordPress user display names and emails keyed
		 * by their Asana gid.
		 */
		public static function get_workspace_user_options( string $workspace_gid = '' ) : array {

			$wp_users = [];

			try {
				$wp_users = self::find_workspace_users( $workspace_gid );
				foreach ( $wp_users as $gid => $wp_user ) {
					$wp_users[ $gid ] = "{$wp_user->display_name} ({$wp_user->user_email})";
				}
				$wp_users += self::get_connected_workspace_user_options( $workspace_gid );
			} catch ( \Exception $e ) {
				error_log( HTML_Builder::format_error_string( $e, 'Failed to get_workspace_user_options().' ) );
				$wp_users = [ 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) ];
			}

			return $wp_users;
		}

		/**
		 * Gets an array of WordPress user display names and emails keyed by their
		 * Asana gid.
		 *
		 * @see get_connected_workspace_users() For how users are selected.
		 *
		 * @since 1.1.0
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
		 * @since 1.1.0
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
		 * @since 1.0.0
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
		 * Tests if a WordPress user has successfully connected their Asana account.
		 *
		 * @since 1.0.0
		 *
		 * @param int $user_id Optional. The WordPress user's ID. Default 0 to use
		 * the currently loaded user's ID. If a user has not yet been loaded, the
		 * current WordPress user's ID will be used.
		 * @return bool If the user is successfully authorized to use Asana. Note
		 * that any API errors will cause false to be returned.
		 */
		public static function has_connected_asana( int $user_id = 0 ) : bool {

			if (
				0 === $user_id
				&& isset( self::$wp_user_id )
				&& $user_id !== self::$wp_user_id
			) {
				$user_id = self::$wp_user_id;
			}

			$asana_personal_access_token = Options::get( Options::ASANA_PAT, $user_id );
			if (
				false === $asana_personal_access_token
				|| '' === $asana_personal_access_token
			) {
				return false;
			}

			require_once PLUGIN_PATH . '/vendor/autoload.php';
			$asana = \Asana\Client::accessToken( $asana_personal_access_token );

			try {
				$asana->users->me();
			} catch ( \Exception $e ) {
				return false;
			}

			return true;
		}

		/**
		 * Gets the external link to a user's task list in Asana for the chosen
		 * workspace.
		 *
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * Parses project view information from an Asana project link.
		 *
		 * @since [unreleased]
		 */
		public static function parse_project_link( string $project_link ) {

			$parsed_project_data = array();

			$project_link = esc_url_raw( $project_link );

			if ( preg_match( '/\/([0-9]+)\/([a-z]+)$/', $project_link, $matches ) ) {
				// Copied project URL from web browser address bar.
				// ex. https://app.asana.com/0/1234567890/list
				if ( ! empty( $matches[1] ) ) {
					$parsed_project_data['gid'] = Options::sanitize( 'gid', $matches[1] );
				}
				if ( ! empty( $matches[2] ) && 'overview' !== $matches[2] ) {
					$parsed_project_data['layout'] = $matches[2];
				}
			} elseif ( preg_match( '/\/([0-9]+)$/', $project_link, $matches ) ) {
				// Copied project URL from project details dropdown in Asana.
				// ex. https://app.asana.com/0/1234567890/1234567890
				if ( ! empty( $matches[1] ) ) {
					$parsed_project_data['gid'] = Options::sanitize( 'gid', $matches[1] );
				}
			}

			return $parsed_project_data;
		}

		/**
		 * Gets tasks for a given project, organized by project sections.
		 *
		 * @since [unreleased]
		 */
		public static function get_project_data(
			string $project_gid,
			array $args = array()
		) {

			$args = wp_parse_args(
				array_map( 'rest_sanitize_boolean', $args ),
				array(
					'show_gids'              => true,
					'show_name'              => true,
					'show_description'       => true,
					'show_status'            => true,
					'show_modified'          => true,
					'show_due'               => true,
					'show_tasks_description' => true,
					'show_tasks_assignee'    => true,
					'show_tasks_subtasks'    => true,
					'show_tasks_completed'   => true,
					'show_tasks_due'         => true,
				)
			);

			$project_gid = Options::sanitize( 'gid', $project_gid );
			if ( '' == $project_gid ) {
				return array();
			}

			if ( ! isset( self::$asana ) ) {
				$asana = self::get_client();
			} else {
				$asana = self::$asana;
			}

			// Get project data.

			$project_fields = 'sections,this.sections.name';

			if ( $args['show_name'] ) {
				$project_fields .= ',name';
			}
			if ( $args['show_description'] ) {
				$project_fields .= ',html_notes';
			}
			if ( $args['show_status'] ) {
				$project_fields .= ',completed,completed_at,current_status,this.current_status.created_at,this.current_status.html_text,this.current_status.title,this.current_status.color';
			}
			if ( $args['show_modified'] ) {
				$project_fields .= ',modified_at';
			}
			if ( $args['show_due'] ) {
				$project_fields .= ',due_on';
			}

			$project = $asana->projects->getProject(
				$project_gid,
				array(),
				array(
					'fields' => $project_fields,
				)
			);

			// Clean project data.
			if ( isset( $project->html_notes ) ) {
				$project->html_notes = wp_kses_post( $project->html_notes );
			}

			// Map section GIDs to section indices.
			$sections_map = array();
			foreach ( $project->sections as $i => &$section ) {
				$sections_map[ $section->gid ] = $i;
			}

			// Get project tasks data.

			$task_fields = 'name';

			if ( $args['show_tasks_description'] ) {
				$task_fields .= ',html_notes';
			}
			if ( $args['show_tasks_assignee'] ) {
				$task_fields .= ',assignee,this.assignee.name,this.assignee.photo.image_27x27';
			}
			if ( $args['show_tasks_completed'] ) {
				$task_fields .= ',completed';
			}
			if ( $args['show_tasks_due'] ) {
				$task_fields .= ',due_on';
			}

			$tasks = $asana->tasks->getTasksForProject(
				$project_gid,
				array(),
				array(
					'fields' => "{$task_fields},memberships,this.memberships.section",
					'limit' => 100,
				)
			);

			$tasks = iterator_to_array( $tasks );

			if ( $args['show_tasks_subtasks'] ) {
				self::load_subtasks( $tasks, $task_fields );
			}

			// Map tasks to sections and clean data.
			foreach ( $tasks as &$task ) {
				foreach ( $task->memberships as &$membership ) {
					if ( isset( $sections_map[ $membership->section->gid ] ) ) {

						if ( isset( $task->html_notes ) ) {
							$task->html_notes = wp_kses_post( $task->html_notes );
						}

						if ( isset( $task->subtasks ) ) {
							foreach ( $task->subtasks as &$subtask ) {
								if ( isset( $subtask->html_notes ) ) {
									$subtask->html_notes = wp_kses_post( $subtask->html_notes );
								}
							}
						}

						$task_clone = clone $task;
						unset( $task_clone->memberships );

						$project->sections[ $sections_map[ $membership->section->gid ] ]->tasks[] = $task_clone;
					}
				}
			}

			// Remove all GIDs if desired.
			if ( ! $args['show_gids'] ) {
				unset( $project->gid );
				foreach ( $project->sections as &$section ) {
					unset( $section->gid );
					foreach ( $section->tasks as &$task ) {
						unset( $task->gid );
						if ( isset( $task->assignee ) ) {
							unset( $task->assignee->gid );
						}
						if ( isset( $task->subtasks ) ) {
							foreach ( $task->subtasks as &$subtask ) {
								unset( $subtask->gid );
								if ( isset( $subtask->assignee ) ) {
									unset( $subtask->assignee->gid );
								}
							}
						}
					}
				}
			}

			return $project;
		}

		/**
		 * Loads subtask records onto each parent task.
		 *
		 * @since [unreleased]
		 *
		 * @param \stdClass[] $parent_tasks The tasks for which to get subtasks.
		 *
		 * @param string $opt_fields Optional. A csv of task fields to retrieve.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		static function load_subtasks( array &$parent_tasks, string $opt_fields = '' ) {

			if ( ! isset( self::$asana ) ) {
				$asana = self::get_client();
			} else {
				$asana = self::$asana;
			}

			$opt_fields = explode( ',', $opt_fields );

			$actions = [];
			$last = count( $parent_tasks ) - 1;
			foreach ( $parent_tasks as $i => &$task ) {

				if ( ! isset( $task->gid ) ) { continue; }

				$task_gid = Options::sanitize( 'gid', $task->gid );
				if ( '' == $task_gid ) { continue; }

				$actions[] = [
					'method' => 'GET',
					'relative_path' => sprintf( '/tasks/%s/subtasks', $task_gid ),
					'options' => [
						'fields' => $opt_fields,
					],
				];

				$actions_count = count( $actions );
				if ( ( $actions_count % 9 === 0 || $i == $last ) && $actions_count > 0 ) {

					$res = $asana->post( '/batch', [ 'actions' => $actions ] );
					$actions = [];

					$last_res_i = count( $res ) - 1;
					for (
						$parent_i = $i + 1 - $actions_count, $res_i = 0;
						$parent_i <= $i, $res_i <= $last_res_i;
						++$parent_i, ++$res_i
					) {

						$parent_tasks[ $parent_i ]->subtasks = [];

						$current_res = $res[ $res_i ];
						if ( 200 == $current_res->status_code && count( $current_res->body->data ) > 0 ) {
							$parent_tasks[ $parent_i ]->subtasks = $current_res->body->data;
						}
					}//end foreach batch result
				}//end if batch ready to send
			}//end foreach parent task
		}//end get_subtasks()

		/**
		 * Attempts to retrieve task data. Providing the post id of the provided
		 * pinned task gid will also attempt data self-healing.
		 *
		 * @since 3.1.0 Marked $opt_fields param as deprecated.
		 * @since 1.0.0
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

				$task->action_link = HTML_Builder::get_task_action_link( $task->gid );

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
		 * @since 1.0.0
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
		 * @since 3.1.0 Marked $opt_fields param as deprecated.
		 * @since 1.0.0
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
		 * @since 3.1.0 Marked $opt_fields param as deprecated.
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * @since 1.0.0
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
		 * @since 1.0.0
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

		/**
		 * Deletes all task pins except for those in the provided array.
		 *
		 * @since 1.0.0
		 *
		 * @param \stdClass[] $keep_tasks An array of task objects with the "gid"
		 * string member set to not be deleted.
		 * @return int The number of deleted task pins.
		 */
		public static function delete_pinned_tasks_except( array $keep_tasks ) : int {

			if ( empty( $keep_tasks ) ) {
				return 0;
			}

			$keep_gids = [];
			foreach ( $keep_tasks as $task ) {
				if ( isset( $task->gid ) ) {
					$sanitized_gid = Options::sanitize( 'gid', $task->gid );
					if ( $sanitized_gid === $task->gid ) {
						$keep_gids[] = $task->gid;
					} else {
						return 0;
					}
				} else {
					return 0;
				}
			}

			$meta_key = Options::get( Options::ASANA_TAG_GID );
			if ( '' === $meta_key ) {
				return 0;
			}

			global $wpdb;
			$format_vars[] = $meta_key;

			if ( empty( $keep_gids ) ) {
				$sql = "
								DELETE FROM {$wpdb->postmeta}
								WHERE meta_key = %s
							 ";
			} else {
				$sql = "
								DELETE FROM {$wpdb->postmeta}
								WHERE meta_key = %s
									AND meta_value NOT IN(
							 ";

				$last_task_index = count( $keep_gids ) - 1;
				foreach ( $keep_gids as $i => $gid ) {
					if ( $i < $last_task_index ) {
						$sql .= '%s,';
					} else {
						$sql .= '%s)';
					}
					$format_vars[] = $gid;
				}
			}//end if empty keep_gids

			$res = $wpdb->query( $wpdb->prepare( $sql, $format_vars ) );

			if ( is_numeric( $res ) && $res > 0 ) {
				error_log( "Deleted {$unpinned_count} task pins: " . __FUNCTION__ );
			}

			return (int) $res;
		}

		/**
		 * Deletes a task in Asana.
		 *
		 * @since 3.1.0
		 *
		 * @param string $task_gid The task gid to delete.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function delete_task( string $task_gid ) {
			// Request the deletion in Asana.
			$asana = self::get_client();
			$asana->tasks->delete( $task_gid );
			// @TODO - Update the cache.
		}

		/**
		 * Creates a task in Asana and optionally pins it to a WordPress post.
		 *
		 * @since 1.1.0
		 *
		 * @param array $args The unsanitized task params. Accepted keys are:
		 * * 'name' => (string) Required. The task title.
		 * * 'post_id' => (int) The WordPress post ID on which to pin the new task.
		 * * 'assignee' => (gid) The assignee's Asana gid.
		 * * 'due_on' => (date string) The task due date.
		 * * 'project' => (gid) The Asana project gid to house the task.
		 * * 'notes' => (string) The task description.
		 * @param string|int $user_id_or_gid Optional. The task author's Asana user
		 * GID string or WordPress user ID integer. Default 0 to use the current
		 * WordPress user.
		 * @return \stdClass The created task object response from Asana.
		 *
		 * @throws \Exception Authentication may fail when first loading the client
		 * or requests could fail due to request limits or server issues.
		 */
		public static function create_task( array $args, $user_id_or_gid = 0 ) : \stdClass {

			if ( ! isset( $args['name'] ) ) {
				throw new \Exception( 'A task name is required.', 409 );
			}

			$asana = self::get_client( $user_id_or_gid );

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

			$task = $asana->tasks->create( $params );

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

			$task->action_link = HTML_Builder::get_task_action_link( $task->gid );

			return $task;
		}//end create_task()
	}//end class
}//end if class_exists
