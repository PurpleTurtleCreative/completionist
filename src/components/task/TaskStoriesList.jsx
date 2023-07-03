/**
 * TaskStoriesList component
 *
 * @since [unreleased]
 */

import { getLocaleString } from '../generic/util.jsx';

import '../../../assets/styles/scss/components/task/_TaskStoriesList.scss';

// const { useState } = wp.element;

export default function TaskStoriesList({ stories }) {

	let maybeStories = <p>No activity</p>;
	if ( stories.length > 0 ) {
		maybeStories = (
			<ol className="task-stories-list">
				{
					stories.map((story, index) => {

						let storyIcon = null;
						if (
							'created_by' in story &&
							story.created_by &&
							'photo' in story.created_by &&
							story.created_by.photo &&
							'image_36x36' in story.created_by.photo &&
							story.created_by.photo.image_36x36
						) {
							storyIcon = <img src={story.created_by.photo.image_36x36} width="36" height="36" />;
						}

						return (
							<li>
								<div className="story-icon">
									{storyIcon}
								</div>
								<div className="story-content">
									<div className="story-header">
										<span className="created-by-name">{story.created_by.name}</span>
										<span className="created-at">{getLocaleString(story.created_at)}</span>
									</div>
									<div className="story-body" dangerouslySetInnerHTML={ { __html: story.html_text } } />
								</div>
							</li>
						);
					})
				}
			</ol>
		);
	}

	return (
		<div className="ptc-TaskStoriesList">
			{maybeStories}
		</div>
	);
}
