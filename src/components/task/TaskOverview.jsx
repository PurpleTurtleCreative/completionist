import { filterIncompleteTasks } from './taskUtil.jsx';

const { useMemo } = wp.element;

export default function TaskOverview({tasks}) {

	const incompleteTasks = useMemo(() => filterIncompleteTasks(tasks), [tasks]);

	const completedCount = tasks.length - incompleteTasks.length;
	const completedPercent = Math.round( ( completedCount / tasks.length ) * 100 );

	return (
		<div className="ptc-TaskOverview">
			<div className="feature">
				<p>{completedPercent}%</p>
				<p>Complete</p>
			</div>
			<div className="details">
				<p><span className="task-count">{incompleteTasks.length}</span> Remaining</p>
				<div>
					<div className="progress-bar-wrapper">
						<div className="progress-bar" style={{width: `${completedPercent}%`}}></div>
					</div>
					<p>Completed <span className="completed-tasks-count">{completedCount}</span> of <span className="total-tasks-count">{tasks.length}</span></p>
				</div>
			</div>
		</div>
	);
}
