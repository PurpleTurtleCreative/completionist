<?php
/**
 * Asana Interface class
 *
 * Loads the Asana API client and translates common interactions
 * between Asana and WordPress.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

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
	public const TASK_OPT_FIELDS = 'name,completed,notes,due_on,assignee,assignee.name,workspace,tags';

	/**
	 * The $options array for \Asana\Client instantiation.
	 *
	 * @since 3.5.2
	 *
	 * @var array ASANA_CLIENT_OPTIONS
	 */
	public const ASANA_CLIENT_OPTIONS = array(
		'headers' => array(
			'asana-enable' => 'new_user_task_lists,new_project_templates,new_memberships,new_goal_memberships',
		),
	);

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
	 * Gets the currently authenticated WordPress user's ID.
	 *
	 * @since 3.9.0
	 *
	 * @return int The WordPress user ID.
	 */
	public static function get_wp_user_id() : int {
		return self::$wp_user_id;
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
	 * @throws \PTC_Completionist\Errors\No_Authorization If the user does not
	 * have a valid Asana PAT saved.
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	private static function maybe_load_client( int $user_id = 0 ) : \Asana\Client {

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$asana_personal_access_token = Options::get( Options::ASANA_PAT, $user_id );
		if (
			false === $asana_personal_access_token
			|| '' === $asana_personal_access_token
		) {
			throw new Errors\No_Authorization( 'No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.', 401 );
		} else {
			self::$wp_user_id = $user_id;
		}

		$asana = \Asana\Client::accessToken(
			$asana_personal_access_token,
			self::ASANA_CLIENT_OPTIONS
		);

		try {
			self::$me = $asana->users->me();
		} catch ( \Asana\Errors\NoAuthorizationError $e ) {
			Options::delete( Options::ASANA_PAT );
			throw new Errors\No_Authorization( 'Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.', intval( $e->getCode() ) );
		} catch ( \Exception $e ) {
			/* Don't delete option here because could be server error or API limit... */
			throw new \Exception( 'Asana authorization failure ' . esc_html( $e->getCode() ) . ': ' . esc_html( $e->getMessage() ), intval( $e->getCode() ) );
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
			return array();
		}

		$params = array( 'opt_fields' => 'email' );
		$asana_users = self::get_client()->users->findByWorkspace( $workspace_gid, $params );

		$wp_users = array();

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
			return array();
		}

		$users_with_pat = get_users( array( 'meta_key' => Options::ASANA_PAT ) );
		$wp_users = array();

		foreach ( $users_with_pat as $wp_user ) {

			$asana_personal_access_token = Options::get( Options::ASANA_PAT, $wp_user->ID );
			if (
				false === $asana_personal_access_token
				|| '' === $asana_personal_access_token
			) {
				continue;
			}

			$asana = \Asana\Client::accessToken(
				$asana_personal_access_token,
				self::ASANA_CLIENT_OPTIONS
			);

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

		$wp_users = array();

		try {
			$wp_users = self::find_workspace_users( $workspace_gid );
			foreach ( $wp_users as $gid => $wp_user ) {
				$wp_users[ $gid ] = "{$wp_user->display_name} ({$wp_user->user_email})";
			}
			$wp_users += self::get_connected_workspace_user_options( $workspace_gid );
		} catch ( \Exception $e ) {
			error_log( HTML_Builder::format_error_string( $e, 'Failed to get_workspace_user_options().' ) );
			$wp_users = array( 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) );
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

		$wp_users = array();

		try {
			$wp_users = self::get_connected_workspace_users( $workspace_gid );
			foreach ( $wp_users as $gid => $wp_user ) {
				$wp_users[ $gid ] = "{$wp_user->display_name} ({$wp_user->user_email})";
			}
		} catch ( \Exception $e ) {
			error_log( HTML_Builder::format_error_string( $e, 'Failed to get_connected_workspace_user_options().' ) );
			$wp_users = array( 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) );
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

		$project_options = array();

		try {
			$params = array(
				'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
				'archived' => false,
				'opt_fields' => 'gid,name',
			);
			$projects = self::get_client()->projects->findAll( $params );
			foreach ( $projects as $project ) {
				$project_options[ $project->gid ] = $project->name;
			}
		} catch ( \Exception $e ) {
			error_log( HTML_Builder::format_error_string( $e, 'Failed to get_workspace_project_options().' ) );
			$project_options = array( 'error' => 'ERROR ' . HTML_Builder::get_error_code( $e ) );
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

		$query_args = array(
			'meta_key'   => Options::ASANA_USER_GID,
			'meta_value' => $user_gid,
			'fields'     => 'ID',
		);

		$user_ids = get_users( $query_args );

		if ( isset( $user_ids[0] ) && $user_ids[0] > 0 ) {
			// Multiple WordPress users may be using the same
			// Asana user GID, but just use the first result.
			// This is a common use case to limit an agency's seats
			// in a client's Asana workspace, while still tracking
			// agency workers separately in WordPress.
			return (int) $user_ids[0];
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

		try {
			$asana = \Asana\Client::accessToken(
				$asana_personal_access_token,
				self::ASANA_CLIENT_OPTIONS
			);
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
			$params = array(
				'workspace' => $workspace_gid,
				'opt_fields' => 'gid',
			);

			$user_task_list = $asana->usertasklists->findByUser( $user_gid, $params );
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
			&& ! empty( $matches[1] )
		) {
			return Options::sanitize( 'gid', $matches[1] );
		}

		return '';
	}

	/**
	 * Parses project view information from an Asana project link.
	 *
	 * @since 3.4.0
	 *
	 * @param string $project_link An Asana project URL.
	 * @return array The parsed project GID and layout, if possible.
	 */
	public static function parse_project_link( string $project_link ) : array {

		$parsed_project_data = array();

		$project_link = esc_url_raw( $project_link );

		if ( preg_match( '/\/([0-9]+)\/([a-z]+)$/', $project_link, $matches ) ) {
			/*
			 * Copied project URL from web browser address bar.
			 * ex. https://app.asana.com/0/1234567890/list
			 */
			if ( ! empty( $matches[1] ) ) {
				$parsed_project_data['gid'] = Options::sanitize( 'gid', $matches[1] );
			}
			if ( ! empty( $matches[2] ) && 'overview' !== $matches[2] ) {
				$parsed_project_data['layout'] = $matches[2];
			}
		} elseif ( preg_match( '/\/([0-9]+)\/[0-9]+$/', $project_link, $matches ) ) {
			/*
			 * Copied project URL from project details dropdown in Asana.
			 * ex. https://app.asana.com/0/1234567890/1234567890
				 *
				 * Or new project URL from the web browser address bar
				 * with a project view GID.
				 * ex. https://app.asana.com/0/1234567890/2345678901
			 */
			if ( ! empty( $matches[1] ) ) {
				$parsed_project_data['gid'] = Options::sanitize( 'gid', $matches[1] );
			}
		}

		return $parsed_project_data;
	}

	/**
	 * Gets status, sections, tasks, and metadata for a given project.
	 *
	 * @since 3.4.0
	 *
	 * @param string $project_gid The Asana project GID.
	 * @param array  $args Optional. Arguments to modify the request and
	 * resulting response data. Default empty to return all data.
	 * @return \stdClass The Asana project data.
	 */
	public static function get_project_data(
		string $project_gid,
		array $args = array()
	) : \stdClass {

		// Check project GID.
		$project_gid = Options::sanitize( 'gid', $project_gid );
		if ( '' == $project_gid ) {
			// Invalid project GID.
			return new \stdClass();
		}

		// Load Asana client.
		$asana = null;
		if ( ! isset( self::$asana ) ) {
			// Might throw exception.
			$asana = self::get_client();
		} else {
			$asana = self::$asana;
		}

		/**
		 * Filters the default arguments for retrieving Asana
		 * project data.
		 *
		 * @since 3.9.0
		 *
		 * @param array $default_args The default argument values.
		 */
		$default_args = apply_filters(
			'ptc_completionist_project_default_args',
			array(
				'include_sections'       => '',
				'exclude_sections'       => '',
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
				'show_tasks_attachments' => true,
				'show_tasks_tags'        => true,
				'sort_tasks_by'          => '',
			)
		);

		// Sanitize provided args.
		foreach ( $args as $key => &$value ) {
			if ( isset( $default_args[ $key ] ) ) {
				if ( is_bool( $default_args[ $key ] ) ) {
					$value = (bool) rest_sanitize_boolean( $value );
				} else {
					$value = (string) sanitize_text_field( $value );
				}
			}
		}

		// Merge default args.
		$args = array_merge( $default_args, $args );

		// Start request token buffering.
		Request_Token::buffer_start();

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

		// Prepare project data.

		if ( isset( $project->html_notes ) ) {
			$project->html_notes = wpautop( wp_kses_post( $project->html_notes ) );
		}

		if ( isset( $project->current_status->html_text ) ) {
			$project->current_status->html_text = wpautop( wp_kses_post( $project->current_status->html_text ) );
		}

		if ( isset( $project->current_status->color ) ) {
			// Add status color labels, as seen in Asana's UI.
			switch ( $project->current_status->color ) {
				case 'green':
					$project->current_status->color_label = 'On track';
					break;
				case 'yellow':
					$project->current_status->color_label = 'At risk';
					break;
				case 'red':
					$project->current_status->color_label = 'Off track';
					break;
				case 'blue':
					$project->current_status->color_label = 'On hold';
					break;
				case 'complete':
					$project->current_status->color_label = 'Complete';
					break;
			}
		}

		// Map section GIDs to section indices.

		$sections_map = array();

		/**
		 * Filters the project section names to be erased when retrieving
		 * Asana project data.
		 *
		 * Note that this only erases the section's name rather than
		 * remove the entire project section's data.
		 *
		 * @since 3.4.0
		 *
		 * @param string[] $names Project section names to erase.
		 * @param string   $project_gid The Asana project being processed.
		 * @param array    $args The request arguments.
		 */
		$erase_section_names = apply_filters(
			'ptc_completionist_project_section_names_to_erase',
			array(
				'(no section)',
				'untitled section',
				'Untitled section',
				'Untitled Section',
			),
			$project_gid,
			$args
		);

		// Parse included project section names.
		if ( ! empty( $args['include_sections'] ) ) {
			$include_section_names = explode( ',', $args['include_sections'] );
			if (
				! empty( $include_section_names ) &&
				is_array( $include_section_names )
			) {
				$include_section_names = array_map( 'trim', $include_section_names );
				$keep_sections         = array();
				foreach ( $project->sections as $i => &$section ) {
					if ( in_array( trim( $section->name ), $include_section_names, true ) ) {
						// Keep section if name is in include list.
						$keep_sections[] = $section;
					}
				}
				$project->sections = $keep_sections;
			}
		}

		// Parse excluded project section names.
		if ( ! empty( $args['exclude_sections'] ) ) {
			$exclude_section_names = explode( ',', $args['exclude_sections'] );
			if (
				! empty( $exclude_section_names ) &&
				is_array( $exclude_section_names )
			) {
				$exclude_section_names = array_map( 'trim', $exclude_section_names );
				$keep_sections         = array();
				foreach ( $project->sections as $i => &$section ) {
					if ( ! in_array( trim( $section->name ), $exclude_section_names, true ) ) {
						// Keep section if name is not in exclude list.
						$keep_sections[] = $section;
					}
				}
				$project->sections = $keep_sections;
			}
		}

		// Map project section GIDs to their index.
		foreach ( $project->sections as $i => &$section ) {
			if ( true === in_array( $section->name, $erase_section_names, true ) ) {
				// Remove Asana default title for a nameless section.
				$section->name = null;
			}
			$sections_map[ $section->gid ] = $i;
		}

		// Check if there are any project sections.
		if ( empty( $project->sections ) || empty( $sections_map ) ) {
			unset( $project->sections );
		} else {

			// Get tasks for each project section.

			$task_fields = 'name';
			$task_request_params = array();

			/*
			 * Note that completed tasks are not returned when
			 * ?completed_since=now
			 *
			 * @see https://developers.asana.com/reference/gettasksforproject
			 */
			if ( $args['show_tasks_completed'] ) {
				// Show whether a task is completed or not.
				$task_fields .= ',completed';
			} else {
				// Exclude all completed tasks.
				$task_request_params['completed_since'] = 'now';
			}

			if ( $args['show_tasks_description'] ) {
				$task_fields .= ',html_notes';
			}
			if ( $args['show_tasks_assignee'] ) {
				$task_fields .= ',assignee,assignee.name,assignee.photo.image_36x36';
			}
			if ( $args['show_tasks_due'] ) {
				$task_fields .= ',due_on';
			}
			if ( $args['show_tasks_attachments'] ) {
				$task_fields .= ',attachments.name,attachments.host,attachments.download_url,attachments.view_url';
			}
			if ( $args['show_tasks_tags'] ) {
				$task_fields .= ',tags,tags.name,tags.color';
			}

			/**
			 * Filters the task fields that will be retrieved for
			 * each task in an Asana project.
			 *
			 * @link https://developers.asana.com/reference/gettask To see
			 * all available task fields available.
			 *
			 * @since 3.11.0
			 *
			 * @param string $task_fields Task fields to retrieve.
			 * @param string $project_gid The Asana project being processed.
			 * @param array  $args The request arguments.
			 */
			$task_fields = apply_filters(
				'ptc_completionist_project_task_fields',
				$task_fields,
				$project_gid,
				$args
			);

			$do_remove_tasks_sort_field = false;
			if (
				$args['sort_tasks_by'] &&
				false === in_array(
					$args['sort_tasks_by'],
					explode( ',', $task_fields )
				)
			) {
				// Ensure sorting field is returned.
				// Always add "name" subfield in case its an object like "assignee".
				$task_fields .= ",{$args['sort_tasks_by']},{$args['sort_tasks_by']}.name";
				$do_remove_tasks_sort_field = true;
			}

			$tasks = $asana->tasks->getTasksForProject(
				$project_gid,
				$task_request_params,
				array(
					'fields' => "{$task_fields},memberships,memberships.section",
					'limit'  => 100,
				)
			);

			$tasks = iterator_to_array( $tasks );

			if ( $args['show_tasks_subtasks'] ) {
				$subtask_fields = $task_fields;
				if ( ! $args['show_tasks_completed'] ) {
					// Loading subtasks doesn't support requesting
					// incomplete tasks only, so must request the
					// 'completed' field for filtering later.
					$subtask_fields .= ',completed';
				}
				self::load_subtasks( $tasks, $subtask_fields );
			}

			if ( $args['sort_tasks_by'] ) {
				static::sort_tasks_by( $tasks, $args['sort_tasks_by'] );
			}

			// Clean data and map tasks to project sections.

			foreach ( $tasks as &$task ) {
				foreach ( $task->memberships as &$membership ) {
					if ( isset( $sections_map[ $membership->section->gid ] ) ) {

						// Don't recursively localize tasks since some
						// subtasks might end up being removed.
						static::localize_task( $task, false );

						// Process subtasks.
						if ( isset( $task->subtasks ) ) {

							foreach ( $task->subtasks as $subtasks_i => &$subtask ) {

								if ( isset( $subtask->completed ) ) {
									if ( ! $args['show_tasks_completed'] ) {
										if ( $subtask->completed ) {
											// Don't show completed tasks.
											unset( $task->subtasks[ $subtasks_i ] );
											continue;
										} else {
											// Don't show completed status
											// for incomplete tasks.
											unset( $subtask->completed );
										}
									}
								}

								// Now recursively localize tasks since
								// no further subtasks will be removed.
								//
								// Though note that recursion isn't actually
								// needed here since only one level of subtasks
								// was loaded, anyways.
								static::localize_task( $subtask, true );
							}//end foreach.

							// Fix index gaps from possible removals.
							if ( ! $args['show_tasks_completed'] ) {
								$task->subtasks = array_values( $task->subtasks );
							}

							// Asana doesn't currently sort subtasks when the
							// view's sort is changed, but we will.
							if ( $args['sort_tasks_by'] ) {
								static::sort_tasks_by( $task->subtasks, $args['sort_tasks_by'] );
							}
						}

						// Clone in case the task appears in another membership.
						$task_clone = clone $task;
						// Remove other memberships (projects) data.
						unset( $task_clone->memberships );
						// Organize task into project section.
						$project->sections[ $sections_map[ $membership->section->gid ] ]->tasks[] = $task_clone;
					}
				}
			}

			if (
				$args['sort_tasks_by'] &&
				true === $do_remove_tasks_sort_field
			) {
				// Remove extra field only used for sorting, not for display.
				Util::deep_unset_prop( $project, $args['sort_tasks_by'] );
			}
		}

		// Commit all buffered request tokens.
		Request_Token::buffer_end_flush();

		/**
		 * Filters Asana project data.
		 *
		 * @since 3.9.0
		 *
		 * @param \stdClass     $project The Asana project data.
		 * @param array         $args The request arguments.
		 * @param \Asana\Client $asana The authenticated Asana
		 * client instance.
		 */
		$project = apply_filters(
			'ptc_completionist_project_data',
			$project,
			$args,
			$asana
		);

		// Remove all GIDs if desired.
		if ( ! $args['show_gids'] ) {
			Util::deep_unset_prop( $project, 'gid' );
		}

		return $project;
	}

	/**
	 * Sorts tasks by the given field.
	 *
	 * @since 4.1.0
	 *
	 * @param \stdClass[] $tasks The tasks to be sorted.
	 * @param string      $sort_field The task attribute to sort tasks by.
	 */
	public static function sort_tasks_by( array &$tasks, string $sort_field ) {
		usort(
			$tasks,
			function ( $task1, $task2 ) use ( $sort_field ) {

				// Ensure the specified field exists in both tasks.
				if (
					isset( $task1->{$sort_field} ) &&
					isset( $task2->{$sort_field} )
				) {

					$value1 = &$task1->{$sort_field};
					$value2 = &$task2->{$sort_field};

					if ( is_bool( $value1 ) && is_bool( $value2 ) ) {
						if (
							true === $value1 &&
							true === $value2 &&
							isset( $task1->name ) &&
							isset( $task2->name )
						) {
							// If both true, sort alphabetically by task name.
							return strcmp( $task1->name, $task2->name );
						}
						// Sort true values first.
						return $value2 - $value1;
					} elseif ( is_object( $value1 ) && is_object( $value2 ) ) {
						if (
							isset( $value1->name ) &&
							isset( $value2->name )
						) {
							// Sort by the objects' name fields, such as "assignee".
							return strcmp( $value1->name, $value2->name );
						}
						// Don't know how to sort by object.
						return 0;
					} elseif ( is_numeric( $value1 ) && is_numeric( $value2 ) ) {
						return $value1 - $value2; // Numeric comparison.
					} elseif ( is_string( $value1 ) && is_string( $value2 ) ) {
						return strcmp( $value1, $value2 ); // String comparison.
					} else {
						return 0; // Not sure how to sort.
					}
				} elseif (
					isset( $task1->{$sort_field} ) &&
					! isset( $task2->{$sort_field} )
				) {
					// If the first task has the field, then put it first.
					return -1;
				} elseif (
					! isset( $task1->{$sort_field} ) &&
					isset( $task2->{$sort_field} )
				) {
					// If the second task has the field, then put it first.
					return 1;
				}

				// No opinion when both tasks are missing the specified field.
				return 0;
			}
		);
	}

	/**
	 * Sanitizes, localizes, and tidies a task object.
	 *
	 * @since 3.7.0
	 *
	 * @param \stdClass $task The task to edit.
	 * @param bool      $recursive Optional. If to recursively edit
	 * all subtasks of the given task. Default true.
	 */
	public static function localize_task(
		\stdClass &$task,
		bool $recursive = true
	) {

		$inline_attachment_urls = array();

		// Process task description.
		if ( isset( $task->html_notes ) ) {
			// Sanitize HTML and format paragraphs.
			//
			// Note that sanitization should occur before adding
			// oEmbed HTML since foreign iframes will be stripped
			// as they could be malicious. WordPress doesn't include
			// <iframe> in their global $allowedposttags for this
			// reason. Adding our own trusted oEmbeds iframes
			// later ensures they are the only iframes present.
			$task->html_notes = wpautop( HTML_Builder::kses_post( $task->html_notes ) );
			// Use local attachment URLs.
			$task->html_notes = HTML_Builder::localize_attachment_urls(
				$task->html_notes,
				-1,
				static::$wp_user_id,
				$inline_attachment_urls
			);
			// Render embedded HTML objects.
			$task->html_notes = HTML_Builder::replace_urls_with_oembeds(
				$task->html_notes,
				$inline_attachment_urls
			);
		}

		// Process attachments.
		if ( isset( $task->attachments ) ) {
			static::localize_attachments(
				$task->attachments,
				$inline_attachment_urls
			);
		}

		// Recursively localize subtasks.
		if ( $recursive && isset( $task->subtasks ) ) {
			foreach ( $task->subtasks as &$subtask ) {
				static::localize_task( $subtask, $recursive );
			}
		}
	}

	/**
	 * Localizes Asana attachments.
	 *
	 * @since 3.9.0
	 *
	 * @param \stdClass[] $attachments The Asana attachment
	 * objects to be localized.
	 * @param string[]    $removal_urls Optional. The URLs of
	 * attachments which should be removed from the collection.
	 * Default empty array to keep all attachments.
	 */
	public static function localize_attachments(
		array &$attachments,
		array $removal_urls = array()
	) {

		$keep_attachments = array();
		foreach ( $attachments as $i => &$attachment ) {

			// Ensure attachment is localized.
			if ( empty( $attachment->_ptc_view_url ) ) {
				$attachment->_ptc_view_url = HTML_Builder::get_local_attachment_view_url(
					$attachment->gid,
					-1,
					static::$wp_user_id
				);
			}

			// Skip if attachment is marked for removal.
			if (
				true === in_array( $attachment->_ptc_view_url, $removal_urls, true ) ||
				true === in_array( $attachment->view_url, $removal_urls, true )
			) {
				continue;
			}

			if (
				isset( $attachment->host ) &&
				'external' === $attachment->host &&
				! empty( $attachment->view_url )
			) {
				// See if we can get the oEmbed HTML to view external media.
				$oembed_html = HTML_Builder::get_oembed_for_url( $attachment->view_url );
				if ( ! empty( $oembed_html ) ) {
					$attachment->_ptc_oembed_html = $oembed_html;
				}
			}

			$keep_attachments[] = $attachment;
		}

		$attachments = $keep_attachments;
	}

	/**
	 * Gets data for a given attachment.
	 *
	 * @since 3.5.0
	 *
	 * @param string $attachment_gid The Asana attachment GID.
	 * @return \stdClass The Asana attachment data.
	 */
	public static function get_attachment_data( string $attachment_gid ) : \stdClass {

		if ( ! isset( self::$asana ) ) {
			$asana = self::get_client();
		} else {
			$asana = self::$asana;
		}

		return $asana->attachments->findById(
			$attachment_gid,
			array(),
			array(
				'fields' => 'name,host,download_url,view_url',
				'limit'  => 100,
			)
		);
	}

	/**
	 * Loads subtask records onto each parent task.
	 *
	 * @since 3.4.0
	 *
	 * @param \stdClass[] $parent_tasks The tasks for which
	 * to get subtasks.
	 * @param string      $opt_fields Optional. A csv of task
	 * fields to retrieve.
	 *
	 * @throws \Exception Authentication may fail when first
	 * loading the client or requests could fail due to
	 * request limits or server issues.
	 */
	public static function load_subtasks(
		array &$parent_tasks,
		string $opt_fields = ''
	) {

		if ( ! isset( self::$asana ) ) {
			$asana = self::get_client();
		} else {
			$asana = self::$asana;
		}

		// Prepare for batch processing.

		$asana_subtasks_batcher = new Asana_Batch(
			$asana,
			function ( &$res, &$task ) {

				$task->subtasks = array();

				if (
					200 === intval( $res->status_code ) &&
					! empty( $res->body->data ) &&
					is_array( $res->body->data )
				) {
					$task->subtasks = $res->body->data;
				}
			},
			function ( $err ) {
				trigger_error(
					'Failed to load subtasks. Error: ' . esc_html( $err->getMessage() ),
					\E_USER_WARNING
				);
			}
		);

		// Prepare and send batch requests.

		$subtask_options = array(
			'limit'  => 100,
			'fields' => explode( ',', $opt_fields ),
		);

		foreach ( $parent_tasks as &$task ) {
			$asana_subtasks_batcher->add_action(
				'GET',
				"/tasks/{$task->gid}/subtasks",
				null,
				$subtask_options,
				array( $task )
			);
		}

		// Process last (incomplete) batch.
		$asana_subtasks_batcher->process();
	}//end load_subtasks()

	/**
	 * Attempts to retrieve task data. Providing the post id of the provided
	 * pinned task gid will also attempt data self-healing.
	 *
	 * @since 4.3.0 Revived $opt_fields param.
	 * @since 3.1.0 Marked $opt_fields param as deprecated.
	 * @since 1.0.0
	 *
	 * @param string $task_gid The gid of the task to retrieve.
	 * @param string $opt_fields Optional. The task fields to be retrieved.
	 * Default '' to use Asana_Interface::TASK_OPT_FIELDS.
	 * @param int    $post_id Optional. The post ID on which
	 * the task belongs to attempt self-healing on certain error
	 * responses. Default 0 to take no action on failure.
	 * @return \stdClass The task data returned from Asana.
	 *
	 * @throws \Exception The Asana client may not be authenticated or the API
	 * request may fail. Additional custom exceptions are:
	 * * 400: Invalid task gid - The provided task gid is invalid.
	 * * 410: Invalid task - The task is no longer available or relevent.
	 */
	public static function maybe_get_task_data( string $task_gid, string $opt_fields = '', int $post_id = 0 ) : \stdClass {

		if ( empty( $opt_fields ) ) {
			$opt_fields = self::TASK_OPT_FIELDS;
		}

		$task_gid = Options::sanitize( 'gid', $task_gid );
		if ( empty( $task_gid ) ) {
			throw new \Exception( 'Invalid task gid', 400 );
		}

		try {

			// Load Asana client.
			$asana = null;
			if ( ! isset( self::$asana ) ) {
				// Might throw exception.
				$asana = self::get_client();
			} else {
				$asana = self::$asana;
			}

			// Fetch the task data.
			$task = $asana->tasks->findById( $task_gid, array( 'opt_fields' => $opt_fields ) );

			if (
				isset( $task->workspace->gid ) &&
				Options::get( Options::ASANA_WORKSPACE_GID ) != $task->workspace->gid &&
				$post_id > 0
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
			$task = $asana->tasks->findById( $task_gid, array( 'opt_fields' => 'workspace' ) );
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

		$tasks = array();

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'Unable to retrieve site tasks when no site tag has been set.', 409 );
		}

		$params = array(
			'opt_fields' => self::TASK_OPT_FIELDS,
		);

		$options = array(
			'page_size' => 100, // Max page_size = 100.
			'item_limit' => 15000,
		);

		/*
		 * An Asana Collection (Iterator) is returned. To actually perform the
		 * API requests to get all the tasks, we must use the Iterator.
		 */
		$site_tasks = $asana->tasks->findByTag( $site_tag_gid, $params, $options );
		$all_tasks = array();
		foreach ( $site_tasks as $task ) {
			$task->action_link = HTML_Builder::get_task_action_link( $task->gid );
			$all_tasks[ $task->gid ] = $task;
		}

		return $all_tasks;
	}

	/**
	 * Sends a batch request to tag and comment on a task in Asana.
	 *
	 * @since 4.0.0 Removed deprecated $opt_fields param.
	 * @since 3.1.0 Marked $opt_fields param as deprecated.
	 * @since 1.0.0
	 *
	 * @param string $task_gid The task to act on.
	 * @param string $tag_gid The tag to add.
	 * @param string $comment The comment text.
	 * @return \stdClass[] An array of response objects. Instance members
	 * include 'body', 'status_code', and 'headers'.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function tag_and_comment( string $task_gid, string $tag_gid, string $comment ) : array {

		$asana = self::get_client();

		$task_gid = Options::sanitize( 'gid', $task_gid );
		if ( ! $task_gid ) {
			throw new \Exception( 'Invalid task GID to tag and comment.', 400 );
		}

		$tag_gid = Options::sanitize( 'gid', $tag_gid );
		if ( ! $tag_gid ) {
			throw new \Exception( 'Invalid tag GID to tag and comment.', 400 );
		}

		$comment = Options::sanitize( 'string', $comment );

		$opt_fields = explode( ',', self::TASK_OPT_FIELDS );

		$data = array(
			'actions' => array(
				array(
					'method'        => 'POST',
					'relative_path' => sprintf( '/tasks/%s/addTag', $task_gid ),
					'data'          => array( 'tag' => $tag_gid ),
					'options'       => array( 'fields' => $opt_fields ),
				),
			),
		);

		if ( $comment ) {
			$data['actions'][] = array(
				'method'        => 'POST',
				'relative_path' => sprintf( '/tasks/%s/stories', $task_gid ),
				'data'          => array( 'text' => $comment ),
				'options'       => array( 'fields' => $opt_fields ),
			);
		}

		return $asana->post( '/batch', $data );
	}

	/**
	 * Sends batch requests to tag multiple tasks.
	 *
	 * @since 1.0.0
	 *
	 * @param string[] $task_gids The tasks to tag.
	 * @param string   $tag_gid The tag.
	 * @return int Count of successfully tagged tasks.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function tag_all( array $task_gids, string $tag_gid ) : int {

		$asana = self::get_client();

		$data = array();
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

			$data['actions'][] = array(
				'method' => 'POST',
				'relative_path' => sprintf( '/tasks/%s/addTag', $task_gid ),
				'data' => array(
					'tag' => $tag_gid,
				),
			);

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

				$data = array();

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
			$data = array();
		}

		return $success_count;
	}

	/**
	 * Determines if a task has a tag.
	 *
	 * @since 1.0.0
	 *
	 * @param \stdClass $task The task object.
	 * @param string    $tag_gid The tag gid.
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
			$tag = $asana->tags->findById( $saved_tag_gid, array( 'opt_fields' => 'workspace' ) );
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

		$arr = array();

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

		$keep_gids = array();
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

		$res = $wpdb->query( $wpdb->prepare( $sql, $format_vars ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( is_numeric( $res ) && $res > 0 ) {
			error_log( "Deleted {$unpinned_count} task pins: " . __FUNCTION__ );
		}

		return (int) $res;
	}

	/**
	 * Deletes a task in Asana.
	 *
	 * @link https://developers.asana.com/reference/deletetask
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
	}

	/**
	 * Creates a task in Asana and optionally pins it to a WordPress post.
	 *
	 * @link https://developers.asana.com/reference/createtask
	 *
	 * @since 4.0.0 Adds a comment on the Asana task if
	 * a valid 'post_id' is provided.
	 * @since 1.1.0
	 *
	 * @param array      $args The unsanitized task params. See
	 * Asana_Interface::prepare_task_args() for supported fields.
	 * @param string|int $user_id_or_gid Optional. The task author's Asana user
	 * GID string or WordPress user ID integer. Default 0 to use the current
	 * WordPress user.
	 * @return \stdClass The created task object response from Asana.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function create_task( array $args, $user_id_or_gid = 0 ) : \stdClass {

		// Always add the site tag.

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'A site tag is required to create a task. Please set a site tag in Completionist\'s settings.', 409 );
		}

		if ( ! isset( $args['tags'] ) ) {
			$args['tags'] = array( $site_tag_gid );
		} else {
			$args['tags'][] = $site_tag_gid;
		}

		// Prepare task fields.

		$params = static::prepare_task_args( $args );

		if ( ! isset( $params['name'] ) ) {
			throw new \Exception( 'A task name is required to create a task.', 409 );
		}

		if ( ! isset( $params['post_id'] ) && isset( $args['post_id'] ) ) {
			throw new \Exception( 'The provided post ID is invalid.', 400 );
		}

		// Create the task.

		$asana = self::get_client( $user_id_or_gid );
		$task  = $asana->tasks->create( $params );
		if ( ! isset( $task->gid ) ) {
			throw new \Exception( 'Unrecognized API response to create task.', 409 );
		}

		$task->action_link = HTML_Builder::get_task_action_link( $task->gid );

		// Pin the task if desired.
		if ( isset( $params['post_id'] ) ) {
			static::pin_task( $task->gid, $params['post_id'] );
		}

		return $task;
	}//end create_task()

	/**
	 * Pins an Asana task to a WordPress post and leaves a comment
	 * if successful.
	 *
	 * @since 4.0.0
	 *
	 * @param string $task_gid The Asana task GID.
	 * @param int    $post_id The WordPress post ID.
	 * @return bool If the task was pinned to the WordPress post.
	 *
	 * @throws \Exception For the following reasons:
	 * - Site tag is not set in the plugin settings.
	 * - The task is already pinned to the post.
	 * - The task does not belong to the site's assigned workspace.
	 */
	public static function pin_task( string $task_gid, int $post_id = -1 ) : bool {

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'A site tag is required to pin tasks. Please set a site tag in Completionist\'s settings.', 409 );
		}

		$params = static::prepare_task_args(
			array(
				'gid'     => $task_gid,
				'post_id' => $post_id,
			)
		);

		if ( ! isset( $params['gid'] ) ) {
			throw new \Exception( 'Invalid task gid.', 400 );
		}

		if ( ! static::is_workspace_task( $params['gid'] ) ) {
			throw new \Exception( 'Task does not belong to this site\'s assigned workspace.', 409 );
		}

		$do_tag_task = false;
		if ( -1 !== $post_id ) {
			// Pin task to specific post.

			if ( ! isset( $params['post_id'] ) ) {
				throw new \Exception( 'Invalid post identifier.', 400 );
			}

			if (
				Options::postmeta_exists(
					Options::PINNED_TASK_GID,
					$params['gid'],
					$params['post_id']
				)
			) {
				throw new \Exception( 'That task is already pinned to the post.', 409 );
			}

			try {
				$did_pin_task = Options::save(
					Options::PINNED_TASK_GID,
					$params['gid'],
					false,
					$params['post_id']
				);
			} catch ( \Exception $e ) {
				$did_pin_task = false;
			}

			if ( $did_pin_task ) {
				$do_tag_task = true;
			}
		} else {
			// Pin task to site, so simply tag it.
			$do_tag_task = true;
		}

		// Add site tag and comment.
		if ( $do_tag_task ) {
			try {

				$referrer = '';
				if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					$referrer = wp_unslash( $_SERVER['HTTP_REFERER'] );
				} else {
					$referrer = get_site_url();
				}

				$comment_text = sprintf(
					'I just pinned this task using Completionist on the "%s" WordPress website, here: %s',
					get_bloginfo( 'name', 'display' ),
					esc_url_raw( $referrer )
				);

				/**
				 * Filters the comment text to add on the Asana task
				 * after it is pinned to a WordPress post.
				 *
				 * @since 4.0.0
				 *
				 * @param string $comment_text The comment text. Return
				 * empty string to not leave a comment.
				 * @param string $task_gid The Asana task GID.
				 * @param int $post_id The WordPress post ID, or null if
				 * pinning to the site generically.
				 */
				$comment_text = (string) apply_filters( 'ptc_completionist_pinned_task_comment', $comment_text, $params['gid'], $params['post_id'] ?? null );

				// @TODO - Check if the task was successfully tagged
				// per the returned batch responses. THEN we actually
				// know that the task was successfully pinned.
				static::tag_and_comment(
					$params['gid'],
					$site_tag_gid,
					$comment_text
				);

				// Successfully pinned the task!
				return true;
			} catch ( \Exception $err ) {
				trigger_error(
					wp_kses_post( HTML_Builder::format_error_string( $err, 'Failed to tag and/or leave pinned task comment.' ) ),
					\E_USER_NOTICE
				);
			}//endcatch
		}

		// Something went wrong.
		return false;
	}

	/**
	 * Unpins an Asana task from a WordPress post or the entire site.
	 *
	 * @since 4.0.0
	 *
	 * @param string $task_gid The Asana task GID.
	 * @param int    $post_id Optional. The WordPress post ID to
	 * unpin the task from. Default -1 to unpin the task from
	 * the entire site.
	 * @return bool If the task was unpinned.
	 *
	 * @throws \Exception Asana API requests may fail or the
	 * provided arguments are invalid.
	 */
	public static function unpin_task( string $task_gid, int $post_id = -1 ) : bool {

		$site_tag_gid = Options::get( Options::ASANA_TAG_GID );
		if ( '' === $site_tag_gid ) {
			throw new \Exception( 'A site tag is required to unpin tasks. Please set a site tag in Completionist\'s settings.', 409 );
		}

		$params = static::prepare_task_args(
			array(
				'gid'     => $task_gid,
				'post_id' => $post_id,
			)
		);

		if ( ! isset( $params['gid'] ) ) {
			throw new \Exception( 'Invalid task gid.', 400 );
		}

		$did_unpin_task = false;
		if ( -1 !== $post_id ) {
			// Unpin task from specific post.

			if ( ! isset( $params['post_id'] ) ) {
				throw new \Exception( 'Invalid post identifier.', 400 );
			}

			if (
				! Options::postmeta_exists(
					Options::PINNED_TASK_GID,
					$params['gid'],
					$params['post_id']
				)
			) {
				throw new \Exception( 'That task is not currently pinned to the post.', 409 );
			}

			try {
				$did_unpin_task = Options::delete(
					Options::PINNED_TASK_GID,
					$params['post_id'],
					$params['gid']
				);
			} catch ( \Exception $e ) {
				$did_unpin_task = false;
			}
		} else {
			// Unpin task from entire site.
			try {
				$did_unpin_task = Options::delete(
					Options::PINNED_TASK_GID,
					-1, // all posts.
					$params['gid']
				);
			} catch ( \Exception $e ) {
				$did_unpin_task = false;
			}
		}

		// Remove site tag if completely unpinned.
		if (
				! Options::postmeta_exists(
					Options::PINNED_TASK_GID,
					$params['gid'],
					-1 // any post.
				)
		) {
			try {
				$asana = static::get_client();
				$asana->tasks->removeTag(
					$params['gid'],
					array( 'tag' => $site_tag_gid )
				);
				// If not pinned to any post and the site tag was
				// successfully removed, then the task is effectively
				// unpinned from the entire site.
				$did_unpin_task = true;
			} catch ( \Exception $err ) {
				throw new \Exception(
					wp_kses_post( HTML_Builder::format_error_string( $err, 'Failed to remove site tag from the unpinned task.' ) ),
					intval( HTML_Builder::get_error_code( $err ) )
				);
			}
		}

		return $did_unpin_task;
	}

	/**
	 * Updates a task in Asana.
	 *
	 * @link https://developers.asana.com/reference/updatetask
	 *
	 * @since 4.0.0
	 *
	 * @param string $task_gid The task GID to update.
	 * @param array  $args The unsanitized task params. See
	 * Asana_Interface::prepare_task_args() for supported fields.
	 * @return \stdClass The complete updated task object.
	 *
	 * @throws \Exception Authentication may fail when first loading the client
	 * or requests could fail due to request limits or server issues.
	 */
	public static function update_task( string $task_gid, array $args ) : \stdClass {

		$params = static::prepare_task_args( $args );

		// Remove allowed task args that don't make sense in this context.
		unset( $params['gid'], $params['post_id'] );

		if ( empty( $params ) ) {
			throw new \Exception( 'No valid parameters were supplied for task update.', 400 );
		}

		$asana = static::get_client();
		$task  = $asana->tasks->update( $task_gid, $params );
		if ( ! isset( $task->gid ) ) {
			throw new \Exception( 'Unrecognized API response to update task.', 409 );
		}

		$task->action_link = HTML_Builder::get_task_action_link( $task->gid );

		return $task;
	}

	/**
	 * Prepares task arguments for Asana requests.
	 *
	 * @link https://developers.asana.com/reference/createtask
	 * Specifies all possible field definitions for reference.
	 * Note that not all fields may be supported by this function.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args An associative array of task fields.
	 * The following Asana task fields are currently supported:
	 * - assignee
	 * - completed
	 * - due_on
	 * - gid
	 * - name
	 * - notes
	 * - projects
	 * - tags
	 * - workspace
	 * The following additional fields are custom supports:
	 * - post_id (int) WordPress post ID to pin the task.
	 * - project (string) A single Asana project GID.
	 * @return array The prepared task arguments.
	 */
	public static function prepare_task_args( array $args ) : array {

		// Select only supported arguments.
		$args = array_intersect_key(
			$args,
			array(
				'assignee'  => true,
				'completed' => true,
				'due_on'    => true,
				'gid'       => true,
				'name'      => true,
				'notes'     => true,
				'post_id'   => true,
				'project'   => true,
				'projects'  => true,
				'tags'      => true,
				'workspace' => true,
			)
		);

		// Used for pinning a task to a WordPress post.
		if ( isset( $args['post_id'] ) ) {
			// Using absint changes the actual value that was provided,
			// such as turning -1 (a common default value) into 1 (a
			// potentially valid post ID which might not have been
			// the intention). So using intval for least transformation.
			$args['post_id'] = intval( $args['post_id'] );
			if ( null === get_post( $args['post_id'] ) ) {
				// Not a valid post ID.
				unset( $args['post_id'] );
			}
		}

		// Basic fields.

		if ( isset( $args['gid'] ) ) {
			$args['gid'] = Options::sanitize( 'gid', $args['gid'] );
		}

		if ( isset( $args['completed'] ) ) {
			$args['completed'] = rest_sanitize_boolean( $args['completed'] );
		}

		if ( isset( $args['name'] ) ) {
			$args['name'] = sanitize_text_field( wp_unslash( $args['name'] ) );
		}

		if ( isset( $args['assignee'] ) ) {
			$args['assignee'] = Options::sanitize( 'gid', $args['assignee'] );
		}

		if ( isset( $args['due_on'] ) ) {
			$args['due_on'] = Options::sanitize( 'date', $args['due_on'] );
		}

		if ( isset( $args['notes'] ) ) {
			$args['notes'] = sanitize_textarea_field( wp_unslash( $args['notes'] ) );
		}

		if ( isset( $args['tags'] ) ) {
			foreach ( $args['tags'] as &$tag_gid ) {
				$tag_gid = Options::sanitize( 'gid', $tag_gid );
			}
			$args['tags'] = array_filter( $args['tags'] );
		}

		if ( isset( $args['workspace'] ) ) {
			$args['workspace'] = Options::sanitize( 'gid', $args['workspace'] );
		}

		// Projects.

		if ( ! isset( $args['projects'] ) ) {
			$args['projects'] = array();
		}

		if ( isset( $args['project'] ) ) {
			// Asana expects an array of project GIDs.
			$args['projects'][] = $args['project'];
			// This isn't a valid argument for Asana, so remove it.
			unset( $args['project'] );
		}

		foreach ( $args['projects'] as &$project_gid ) {
			$project_gid = Options::sanitize( 'gid', $project_gid );
		}

		$args['projects'] = array_filter( $args['projects'] );

		if ( empty( $args['projects'] ) && empty( $args['workspace'] ) ) {
			// A workspace is required if a project hasn't been provided.
			$workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
			if ( ! empty( $workspace_gid ) ) {
				$args['workspace'] = $workspace_gid;
			}
		}

		// Remove empty fields that aren't explicitly false.
		$args = array_filter(
			$args,
			function ( $value ) {
				if ( is_bool( $value ) ) {
					return true;
				}
				return ! empty( $value );
			}
		);

		// All done!
		return $args;
	}
}//end class
