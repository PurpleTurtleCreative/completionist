import { TaskContext } from './TaskContext.jsx';

const { useState, useCallback, useMemo, useContext, useEffect } = wp.element;

export default function TaskFilters({tasks, onChange}) {
	const [activeFilter, setActiveFilter] = useState('none');
	const { filterIncompleteTasks, filterCriticalTasks, filterMyTasks, filterGeneralTasks, filterPinnedTasks } = useContext(TaskContext);

	const filters = useMemo(() => {

		const incompleteTasks = filterIncompleteTasks(tasks);

		return [
			{
				"key": 'none',
				"title": 'All Tasks',
				"tasks": incompleteTasks
			},
			{
				"key": 'pinned',
				"title": 'Pinned',
				"tasks": filterPinnedTasks(incompleteTasks)
			},
			{
				"key": 'general',
				"title": 'General',
				"tasks": filterGeneralTasks(incompleteTasks)
			},
			{
				"key": 'myTasks',
				"title": 'My Tasks',
				"tasks": filterMyTasks(window.PTCCompletionist.me.gid, incompleteTasks)
			},
			{
				"key": 'critical',
				"title": 'Critical',
				"tasks": filterCriticalTasks(incompleteTasks)
			},
		]
	}, [tasks]);

	useEffect(() => {
		const filteredTasks = filters.find(f => activeFilter === f.key).tasks;
		onChange(activeFilter, filteredTasks);
	}, [filters, activeFilter, onChange]);

	const handleClickFilter = useCallback((key, filteredTasks) => {
		setActiveFilter(key);
		onChange(key, filteredTasks);
	}, [activeFilter, setActiveFilter, onChange]);

	const renderedFilterButtons = filters.map(f => {
		let className = `filter-${f.key}`;
		if ( activeFilter === f.key ) {
			className += ' --is-active';
		}
		return (
			<button key={f.key} type="button" className={className} onClick={() => handleClickFilter(f.key, f.tasks)}>{`${f.title} (${f.tasks.length})`}</button>
		);
	});

	return (
		<div className="ptc-TaskFilters">
			{renderedFilterButtons}
		</div>
	);
}
