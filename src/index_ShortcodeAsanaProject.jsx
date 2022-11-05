/**
 * Renders the [ptc-asana-project] shortcode.
 *
 * @since 3.4.0
 */

import ProjectTaskList from './components/project/ProjectTaskList.jsx';

const { render } = wp.element;

document.addEventListener('DOMContentLoaded', () => {
	document
		.querySelectorAll('.ptc-shortcode.ptc-asana-project[data-src]')
		.forEach( rootNode => {
			if ( rootNode.dataset.src ) {
				render(
					<ProjectTaskList src={rootNode.dataset.src} />,
					rootNode
				);
			}
		});
});
