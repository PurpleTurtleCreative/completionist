import TaskActions from './TaskActions.jsx';

const { useState, useCallback } = wp.element;

export default function TaskRow({task}) {
	const [showDescription, setShowDescription] = useState(false);

	const handleMarkComplete = useCallback((taskGID) => {
		console.log(`@TODO - Handle mark complete for task ${taskGID}`);
	}, []);

	const handleToggleDescription = useCallback(() => {
		if ( ! task.notes ) {
			return;
		}
		setShowDescription(!showDescription);
	}, [task, showDescription, setShowDescription]);

	const notesIconClassName = ( showDescription ) ? 'fas' : 'far';

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
				{task.assignee && <div className="assignee">{task.assignee.gid}</div>}
				{task.due_on && <div className="due"><i className="fas fa-clock"></i>{task.due_on}</div>}
			</div>

			{showDescription && <div className="description">{task.notes}</div>}

			<TaskActions taskGID={task.gid} />

			<div className="cta-button">
				{/* @TODO: Either view in Asana or edit pinned post. */}
				<a href="#TODO" target="">
					@TODO
					<i className="fas fa-long-arrow-alt-right"></i>
				</a>
			</div>
		</div>
	);
}
