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
require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';

try {

  $asana = Asana_Interface::get_client();

  if ( ! Asana_Interface::is_workspace_member() ) {
    throw new \Exception( 'You are not a member of the assigned Asana Workspace.', 403 );
  }

  $workspace_users = Asana_Interface::find_workspace_users();

  $params = [
    'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
    'archived' => FALSE,
    'opt_fields' => 'gid,name',
  ];
  $projects = $asana->projects->findAll( $params );

  /* User is authenticated for API usage. */
  ?>
  <aside id="pin-a-task">

    <div id="task-toolbar">

      <input id="asana-task-link-url" type="url" placeholder="Paste a task link...">
      <button id="submit-pin-existing" title="Pin existing Asana task" type="button"><i class="fas fa-thumbtack"></i></button>

      <button id="toggle-create-new" title="Add a new task" type="button"><i class="fas fa-plus"></i></button>

    </div>

    <div id="pin-new-task" style="display:none;">

      <input id="ptc-new-task_name" type="text" placeholder="Write a task name...">

      <div class="form-group">
        <label for="ptc-new-task_assignee">Assignee</label>
        <select id="ptc-new-task_assignee">
          <option value="">None (Unassigned)</option>
          <?php
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
    <p class="task-loader"><i class="fas fa-circle-notch fa-spin"></i>Waiting to load tasks...</p>
  </main>
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
