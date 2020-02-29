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

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-options.php';
require_once $ptc_completionist->plugin_path . 'src/errors.php';

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
     * @since 1.0.0
     *
     * @return \Asana\Client The authenticated Asana API client.
     *
     * @throws \Exception Authentication may fail when first loading the client
     * or requests could fail due to request limits or server issues.
     */
    static function get_client() : \Asana\Client {

      if ( ! isset( self::$asana ) ) {
        self::$asana = self::maybe_load_client();
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
    static function get_me() : \stdClass {

      if ( ! isset( self::$me ) ) {
        self::$me = self::get_client()->users->me();
      }

      return self::$me;

    }

    /**
     * Loads an authenticated Asana API client.
     *
     * @see get_client() To get the loaded client.
     *
     * @since 1.0.0
     *
     * @return \Asana\Client The authenticated Asana API client.
     *
     * @throws \Exception Authentication may fail when first loading the client
     * or requests could fail due to request limits or server issues.
     */
    private static function maybe_load_client() : \Asana\Client {

      $asana_personal_access_token = Options::get( Options::ASANA_PAT );
      if (
        FALSE === $asana_personal_access_token
        || '' === $asana_personal_access_token
      ) {
        throw new Errors\NoAuthorization( 'No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.' );
      }

      global $ptc_completionist;
      require_once $ptc_completionist->plugin_path . '/vendor/autoload.php';
      $asana = \Asana\Client::accessToken( $asana_personal_access_token );

      try {
        self::$me = $asana->users->me();
      } catch ( \Asana\Errors\NoAuthorizationError $e ) {
        Options::delete( Options::ASANA_PAT );
        throw new Errors\NoAuthorization( 'Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.' );
      } catch ( \Exception $e ) {
        /* Don't delete option here because could be server error or API limit... */
        $error_code = esc_html( $e->getCode() );
        $error_msg = esc_html( $e->getMessage() );
        throw new \Exception( "Asana authorization failure {$error_code}: {$error_msg}" );
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
     *
     * @return bool If the current Asana user is a member of the workspace.
     *
     * @throws \Exception Authentication may fail when first loading the client
     * or requests could fail due to request limits or server issues.
     */
    static function is_workspace_member( string $workspace_gid = '' ) : bool {

      if ( '' === $workspace_gid ) {
        $workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
      }

      $me = self::get_me();

      foreach ( $me->workspaces as $workspace ) {
        if ( $workspace->gid === $workspace_gid ) {
          return TRUE;
        }
      }

      return FALSE;

    }

    /**
     * Get WordPress users that match Asana users by email.
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
    static function find_workspace_users( string $workspace_gid = '' ) : array {

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
          $wp_user !== FALSE
          && $wp_user instanceof \WP_User
        ) {
          $wp_users[ $user->gid ] = $wp_user;
        }
      }

      return $wp_users;

    }

    /**
     * Get a WordPress user ID by Asana user GID. Note that a user's GID is
     * stored as long as the user is successfully authorized.
     *
     * @since 1.0.0
     *
     * @param string $user_gid The Asana user GID for searching.
     *
     * @return int The WordPress user's ID. Default 0.
     */
    static function get_user_id_by_gid( string $user_gid ) : int {

      $user_gid = Options::sanitize( 'gid', $user_gid );

      $query_args = [
        'meta_key' => Options::ASANA_USER_GID,
        'meta_value' => $user_gid,
        'fields' => 'ID',
      ];

      $id = (int) get_users( $query_args );

      if ( $id > 0 ) {
        return (int) $id;
      }

      return 0;

    }

    /**
     * Test if a WordPress user has successfully connected their Asana account.
     *
     * @since 1.0.0
     *
     * @param int $user_id Optional. The WordPress user's ID. Default 0 to use
     * current user's ID.
     *
     * @return bool If the user is successfully authorized to use Asana. Note
     * that any API errors will cause FALSE to be returned.
     */
    static function has_connected_asana( int $user_id = 0 ) : bool {

      $asana_personal_access_token = Options::get( Options::ASANA_PAT, $user_id );
      if (
        FALSE === $asana_personal_access_token
        || '' === $asana_personal_access_token
      ) {
        return FALSE;
      }

      global $ptc_completionist;
      require_once $ptc_completionist->plugin_path . '/vendor/autoload.php';
      $asana = \Asana\Client::accessToken( $asana_personal_access_token );

      try {
        $asana->users->me();
      } catch ( \Exception $e ) {
        return FALSE;
      }

      return TRUE;

    }

    /**
     * Get the external link to a user's task list in Asana for the chosen
     * workspace.
     *
     * @since 1.0.0
     *
     * @param int $user_id Optional. The WordPress user's ID. Default 0 to use
     * current user's ID.
     *
     * @return string The link to the task list on Asana. Default ''.
     */
    static function get_task_list_external_link( int $user_id = 0 ) : string {

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
    static function get_task_gid_from_task_link( string $task_link ) : string {

      $task_link = filter_var(
            wp_unslash( $task_link ),
            FILTER_SANITIZE_URL
          );

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
     * Attempt to retrieve task data to use for display. Providing the post id
     * of the provided pinned task gid will also attempt data self-healing.
     *
     * @since 1.0.0
     *
     * @param string $task_gid The gid of the task to retrieve.
     *
     * @param string $opt_fields A csv of task fields to retrieve, excluding
     * 'workspace', which is required for data healing.
     *
     * @param int $post_id Optional. The post ID on which the task belongs to
     * attempt self-healing on certain error responses. Default 0 to take no
     * action on failure.
     *
     * @return \stdClass The task data returned from Asana.
     *
     * @throws \Exception The Asana client may not be authenticated or the API
     * request may fail. Additional custom exceptions are:
     * * 400: Invalid task gid - The provided task gid is invalid.
     * * 410: Unpinned task - The API returned 404, so the task was unpinned.
     * * 410: Unpinned Foreign Task - The task does not belong to the assigned
     * workspace, so it was unpinned.
     * * 0: Failed to get task data - This is presumably unreachable.
     */
    static function maybe_get_task_data( string $task_gid, string $opt_fields, int $post_id = 0 ) : \stdClass {

      $task_gid = Options::sanitize( 'gid', $task_gid );
      if ( empty( $task_gid ) ) {
        throw new \Exception( 'Invalid task gid', 400 );
      }

      try {

        $asana = self::get_client();
        $task = $asana->tasks->findById( $task_gid, [ 'opt_fields' => $opt_fields . ',workspace' ] );

        if (
          isset( $task->workspace->gid )
          && $task->workspace->gid !== Options::get( Options::ASANA_WORKSPACE_GID )
          && $post_id > 0
        ) {
          if ( $task_gid != '' && Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
            error_log( "Unpinned foreign task from post $post_id." );
            throw new \Exception( 'Unpinned Foreign Task', 410 );
          }
        }

        return $task;

      } catch ( \Exception $e ) {

        $error_code = $e->getCode();
        $error_msg = $e->getMessage();

        if (
          404 == $error_code
          && $post_id > 0
        ) {
          if ( $task_gid != '' && Options::delete( Options::PINNED_TASK_GID, $post_id, $task_gid ) ) {
            error_log( "Unpinned [404: Not Found] task from post $post_id." );
            throw new \Exception( 'Unpinned Task', 410 );
          }
        } elseif (
          'Forbidden' !== $error_msg
          && 410 !== $error_code
        ) {
          error_log( "Failed to fetch task data, error $error_code: $error_msg" );
        }

        throw $e;

      }

      throw new \Exception( 'Failed to get task data', 0 );

    }

    /**
     * Determines if a task belongs to a workspace.
     *
     * @since 1.0.0
     *
     * @param string $task_gid
     *
     * @return bool If the task belongs to the workspace. Note that any API
     * errors will cause FALSE to be returned.
     *
     * @throws \Exception If a parameter is invalid, a 400 error will be thrown:
     * * 400: Invalid task gid
     * * 400: Invalid workspace gid
     */
    static function is_workspace_task( string $task_gid, string $workspace_gid = '' ) : bool {

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
          return TRUE;
        }
      } catch ( \Exception $e ) {
        return FALSE;
      }

      return FALSE;

    }

    static function get_assigned_pins( int $user_id = 0 ) : array {

      // get user's gid

      // get user's assigned tasks in Asana workspace

      // build query WHERE meta_value IN( [returned Asana gids] )

      // return arr[ post_id ][ task_gid ]

    }

  }//end class
}//end if class_exists
