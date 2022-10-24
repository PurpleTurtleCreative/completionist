import TaskListItem from '../task/TaskListItem.jsx';

import '/assets/styles/scss/components/project/_ProjectSection.scss';

// const { useState } = wp.element;

export default function ProjectSection({ section }) {

	let maybeName = null;
	if ( 'name' in section && section.name ) {
		maybeName = <h3 className="section-name">{section.name}</h3>;
	}

	let maybeTasksList = null;
	if ( 'tasks' in section && section.tasks && section.tasks.length > 0 ) {
		maybeTasksList = (
			<ol className="tasks">
				{section.tasks.map(task => <TaskListItem task={task} />)}
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
