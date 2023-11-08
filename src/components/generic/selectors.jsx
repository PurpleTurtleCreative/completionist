/**
 * Generic selectors.
 */

import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

export function selectEditorCurrentPostId( select ) {
	let id = select( editorStore ).getCurrentPostId();
	if ( ! id ) {
		// Fallback check for Classic Editor.
		const postIdInput = document.getElementById( 'post_ID' );
		if ( postIdInput && postIdInput.value ) {
			id = postIdInput.value;
		}
	}
	return id;
}
