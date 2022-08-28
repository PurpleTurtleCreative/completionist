import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContext } from './task/TaskContext.jsx';

import '/assets/styles/scss/components/BlockEditorPanelTasks.scss';

const { useContext } = wp.element;

export default function BlockEditorPanelTasks() {
	const { tasks } = useContext(TaskContext);

	return (
		<div className="ptc-BlockEditorPanelTasks">
			<TaskListPaginated limit={3} tasks={tasks} />
		</div>
	);
}
