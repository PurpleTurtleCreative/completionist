/**
 * Utility functions unrelated to application state.
 */

import { isImage, isVideo, isFileType } from '../attachment/util.jsx';

export function getTaskUrl(taskGID) {
	// @TODO - Prefer task permalink_url ..?
	return `https://app.asana.com/0/0/${taskGID}/f`;
}

export function getTaskGIDFromTaskUrl(taskLink) {

	let matches;

	if (
		( matches = taskLink.match(/\/task\/([0-9]+)/) ) &&
		matches[1]
	) {
		// Task link in URL schema v1 (eg. https://app.asana.com/1/<workspace_id>/project/<project_id>/task/<task_id>)
		return matches[1];
	} else if (
		( matches = taskLink.match(/\/([0-9]+)\/.$/) ) &&
		matches[1]
	) {
		// Task link in URL schema v0 (eg. https://app.asana.com/0/<project_id>/<task_id>/f)
		return matches[1];
	}

	return '';
}

export function isCriticalTask(task) {
	const DAY_IN_SECONDS = 86400;
	const limit = 7 * DAY_IN_SECONDS;
	return ( ( Date.parse(task.due_on) - Date.now() ) < limit );
}

export function countIncompleteTasks(tasks) {
	return filterIncompleteTasks(tasks).length;
}

export function sortTasksByCompleted(tasks) {
	tasks.sort((a, b) => {
		const taskCompletedA = getTaskCompleted(a)[0];
		const taskCompletedB = getTaskCompleted(b)[0];
		if ( taskCompletedA === taskCompletedB ) {
			// Order by task name if same completion group.
			return getTaskName(a).localeCompare(getTaskName(b));
		}
		// Order completed tasks after incomplete tasks.
		return ( true === taskCompletedA ) ? 1 : -1;
	});
}

export function filterIncompleteTasks(tasks) {
	return tasks.filter(t => !t.completed);
}

export function filterCriticalTasks(tasks) {
	return tasks.filter(t => isCriticalTask(t));
}

export function filterMyTasks(userGID, tasks) {
	return tasks.filter(t => {
		if ( t.assignee ) {
			return ( userGID === t.assignee.gid );
		}
		return false;
	});
}

export function filterGeneralTasks(tasks) {
	return tasks.filter(t => {
		if ( t.action_link && t.action_link.post_id > 0 ) {
			return false;
		}
		return true;
	});
}

export function filterPinnedTasks(tasks) {
	return tasks.filter(t => {
		if ( t.action_link && t.action_link.post_id > 0 ) {
			return true;
		}
		return false;
	});
}

export function getAssigneeDisplayName(task) {
	let assigneeDisplayName = null;
	if ( task?.assignee ) {
		if ( window.PTCCompletionist.users[ task.assignee.gid ] ) {
			assigneeDisplayName = window.PTCCompletionist.users[ task.assignee.gid ].data.display_name;
		} else if ( task.assignee?.name ) {
			assigneeDisplayName = task.assignee.name;
		} else {
			assigneeDisplayName = '(Not Connected)';
		}
	}
	return assigneeDisplayName;
}

export function getWorkspaceProjectSelectOptions() {
	const projectOptions = [];
	for ( const projectGID in window.PTCCompletionist.projects ) {
		projectOptions.push(<option value={projectGID} key={projectGID}>{window.PTCCompletionist.projects[projectGID]}</option>);
	}
	return projectOptions;
}

export function getWorkspaceUserSelectOptions() {
	const userOptions = [];
	for ( const userGID in window.PTCCompletionist.users ) {
		const user = window.PTCCompletionist.users[userGID].data;
		userOptions.push(<option value={userGID} key={userGID}>{`${user.display_name} (${user.user_email})`}</option>);
	}
	return userOptions;
}

//
// Data getters.
//

export function getTaskCompleted(task) {

	let completed = null;
	let label = null;

	if ( task && 'completed' in task ) {

		label = 'Incomplete';

		if ( true === task.completed ) {
			label = 'Completed';
		}

		completed = task.completed;
	}

	return [ completed, label ];
}

export function getTaskName(task) {

	let name = null;
	if ( task && 'name' in task && task.name ) {
		name = task.name;
	}

	return name;
}

export function getTaskAssignee(task) {

	let assigneeName = null;
	let assigneeImage = null;

	if ( task && 'assignee' in task && task.assignee ) {

		if (
			'name' in task.assignee &&
			task.assignee.name
		) {
			assigneeName = task.assignee.name;
		}

		if (
			'photo' in task.assignee &&
			task.assignee.photo &&
			'image_36x36' in task.assignee.photo &&
			task.assignee.photo.image_36x36
		) {
			assigneeImage = task.assignee.photo.image_36x36;
		}
	}

	return [ assigneeName, assigneeImage ];
}

export function getTaskDueOn(task) {

	let dueOn = null;
	if ( task && 'due_on' in task && task.due_on ) {
		dueOn = task.due_on;
	}

	return dueOn;
}

export function getTaskHtmlNotes(task) {

	let description = null;
	if ( task && 'html_notes' in task && task.html_notes ) {
		description = task.html_notes;
	}

	return description;
}

export function getTaskSubtasks(task) {

	let subtasks = [];

	if (
		task &&
		'subtasks' in task &&
		task.subtasks &&
		Array.isArray( task.subtasks )
	) {
		subtasks = task.subtasks;
	}

	return subtasks;
}

export function getTaskAttachments(task) {

	let attachments = [];

	if (
		task &&
		'attachments' in task &&
		task.attachments &&
		Array.isArray( task.attachments ) &&
		task.attachments.length > 0
	) {
		attachments = task.attachments;
	}

	return attachments;
}

export function getTaskLastImageAttachment(task) {

	const attachments = getTaskAttachments(task);

	for ( let i = attachments.length - 1; i >= 0; --i ) {
		const current = attachments[ i ];
		if ( isImage(current) ) {
			return current;
		}
	}

	return null;
}

export function getTaskTags(task) {

	let tags = [];

	if (
		task &&
		'tags' in task &&
		task.tags &&
		Array.isArray( task.tags )
	) {
		tags = task.tags;
	}

	if ( tags.length > 0 ) {
		tags.sort((a, b) => a.name.localeCompare(b.name));
	}

	return tags;
}

export function getTaskStories(task) {

	let stories = [];

	if (
		task &&
		'stories' in task &&
		task.stories &&
		Array.isArray( task.stories )
	) {
		stories = task.stories;
	}

	// Should already be sorted by story.create_at datetime
	// from Asana API response.

	return stories;
}
