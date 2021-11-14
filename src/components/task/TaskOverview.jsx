import { filterIncompleteTasks } from './taskUtil.jsx';

const { useMemo } = wp.element;

export default function TaskOverview({tasks}) {

	const incompleteTasks = useMemo(() => filterIncompleteTasks(tasks), [tasks]);

	return (
		<div className="ptc-TaskOverview">
			<div class="task-box-icon">
				<i class="fas fa-clipboard-list"></i>
			</div>
			<div class="task-box-data">
				<p><span class="task-count">{incompleteTasks.length}</span> Tasks</p>
				<div>
					<div class="progress-bar-wrapper">
						<div class="progress-bar"></div>
					</div>
					<p><span class="completed-tasks-count">{tasks.length - incompleteTasks.length}</span> of <span class="total-tasks-count">{tasks.length}</span></p>
				</div>
			</div>
		</div>
	);
}
