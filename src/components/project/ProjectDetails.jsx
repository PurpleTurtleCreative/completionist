import ProjectStatus from './ProjectStatus.jsx';

import { getLocaleString } from '../generic/util.jsx';

export default function ProjectDetails({ details }) {

	let maybeName = null;
	if ( 'name' in details && details.name ) {
		maybeName = <h2 className="project-name">{details.name}</h2>;
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
		const dateTimeString = getLocaleString( details.modified_at );
		maybeModifiedAt = <p className="modified">{'Last modified: '+dateTimeString}</p>;
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
