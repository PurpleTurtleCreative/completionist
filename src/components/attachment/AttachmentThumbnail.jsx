/**
 * AttachmentThumbnail component
 *
 * @since [unreleased]
 */

import { isImage, fetchRefreshedAttachment } from './util.jsx';

// import '../../../assets/styles/scss/components/task/_TaskActions.scss';

const { useState } = wp.element;

export default function AttachmentThumbnail({ attachment: initAttachment }) {
	const [ attachment, setAttachment ] = useState(initAttachment);

	function handleError(event) {
		const attachmentEl = event.target;
		if ( 'IMG' === attachmentEl.tagName ) {
			// Fetch a fresh source URL since the AWS security
			// token has likely expired, hence this error.
			fetchRefreshedAttachment(attachment)
				.then( res => {
					if ( 200 !== res.status ) {
						return Promise.reject( `Error ${res.status}. Failed to refresh attachment.` );
					}
					return res.json();
				})
				.then( data => {
					setAttachment(data);
					return Promise.resolve();
				})
				.catch( err => {
					window.console.log(err);
				});
		} else {
			window.console.error(`Unsupported attachment type ${attachmentEl.tagName} for element:`, attachmentEl);
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
			<img src={attachment.view_url} alt={attachment.name} onError={handleError} />
		</div>
	);
}
