import PTCCompletionist_Automations from './components/PTCCompletionist_Automations.js';
import PTCCompletionistTasksDashboardWidget from './components/PTCCompletionistTasksDashboardWidget.jsx';

const { render } = wp.element;

jQuery(function($) {
	try {
		const rootNode = document.getElementById('ptc-completionist-automations-root');
		if ( rootNode !== null ) {
			render( <PTCCompletionist_Automations />, rootNode );
		}//end if rootNode
	} catch ( e ) {
		console.error( e );
	}
});

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-completionist-tasks-dashboard-widget');
	if ( null !== rootNode ) {
		render(<PTCCompletionistTasksDashboardWidget />, rootNode);
	}
});
