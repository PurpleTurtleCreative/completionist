import TaskListPaginated from './task/TaskListPaginated.jsx';

export default function PTCCompletionistTasksDashboardWidget() {
	return <TaskListPaginated limit={3} tasks={window.PTC.tasks} />;
}
