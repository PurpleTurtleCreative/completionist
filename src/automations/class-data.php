<?php
/**
 * Automations Data class
 *
 * CRUD database methods for Automations.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist\Automations;

defined( 'ABSPATH' ) || die();

require_once 'class-events.php';
require_once 'class-fields.php';
require_once 'class-actions.php';
require_once 'class-automation.php';
require_once __DIR__ . '/../class-database-manager.php';
require_once __DIR__ . '/../class-options.php';
require_once __DIR__ . '/../class-html-builder.php';

use \PTC_Completionist\Database_Manager;
use \PTC_Completionist\Options;
use \PTC_Completionist\HTML_Builder;
use \PTC_Completionist\Automation;

if ( ! class_exists( __NAMESPACE__ . '\Data' ) ) {
  /**
   * Static class of CRUD database methods for Automations.
   */
  class Data {

    /* Basic Interfacing */

    /**
     * Description
     *
     * @since 1.1.0
     *
     * @param \stdClass $automation A full object representation of automation
     * data to be compared and saved to the database. Note that missing data
     * for existing automation records is assumed to be deleted. The structure
     * for the object is as follows:
     * - ID (0 to create or existing ID to update)
     * - title
     * - description
     * - hook_name
     * - last_modified (unused)
     * - conditions[]
     *   - ID (0 to create or existing ID to update)
     *   - property
     *   - comparison_method
     *   - value
     * - actions[]
     *   - ID (0 to create or existing ID to update)
     *   - action
     *   - triggered_count
     *   - last_triggered (unused)
     *   - meta{} (meta_key properties with meta_value values)
     *
     * @return \stdClass The automation data now in the database for the new
     * or updated automation. An empty object is returned on failure to create
     * a new automation or when attempting to update an automation that does
     * not exist.
     */
    static function save_automation( \stdClass $automation ) : \stdClass {

      $saved_automation = new \stdClass();
      $automation->ID = (int) $automation->ID;

      if ( $automation->ID <= 0 ) {
        /* Create new automation */
        $new_automation_id = self::add_automation(
          $automation->title,
          $automation->description,
          $automation->hook_name
        );

        if ( $new_automation_id <= 0 ) {
          error_log('Failed to add new automation.');
          return FALSE;
        }

        // TODO: use bulk insertion queries rather than multiple write calls

        foreach ( $automation->conditions as $condition ) {
          if (
            self::add_condition(
              $new_automation_id,
              $condition->property,
              $condition->comparison_method,
              $condition->value
            ) <= 0
          ) {
            error_log('Failed to add new automation condition.');
          }
        }//end foreach conditions

        foreach ( $automation->actions as $action ) {
          $new_action_id = self::add_action(
            $new_automation_id,
            $action->action
          );
          if ( $new_action_id <= 0 ) {
            error_log('Failed to add new automation action.');
            continue;
          } elseif ( isset( $action->meta ) ) {
            foreach ( $action->meta as $meta_key => $meta_value ) {
              if (
                self::add_action_meta(
                  $new_action_id,
                  $meta_key,
                  $meta_value
                ) <= 0
              ) {
                error_log('Failed to add new action meta.');
              }
            }
          }
        }//end foreach actions

        try {
          $saved_automation = ( new Automation( $new_automation_id ) )->to_stdClass();
        } catch ( \Exception $e ) {
          error_log( HTML_Builder::format_error_string( $e, 'Failed to retrieve newly created automation data.' ) );
        }

      } else {
        try {
          // TODO: implement updating an existing automation
          $old_automation = ( new Automation( $automation->ID ) )->to_stdClass();
          error_log( 'Automation exists with ID: ' . $automation->ID );
          error_log( "as stdClass ---> \n" . print_r( $old_automation, TRUE ) );
        } catch ( \Exception $e ) {
          $saved_automation = \stdClass();
          error_log( HTML_Builder::format_error_string( $e, 'Failed to update existing automation.' ) );
        }
      }

      return $saved_automation;

    }//end save_automation()

    /* Main Record Queries */

    /**
     * Selects an automation record by ID.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The record's ID.
     *
     * @return \stdClass|null The retrieved automation record or NULL if no
     * matching record was found.
     */
    static function get_automation( int $automation_id ) {

      global $wpdb;
      $table = Database_Manager::$automations_table;
      $res = $wpdb->get_row(
        $wpdb->prepare(
          "SELECT * FROM $table
            WHERE ID = %d LIMIT 1",
          $automation_id
        )
      );

      if ( $res !== NULL ) {
        $res->ID = (int) $res->ID;
        $res->title = wp_unslash( $res->title );
        $res->hook_name = wp_unslash( $res->hook_name );
        $res->last_modified = wp_unslash( $res->last_modified );
      }

      return $res;

    }

    /**
     * Selects all condtion records for an automation.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID.
     *
     * @return array The condition records.
     */
    static function get_conditions( int $automation_id ) : array {

      global $wpdb;
      $table = Database_Manager::$automation_conditions_table;
      $res = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT * FROM $table
            WHERE automation_id = %d",
          $automation_id
        )
      );

      if ( $res ) {
        foreach ( $res as &$item ) {
          $item->ID = (int) $item->ID;
          $item->automation_id = (int) $item->automation_id;
          $item->property = wp_unslash( $item->property );
          $item->comparison_method = wp_unslash( $item->comparison_method );
          $item->value = wp_unslash( $item->value );
        }
      }

      return $res;

    }

    /**
     * Selects all action records for an automation.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID.
     *
     * @return array The action records.
     */
    static function get_actions( int $automation_id ) : array {

      global $wpdb;
      $table = Database_Manager::$automation_actions_table;
      $res = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT * FROM $table
            WHERE automation_id = %d",
          $automation_id
        )
      );

      if ( $res ) {
        foreach ( $res as &$item ) {
          $item->ID = (int) $item->ID;
          $item->automation_id = (int) $item->automation_id;
          $item->action = wp_unslash( $item->action );
          $item->last_triggered = wp_unslash( $item->last_triggered );
        }
      }

      return $res;

    }

    /**
     * Selects all action meta records for an automation.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID.
     *
     * @return array The action meta records.
     */
    static function get_actions_meta( int $automation_id ) : array {

      global $wpdb;
      $automations_table = Database_Manager::$automations_table;
      $automation_actions_table = Database_Manager::$automation_actions_table;
      $automation_actions_meta_table = Database_Manager::$automation_actions_meta_table;
      $res = $wpdb->get_results(
        $wpdb->prepare(
          "SELECT actions_meta.*
            FROM $automations_table automations
            JOIN $automation_actions_table actions
              ON actions.automation_id = automations.ID
              AND automations.ID = %d
            JOIN $automation_actions_meta_table actions_meta
              ON actions_meta.action_id = actions.ID",
          $automation_id
        )
      );

      if ( $res ) {
        foreach ( $res as &$item ) {
          $item->ID = (int) $item->ID;
          $item->action_id = (int) $item->action_id;
          $item->meta_key = wp_unslash( $item->meta_key );
          $item->meta_value = wp_unslash( $item->meta_value );
        }
      }

      return $res;

    }

    /* Update */

    static function update_automation( int $automation_id, array $params ) : bool {}

    static function update_condition( int $condition_id, array $params ) : bool {}

    static function update_action( int $action_id, array $params ) : bool {}

    static function update_action_meta( int $action_meta_id, array $params ) : bool {}

    /* Create */

    /**
     * Inserts an automation record.
     *
     * @since 1.1.0
     *
     * @param string $title The title.
     *
     * @param string $hook_name The hook.
     *
     * @return int The automation ID. Default 0 on error.
     */
    static function add_automation( string $title, string $description, string $hook_name ) : int {

      $title = Options::sanitize( 'string', $title );
      $description = sanitize_textarea_field( $description );
      $hook_name = Options::sanitize( 'string', $hook_name );

      if (
        $title === ''
        || $hook_name === ''
        || ! self::validate_automation_hook_name( $hook_name )
      ) {
        return 0;
      }

      global $wpdb;
      $res = $wpdb->insert(
        Database_Manager::$automations_table,
        [
          'title' => $title,
          'description' => $description,
          'hook_name' => $hook_name,
        ],
        [
          '%s',
          '%s',
          '%s',
        ]
      );

      if ( $res !== 1 ) {
        return 0;
      }

      return $wpdb->insert_id;

    }

    /**
     * Inserts an automation condition record.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID to which this condition
     * applies.
     *
     * @param string $property The field accessor string.
     *
     * @param string $comparison_method The comparison method string.
     *
     * @param string $value The value for evaluation.
     *
     * @return int The condition ID. Default 0 on error.
     */
    static function add_condition( int $automation_id, string $property, string $comparison_method, string $value ) : int {

      $property = Options::sanitize( 'string', $property );
      $comparison_method = Options::sanitize( 'string', $comparison_method );
      $value = Options::sanitize( 'string', $value );

      if (
        $automation_id <= 0
        || $property === ''
        || $comparison_method === ''
        || $value === ''
        || ! self::validate_condition_comparison_method( $comparison_method )
        || ! self::automation_exists( $automation_id )
      ) {
        return 0;
      }

      global $wpdb;
      $res = $wpdb->insert(
        Database_Manager::$automation_conditions_table,
        [
          'automation_id' => $automation_id,
          'property' => $property,
          'comparison_method' => $comparison_method,
          'value' => $value,
        ],
        [
          '%d',
          '%s',
          '%s',
          '%s',
        ]
      );

      if ( $res !== 1 ) {
        return 0;
      }

      return $wpdb->insert_id;

    }

    /**
     * Inserts an automation action record.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID to which this action applies.
     *
     * @param string $action_name The action key.
     *
     * @return int The action ID. Default 0 on error.
     */
    static function add_action( int $automation_id, string $action_name ) : int {

      $action_name = Options::sanitize( 'string', $action_name );

      if (
        $automation_id <= 0
        || $action_name === ''
        || ! self::validate_action_key( $action_name )
        || ! self::automation_exists( $automation_id )
      ) {
        return 0;
      }

      global $wpdb;
      $res = $wpdb->insert(
        Database_Manager::$automation_actions_table,
        [
          'automation_id' => $automation_id,
          'action' => $action_name,
        ],
        [
          '%d',
          '%s',
        ]
      );

      if ( $res !== 1 ) {
        return 0;
      }

      return $wpdb->insert_id;

    }

    /**
     * Inserts an automation action meta record.
     *
     * @since 1.1.0
     *
     * @param int $action_id The action ID to which this meta applies.
     *
     * @param string $meta_key The meta key.
     *
     * @param string $meta_value The meta value.
     *
     * @return int The action meta ID. Default 0 on error.
     */
    static function add_action_meta( int $action_id, string $meta_key, string $meta_value ) : int {

      $meta_key = Options::sanitize( 'string', $meta_key );

      if ( $meta_key == 'notes' ) {
        $meta_value = sanitize_textarea_field( $meta_value );
      } else {
        $meta_value = Options::sanitize( 'string', $meta_value );
      }

      if (
        $action_id <= 0
        || $meta_key === ''
        || $meta_value === ''
        || ! self::action_exists( $action_id )
      ) {
        return 0;
      }

      // TODO: check if meta key already exists, update meta value instead

      global $wpdb;
      $res = $wpdb->insert(
        Database_Manager::$automation_actions_meta_table,
        [
          'action_id' => $action_id,
          'meta_key' => $meta_key,
          'meta_value' => $meta_value,
        ],
        [
          '%d',
          '%s',
          '%s',
        ]
      );

      if ( $res !== 1 ) {
        return 0;
      }

      return $wpdb->insert_id;

    }

    /* Delete */

    /**
     * Delete an automation and its related data.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The automation ID.
     *
     * @return bool If deleted successfully.
     */
    static function delete_automation( int $automation_id ) : bool {

      global $wpdb;
      $res = $wpdb->delete(
        Database_Manager::$automations_table,
        [
          'ID' => $automation_id,
        ],
        [
          '%d',
        ]
      );

      return ( $res !== FALSE );

    }

    /**
     * Delete an automation condition.
     *
     * @since 1.1.0
     *
     * @param int $condition_id The automation condition ID.
     *
     * @return bool If deleted successfully.
     */
    static function delete_condition( int $condition_id ) : bool {

      global $wpdb;
      $res = $wpdb->delete(
        Database_Manager::$automation_conditions_table,
        [
          'ID' => $condition_id,
        ],
        [
          '%d',
        ]
      );

      return ( $res !== FALSE );

    }

    /**
     * Delete an automation action and its related data.
     *
     * @since 1.1.0
     *
     * @param int $action_id The automation action ID.
     *
     * @return bool If deleted successfully.
     */
    static function delete_action( int $action_id ) : bool {

      global $wpdb;
      $res = $wpdb->delete(
        Database_Manager::$automation_actions_table,
        [
          'ID' => $action_id,
        ],
        [
          '%d',
        ]
      );

      return ( $res !== FALSE );

    }

    /**
     * Delete an action meta record.
     *
     * @since 1.1.0
     *
     * @param int $action_meta_id The action meta ID.
     *
     * @return bool If deleted successfully.
     */
    static function delete_action_meta( int $action_meta_id ) : bool {

      global $wpdb;
      $res = $wpdb->delete(
        Database_Manager::$automation_actions_meta_table,
        [
          'ID' => $action_meta_id,
        ],
        [
          '%d',
        ]
      );

      return ( $res !== FALSE );

    }

    static function delete_action_meta_by_key( int $action_id, string $meta_key ) : bool {}

    /* Special Queries */

    /**
     * Checks if an automation record exists.
     *
     * @since 1.1.0
     *
     * @param int $automation_id The ID.
     *
     * @return bool If the automation exists.
     */
    static function automation_exists( int $automation_id ) : bool {

      global $wpdb;
      $table = Database_Manager::$automations_table;
      $res = $wpdb->get_var( $wpdb->prepare(
          "SELECT ID FROM $table
            WHERE ID = %d",
          $automation_id
        )
      );

      if ( $res === NULL ) {
        return FALSE;
      }

      return TRUE;

    }

    /**
     * Checks if an automation action record exists.
     *
     * @since 1.1.0
     *
     * @param int $action_id The ID.
     *
     * @return bool If the action exists.
     */
    static function action_exists( int $action_id ) : bool {

      global $wpdb;
      $table = Database_Manager::$automation_actions_table;
      $res = $wpdb->get_var( $wpdb->prepare(
          "SELECT ID FROM $table
            WHERE ID = %d",
          $action_id
        )
      );

      if ( $res === NULL ) {
        return FALSE;
      }

      return TRUE;

    }

    /**
     * Selects all main automation records with additional overview information
     * for each.
     *
     * @since 1.1.0
     *
     * @param string $order_by Optional. The column to sort by in
     * descending order. Default 'title'. May be one of any returned columns:
     * - ID
     * - title (default)
     * - description
     * - hook_name
     * - last_modified
     * - total_conditions
     * - total_actions
     * - last_triggered
     * - total_triggered
     *
     * @return \stdClass[] The automation overview records.
     */
    static function get_automation_overviews( string $order_by = 'title' ) : array {

      global $wpdb;
      $automations_table = Database_Manager::$automations_table;
      $automation_actions_table = Database_Manager::$automation_actions_table;
      $automation_conditions_table = Database_Manager::$automation_conditions_table;
      $res = $wpdb->get_results(
        "SELECT
          automations.*,
          (
            SELECT
              COUNT(ID)
            FROM
              $automation_conditions_table
            WHERE
              automation_id = automations.ID
          ) AS 'total_conditions',
          (
            SELECT
              COUNT(ID)
            FROM
              $automation_actions_table
            WHERE
              automation_id = automations.ID
          ) AS 'total_actions',
          (
            SELECT
              MAX(last_triggered)
            FROM
              $automation_actions_table
            WHERE
              automation_id = automations.ID
          ) AS 'last_triggered',
          (
            SELECT
              SUM(triggered_count)
            FROM
              $automation_actions_table
            WHERE
              automation_id = automations.ID
          ) AS 'total_triggered'
        FROM
          $automations_table automations
        ORDER BY $order_by DESC"
      );

      if ( $res ) {
        foreach ( $res as &$item ) {
          $item->ID = (int) $item->ID;
          $item->title = wp_unslash( $item->title );
          $item->description = wp_unslash( $item->description );
          $item->hook_name = wp_unslash( $item->hook_name );
          $item->last_modified = wp_unslash( $item->last_modified );
          $item->total_conditions = (int) $item->total_conditions;
          $item->total_actions = (int) $item->total_actions;
          $item->last_triggered = wp_unslash( $item->last_triggered );
          $item->total_triggered = (int) $item->total_triggered;
        }
      }

      return $res;

    }

    /**
     * Selects all distinct hook names.
     *
     * @since 1.1.0
     *
     * @return array The hook names.
     */
    static function get_all_hook_names() : array {

      global $wpdb;
      $automations_table = Database_Manager::$automations_table;
      return $wpdb->get_col( "SELECT DISTINCT hook_name FROM $automations_table" );

    }

    /**
     * Selects all automation IDs for a given hook.
     *
     * @since 1.1.0
     *
     * @param string $hook_name The hook name.
     *
     * @return int[] The IDs.
     */
    static function get_all_automation_ids_for( string $hook_name ) : array {

      global $wpdb;
      $table = Database_Manager::$automations_table;
      $res = $wpdb->get_col(
        $wpdb->prepare(
          "SELECT DISTINCT ID FROM $table
            WHERE hook_name = %s",
          $hook_name
        )
      );

      return array_map( 'intval', $res );

    }

    /**
     * Counts the number of actions for a given hook.
     *
     * @since 1.1.0
     *
     * @param string $hook_name The hook name.
     *
     * @return int The count. Default 0.
     */
    static function count_actions_for( string $hook_name ) : int {

      global $wpdb;
      $automations_table = Database_Manager::$automations_table;
      $automation_actions_table = Database_Manager::$automation_actions_table;
      $count = $wpdb->get_var(
        $wpdb->prepare(
          "SELECT COUNT(actions.ID) FROM $automation_actions_table actions
            JOIN $automations_table automations
              ON automations.ID = actions.automation_id
              AND automations.hook_name = %s",
          $hook_name
        )
      );

      if ( ! is_numeric( $count ) ) {
        $count = 0;
      }

      return (int) $count;

    }

    /**
     * Determines if actions exist for a given hook.
     *
     * @since 1.1.0
     *
     * @param string $hook_name The hook name.
     *
     * @return bool If actions exist for the given hook.
     */
    static function actions_exist_for( string $hook_name ) : bool {
      return ( self::count_actions_for( $hook_name ) > 0 );
    }

    /**
     * Updates an automation's last_modified field to the current time (GMT).
     *
     * @since 1.1.0
     *
     * @param int $automation_id The ID of the automation to update.
     *
     * @return bool If successfully updated.
     */
    static function update_automation_last_modified( int $automation_id ) : bool {

      global $wpdb;
      $table = Database_Manager::$automations_table;
      $rows_affected = $wpdb->query(
        $wpdb->prepare(
          "UPDATE $table
            SET last_modified = CURRENT_TIMESTAMP
            WHERE automation_id = %d",
          $automation_id
        )
      );

      return ( $rows_affected === 1 );

    }

    /**
     * Updates an action's last_triggered field to the current time (GMT) and
     * increments the triggered_count.
     *
     * @since 1.1.0
     *
     * @param int $action_id The ID of the action to update.
     *
     * @return bool If successfully updated.
     */
    static function update_action_last_triggered( int $action_id ) : bool {

      global $wpdb;
      $table = Database_Manager::$automation_actions_table;
      $rows_affected = $wpdb->query(
        $wpdb->prepare(
          "UPDATE $table
            SET
              last_triggered = CURRENT_TIMESTAMP,
              triggered_count = ( triggered_count + 1 )
            WHERE ID = %d",
          $action_id
        )
      );

      return ( $rows_affected === 1 );

    }

    /**
     * Checks if the hook name is supported.
     *
     * @since 1.1.0
     *
     * @param string $hook_name The hook name.
     *
     * @return bool If the hook name is valid.
     */
    static function validate_automation_hook_name( string $hook_name ) : bool {
      return (
        in_array( $hook_name, array_keys( Events::USER_OPTIONS ) )
        || in_array( $hook_name, array_keys( Events::POST_OPTIONS ) )
      );
    }

    /**
     * Validates a comparison method string.
     *
     * @since 1.1.0
     *
     * @param string $comparison_method The comparison method.
     *
     * @return bool If the comparison method is valid.
     */
    static function validate_condition_comparison_method( string $comparison_method ) : bool {
      return in_array( $comparison_method, Fields::COMPARISON_METHODS );
    }

    /**
     * Validates an action string.
     *
     * @since 1.1.0
     *
     * @param string $action_key The action key.
     *
     * @return bool If the action key is valid.
     */
    static function validate_action_key( string $action_key ) : bool {
      return in_array( $action_key, array_keys( Actions::ACTION_OPTIONS ) );
    }

  }//end class
}//end if class_exists
