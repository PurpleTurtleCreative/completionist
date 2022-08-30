import { TaskContext } from './TaskContext.jsx';

const { useState, useContext } = wp.element;

export default function TaskPinToPostForm({ postId }) {
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
		<form className="ptc-TaskPinToPostForm" onSubmit={handleFormSubmit} disabled={isProcessing}>
			<input type="url" placeholder="Paste a task link..." value={taskLink} onChange={e => setTaskLink(e.target.value)} disabled={isProcessing} required />
			<button title="Pin existing Asana task" type="submit"><i className={submitIconClass} disabled={isProcessing}></i></button>
		</form>
	);
}
