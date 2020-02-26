<?php
/**
 * The content of the Pinned Tasks metabox.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

try {

  $asana = Asana_Interface::get_client();

  /* User is authenticated for API usage. */
  ?>
  <aside id="pin-a-task">

    <div id="task-toolbar">

      <input id="asana-task-link-url" type="url" placeholder="Paste a task link...">
      <button id="submit-pin-existing" type="button"><i class="fas fa-thumbtack"></i></button>

      <button id="toggle-create-new" type="button"><i class="fas fa-plus"></i></button>

    </div>

    <div id="pin-new-task" style="display:none;">

      <input id="ptc-new-task_name" type="text" placeholder="Write a task name...">

      <div class="form-group">
        <label for="ptc-new-task_assignee">Assignee</label>
        <select id="ptc-new-task_assignee">
          <option value="">None (Unassigned)</option>
          <?php
          $workspace_users = Asana_Interface::find_workspace_users();
          foreach ( $workspace_users as $user_gid => $wp_user ) {
            echo  '<option value="' . esc_attr( $user_gid ) . '">' .
                    esc_html( $wp_user->display_name ) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="ptc-new-task_due_on">Due Date</label>
        <input id="ptc-new-task_due_on" type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd">
      </div>

      <div class="form-group">
        <label for="ptc-new-task_project">Project</label>
        <select id="ptc-new-task_project">
          <option value="">None (Private Task)</option>
          <?php
          $asana = Asana_Interface::get_client();
          $params = [
            'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
            'archived' => FALSE,
            'opt_fields' => 'gid,name',
          ];
          $projects = $asana->projects->findAll( $params );
          foreach ( $projects as $project ) {
            echo  '<option value="' . esc_attr( $project->gid ) . '">' .
                    esc_html( $project->name ) . '</option>';
          }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="ptc-new-task_notes">Description</label>
        <textarea id="ptc-new-task_notes"></textarea>
      </div>

      <button id="submit-create-new" type="button"><i class="fas fa-plus"></i>Add Task</button>

    </div>

  </aside>

  <main id="task-list">
    <p><i class="fas fa-circle-notch fa-spin"></i>Waiting to load tasks...</p>
  </main>
  <?php
} catch ( \PTC_Completionist\Errors\NoAuthorization $e ) {
  /* User is not authenticated for API usage. */
  $settings_url = $ptc_completionist->settings_url;
  ?>
  <div id="ptc-asana-dashboard-error" class="note-box note-box-error">
    <i class="fas fa-times"></i>
    <p><strong>Not authorized.</strong> Please connect your Asana account to use Completionist.<a href="<?php echo esc_url( $settings_url ); ?>">Go to Settings<i class="fas fa-long-arrow-alt-right"></i></a></p>
  </div>
  <?php
} catch ( \Exception $e ) {
  ?>
  <div id="ptc-asana-dashboard-error" class="note-box note-box-error">
    <i class="fas fa-times"></i>
    <p><strong>Error <?php echo esc_html( $e->getCode() ); ?>.</strong> <?php echo esc_html( $e->getMessage() ); ?></p>
  </div>
  <?php
}//end try catch asana client
