/**
 * Renders the [ptc_asana_project] shortcode.
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
				try {
					window.PTCCompletionistPro.actions.renderAsanaProject(rootNode);
				} catch (err) {
					render(
						<ProjectTaskList src={rootNode.dataset.src} />,
						rootNode
					);
				}
			}
		});
});
