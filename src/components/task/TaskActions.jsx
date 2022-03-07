import { TaskContext } from './TaskContext.jsx';
import { getTaskUrl } from './util';

const { useCallback, useContext } = wp.element;

export default function TaskActions({taskGID, processingStatus}) {
	const { deleteTask, unpinTask, removeTask, setTaskProcessingStatus } = useContext(TaskContext);

	const handleUnpinTask = useCallback((taskGID) => {
		if ( processingStatus ) {
			console.error(`Rejected handleUnpinTask. Currently ${processingStatus} task ${taskGID}.`);
			return;
		}
		setTaskProcessingStatus(taskGID, 'unpinning');
		unpinTask(taskGID).then(success => {
			if ( ! success ) {
				// Only set processing status if task wasn't successfully removed.
				setTaskProcessingStatus(taskGID, false);
			}
		});
	}, [processingStatus, setTaskProcessingStatus, unpinTask]);

	const handleDeleteTask = useCallback((taskGID) => {
		if ( processingStatus ) {
			console.error(`Rejected handleDeleteTask. Currently ${processingStatus} task ${taskGID}.`);
			return;
		}
		setTaskProcessingStatus(taskGID, 'deleting');
		deleteTask(taskGID).then(success => {
			if ( ! success ) {
				// Only set processing status if task wasn't removed.
				setTaskProcessingStatus(taskGID, false);
			}
		});
	}, [processingStatus, setTaskProcessingStatus, removeTask]);

	const task_url = getTaskUrl(taskGID);

	const unpinIcon = ('unpinning' === processingStatus) ? 'fa-sync-alt fa-spin' : 'fa-thumbtack';
	const deleteIcon = ('deleting' === processingStatus) ? 'fa-sync-alt fa-spin' : 'fa-minus';

	return (
		<div className="ptc-TaskActions">
			<a href={task_url} target="_asana">
				<button title="View in Asana" className="view" type="button">
					<i className="fas fa-link"></i>
				</button>
			</a>
			<button title="Unpin from Site" className="unpin" type="button" onClick={() => handleUnpinTask(taskGID)} disabled={!!processingStatus}>
				<i className={`fas ${unpinIcon}`}></i>
			</button>
			<button title="Delete from Asana" className="delete" type="button" onClick={() => handleDeleteTask(taskGID)} disabled={!!processingStatus}>
				<i className={`fas ${deleteIcon}`}></i>
			</button>
		</div>
	);
}
