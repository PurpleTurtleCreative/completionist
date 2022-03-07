/**
 * Utility functions unrelated to application state.
 */

export function getTaskUrl(taskGID) {
	return `https://app.asana.com/0/0/${taskGID}/f`;
}

export function isCriticalTask(task) {
	const DAY_IN_SECONDS = 86400;
	const limit = 7 * DAY_IN_SECONDS;
	return ( ( Date.parse(task.due_on) - Date.now() ) < limit );
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
