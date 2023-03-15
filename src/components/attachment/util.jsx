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
