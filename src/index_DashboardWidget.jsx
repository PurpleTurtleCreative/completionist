import DashboardWidget from './components/DashboardWidget.jsx';
import NoteBox from './components/notice/NoteBox.jsx';
import NoticesContainer from './components/notice/NoticesContainer.jsx';

import { NoticeContextProvider } from './components/notice/NoticeContext.jsx';
import { TaskContextProvider } from './components/task/TaskContext.jsx';

import { createRoot } from '@wordpress/element';

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-DashboardWidget');
	if ( null !== rootNode ) {
		if ( 'error' in window.PTCCompletionist ) {
			createRoot( rootNode ).render(
				<NoteBox type="error" message={window.PTCCompletionist.error.message} code={window.PTCCompletionist.error.code} />
			);
		} else {
			createRoot( rootNode ).render(
				<NoticeContextProvider>
					<TaskContextProvider>
						<NoticesContainer />
						<DashboardWidget />
					</TaskContextProvider>
				</NoticeContextProvider>
			);
		}
	}
});
