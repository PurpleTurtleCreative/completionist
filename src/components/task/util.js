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

export function getAssigneeDisplayName(task) {
	let assigneeDisplayName = null;
	if ( task.assignee ) {
		if ( window.PTCCompletionist.users[ task.assignee.gid ] ) {
			assigneeDisplayName = window.PTCCompletionist.users[ task.assignee.gid ].data.display_name;
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
