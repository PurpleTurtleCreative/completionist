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

  $all_tasks = Asana_Interface::maybe_get_all_site_tasks( HTML_Builder::TASK_OPT_FIELDS );
  Asana_Interface::delete_pinned_tasks_except( $all_tasks );

  echo count( $all_tasks ) . '<br>';
  echo Asana_Interface::count_completed_tasks( $all_tasks ) . '<br><br>';

  $categorized_tasks = new Task_Categorizer\Pinned( $all_tasks );
  echo 'Pinned: ' . $categorized_tasks->get_total_count() . '<br><br>';
  echo 'Completed: ' . $categorized_tasks->get_completed_count() . '<br><br>';
  echo 'Incomplete: ' . $categorized_tasks->get_incomplete_count() . '<br><br>';

  $categorized_tasks = new Task_Categorizer\General( $all_tasks );
  echo 'General: ' . $categorized_tasks->get_total_count() . '<br><br>';
  echo 'Completed: ' . $categorized_tasks->get_completed_count() . '<br><br>';
  echo 'Incomplete: ' . $categorized_tasks->get_incomplete_count() . '<br><br>';

  $categorized_tasks = new Task_Categorizer\Critical( $all_tasks );
  echo 'Critical: ' . $categorized_tasks->get_total_count() . '<br><br>';
  echo 'Completed: ' . $categorized_tasks->get_completed_count() . '<br><br>';
  echo 'Incomplete: ' . $categorized_tasks->get_incomplete_count() . '<br><br>';

  $categorized_tasks = new Task_Categorizer\My_Tasks( $all_tasks );
  echo 'My Tasks: ' . $categorized_tasks->get_total_count() . '<br><br>';
  echo 'Completed: ' . $categorized_tasks->get_completed_count() . '<br><br>';
  echo 'Incomplete: ' . $categorized_tasks->get_incomplete_count() . '<br><br>';

  // $res = Asana_Interface::tag_and_comment(
  //   '1163205717983516',
  //   '1164230858555558',
  //   'Testing Batch API request to tag and comment. This should have the "Code" tag!',
  //   HTML_Builder::TASK_OPT_FIELDS
  // );

  // try {
  //   $res = $asana->tags->create( ['name'=>'Test Tag Create','workspace' => '12345'] );
  // } catch ( \Exception $e ) {
  //   $res = $e;
  // }

  // $pinned_tasks = Options::get_all_pinned_tasks();

  // $start = microtime(TRUE);
  // echo '<p>' . empty($pinned_tasks) . '</p>';
  // $end = microtime(TRUE);
  // echo '<p>' . ( $end - $start ) . '</p>';

  // $start = microtime(TRUE);
  // echo '<p>' . (count($pinned_tasks) === 0) . '</p>';
  // $end = microtime(TRUE);
  // echo '<p>' . ( $end - $start ) . '</p>';


  // echo '<pre>';
  // var_dump( $site_tasks );
  // echo '</pre>';
  // echo '<pre>';
  // var_dump( get_class_methods( $site_tasks ) );
  // echo '</pre>';

  /* User is authenticated for API usage. */
  ?>
  <p>Shoop-da-whoop!</p>
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
