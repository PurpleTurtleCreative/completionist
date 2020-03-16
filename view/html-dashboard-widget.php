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

  $all_incomplete_tasks = [];
  foreach ( $all_tasks as $task ) {
    if ( isset( $task->completed ) && is_bool( $task->completed ) ) {
      if ( FALSE === $task->completed ) {
        $all_incomplete_tasks[] = $task;
      }
    }
  }

  /* Sort By Due */

  $sorted_all_incompleted_tasks = HTML_Builder::sort_tasks_by_due( $all_incomplete_tasks );
  if ( ! empty( $sorted_all_incompleted_tasks ) ) {
    $all_incomplete_tasks = $sorted_all_incompleted_tasks;
  }

  /* Counts and Stats */

  $total_tasks_count = count( $all_tasks );
  $incomplete_tasks_count = count( $all_incomplete_tasks );
  $completed_tasks_count = ( $total_tasks_count - $incomplete_tasks_count );

  /* Categories */

  $pinned_tasks = new Task_Categorizer\Pinned( $all_incomplete_tasks );
  $general_tasks = new Task_Categorizer\General( $all_incomplete_tasks );
  $critical_tasks = new Task_Categorizer\Critical( $all_incomplete_tasks );
  $my_tasks = new Task_Categorizer\My_Tasks( $all_incomplete_tasks );

  /* Task List Pagination */

  $page_size = 20;
  $total_pages = ceil( $incomplete_tasks_count / $page_size );
  $disable_next_button = ( $total_pages > 1 ) ? '' : 'disabled="disabled"';

  /* Display */
  ?>

  <header>

    <button id="all-site-tasks" title="View All Site Tasks" type="button" data-viewing-tasks="true" data-category-task-gids='<?php echo json_encode( Asana_Interface::get_tasks_gid_array( $all_incomplete_tasks ) ); ?>'>
      <div>
        <i class="fas fa-clipboard-list"></i>
      </div>
      <div>
        <p><span class="task-count"><?php echo esc_html( $incomplete_tasks_count ); ?></span> Tasks</p>
        <div>
          <div class="progress-bar-wrapper">
            <div class="progress-bar"></div>
          </div>
          <p><span class="completed-tasks-count"><?php echo esc_html( $completed_tasks_count ); ?></span> of <span class="total-tasks-count"><?php echo esc_html( $total_tasks_count ); ?></span></p>
        </div>
      </div>
      <div>
        <i class="far fa-circle"></i>
      </div>
    </button>

    <div id="ptc-asana-task-categories">

      <button id="pinned-tasks" title="View Pinned Tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $pinned_tasks->get_tasks_gid_array() ); ?>'>
        <p><span class="task-count"><?php echo esc_html( $pinned_tasks->get_incomplete_count() ); ?></span>Pinned</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="general-tasks" title="View Generic Tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $general_tasks->get_tasks_gid_array() ); ?>'>
        <p><span class="task-count"><?php echo esc_html( $general_tasks->get_incomplete_count() ); ?></span>General</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="critical-tasks" title="View Due and Upcoming Tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $critical_tasks->get_tasks_gid_array() ); ?>'>
        <p><span class="task-count"><?php echo esc_html( $critical_tasks->get_incomplete_count() ); ?></span>Critical</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

      <button id="my-tasks" title="View My Tasks" type="button" data-viewing-tasks="false" data-category-task-gids='<?php echo json_encode( $my_tasks->get_tasks_gid_array() ); ?>'>
        <p><span class="task-count"><?php echo esc_html( $my_tasks->get_incomplete_count() ); ?></span>My Tasks</p>
        <div>
          <i class="far fa-circle"></i>
        </div>
      </button>

    </div>

  </header>

  <main id="ptc-asana-task-list">
    <?php
    foreach ( $all_incomplete_tasks as $i => $task ) {
      echo HTML_Builder::format_task_row( $task, TRUE );//phpcs:ignore WordPress.Security.EscapeOutput
      if ( $i === ( $page_size - 1 ) ) {
        break;
      }
    }
    ?>
  </main>

  <footer>
    <nav id="ptc-asana-tasks-pagination">
      <button data-page="prev" type="button" title="Previous Page" disabled="disabled">
        <i class="fas fa-angle-left"></i>
      </button>
      <button class="page-option" data-page="1" type="button" title="Page 1" disabled="disabled">
        1
      </button>
      <?php
      for ( $i = 2; $i <= $total_pages; ++$i ) {
        echo '<button class="page-option" data-page="' . esc_attr( $i ) . '" type="button" title="Page ' . esc_attr( $i ) . '">' .
              esc_html( $i ) . '</button>';
      }
      ?>
      <button data-page="next" type="button" title="Next Page" <?php echo $disable_next_button; ?>>
        <i class="fas fa-angle-right"></i>
      </button>
    </nav>
    <a href="<?php echo esc_url( HTML_Builder::get_asana_tag_url() ); ?>" target="_asana">
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
