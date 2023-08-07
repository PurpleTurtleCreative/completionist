/**
 * Renders the plugin settings page.
 *
 * @since [unreleased]
 */

import SettingsPage from './components/settings/SettingsPage.jsx';

const { render } = wp.element;

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-Settings');
	if ( rootNode && rootNode.dataset.src ) {
		render(
			<SettingsPage src={rootNode.dataset.src} />,
			rootNode
		);
	}
});
