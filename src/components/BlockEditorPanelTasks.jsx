import TaskPinToPostBar from './task/TaskPinToPostBar.jsx';
import TaskList from './task/TaskList.jsx';

import { TaskContext } from './task/TaskContext.jsx';

import '../../assets/styles/scss/components/_BlockEditorPanelTasks.scss';

import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

import { useContext } from '@wordpress/element';

export default function BlockEditorPanelTasks() {
	const { tasks } = useContext(TaskContext);
	const currentPostId = useSelect(
		select => {
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
	);

	return (
		<div className="ptc-BlockEditorPanelTasks">
			<TaskPinToPostBar postId={currentPostId} />
			<TaskList tasks={tasks} />
		</div>
	);
}
