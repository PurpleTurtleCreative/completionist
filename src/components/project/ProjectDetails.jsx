import ProjectStatus from './ProjectStatus.jsx';

// import '/assets/styles/scss/components/task/_TaskList.scss';

// const { useState } = wp.element;

export default function ProjectDetails({ details }) {

	let maybeName = null;
	if ( 'name' in details && details.name ) {
		maybeName = <h2 className="name">{details.name}</h2>;
	}

	let maybeDescription = null;
	if ( 'html_notes' in details && details.html_notes ) {
		maybeDescription = (
			<div
				className="description"
				dangerouslySetInnerHTML={ { __html: details.html_notes } }
			/>
		);
	}

	let maybeModifiedAt = null;
	if ( 'modified_at' in details && details.modified_at ) {
		const dateTime = new Date( details.modified_at );
		maybeModifiedAt = <p className="modified">{'Last modified: '+dateTime.toLocaleString()}</p>;
	}

	let maybeCurrentStatus = null;
	if ( 'current_status' in details && details.current_status ) {
		maybeCurrentStatus = <ProjectStatus {...details.current_status} />
	}

	return (
		<div className="ptc-ProjectDetails">
			{maybeName}
			{maybeModifiedAt}
			{maybeDescription}
			{maybeCurrentStatus}
		</div>
	);
}
