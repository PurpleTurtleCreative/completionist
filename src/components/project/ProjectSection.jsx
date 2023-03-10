/**
 * ProjectSection component
 *
 * @since 3.4.0
 */

import TaskListItem from '../task/TaskListItem.jsx';

import '../../../assets/styles/scss/components/project/_ProjectSection.scss';

export default function ProjectSection({ name, tasks }) {

	let maybeName = null;
	if ( name ) {
		maybeName = <h3 className="section-name">{name}</h3>;
	}

	let maybeTasksList = null;
	if ( tasks && tasks.length > 0 ) {
		maybeTasksList = (
			<ol className="tasks">
				{tasks.map(( task, index ) => <TaskListItem task={task} rowNumber={index+1} />)}
			</ol>
		);
	} else {
		maybeTasksList = <p className="ptc-no-results">No tasks</p>;
	}

	return (
		<div className="ptc-ProjectSection">
			{maybeName}
			{maybeTasksList}
		</div>
	);
}
