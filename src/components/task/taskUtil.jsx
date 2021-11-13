export function deleteTask(taskGID) {
	console.log(`@TODO: Delete task ${taskGID}`);
}

export function unpinTask(taskGID) {
	console.log(`@TODO: Unpin task ${taskGID}`);
}

export function getTaskUrl(taskGID) {
	return `https://app.asana.com/0/0/${taskGID}/f`;
}
