export default function TaskRow({task}) {
	return (
		<section className="ptc-completionist-task">

			<button title="Mark Complete" className="mark-complete" type="button">
				<i className="fas fa-check"></i>
			</button>

			<div className="name">
				{task.name}
				{task.notes && <i className="far fa-sticky-note"></i>}
			</div>

			<div className="details">
				{task.assignee && <div className="assignee">{task.assignee.gid}</div>}
			</div>
		</section>
	);
}
