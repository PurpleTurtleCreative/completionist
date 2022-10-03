import DashboardWidget from './components/DashboardWidget.jsx';
import NoteBox from './components/notice/NoteBox.jsx';

import { TaskContextProvider } from './components/task/TaskContext.jsx';

const { render } = wp.element;

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-DashboardWidget');
	if ( null !== rootNode ) {
		if ( 'error' in window.PTCCompletionist ) {
			render(
				<NoteBox type="error" message={window.PTCCompletionist.error.message} code={window.PTCCompletionist.error.code} />
			, rootNode);
		} else {
			render(
				<TaskContextProvider>
					<DashboardWidget />
				</TaskContextProvider>
			, rootNode);
		}
	}
});
