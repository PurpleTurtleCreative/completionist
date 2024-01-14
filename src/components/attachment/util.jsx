/**
 * Attachment utility functions.
 *
 * @since 3.5.0
 */

export function isFileType(attachment, fileExtensions) {
	if (
		attachment &&
		'name' in attachment &&
		attachment.name
	) {
		const attachmentName = attachment.name.toLowerCase();
		return fileExtensions.some(extension => attachmentName.endsWith(`.${extension.toLowerCase()}`));
	}

	return false;
}

export function isImage(attachment) {
	const imageExtensions = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
	return isFileType(attachment, imageExtensions);
}

export function isVideo(attachment) {
	const videoExtensions = ['mp4'];
	return isFileType(attachment, videoExtensions);
}

export function findAndMonitorLoadingMedia(rootNode) {

	for ( let img of rootNode.querySelectorAll('img:not(.load-monitoring-disabled)') ) {
		if ( 'complete' in img && false === img.complete ) {
			// Image has not yet loaded.
			img.classList.add('--is-loading');
			// Listen for when image is loaded.
			img.addEventListener('load', handleMediaLoad);
			// Listen for when image fails to load.
			img.addEventListener('error', handleMediaError);
		}
	}

	for ( let video of rootNode.querySelectorAll('video:not(.load-monitoring-disabled)') ) {
		if ( 'readyState' in video && video.readyState < 2 ) {
			// Video has not yet loaded data.
			video.classList.add('--is-loading');
			// Listen for when video data is loaded.
			video.addEventListener('loadeddata', handleMediaLoad);
			// Listen for when video fails to load.
			video.addEventListener('error', handleMediaError);
		}
	}

	for ( let objectFrame of rootNode.querySelectorAll('object:not(.load-monitoring-disabled)') ) {
		if ( objectFrame ) {
			// Object has not yet loaded data.
			objectFrame.classList.add('--is-loading');
			// Listen for when object data is loaded.
			objectFrame.addEventListener('load', handleMediaLoad);
			// Listen for when object fails to load.
			objectFrame.addEventListener('error', handleMediaError);
		}
	}
}

const handleMediaLoad = (event) => {
	event.target.classList.remove('--is-loading');
}

const handleMediaError = (event) => {
	event.target.classList.remove('--is-loading');
	event.target.classList.add('--is-error');
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
