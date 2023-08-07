/**
 * SettingsPage component
 *
 * @since [unreleased]
 */

// import ProjectDetails from './ProjectDetails.jsx';
// import ProjectSection from './ProjectSection.jsx';

// import '../../../assets/styles/scss/components/project/_ProjectTaskList.scss';

const { useEffect, useState } = wp.element;

export default function SettingsPage({ src }) {
	const [ status, setStatus ] = useState('idle');
	const [ settings, setSettings ] = useState(null);

	useEffect(() => {
		// Load plugin settings data from src on mount.
		if ( 'idle' === status && null === settings ) {
			// Signal loading.
			setStatus('loading');
			// Request data from src URL string.
			window
				.fetch(src)
				.then( res => {
					if ( 200 !== res.status ) {
						return Promise.reject( `Error ${res.status}. Failed to load plugin settings.` );
					}
					return res.json();
				})
				.then( data => {
					setSettings(data);
					setStatus('success');
					return Promise.resolve();
				})
				.catch( err => {
					setStatus(err);
					setSettings(null);
				});
		}
	}, []);

	// Render.

	let innerContent = null;
	switch ( status ) {

		case 'success':
			innerContent = (
				<div className="sections-of-tasks">
					{ sections.map(section => <ProjectSection {...section} />) }
				</div>
			);
			break;

		case 'loading':
			innerContent = <p className="ptc-loader">Loading plugin settings...</p>;
			break;

		case 'error':
			innerContent = <p className="ptc-error">Failed to load plugin settings.</p>;
			break;

		case 'idle':
			innerContent = null;
			break;

		default:
			innerContent = <p className="ptc-error">{status}</p>;
			break;
	}

	return (
		<div className="ptc-SettingsPage">
			{innerContent}
		</div>
	);
}
