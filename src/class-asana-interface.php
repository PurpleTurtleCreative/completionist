<?php
/**
 * Asana Interface class
 *
 * Loads the Asana API client and translates common interactions between Asana
 * and WordPress. These functions are expected to use API calls. This class's
 * primary use is to refresh locally stored data that is found to be expired.
 *
 * @see Data_Store To work with loaded data.
 *
 * @see Options To find expiry times.
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
   * instance and WordPress.
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
     * Gets the authenticated Asana API client.
     *
     * @see maybe_load_client() For Exception cases.
     *
     * @since 1.0.0
     *
     * @return \Asana\Client The authenticated Asana API client.
     *
     * @throws \Exception Authentication may fail when first loading the client.
     */
    static function get_client() : \Asana\Client {

      if ( ! isset( self::$asana ) ) {
        self::$asana = self::maybe_load_client();
      }

      return self::$asana;

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
     * @throws \Exception When authentication fails.
     */
    private static function maybe_load_client() : \Asana\Client {

      $asana_personal_access_token = Options::get( Options::ASANA_PAT );
      if (
        FALSE === $asana_personal_access_token
        || '' === $asana_personal_access_token
      ) {
        throw new \Exception( 'ERROR: No Asana authentication provided. Please save a valid personal access token in Completionist\'s settings.' );
      }

      global $ptc_completionist;
      require_once $ptc_completionist->plugin_path . '/vendor/autoload.php';
      $asana = \Asana\Client::accessToken( $asana_personal_access_token );

      try {
        $asana->users->me();
      } catch ( \Asana\Errors\NoAuthorizationError $e ) {
        throw new \Exception( 'ERROR: Asana authorization failed. Please provide a new personal access token in Completionist\'s settings.' );
      } catch ( \AccessTokenDispatcher $e ) {
        throw new \Exception( 'ERROR: Missing Asana authentication token. Please save a valid personal access token in Completionist\'s settings.' );
      }

      return $asana;

    }

    static function maybe_reload() : bool {}

    static function is_expired() : bool {}

    static function reload_tasks() : bool {}

    static function reload_user_gids() : bool {}
    static function delete_all_user_gids() : bool {}

    static function remote_create_task() : bool {}
    static function remote_update_task() : bool {}
    static function remote_delete_task() : bool {}

  }//end class
}//end if class_exists
