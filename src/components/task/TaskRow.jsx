import TaskActions from './TaskActions.jsx';

export default function TaskRow({task}) {
	return (
		<div className="ptc-TaskRow">

			<button title="Mark Complete" className="mark-complete" type="button">
				<i className="fas fa-check"></i>
			</button>

			<div className="name">
				{task.name}
				{task.notes && <i className="far fa-sticky-note"></i>}
			</div>

			<div className="details">
				{task.assignee && <div className="assignee">{task.assignee.gid}</div>}
				{task.due_on && <div className="due"><i className="fas fa-clock"></i>{task.due_on}</div>}
			</div>

			{task.notes && <div className="description">{task.notes}</div>}

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
