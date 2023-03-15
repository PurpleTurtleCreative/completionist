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
