import ProjectDetails from './ProjectDetails.jsx';

// import '/assets/styles/scss/components/task/_TaskList.scss';

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
					window.console.error('Promise catch:', err);
					// addNotice(
					// 	'Failed to load project.',
					// 	'error'
					// );
					setStatus('error');
					setProject(null);
				});
		}
	}, [status, project]);

	let innerContent = null;
	switch ( status ) {

		case 'success':
			innerContent = project.sections.map(section => (
				<div className="project-section">
					<h3>{section.name}</h3>
					<ul>
						{section.tasks.map(task => <li>{task.name}</li>)}
					</ul>
				</div>
			));
			break;

		case 'loading':
			innerContent = <p>Loading tasks...</p>;
			break;

		case 'error':
			innerContent = <p>Failed to load project.</p>;
			break;
	}

	return (
		<div className="ptc-ProjectTaskList">
			{ project && <ProjectDetails project={project} /> }
			{innerContent}
		</div>
	);
}
