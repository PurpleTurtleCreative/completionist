/**
 * TaskStoriesModal component
 *
 * @since [unreleased]
 */

import { ReactComponent as CloseIcon } from '../../../assets/icons/fa-xmark-solid.svg';

import TaskStoriesList from './TaskStoriesList.jsx';

import '../../../assets/styles/scss/components/task/_TaskStoriesModal.scss';

const { useState } = wp.element;

export default function TaskStoriesModal({ stories, onCloseClick }) {

	return (
		<div className="ptc-TaskStoriesModal">
			<div className="modal-overlay" onClick={onCloseClick}></div>
			<div className="modal-content">
				<div className="modal-header">
					<button type="button" className="modal-close" onClick={onCloseClick} aria-label="Close task activity">
						<CloseIcon preserveAspectRatio="xMidYMid meet" />
					</button>
				</div>
				<div className="modal-body">
					<TaskStoriesList stories={stories} />
				</div>
			</div>
		</div>
	);
}
