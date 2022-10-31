import ProjectStatus from './ProjectStatus.jsx';

import { getLocaleString } from '../generic/util.jsx';

import '/assets/styles/scss/components/project/_ProjectDetails.scss';

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

	let maybeDueOn = null;
	if ( 'due_on' in details && details.due_on ) {
		const dateTimeString = new Date( details.due_on ).toLocaleDateString(
			undefined,
			{
				dateStyle: 'long',
				timeZone: 'UTC',
			}
		);
		maybeDueOn = (
			<p className="due">
				<span>Due</span>
				{dateTimeString}
			</p>
		);
	}

	let maybeModifiedAt = null;
	if ( 'modified_at' in details && details.modified_at ) {
		const dateTimeString = getLocaleString( details.modified_at );
		maybeModifiedAt = (
			<p className="modified">
				<span>Modified</span>
				{dateTimeString}
			</p>
		);
	}

	let maybeCurrentStatus = null;
	if ( 'current_status' in details && details.current_status ) {
		maybeCurrentStatus = <ProjectStatus {...details.current_status} />;
	}

	return (
		<div className="ptc-ProjectDetails">
			{maybeName}
			<div className="row">
				{maybeDueOn}
				{maybeModifiedAt}
			</div>
			{maybeDescription}
			{maybeCurrentStatus}
		</div>
	);
}
