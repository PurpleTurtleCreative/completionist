<?php
/**
 * The content of the Pinned Tasks metabox.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';
require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';

try {

	Asana_Interface::require_settings();
	$asana = Asana_Interface::get_client();

	if ( ! Asana_Interface::is_workspace_member() ) {
		throw new \Exception( 'You are not a member of the assigned Asana Workspace.', 403 );
	}

	$workspace_user_options = Asana_Interface::get_workspace_user_options();
	$projects = Asana_Interface::get_workspace_project_options();

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
					foreach ( $workspace_user_options as $user_gid => $option_label ) {
						printf(
							'<option value="%s">%s</option>',
							esc_attr( $user_gid ),
							esc_html( $option_label )
						);
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
					foreach ( $projects as $gid => $name ) {
						printf(
							'<option value="%s">%s</option>',
							esc_attr( $gid ),
							esc_html( $name )
						);
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
} catch ( \PTC_Completionist\Errors\No_Authorization $e ) {
	/* User is not authenticated for API usage. */
	require_once PLUGIN_PATH . 'src/admin/class-admin-pages.php';
	?>
	<div class="note-box note-box-error">
		<p>
			<strong>Not authorized.</strong>
			<br>
			Please connect your Asana account to use Completionist.
			<?php echo HTML_Builder::format_note_box_cta_button( Admin_Pages::get_settings_url(), 'Go to Settings' ); ?>
		</p>
		<div class="note-box-dismiss">
			<i class="fas fa-times"></i>
		</div>
	</div>
	<?php
} catch ( \Exception $e ) {
	echo HTML_Builder::format_error_box( $e, 'Feature unavailable. ' );//phpcs:ignore WordPress.Security.EscapeOutput
}//end try catch asana client
