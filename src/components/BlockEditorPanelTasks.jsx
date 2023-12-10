import TaskPinToPostBar from './task/TaskPinToPostBar.jsx';
import TaskList from './task/TaskList.jsx';

import { TaskContext } from './task/TaskContext.jsx';

import '../../assets/styles/scss/components/_BlockEditorPanelTasks.scss';

import { selectEditorCurrentPostId } from './generic/selectors.jsx';
import { useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';

import { useContext } from '@wordpress/element';

export default function BlockEditorPanelTasks() {
	const { tasks } = useContext(TaskContext);
	const currentPostId = useSelect(selectEditorCurrentPostId);

	// @TODO - Check if currentPostId null due to being a new draft
	// post and display a notice to first save the post.
	// I haven't experienced this personally, though, so proper
	// testing needs to be done to confirm this state possibility.

	return (
		<div className="ptc-BlockEditorPanelTasks">
			<TaskPinToPostBar postId={currentPostId} />
			<TaskList tasks={tasks} />
		</div>
	);
}
