import { TaskContext } from './TaskContext.jsx';
import {
	getWorkspaceProjectSelectOptions,
	getWorkspaceUserSelectOptions
} from './util';

import '../../../assets/styles/scss/components/task/_TaskCreationForm.scss';

const { useContext, useState, useMemo } = wp.element;

export default function TaskCreationForm({ postId = null }) {
	const [ isProcessing, setIsProcessing ] = useState(false);
	const [ taskName, setTaskName ] = useState('');
	const [ taskAssigneeGID, setTaskAssigneeGID ] = useState('');
	const [ taskDueDate, setTaskDueDate ] = useState('');
	const [ taskProjectGID, setTaskProjectGID ] = useState('');
	const [ taskNotes, setTaskNotes ] = useState('');
	const { createTask } = useContext(TaskContext);

	const workspaceProjectSelectOptions = useMemo(getWorkspaceProjectSelectOptions, []);
	const workspaceUserSelectOptions = useMemo(getWorkspaceUserSelectOptions, []);

	const handleFormSubmit = (event) => {

		event.preventDefault();
		if ( isProcessing ) {
			return;
		}

		setIsProcessing(true);

		const taskData = {
			'name': taskName,
			'assignee': taskAssigneeGID,
			'due_on': taskDueDate,
			'project': taskProjectGID,
			'notes': taskNotes,
		};

		createTask(taskData, postId).then(success => {
			if ( success ) {
				setTaskName('');
				setTaskAssigneeGID('');
				setTaskDueDate('');
				setTaskProjectGID('');
				setTaskNotes('');
			}
			setIsProcessing(false);
		});
	}

	const submitButtonContent = ( isProcessing )
		? <><i className="fas fa-sync-alt fa-spin" aria-hidden="true"></i>Creating task...</>
		: <><i className="fas fa-plus" aria-hidden="true"></i>Add Task</>;

	return (
		<form className="ptc-TaskCreationForm" onSubmit={handleFormSubmit} disabled={isProcessing}>

			<input id="ptc-new-task_name" type="text" placeholder="Write a task name..." value={taskName} onChange={e => setTaskName(e.target.value)} disabled={isProcessing} required />

			<div className="form-group">
				<label htmlFor="ptc-new-task_assignee">Assignee</label>
				<select id="ptc-new-task_assignee" value={taskAssigneeGID} onChange={e => setTaskAssigneeGID(e.target.value)} disabled={isProcessing}>
					<option value="">None (Unassigned)</option>
					{workspaceUserSelectOptions}
				</select>
			</div>

			<div className="form-group">
				<label htmlFor="ptc-new-task_due_on">Due Date</label>
				<input id="ptc-new-task_due_on" type="date" pattern="\d\d\d\d-\d\d-\d\d" placeholder="yyyy-mm-dd" value={taskDueDate} onChange={e => setTaskDueDate(e.target.value)} disabled={isProcessing} />
			</div>

			<div className="form-group">
				<label htmlFor="ptc-new-task_project">Project</label>
				<select id="ptc-new-task_project" value={taskProjectGID} onChange={e => setTaskProjectGID(e.target.value)} disabled={isProcessing}>
					<option value="">None (Private Task)</option>
					{workspaceProjectSelectOptions}
				</select>
			</div>

			<div className="form-group">
				<label htmlFor="ptc-new-task_notes">Description</label>
				<textarea id="ptc-new-task_notes" value={taskNotes} onChange={e => setTaskNotes(e.target.value)} disabled={isProcessing}></textarea>
			</div>

			<button type="submit" disabled={isProcessing}>{submitButtonContent}</button>

		</form>
	);
}
