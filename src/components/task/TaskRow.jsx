import { isCriticalTask } from './taskUtil.jsx';

import TaskActions from './TaskActions.jsx';

const { useState, useCallback } = wp.element;

export default function TaskRow({task}) {
	const [showDescription, setShowDescription] = useState(false);

	const handleMarkComplete = useCallback((taskGID) => {
		console.warn(`@TODO - Handle mark complete for task ${taskGID}`);
	}, []);

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

	return (
		<div className={"ptc-TaskRow"+extraClassNames}>

			<button title="Mark Complete" className="mark-complete" type="button" onClick={() => handleMarkComplete(task.gid)}>
				<i className="fas fa-check"></i>
			</button>

			<div className="body">

				<p className="name" onClick={handleToggleDescription}>{task.name}{task.notes && <i className={`${notesIconClassName} fa-sticky-note`}></i>}</p>

				<div className="details">
					{assigneeDisplayName && <p className="assignee"><i class="fas fa-user"></i> {assigneeDisplayName}</p>}
					{task.due_on && <p className="due"><i className="fas fa-clock"></i> {new Date(task.due_on).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})}</p>}
				</div>

				{showDescription && <p className="description">{task.notes}</p>}

			</div>

			<div className="actions">
				<a className="cta-button" href={task.action_link.href} target={task.action_link.target}>{task.action_link.label} <i className="fas fa-long-arrow-alt-right"></i></a>
				<TaskActions taskGID={task.gid} />
			</div>

		</div>
	);
}
