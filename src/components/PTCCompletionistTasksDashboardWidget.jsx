import TaskListPaginated from './task/TaskListPaginated.jsx';
import TaskFilters from './task/TaskFilters.jsx';

const { useState } = wp.element;

export default function PTCCompletionistTasksDashboardWidget({tasks}) {
	const [visibleTasks, setVisibleTasks] = useState(tasks);

	return (
		<div className="ptc-PTCCompletionistTasksDashboardWidget">
		<TaskFilters tasks={tasks} onChange={(_key, selectedTasks) => setVisibleTasks(selectedTasks)} />
		<TaskListPaginated limit={3} tasks={visibleTasks} />
		</div>
	);
}
