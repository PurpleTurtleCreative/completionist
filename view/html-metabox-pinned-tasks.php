<?php
/**
 * The content of the Pinned Tasks metabox with functions to view, create, pin,
 * unpin, and delete tasks for the current post.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';

$pinned_task_gids = Options::get( Options::PINNED_TASK_GID, get_the_ID() );

?>
<div id="task-list">
  <?php
  if ( is_array( $pinned_task_gids ) && ! empty( $pinned_task_gids ) ) {
    echo '<ol>';
    foreach ( $pinned_task_gids as $task_gid ) {
      echo '<li>' . esc_html( $task_gid ) . '</li>';
    }
    echo '</ol>';
  } else {
    echo '<p><i class="fas fa-clipboard-check"></i>There are no pinned tasks!</p>';
  }
  ?>
</div>

<div id="pin-a-task">

  <div id="pin-existing-task">
    <input id="asana-task-link-url" name="asana_task_link_url" type="url" placeholder="Paste a task link...">
    <button id="submit-pin-existing" class="ptc-icon-button" type="button"><i class="fas fa-thumbtack"></i></button>
  </div>

  <button id="toggle-create-new" class="ptc-icon-button" type="button"><i class="fas fa-plus"></i>New Task</button>

  <div id="pin-new-task" style="display:none;">
    <label>Title:</label>
    <input type="text">
    <label>Description:</label>
    <textarea></textarea>
    <label>Due:</label>
    <input type="date">
    <label>Assignee:</label>
    <select>
      <option>Placeholder</option>
    </select>
    <button id="submit-create-new" class="ptc-icon-button" type="button"><i class="fas fa-plus"></i>Create Task</button>
  </div>

</div>
<?php
