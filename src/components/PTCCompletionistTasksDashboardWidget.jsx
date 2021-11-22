import TaskOverview from './task/TaskOverview.jsx';
import TaskFilters from './task/TaskFilters.jsx';
import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContext } from './task/TaskContext.jsx';

const { useContext, useState, useEffect } = wp.element;

export default function PTCCompletionistTasksDashboardWidget() {
	const { tasks } = useContext(TaskContext);
	const [visibleTasks, setVisibleTasks] = useState(tasks);

	// @TODO: still having rendering issues with TaskActions being updated...
	useEffect(() => setVisibleTasks(tasks), [tasks, setVisibleTasks]);

	return (
		<div className="ptc-PTCCompletionistTasksDashboardWidget">
			<TaskOverview tasks={tasks} />
			<TaskFilters tasks={tasks} onChange={(_key, selectedTasks) => setVisibleTasks(selectedTasks)} />
			<TaskListPaginated limit={3} tasks={visibleTasks} />
		</div>
	);
}
