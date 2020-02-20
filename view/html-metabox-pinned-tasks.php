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
  <main id="task-list">
    <p><i class="fas fa-circle-notch fa-spin"></i>Waiting to load tasks...</p>
  </main>

  <aside id="pin-a-task">

    <div id="task-toolbar">
      <input id="asana-task-link-url" name="asana_task_link_url" type="url" placeholder="Paste a task link...">
      <button id="submit-pin-existing" type="button"><i class="fas fa-thumbtack"></i></button>
      <button id="toggle-create-new" type="button"><i class="fas fa-plus"></i></button>
    </div>

    <div id="pin-new-task" style="display:none;">
      <label>Title:</label>
      <input type="text">
      <label>Description:</label>
      <textarea></textarea>
      <label>Due:</label>
      <input type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" id="new-task-due-on" name="new_task_due_on">
      <label>Assignee:</label>
      <select>
        <option>Placeholder</option>
      </select>
      <button id="submit-create-new" type="button"><i class="fas fa-plus"></i>Create Task</button>
    </div>

  </aside>
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
