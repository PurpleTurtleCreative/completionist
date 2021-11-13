import { getTaskUrl } from './taskUtil.jsx';

const { useState, useCallback } = wp.element;

export default function TaskActions({taskGID}) {
	const [isProcessing, setIsProcessing] = useState(false);

	const handleUnpinTask = useCallback((taskGID) => {
		console.log(`@TODO - Handle unpin task ${taskGID}`);
	}, []);

	const handleDeleteTask = useCallback((taskGID) => {
		console.log(`@TODO - Handle delete task ${taskGID}`);
	}, []);

	const task_url = getTaskUrl(taskGID);

	return (
		<div className="ptc-TaskActions">
			<a href={task_url} target="_asana">
				<button title="View in Asana" className="view-task" type="button">
					<i className="fas fa-link"></i>
				</button>
			</a>
			<button title="Unpin" className="unpin-task" type="button" onClick={() => handleUnpinTask(taskGID)}>
				<i className="fas fa-thumbtack"></i>
			</button>
			<button title="Delete" className="delete-task" type="button" onClick={() => handleDeleteTask(taskGID)}>
				<i className="fas fa-minus"></i>
			</button>
		</div>
	);
}
