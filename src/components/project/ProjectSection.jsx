// import TaskRow from './TaskRow.jsx';

// import '/assets/styles/scss/components/task/_TaskList.scss';

// const { useState } = wp.element;

export default function ProjectSection({ section }) {

	let maybeName = null;
	if ( 'name' in section && section.name ) {
		maybeName = <h3 className="name">{section.name}</h3>;
	}

	let maybeTasksList = null;
	if ( 'tasks' in section && section.tasks && section.tasks.length > 0 ) {
		maybeTasksList = (
			<ul className="tasks">
				{section.tasks.map(task => <li>{task.name}</li>)}
			</ul>
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
