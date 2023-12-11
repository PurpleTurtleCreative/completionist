import BlockEditorPanelTasks from './components/BlockEditorPanelTasks.jsx';
import NoteBox from './components/notice/NoteBox.jsx';
import NoticesContainer from './components/notice/NoticesContainer.jsx';

import { NoticeContextProvider } from './components/notice/NoticeContext.jsx';
import { TaskContextProvider } from './components/task/TaskContext.jsx';

import { createRoot } from '@wordpress/element';

const PinnedTasksMetabox = () => {

	let tasksPanelContent = null;
	if ( 'error' in window.PTCCompletionist ) {
		tasksPanelContent = <NoteBox type="error" message={window.PTCCompletionist.error.message} code={window.PTCCompletionist.error.code} />;
	} else {
		tasksPanelContent = (
			<NoticeContextProvider>
				<TaskContextProvider>
					<NoticesContainer />
					<BlockEditorPanelTasks />
				</TaskContextProvider>
			</NoticeContextProvider>
		);
	}

	return tasksPanelContent;
}

document.addEventListener('DOMContentLoaded', () => {
	try {
		const rootNode = document.getElementById('ptc-PinnedTasksMetabox');
		if ( null !== rootNode ) {
			createRoot( rootNode ).render( <PinnedTasksMetabox /> );
		}//end if rootNode
	} catch ( e ) {
		window.console.error( e );
	}
});
