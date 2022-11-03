import ProjectStatus from './ProjectStatus.jsx';

import { getLocaleString } from '../generic/util.jsx';

import '/assets/styles/scss/components/project/_ProjectDetails.scss';

export default function ProjectDetails({
	completed_at,
	current_status,
	due_on,
	html_notes,
	modified_at,
	name,
}) {

	let maybeName = null;
	if ( name ) {
		maybeName = <h2 className="project-name">{name}</h2>;
	}

	let maybeDescription = null;
	if ( html_notes ) {
		maybeDescription = (
			<div
				className="description"
				dangerouslySetInnerHTML={ { __html: html_notes } }
			/>
		);
	}

	let maybeDueOn = null;
	if ( due_on ) {
		const dateTimeString = new Date( due_on ).toLocaleDateString(
			undefined,
			{
				dateStyle: 'full',
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
	if ( modified_at ) {
		const dateTimeString = getLocaleString( modified_at );
		maybeModifiedAt = (
			<p className="modified">
				<span>Modified</span>
				{dateTimeString}
			</p>
		);
	}

	let maybeCompletedAt = null;
	if ( completed_at ) {
		const dateTimeString = getLocaleString( completed_at );
		maybeCompletedAt = (
			<p className="completed">
				<span>Completed</span>
				{dateTimeString}
			</p>
		);
	}

	let maybeCurrentStatus = null;
	if ( current_status ) {
		maybeCurrentStatus = <ProjectStatus {...current_status} />;
	}

	return (
		<div className="ptc-ProjectDetails">
			{maybeName}
			{
				( maybeDueOn || maybeCompletedAt || maybeModifiedAt ) && (
					<div className="metadata-badges">
						{maybeDueOn}
						{maybeCompletedAt}
						{maybeModifiedAt}
					</div>
				)
			}
			{maybeDescription}
			{maybeCurrentStatus}
		</div>
	);
}
