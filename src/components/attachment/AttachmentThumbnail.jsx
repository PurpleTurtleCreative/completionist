/**
 * AttachmentThumbnail component
 *
 * @since 3.5.0
 */

import { isImage, isVideo, isFileType } from './util.jsx';

import '../../../assets/styles/scss/components/task/_AttachmentThumbnail.scss';

export default function AttachmentThumbnail({ attachment }) {

	let content = <p className="fallback fallback-error">Failed to load attachment</p>;

	if (
		false === (
			'_ptc_view_url' in attachment &&
			attachment._ptc_view_url &&
			'name' in attachment &&
			attachment.name
		)
	) {
		window.console.error('Could not display AttachmentThumbnail for attachment with missing data.', attachment);
	} else {
		if ( isImage(attachment) ) {
			content = <img src={attachment._ptc_view_url} alt={attachment.name} draggable="false" />;
		} else if ( isVideo(attachment) ) {
			content = <video src={attachment._ptc_view_url} controls width="100%" height="auto" />;
		} else if ( isFileType(attachment, ['pdf']) ) {
			content = (
				<object data={attachment._ptc_view_url} type="application/pdf" width="100%" height="600px">
					<p className="fallback fallback-warning">Download <a href={attachment._ptc_view_url}>{attachment.name}</a> to view</p>
				</object>
			);
		} else if ( '_ptc_oembed_html' in attachment && attachment._ptc_oembed_html ) {
			content = <div dangerouslySetInnerHTML={{ __html: attachment._ptc_oembed_html }} />;
		} else {
			window.console.warn('Could not display AttachmentThumbnail for unsupported attachment:', attachment);
			content = <p className="fallback fallback-warning">Failed to display unsupported attachment <em>{attachment.name}</em></p>;
		}
	}

	return (
		<div className="ptc-AttachmentThumbnail">{content}</div>
	);
}
