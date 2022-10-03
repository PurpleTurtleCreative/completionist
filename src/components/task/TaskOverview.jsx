import { TaskContext } from './TaskContext.jsx';
import { filterIncompleteTasks } from './util';

import '/assets/styles/scss/components/task/TaskOverview.scss';

const { useContext, useMemo } = wp.element;

export default function TaskOverview() {
	const { tasks } = useContext(TaskContext);

	const incompleteTasks = useMemo(() => filterIncompleteTasks(tasks), [tasks]);

	const completedCount = tasks.length - incompleteTasks.length;

	let completedPercent = 0;
	if ( tasks.length > 0 ) {
		completedPercent = Math.round( ( completedCount / tasks.length ) * 100 );
	}

	return (
		<div className="ptc-TaskOverview">

			<div className="feature">
				<p className="large">{completedPercent}<span className="small">%</span></p>
				<p className="caption">Complete</p>
			</div>

			<div className="details">

				<p className="incomplete">
					<span className="count">{incompleteTasks.length}</span> Remaining
				</p>

				<div className="progress">
					<div className="progress-bar-wrapper">
						<div className="progress-bar" style={{width: `${completedPercent}%`}}></div>
					</div>
					<p className="caption">
						<span className="completed">Completed {completedCount}</span> of {tasks.length}
					</p>
				</div>

			</div>

		</div>
	);
}
