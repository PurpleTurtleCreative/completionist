// import { TaskContext } from './TaskContext.jsx';

// const { useState, useCallback, useMemo, useEffect } = wp.element;

export default function TaskPinToPostForm({ postId }) {

	const handleFormSubmit = (event) => {
		event.preventDefault();
		window.console.log(`== Submitted TaskPinToPostForm for postId ${postId} ==`);
		window.console.log(event);
	}

	return (
		<form className="ptc-TaskPinToPostForm" onSubmit={handleFormSubmit}>
			<input type="url" placeholder="Paste a task link..." />
			<button title="Pin existing Asana task" type="submit"><i className="fas fa-thumbtack"></i></button>
		</form>
	);
}
