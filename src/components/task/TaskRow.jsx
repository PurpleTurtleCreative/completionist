import TaskActions from './TaskActions.jsx';

import { TaskContext } from './TaskContext.jsx';

const { useState, useCallback, useContext } = wp.element;

export default function TaskRow({task}) {
	const [showDescription, setShowDescription] = useState(false);
	const { isCriticalTask, completeTask, setTaskProcessingStatus } = useContext(TaskContext);

	const handleMarkComplete = useCallback((taskGID) => {
		if ( task.processingStatus ) {
			console.error(`Rejected. Currently ${task.processingStatus} task ${taskGID}.`);
			return;
		}
		setTaskProcessingStatus(taskGID, 'completing');
		completeTask(taskGID).then(success => {
			// @TODO: Handle false case. (ie. failure)
			console.log('handleMarkComplete success:', success);
			setTaskProcessingStatus(taskGID, false);
		});
	}, [task.processingStatus, completeTask]);

	const handleToggleDescription = useCallback(() => {
		if ( ! task.notes ) {
			return;
		}
		setShowDescription(!showDescription);
	}, [task, showDescription, setShowDescription]);

	const notesIconClassName = ( showDescription ) ? 'fas' : 'far';

	let assigneeDisplayName = null;
	if ( task.assignee ) {
		if ( window.PTCCompletionist.users[ task.assignee.gid ] ) {
			assigneeDisplayName = window.PTCCompletionist.users[ task.assignee.gid ].data.display_name;
		} else {
			assigneeDisplayName = '(Not Connected)';
		}
	}

	let extraClassNames = '';
	if ( isCriticalTask(task) ) {
		extraClassNames += ' --is-critical';
	}
	if ( true === task.completed ) {
		extraClassNames += ' --is-complete';
	}
	if ( task.processingStatus ) {
		extraClassNames += ` --is-processing --is-${task.processingStatus}`;
	}

	const markCompleteIcon = ('completing' === task.processingStatus) ? 'fa-sync-alt fa-spin' : 'fa-check';

	const dueOnDateString = new Date(task.due_on).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'});

	return (
		<div className={"ptc-TaskRow"+extraClassNames}>

			<button title="Mark Complete" className="mark-complete" type="button" onClick={() => handleMarkComplete(task.gid)}>
				<i className={`fas ${markCompleteIcon}`}></i>
			</button>

			<div className="body">

				<p className="name" onClick={handleToggleDescription}>{task.name}{task.notes && <i className={`${notesIconClassName} fa-sticky-note`}></i>}</p>

				<div className="details">
					{assigneeDisplayName && <p className="assignee"><i class="fas fa-user"></i> {assigneeDisplayName}</p>}
					{task.due_on && <p className="due"><i className="fas fa-clock"></i> {dueOnDateString}</p>}
				</div>

				{showDescription && <p className="description">{task.notes}</p>}

			</div>

			<div className="actions">
				<a className="cta-button" href={task.action_link.href} target={task.action_link.target}>{task.action_link.label} <i className="fas fa-long-arrow-alt-right"></i></a>
				<TaskActions taskGID={task.gid} processingStatus={task.processingStatus} />
			</div>

		</div>
	);
}
