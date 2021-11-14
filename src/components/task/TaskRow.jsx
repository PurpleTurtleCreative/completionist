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
		if ( window.PTC.users[ task.assignee.gid ] ) {
			assigneeDisplayName = window.PTC.users[ task.assignee.gid ].data.display_name;
		} else {
			assigneeDisplayName = '(Not Connected)';
		}
	}

	let ctaButton = null;
	if ( task.action_link ) {
		ctaButton = (
			<div className="cta-button">
				<a href={task.action_link.href} target={task.action_link.target}>
					{task.action_link.label}
					<i className="fas fa-long-arrow-alt-right"></i>
				</a>
			</div>
		);
	}

	return (
		<div className="ptc-TaskRow">

			<button title="Mark Complete" className="mark-complete" type="button" onClick={() => handleMarkComplete(task.gid)}>
				<i className="fas fa-check"></i>
			</button>

			<div className="name" onClick={handleToggleDescription}>
				{task.name}
				{task.notes && <i className={`${notesIconClassName} fa-sticky-note`}></i>}
			</div>

			<div className="details">
				{assigneeDisplayName && <div className="assignee">{assigneeDisplayName}</div>}
				{task.due_on && <div className="due"><i className="fas fa-clock"></i>{task.due_on}</div>}
			</div>

			{showDescription && <div className="description">{task.notes}</div>}

			<TaskActions taskGID={task.gid} />

			{ctaButton}

		</div>
	);
}
