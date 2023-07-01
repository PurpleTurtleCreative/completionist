/**
 * TaskStoriesModal component
 *
 * @since [unreleased]
 */

import { getLocaleString } from '../generic/util.jsx';

import { ReactComponent as CloseIcon } from '../../../assets/icons/fa-xmark-solid.svg';

import '../../../assets/styles/scss/components/task/_TaskStoriesModal.scss';

const { useState } = wp.element;

export default function TaskStoriesModal({ stories, onCloseClick }) {

	let maybeStories = <p>No activity</p>;
	if ( stories.length > 0 ) {
		maybeStories = (
			<ol className="task-stories-list">
				{
					stories.map((story, index) => {
						return (
							<li>
								<div className="created-by">
									<img src={story.created_by.photo.image_36x36} />
									<span>{story.created_by.name}</span>
								</div>
								<div className="created-at">{"["+getLocaleString(story.created_at)+"]"}</div>
								<div className="text" dangerouslySetInnerHTML={ { __html: story.html_text } } />
							</li>
						);
					})
				}
			</ol>
		);
	}

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
					{maybeStories}
				</div>
			</div>
		</div>
	);
}
