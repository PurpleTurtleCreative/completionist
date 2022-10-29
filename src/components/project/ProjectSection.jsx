import TaskListItem from '../task/TaskListItem.jsx';

import '/assets/styles/scss/components/project/_ProjectSection.scss';

export default function ProjectSection({ section }) {

	let maybeName = null;
	if ( 'name' in section && section.name ) {
		maybeName = <h3 className="section-name">{section.name}</h3>;
	}

	let maybeTasksList = null;
	if ( 'tasks' in section && section.tasks && section.tasks.length > 0 ) {
		maybeTasksList = (
			<ol className="tasks">
				{section.tasks.map(( task, index ) => <TaskListItem task={task} rowNumber={index+1} />)}
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
