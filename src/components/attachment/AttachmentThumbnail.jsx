/**
 * AttachmentThumbnail component
 *
 * @since 3.5.0
 */

import { isImage, isVideo } from './util.jsx';

// import '../../../assets/styles/scss/components/task/_TaskActions.scss';

// const { useState } = wp.element;

export default function AttachmentThumbnail({ attachment }) {

	if (
		false === (
			'_ptc_view_url' in attachment &&
			attachment._ptc_view_url &&
			'name' in attachment &&
			attachment.name
		)
	) {
		window.console.warn('Could not display AttachmentThumbnail for attachment with missing data.', attachment);
		return null;
	}

	let content = null;
	if ( isImage(attachment) ) {
		content = <img src={attachment._ptc_view_url} alt={attachment.name} draggable="false" />;
	} else if ( isVideo(attachment) ) {
		content = <video src={attachment._ptc_view_url} controls />;
	} else {
		window.console.warn('Could not display AttachmentThumbnail for unsupported attachment:', attachment);
		return null;
	}

	return (
		<div className="ptc-AttachmentThumbnail">{content}</div>
	);
}
