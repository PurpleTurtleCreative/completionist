<?php
/**
 * The content of the Site Tasks admin dashboard widget.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';
require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-pinned.php';
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-general.php';
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-critical.php';
require_once $ptc_completionist->plugin_path . 'src/task-categorizers/class-my-tasks.php';

try {

  Asana_Interface::require_settings();
  $asana = Asana_Interface::get_client();

  if ( ! Asana_Interface::is_workspace_member() ) {
    throw new \Exception( 'You are not a member of the assigned Asana Workspace.', 403 );
  }

  /* Get All Tasks */

  $all_tasks = Asana_Interface::maybe_get_all_site_tasks( HTML_Builder::TASK_OPT_FIELDS );
  Asana_Interface::delete_pinned_tasks_except( $all_tasks );

  $all_incomplete_tasks = array_filter( $all_tasks, function( $task ) {
    if ( isset( $task->completed ) && is_bool( $task->completed ) ) {
      if ( FALSE === $task->completed ) {
        return TRUE;
      }
    }
  } );

  /* Counts and Stats */

  $total_tasks_count = count( $all_tasks );
  $incomplete_tasks_count = count( $all_incomplete_tasks );
  $completed_tasks_count = ( $total_tasks_count - $incomplete_tasks_count );

  /* Categories */

  $pinned_tasks = new Task_Categorizer\Pinned( $all_incomplete_tasks );
  $general_tasks = new Task_Categorizer\General( $all_incomplete_tasks );
  $critical_tasks = new Task_Categorizer\Critical( $all_incomplete_tasks );
  $my_tasks = new Task_Categorizer\My_Tasks( $all_incomplete_tasks );

  /* Display */
  ?>

  <header>

    <button id="all-site-tasks" type="button" data-viewing-tasks="false">
      <div>
        <i class="fas fa-clipboard-list"></i>
      </div>
      <div>
        <p><span><?php echo esc_html( $incomplete_tasks_count ); ?></span> Tasks</p>
        <div>
          <div class="progress-bar-wrapper">
            <div class="progress-bar"></div>
          </div>
          <p><?php echo esc_html( "{$completed_tasks_count} of {$total_tasks_count}" ); ?></p>
        </div>
      </div>
      <div>
        <i class="far fa-circle"></i>
      </div>
    </button>

    <div id="ptc-asana-task-categories">

      <button id="pinned-tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $pinned_tasks->get_tasks_gid_array() ); ?>'>
        <p><?php echo esc_html( $pinned_tasks->get_incomplete_count() ); ?></p>
        <p>Pinned</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="general-tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $general_tasks->get_tasks_gid_array() ); ?>'>
        <p><?php echo esc_html( $general_tasks->get_incomplete_count() ); ?></p>
        <p>General</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="critical-tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $critical_tasks->get_tasks_gid_array() ); ?>'>
        <p><?php echo esc_html( $critical_tasks->get_incomplete_count() ); ?></p>
        <p>Critical</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="my-tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $my_tasks->get_tasks_gid_array() ); ?>'>
        <p><?php echo esc_html( $my_tasks->get_incomplete_count() ); ?></p>
        <p>My Tasks</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

    </div>

  </header>

  <main id="ptc-asana-task-list">
    <?php
    foreach ( $all_tasks as $i => $task ) {
      echo HTML_Builder::format_task_row( $task );//phpcs:ignore WordPress.Security.EscapeOutput
      if ( $i === 19 ) {
        break;
      }
    }
    ?>
  </main>

  <footer>
    <ul id="ptc-asana-tasks-pagination"></ul>
    <a href="<?php /* TODO: Tagged tasks list in Asana */ ?>" target="_asana">
      <button title="View All Site Tasks in Asana" class="view-task" type="button">
        <i class="fas fa-tags"></i>
      </button>
    </a>
  </footer>

  <?php
} catch ( \PTC_Completionist\Errors\NoAuthorization $e ) {
  /* User is not authenticated for API usage. */
  $settings_url = $ptc_completionist->settings_url;
  ?>
  <div class="note-box note-box-error">
    <p>
      <strong>Not authorized.</strong>
      <br>
      Please connect your Asana account to use Completionist.
      <a class="note-box-cta" href="<?php echo esc_url( $settings_url ); ?>">Go to Settings<i class="fas fa-long-arrow-alt-right"></i></a>
    </p>
    <div class="note-box-dismiss">
      <i class="fas fa-times"></i>
    </div>
  </div>
  <?php
} catch ( \Exception $e ) {
  echo HTML_Builder::format_error_box( $e, 'Feature unavailable. ' );//phpcs:ignore WordPress.Security.EscapeOutput
}//end try catch asana client
