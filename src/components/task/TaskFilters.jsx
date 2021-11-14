import { filterCriticalTasks, filterMyTasks, filterGeneralTasks, filterPinnedTasks } from './taskUtil.jsx';

const { useState, useCallback, useMemo } = wp.element;

export default function TaskFilters({tasks, onChange}) {
	const [activeFilter, setActiveFilter] = useState('none');

	const filters = useMemo(() => {
		return [
			{
				"key": 'none',
				"title": 'All Tasks',
				"tasks": tasks
			},
			{
				"key": 'pinned',
				"title": 'Pinned',
				"tasks": filterPinnedTasks(tasks)
			},
			{
				"key": 'general',
				"title": 'General',
				"tasks": filterGeneralTasks(tasks)
			},
			{
				"key": 'myTasks',
				"title": 'My Tasks',
				"tasks": filterMyTasks(window.PTC.me.gid, tasks)
			},
			{
				"key": 'critical',
				"title": 'Critical',
				"tasks": filterCriticalTasks(tasks)
			},
		]
	}, [tasks]);

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
			<button key={f.key} type="button" className={className} onClick={() => handleClickFilter(f.key, f.tasks)} style={{width: 'auto'}}>
				{f.title} <span class="task-count">({f.tasks.length})</span>
			</button>
		);
	});

	return (
		<div className="ptc-TaskFilters">
			{renderedFilterButtons}
		</div>
	);
}
