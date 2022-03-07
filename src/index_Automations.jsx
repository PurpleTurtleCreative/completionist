import PTCCompletionistAutomations from './components/PTCCompletionistAutomations.js';

const { render } = wp.element;

document.addEventListener('DOMContentLoaded', () => {
	try {
		const rootNode = document.getElementById('ptc-PTCCompletionistAutomations');
		if ( rootNode !== null ) {
			render( <PTCCompletionistAutomations />, rootNode );
		}//end if rootNode
	} catch ( e ) {
		console.error( e );
	}
});
