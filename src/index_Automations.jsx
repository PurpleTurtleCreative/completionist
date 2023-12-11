import PTCCompletionistAutomations from './components/PTCCompletionistAutomations.js';

import { createRoot } from '@wordpress/element';

document.addEventListener('DOMContentLoaded', () => {
	try {
		const rootNode = document.getElementById('ptc-PTCCompletionistAutomations');
		if ( rootNode !== null ) {
			createRoot( rootNode ).render( <PTCCompletionistAutomations /> );
		}//end if rootNode
	} catch ( e ) {
		console.error( e );
	}
});
