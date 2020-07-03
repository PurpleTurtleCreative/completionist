<?php
/**
 * Automation class
 *
 * Object structure for working with automations.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once 'class-data.php';
require_once 'class-fields.php';
require_once 'class-actions.php';

if ( ! class_exists( __NAMESPACE__ . '\Automation' ) ) {
  /**
   * Object structure for working with automation entries.
   */
  class Automation {

    public $ID = 0;
    public $title = '';
    public $description = '';
    public $hook_name = '';
    public $last_modified = '';

    private $conditions = NULL;
    private $actions = NULL;
    private $actions_meta = NULL;

    public $translation_objects = [];

    /**
     * Loads initial automation details.
     *
     * @since 1.1.0
     *
     * @param int $automation_id
     *
     * @param array $translation_objects
     *
     * @throws \Exception If the automation does not exist.
     */
    function __construct( int $automation_id, array $translation_objects = [] ) {

      $automation_record = Automations\Data::get_automation( $automation_id );
      if ( NULL === $automation_record ) {
        throw new \Exception( "Automation does not exist with ID: {$automation_id}", 404 );
      }

      $this->ID = $automation_record->ID;
      $this->title = $automation_record->title;
      $this->description = $automation_record->description;
      $this->hook_name = $automation_record->hook_name;
      $this->last_modified = $automation_record->last_modified;

      $this->translation_objects = $translation_objects;

    }

    /**
     * Run automation actions if conditions are satisfied.
     *
     * @since 1.1.0
     */
    function maybe_run_actions() {
      if ( $this->satisfies_conditions() === TRUE ) {
        $this->run_actions();
      }
    }

    /**
     * Determines if all automation conditions are true.
     *
     * @since 1.1.0
     *
     * @return bool If all automation conditions are true.
     */
    function satisfies_conditions() : bool {

      $conditions = $this->get_conditions();

      if ( count( $conditions ) <= 0 ) {
        return TRUE;
      }

      foreach ( $conditions as $condition ) {
        if ( FALSE === Automations\Fields::evaluate_condition( $condition, $this->translation_objects ) ) {
          return FALSE;
        }
      }

      return TRUE;

    }

    /**
     * Execute all actions for this automation.
     *
     * @since 1.1.0
     */
    function run_actions() {

      $actions = $this->get_actions( TRUE );

      foreach ( $actions as $action_with_meta ) {
        if ( Automations\Actions::run_action( $action_with_meta, $this->translation_objects ) ) {
          Automations\Data::update_action_last_triggered( $action_with_meta->ID );
        }
      }

    }

    /**
     * Get the conditions associated with this automation entry.
     *
     * @since 1.1.0
     *
     * @return \stdClass[] The condition records.
     */
    function get_conditions() : array {

      if ( $this->conditions === NULL ) {
        $this->conditions = Automations\Data::get_conditions( $this->ID );
      }

      return $this->conditions;

    }

    /**
     * Get the actions associated with this automation entry.
     *
     * @since 1.1.0
     *
     * @param bool $with_metadata Optional. If to include associated action meta
     * records for each action record. Setting this TRUE adds the 'meta' array
     * property to each action record. Default TRUE.
     *
     * @return \stdClass[] The resulting action records.
     */
    function get_actions( bool $with_metadata = TRUE ) : array {

      if ( $this->actions === NULL ) {
        $this->actions = Automations\Data::get_actions( $this->ID );
      }

      if ( ! $with_metadata ) {
        return $this->actions;
      }

      if ( $this->actions_meta === NULL ) {
        $this->actions_meta = Automations\Data::get_actions_meta( $this->ID );
      }

      $actions_with_meta = $this->actions;
      $working_meta_records = $this->actions_meta;
      foreach ( $actions_with_meta as &$action ) {
        $action->meta = [];
        foreach ( $working_meta_records as $i => $meta ) {
          if ( $meta->action_id == $action->ID ) {
            $action->meta[ $meta->meta_key ] = $meta->meta_value;
            unset( $working_meta_records[ $i ] );
          }
        }
        // TODO: error_log count( $working_meta_records ) to see if unset() improves search length
      }

      return $actions_with_meta;

    }

    /**
     * Get the standard object representation of the automation.
     *
     * @see \PTC_Completionist\Automations\Data::save_automation() For returned
     * object structure.
     *
     * @since 1.1.0
     *
     * @return \stdClass The standard object representation of the automation.
     */
    function to_stdClass() : \stdClass {

      $obj = new \stdClass();

      $obj->ID = $this->ID;
      $obj->title = $this->title;
      $obj->description = $this->description;
      $obj->hook_name = $this->hook_name;
      $obj->last_modified = $this->last_modified;

      $obj->conditions = $this->get_conditions();
      $obj->actions = $this->get_actions( TRUE );

      return $obj;

    }

  }//end class
}//end if class_exists