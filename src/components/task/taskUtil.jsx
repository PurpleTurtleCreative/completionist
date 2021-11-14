export function deleteTask(taskGID) {
	console.log(`@TODO: Delete task ${taskGID}`);
}

export function unpinTask(taskGID) {
	console.log(`@TODO: Unpin task ${taskGID}`);
}

export function getTaskUrl(taskGID) {
	return `https://app.asana.com/0/0/${taskGID}/f`;
}

export function isCriticalTask(task) {
	const DAY_IN_SECONDS = 86400;
	const limit = 7 * DAY_IN_SECONDS;
	return ( ( Date.parse(task.due_on) - Date.now() ) < limit );
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
	// @TODO - Need to figure out storing task pinned post IDs.
	console.warn('@TODO - filterGeneralTasks');
	return tasks;
}

export function filterPinnedTasks(tasks) {
	// @TODO - Need to figure out storing task pinned post IDs.
	console.warn('@TODO - filterPinnedTasks');
	return tasks;
}
