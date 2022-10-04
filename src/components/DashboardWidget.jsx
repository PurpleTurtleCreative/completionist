import TaskOverview from './task/TaskOverview.jsx';
import TaskFilters from './task/TaskFilters.jsx';
import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContext } from './task/TaskContext.jsx';
import { filterIncompleteTasks } from './task/util';

import '/assets/styles/scss/components/_DashboardWidget.scss';

const { useContext, useCallback, useState, useEffect } = wp.element;

export default function DashboardWidget() {
	const { tasks } = useContext(TaskContext);
	const [visibleTasks, setVisibleTasks] = useState(filterIncompleteTasks(tasks));

	const handleFilterChange = useCallback((_key, selectedTasks) => setVisibleTasks(selectedTasks), []);

	return (
		<div className="ptc-DashboardWidget">
			<TaskOverview tasks={tasks} />
			<TaskFilters tasks={tasks} onChange={handleFilterChange} />
			<TaskListPaginated limit={5} tasks={visibleTasks} />
		</div>
	);
}
