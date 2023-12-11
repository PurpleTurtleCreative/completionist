/**
 * ProjectTaskList component
 *
 * @since 3.4.0
 */

import ProjectDetails from './ProjectDetails.jsx';
import ProjectSection from './ProjectSection.jsx';

import '../../../assets/styles/scss/components/project/_ProjectTaskList.scss';

const { useEffect, useState } = wp.element;

export default function ProjectTaskList({ src }) {
	const [ status, setStatus ] = useState('idle');
	const [ project, setProject ] = useState(null);

	useEffect(() => {
		// Load project data from src on mount.
		if ( 'idle' === status && null === project ) {

			if ( 'string' === typeof src ) {

				// Signal loading.
				setStatus('loading');

				// Request data from src URL string.
				window
					.fetch(src)
					.then( res => {
						if ( 200 !== res.status ) {
							return Promise.reject( `Error ${res.status}. Failed to load project.` );
						}
						return res.json();
					})
					.then( data => {
						setProject(data);
						setStatus('success');
						return Promise.resolve();
					})
					.catch( err => {
						setStatus(err);
						setProject(null);
					});
			} else if ( 'object' === typeof src ) {
				// Use provided project src data.
				setProject(src);
				setStatus('success');
			} else {
				// Unsupported project src provided!
				setStatus('Failed to load project due to an unexpected error.');
				setProject(null);
				window.console.warn('Unsupported ProjectTaskList[src] type:', src);
			}
		}
	}, []);

	// Render.

	const { sections = null, ...details } = project ?? {};

	let innerContent = null;
	switch ( status ) {

		case 'success':
			if ( sections ) {
				innerContent = (
					<div className="sections-of-tasks">
						{ sections.map(section => <ProjectSection key={JSON.stringify(section)} {...section} />) }
					</div>
				);
			}
			break;

		case 'loading':
			innerContent = <p className="ptc-loader">Loading project...</p>;
			break;

		case 'error':
			innerContent = <p className="ptc-error">Failed to load project.</p>;
			break;

		case 'idle':
			innerContent = null;
			break;

		default:
			innerContent = <p className="ptc-error">{status}</p>;
			break;
	}

	return (
		<div className="ptc-ProjectTaskList">
			{ details && <ProjectDetails {...details} /> }
			{innerContent}
		</div>
	);
}
