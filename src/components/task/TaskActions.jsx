export default function TaskActions({taskGID}) {
	/*
	@TODO: useState vars for component state
	- isProcessing, which action for button loader animation and processing lock down
	*/

	const task_url = get_asana_task_url(taskGID);

	// @TODO: useMemo to memoize function definition.
	const handleUnpinTask = (taskGID) => {

	};

	// @TODO: useMemo to memoize function definition.
	const handleDeleteTask = (taskGID) => {

	};

	return (
		<div className="ptc-TaskActions">
			<a href={task_url} target="_asana">
				<button title="View in Asana" className="view-task" type="button">
					<i className="fas fa-link"></i>
				</button>
			</a>
			<button title="Unpin" className="unpin-task" type="button" onClick={handleUnpinTask(taskGID)}>
				<i className="fas fa-thumbtack"></i>
			</button>
			<button title="Delete" className="delete-task" type="button" onClick={handleDeleteTask(taskGID)}>
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
