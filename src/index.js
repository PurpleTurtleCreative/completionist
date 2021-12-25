import PTCCompletionist_Automations from './components/PTCCompletionist_Automations.js';
import PTCCompletionistTasksDashboardWidget from './components/PTCCompletionistTasksDashboardWidget.jsx';
import NoteBox from './components/notice/NoteBox.jsx';

import { TaskContextProvider } from './components/task/TaskContext.jsx';

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
	const rootNode = document.getElementById('ptc-PTCCompletionistTasksDashboardWidget');
	if ( null !== rootNode ) {
		if ( 'error' in window.PTCCompletionist ) {
			render(
				<NoteBox type="error" message={window.PTCCompletionist.error.message} code={window.PTCCompletionist.error.code} />
			, rootNode);
		} else {
			render(
				<TaskContextProvider>
					<PTCCompletionistTasksDashboardWidget />
				</TaskContextProvider>
			, rootNode);
		}
	}
});
