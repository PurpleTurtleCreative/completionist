import TaskOverview from './task/TaskOverview.jsx';
import TaskFilters from './task/TaskFilters.jsx';
import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContext } from './task/TaskContext.jsx';
import { filterIncompleteTasks } from './task/util';

const { useContext, useCallback, useState, useEffect } = wp.element;

export default function PTCCompletionistTasksDashboardWidget() {
	const { tasks } = useContext(TaskContext);
	const [visibleTasks, setVisibleTasks] = useState(filterIncompleteTasks(tasks));

	// When tasks change from being deleted, completed, or otherwise.
	useEffect(() => setVisibleTasks(filterIncompleteTasks(tasks)), [tasks, setVisibleTasks]);

	const handleFilterChange = useCallback((_key, selectedTasks) => setVisibleTasks(selectedTasks), []);

	return (
		<div className="ptc-PTCCompletionistTasksDashboardWidget">
			<TaskOverview tasks={tasks} />
			<TaskFilters tasks={tasks} onChange={handleFilterChange} />
			<TaskListPaginated limit={5} tasks={visibleTasks} />
		</div>
	);
}
