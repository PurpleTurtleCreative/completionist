/**
 * Renders the [ptc_asana_task] shortcode.
 *
 * @since 4.3.0
 */

import TaskSingleAsync from './components/task/TaskSingleAsync.jsx';

import initGlobalNamespace from './components/GlobalNamespace.jsx';

import { createRoot } from '@wordpress/element';

initGlobalNamespace();

document.addEventListener('DOMContentLoaded', () => {
	document
		.querySelectorAll('.ptc-shortcode.ptc-asana-task[data-src]')
		.forEach( rootNode => {
			if ( rootNode.dataset.src ) {
				/**
				 * Filters the element to be rendered for displaying the
				 * [ptc_asana_task] shortcode.
				 *
				 * @since 4.3.0
				 *
				 * @param {Object} element - The element to render.
				 * Default <TaskSingleAsync />.
				 * @param {HTMLDivElement} rootNode - The root node where
				 * React will render the element.
				 */
				const element = window.Completionist.hooks.applyFilters(
					'shortcodes_ptc_asana_task_render',
					<TaskSingleAsync src={rootNode.dataset.src} />,
					rootNode
				);
				createRoot( rootNode ).render( element );
			}
		});
});
