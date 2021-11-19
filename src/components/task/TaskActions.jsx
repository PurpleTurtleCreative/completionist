import { getTaskUrl } from './taskUtil.jsx';

import { TaskContext } from './TaskContext.jsx';

const { useState, useCallback, useContext } = wp.element;

export default function TaskActions({taskGID}) {
	const [isProcessing, setIsProcessing] = useState(false);
	const {tasks, test} = useContext(TaskContext);
	// console.log('TaskActions context:', tasks);

	const handleUnpinTask = useCallback((taskGID) => {
		console.log(`@TODO - Handle unpin task ${taskGID}`);
	}, []);

	const handleDeleteTask = useCallback((taskGID) => {
		console.log(`@TODO - Handle delete task ${taskGID}`);
		test();
		console.log('After handleDeleteTask:', tasks);
	}, [tasks, test]);

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
