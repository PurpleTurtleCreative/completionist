/**
 * AttachmentThumbnail component
 *
 * @since [unreleased]
 */

import { isImage } from './util.jsx';

// import '../../../assets/styles/scss/components/task/_TaskActions.scss';

// const { useCallback, useContext } = wp.element;

export default function AttachmentThumbnail({ attachment }) {

	function handleError(event) {
		const attachmentEl = event.target;
		if ( 'IMG' === attachmentEl.tagName ) {
			// Fetch a fresh source URL since the AWS security
			// token has likely expired, hence this error.
			attachmentEl.src = 'https://images.pexels.com/photos/129574/pexels-photo-129574.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1';
		}
	}

	if (
		false === (
			'view_url' in attachment &&
			attachment.view_url &&
			'name' in attachment &&
			attachment.name
		)
	) {
		window.console.error('Could not display AttachmentThumbnail for attachment with missing data.', attachment);
		return null;
	}

	if ( ! isImage(attachment) ) {
		// @TODO - Only supporting <img> attachments for now.
		window.console.error('Could not display AttachmentThumbnail for attachment of unsupported type.', attachment);
		return null;
	}

	return (
		<div className="ptc-AttachmentThumbnail">
			<img src={attachment.view_url} alt={attachment.name} onError={handleError} />
		</div>
	);
}
