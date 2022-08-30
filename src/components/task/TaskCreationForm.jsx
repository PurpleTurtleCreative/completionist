// import { TaskContext } from './TaskContext.jsx';
import {
	getAssigneeDisplayName,
	getWorkspaceProjectSelectOptions,
	getWorkspaceUserSelectOptions
} from './util';

const { useMemo } = wp.element;

export default function TaskCreationForm({ postId = null }) {

	const workspaceProjectSelectOptions = useMemo(getWorkspaceProjectSelectOptions, []);
	const workspaceUserSelectOptions = useMemo(getWorkspaceUserSelectOptions, []);

	const handleFormSubmit = (event) => {
		event.preventDefault();
		window.console.log(`== Submitted TaskCreationForm for postId ${postId} ==`);
		window.console.log(event);
	}

	return (
		<form className="ptc-TaskCreationForm" onSubmit={handleFormSubmit}>

			<input id="ptc-new-task_name" type="text" placeholder="Write a task name..." required />

			<div className="form-group">
				<label for="ptc-new-task_assignee">Assignee</label>
				<select id="ptc-new-task_assignee">
					<option value="">None (Unassigned)</option>
					{workspaceUserSelectOptions}
				</select>
			</div>

			<div className="form-group">
				<label for="ptc-new-task_due_on">Due Date</label>
				<input id="ptc-new-task_due_on" type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" />
			</div>

			<div className="form-group">
				<label for="ptc-new-task_project">Project</label>
				<select id="ptc-new-task_project">
					<option value="">None (Private Task)</option>
					{workspaceProjectSelectOptions}
				</select>
			</div>

			<div className="form-group">
				<label for="ptc-new-task_notes">Description</label>
				<textarea id="ptc-new-task_notes"></textarea>
			</div>

			<button type="submit"><i className="fas fa-plus" aria-hidden="true"></i>Add Task</button>

		</form>
	);
}
