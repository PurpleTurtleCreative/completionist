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
		// Load project data from src URL on mount.
		if ( 'idle' === status && null === project ) {

			// Signal loading.
			setStatus('loading');

			// Request data.
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
						{ sections.map(section => <ProjectSection {...section} />) }
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
