import TaskRow from './TaskRow.jsx';

export default function TaskList({tasks}) {
	const renderedTasks = tasks.map( t => <TaskRow key={t.gid} task={t} />);
	return <div className="ptc-asana-task-list">{renderedTasks}</div>;
}
