import { TaskContext } from './TaskContext.jsx';

import '/assets/styles/scss/components/task/_TaskPinToPostForm.scss';

const { useState, useContext } = wp.element;

export default function TaskPinToPostForm({ postId, disabled = false }) {
	const [ taskLink, setTaskLink ] = useState('');
	const [ isProcessing, setIsProcessing ] = useState(false);
	const { pinTask } = useContext(TaskContext);

	const handleFormSubmit = (event) => {

		event.preventDefault();
		if ( isProcessing ) {
			return;
		}

		setIsProcessing(true);
		pinTask(taskLink, postId).then(success => {
			if ( success ) {
				setTaskLink('');
			}
			setIsProcessing(false);
		});
	}

	const submitIconClass = ( isProcessing ) ? 'fas fa-sync-alt fa-spin' : 'fas fa-thumbtack';

	return (
		<form className="ptc-TaskPinToPostForm" onSubmit={handleFormSubmit} disabled={isProcessing || disabled}>
			<input type="url" placeholder="Paste a task link..." value={taskLink} onChange={e => setTaskLink(e.target.value)} disabled={isProcessing || disabled} required />
			<button title="Pin existing Asana task" type="submit" disabled={isProcessing || disabled}><i className={submitIconClass}></i></button>
		</form>
	);
}
