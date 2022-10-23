// import TaskRow from './TaskRow.jsx';

// import '/assets/styles/scss/components/task/_TaskList.scss';

// const { useState } = wp.element;

export default function ProjectDetails({ project }) {

	let maybeName = null;
	if ( 'name' in project && project.name ) {
		maybeName = <h2 className="name">{project.name}</h2>;
	}

	let maybeDescription = null;
	if ( 'html_notes' in project && project.html_notes ) {
		maybeDescription = (
			<p
				className="description"
				dangerouslySetInnerHTML={ { __html: project.html_notes } }
			/>
		);
	}

	let maybeModifiedAt = null;
	if ( 'modified_at' in project && project.modified_at ) {
		const dateTime = new Date( project.modified_at );
		maybeModifiedAt = <p className="modified">{'Last modified: '+dateTime.toLocaleString()}</p>;
	}

	return (
		<div className="ptc-ProjectDetails">
			{maybeName}
			{maybeModifiedAt}
			{maybeDescription}
		</div>
	);
}
