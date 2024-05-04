/**
 * TaskSingleAsync component
 *
 * @since 4.3.0
 */

import TaskListItem from './TaskListItem.jsx';

import '../../../assets/styles/scss/components/task/_TaskSingleAsync.scss';

import { useEffect, useState } from '@wordpress/element';

export default function TaskSingleAsync({ src }) {
	const [ status, setStatus ] = useState('idle');
	const [ task, setTask ] = useState(null);

	useEffect(() => {
		// Load task data from src on mount.
		if ( 'idle' === status && null === task ) {

			if ( 'string' === typeof src ) {

				// Signal loading.
				setStatus('loading');

				// Request data from src URL string.
				window
					.fetch(src)
					.then( res => {
						if ( 200 !== res.status ) {
							return Promise.reject( `Error ${res.status}. Failed to load task.` );
						}
						return res.json();
					})
					.then( data => {
						setTask(data);
						setStatus('success');
						return Promise.resolve();
					})
					.catch( err => {
						setStatus(err);
						setTask(null);
					});
			} else if ( 'object' === typeof src ) {
				// Use provided task src data.
				setTask(src);
				setStatus('success');
			} else {
				// Unsupported task src provided!
				setStatus('Failed to load task due to an unexpected error.');
				setTask(null);
				window.console.warn('Unsupported TaskSingleAsync[src] type:', src);
			}
		}
	}, []);

	// Render.

	let innerContent = null;
	switch ( status ) {

		case 'success':
			if ( task ) {
				innerContent = <TaskListItem tagName="div" task={task} />;
			}
			break;

		case 'loading':
			innerContent = <p className="ptc-loader">Loading task...</p>;
			break;

		case 'error':
			innerContent = <p className="ptc-error">Failed to load task.</p>;
			break;

		case 'idle':
			innerContent = null;
			break;

		default:
			innerContent = <p className="ptc-error">{status}</p>;
			break;
	}

	return (
		<div className="ptc-TaskSingleAsync">
			{innerContent}
		</div>
	);
}
