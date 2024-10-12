import AdminSettingsScreen from './components/screens/AdminSettingsScreen.jsx';

import { createRoot } from '@wordpress/element';

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-AdminSettingsScreen');
	if ( null !== rootNode ) {
		createRoot( rootNode ).render(
			<AdminSettingsScreen />
		);
	}
});
