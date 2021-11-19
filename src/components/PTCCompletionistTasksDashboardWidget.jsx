import TaskOverview from './task/TaskOverview.jsx';
import TaskFilters from './task/TaskFilters.jsx';
import TaskListPaginated from './task/TaskListPaginated.jsx';

import { TaskContextProvider } from './task/TaskContext.jsx';

const { useContext, useState } = wp.element;

export default function PTCCompletionistTasksDashboardWidget({tasks}) {
	const [visibleTasks, setVisibleTasks] = useState(tasks);

	return (
		<TaskContextProvider>
			<div className="ptc-PTCCompletionistTasksDashboardWidget">
				<TaskOverview tasks={tasks} />
				<TaskFilters tasks={tasks} onChange={(_key, selectedTasks) => setVisibleTasks(selectedTasks)} />
				<TaskListPaginated limit={3} tasks={visibleTasks} />
			</div>
		</TaskContextProvider>
	);
}
