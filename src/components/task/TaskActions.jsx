export default function TaskActions({taskGID}) {

	const task_url = get_asana_task_url(taskGID);

	return (
		<div className="ptc-TaskActions">
			<a href={task_url} target="_asana">
				<button title="View in Asana" className="view-task" type="button">
					<i className="fas fa-link"></i>
				</button>
			</a>
			<button title="Unpin" className="unpin-task" type="button">
				<i className="fas fa-thumbtack"></i>
			</button>
			<button title="Delete" className="delete-task" type="button">
				<i className="fas fa-minus"></i>
			</button>
		</div>
	);
}

export function delete_task(taskGID) {
	console.log(`@TODO: Delete task ${taskGID}`);
}

export function unpin_task(taskGID) {
	console.log(`@TODO: Unpin task ${taskGID}`);
}

export function get_asana_task_url(taskGID) {
	return `https://app.asana.com/0/0/${taskGID}/f`;
}
