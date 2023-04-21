/**
 * Renders the [ptc_asana_project] shortcode.
 *
 * @since 3.4.0
 */

import ProjectTaskList from './components/project/ProjectTaskList.jsx';

import initGlobalNamespace from './components/GlobalNamespace.jsx';

const { render } = wp.element;

initGlobalNamespace();

document.addEventListener('DOMContentLoaded', () => {
	document
		.querySelectorAll('.ptc-shortcode.ptc-asana-project[data-src]')
		.forEach( rootNode => {
			if ( rootNode.dataset.src ) {
				const element = window.Completionist.hooks.applyFilters(
					'shortcodes_ptc_asana_project_render',
					<ProjectTaskList src={rootNode.dataset.src} />,
					rootNode
				);
				render( element, rootNode );
			}
		});
});
