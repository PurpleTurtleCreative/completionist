import { TaskContext } from './TaskContext.jsx';

const { useState, useCallback, useContext } = wp.element;

export default function TaskActions({taskGID}) {
	const [isProcessing, setIsProcessing] = useState(false);
	const { getTaskUrl, deleteTask, unpinTask, removeTask } = useContext(TaskContext);

	const handleUnpinTask = useCallback((taskGID) => {
		// @TODO: Loading state handling.
		unpinTask(taskGID);
	}, [unpinTask]);

	const handleDeleteTask = useCallback((taskGID) => {
		// @TODO: Loading state handling.
		// deleteTask(taskGID);
		removeTask(taskGID);
	}, [removeTask]);

	const task_url = getTaskUrl(taskGID);

	return (
		<div className="ptc-TaskActions">
			<a href={task_url} target="_asana">
				<button title="View in Asana" className="view" type="button">
					<i className="fas fa-link"></i>
				</button>
			</a>
			<button title="Unpin from Site" className="unpin" type="button" onClick={() => handleUnpinTask(taskGID)}>
				<i className="fas fa-thumbtack"></i>
			</button>
			<button title="Delete from Asana" className="delete" type="button" onClick={() => handleDeleteTask(taskGID)}>
				<i className="fas fa-minus"></i>
			</button>
		</div>
	);
}
