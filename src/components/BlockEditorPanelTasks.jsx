import TaskPinToPostBar from './task/TaskPinToPostBar.jsx';
import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContext } from './task/TaskContext.jsx';

import '/assets/styles/scss/components/BlockEditorPanelTasks.scss';

const { useContext } = wp.element;

export default function BlockEditorPanelTasks() {
	const { tasks } = useContext(TaskContext);

	const currentPostId = wp.data.select('core/editor').getCurrentPostId();

	return (
		<div className="ptc-BlockEditorPanelTasks">
			<TaskPinToPostBar postId={currentPostId} />
			<TaskListPaginated limit={3} tasks={tasks} />
		</div>
	);
}
