/**
 * Attachment utility functions.
 *
 * @since [unreleased]
 */

export function isImage(attachment) {

	if (
		attachment &&
		'name' in attachment &&
		attachment.name
	) {
		return (
			attachment.name.endsWith('.jpg') ||
			attachment.name.endsWith('.jpeg') ||
			attachment.name.endsWith('.png') ||
			attachment.name.endsWith('.bmp') ||
			attachment.name.endsWith('.gif')
		);
	}

	return false;
}

export function fetchRefreshedAttachment(attachment) {

	if (
		attachment &&
		'_ptc_refresh_url' in attachment &&
		attachment['_ptc_refresh_url']
	) {
		// Request data.
		return window.fetch( attachment['_ptc_refresh_url'] );
	}

	return new Promise((resolve, reject) => {
		window.console.error('Cannot refresh attachment, missing or invalid "_ptc_refresh_url"', attachment);
		reject('Invalid attachment.');
	});
}

export function findAndMonitorInlineAttachments(rootNode, attachments) {

	let foundAttachments = [];

	if ( rootNode && attachments.length > 0 ) {

		const inlineImgAttachments = rootNode.querySelectorAll('img[data-asana-type="attachment"]');

		for ( let img of inlineImgAttachments ) {

			const imgSrcUrl = new window.URL(img.src);

			const attachment = attachments.find(item => {
				const viewUrl = new window.URL(item.view_url);
				return (
					imgSrcUrl.origin == viewUrl.origin &&
					imgSrcUrl.pathname == viewUrl.pathname
				);
			});

			if ( attachment ) {

				foundAttachments.push(attachment);

				img.addEventListener(
					'error',
					event => {

						const attachmentEl = event.target;

						// Fetch a fresh source URL since the AWS security
						// token has likely expired, hence this error.
						fetchRefreshedAttachment(attachment)
							.then( res => {
								if ( 200 !== res.status ) {
									return Promise.reject( `Error ${res.status}. Failed to refresh inline attachment.` );
								}
								return res.json();
							})
							.then( data => {
								if ( data && 'view_url' in data && data.view_url ) {
									attachmentEl.src = data.view_url;
								}
								return Promise.resolve();
							})
							.catch( err => {
								window.console.error(err);
							});
					}
				);
			}
		}
	}

	return foundAttachments;
}
