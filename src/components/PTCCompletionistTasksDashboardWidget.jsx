import TaskList from './task/TaskList.jsx';

export default function PTCCompletionistTasksDashboardWidget() {
	return <TaskList tasks={window.PTC.tasks} />;
}
