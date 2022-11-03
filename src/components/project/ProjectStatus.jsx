import { getLocaleString } from '../generic/util.jsx';

import { ReactComponent as CheckmarkIcon } from '/assets/icons/fa-check-solid.svg';

import '/assets/styles/scss/components/project/_ProjectStatus.scss';

export default function ProjectStatus({
	color,
	color_label,
	created_at,
	html_text,
	title,
}) {

	let maybeColorBadge = null;
	if ( color_label ) {
		let extraBadgeClassNames = '';
		if ( color ) {
			extraBadgeClassNames += ` --is-${color}`;
		}
		maybeColorBadge = (
			<div className={"status-badge"+extraBadgeClassNames}>
				{ ( 'complete' === color ) && <CheckmarkIcon preserveAspectRatio="xMidYMid meet" /> }
				{color_label}
			</div>
		);
	}

	let maybeTitle = null;
	if ( title ) {
		maybeTitle = <h3 className="title">{title}</h3>;
	}

	let maybeCreatedAt = null;
	if ( created_at ) {
		const dateTimeString = getLocaleString( created_at );
		maybeCreatedAt = (
			<p className="created">
				<span>Updated</span>
				{dateTimeString}
			</p>
		);
	}

	let maybeDescription = null;
	if ( html_text ) {
		maybeDescription = (
			<div
				className="description"
				dangerouslySetInnerHTML={ { __html: html_text } }
			/>
		);
	}

	return (
		<div className="ptc-ProjectStatus">
			{
				( maybeColorBadge || maybeCreatedAt ) && (
					<div className="header-meta">
						{maybeColorBadge}
						{maybeCreatedAt}
					</div>
				)
			}
			{maybeTitle}
			{maybeDescription}
		</div>
	);
}
