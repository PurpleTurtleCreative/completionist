<?php
/**
 * Asana Interface class
 *
 * Loads the Asana API client and translates common interactions between Asana
 * and WordPress. Most of these functions are expected to use API calls.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

defined( 'ABSPATH' ) || die();

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

      // TODO: Create custom Exceptions to know if an authentication error occurred or if this was some other client error such as a rate limit or server issue.

      $asana_personal_access_token = Options::get( Options::ASANA_PAT );
      if (
        FALSE === $asana_personal_access_token
        || '' === $asana_personal_access_token
      ) {
        throw new \Exception( 'No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.' );
      }

      global $ptc_completionist;
      require_once $ptc_completionist->plugin_path . '/vendor/autoload.php';
      $asana = \Asana\Client::accessToken( $asana_personal_access_token );

      try {
        self::$me = $asana->users->me();
      } catch ( \Asana\Errors\NoAuthorizationError $e ) {
        Options::delete( Options::ASANA_PAT );
        throw new \Exception( 'Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.' );
      } catch ( \AccessTokenDispatcher $e ) {
        Options::delete( Options::ASANA_PAT );
        throw new \Exception( 'Missing Asana authentication token. Please save a valid personal access token in Completionist\'s settings.' );
      } catch ( \Exception $e ) {
        /* Don't delete option here because could be server error or API limit... */
        throw new \Exception( 'Asana authorization failure ' . $e->getCode() . ': ' . $e->getMessage() );
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

    static function remote_create_task() : bool {}
    static function remote_update_task() : bool {}
    static function remote_delete_task() : bool {}

  }//end class
}//end if class_exists
