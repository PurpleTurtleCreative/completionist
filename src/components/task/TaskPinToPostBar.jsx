import TaskPinToPostForm from './TaskPinToPostForm.jsx';
import TaskCreationForm from './TaskCreationForm.jsx';

import { TaskContext } from './TaskContext.jsx';

const { useState } = wp.element;

export default function TaskPinToPostBar({ postId }) {
	const [showTaskCreationForm, setShowTaskCreationForm] = useState(false);

	const toggleTaskCreationForm = () => {
		setShowTaskCreationForm( ! showTaskCreationForm );
	}

	const creationFormToggleIconClass = ( showTaskCreationForm ) ? 'fas fa-times' : 'fas fa-plus';

	let extraClassNames = '';
	if ( showTaskCreationForm ) {
		extraClassNames += ' --is-showing-creation-form';
	}

	return (
		<div className={"ptc-TaskPinToPostBar"+extraClassNames}>
			<div className="toolbar">
				<TaskPinToPostForm postId={postId} />
				<button type="button" title="Create new task" onClick={toggleTaskCreationForm}><i className={creationFormToggleIconClass} aria-hidden="true"></i></button>
			</div>
			{ showTaskCreationForm && <TaskCreationForm postId={postId} /> }
		</div>
	);
}
