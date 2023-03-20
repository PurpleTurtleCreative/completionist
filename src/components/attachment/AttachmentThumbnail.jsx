/**
 * AttachmentThumbnail component
 *
 * @since 3.5.0
 */

import { isImage } from './util.jsx';

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
		window.console.log('Could not display AttachmentThumbnail for attachment with missing data.', attachment);
		return null;
	}

	if ( ! isImage(attachment) ) {
		// @TODO - Only supporting <img> attachments for now.
		window.console.log('Could not display AttachmentThumbnail for attachment of unsupported type.', attachment);
		return null;
	}

	return (
		<div className="ptc-AttachmentThumbnail">
			<img src={attachment._ptc_view_url} alt={attachment.name} />
		</div>
	);
}
