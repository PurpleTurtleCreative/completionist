import ProjectDetails from './ProjectDetails.jsx';
import ProjectSection from './ProjectSection.jsx';

import '/assets/styles/scss/components/project/_ProjectTaskList.scss';

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
				.then( res => res.json() )
				.then( res => {
					setProject(res);
					setStatus('success');
				})
				.catch( err => {
					window.console.error('Failed to load project:', err);
					setStatus('error');
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
						{ sections.map(section => <ProjectSection section={section} />) }
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
	}

	return (
		<div className="ptc-ProjectTaskList">
			{ details && <ProjectDetails details={details} /> }
			{innerContent}
		</div>
	);
}
