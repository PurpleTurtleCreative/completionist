import TaskRow from './TaskRow.jsx';

export default function TaskList({tasks}) {

	let listContent = <p className="ptc-no-results"><i className="fas fa-clipboard-check"></i>No tasks to display.</p>;

	if ( tasks.length > 0 ) {
		listContent = tasks.map(t => <TaskRow key={t.gid} task={t} />);
	}

	return <div className="ptc-TaskList">{listContent}</div>;
}
