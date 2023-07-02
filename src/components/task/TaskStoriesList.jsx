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
		<div className="ptc-TaskStoriesList">
			{maybeStories}
		</div>
	);
}
