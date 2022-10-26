// import TaskRow from './TaskRow.jsx';

// import '/assets/styles/scss/components/task/_TaskList.scss';

// const { useState } = wp.element;

export default function ProjectStatus({
	color = null,
	color_label = null,
	created_at = null,
	html_text = null,
	title = null,
}) {

	let maybeColorBadge = null;
	if ( color_label ) {
		let extraBadgeClassNames = '';
		if ( color ) {
			extraBadgeClassNames += ` --is-${color}`;
		}
		maybeColorBadge = <div className={"badge"+extraBadgeClassNames}>{color_label}</div>;
	}

	let maybeTitle = null;
	if ( title ) {
		maybeTitle = <h3 className="title">{title}</h3>;
	}

	let maybeCreatedAt = null;
	if ( created_at ) {
		const dateTime = new Date( created_at );
		maybeCreatedAt = <p className="created">{'Created at: '+dateTime.toLocaleString()}</p>;
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
			{maybeColorBadge}
			{maybeTitle}
			{maybeCreatedAt}
			{maybeDescription}
		</div>
	);
}
