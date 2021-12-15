const { createContext, useState } = wp.element;

export const TaskContext = createContext(false);

export function TaskContextProvider({children}) {
	const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));

	const context = {

		"tasks": tasks,

		setTaskProcessingStatus: (taskGID, processingStatus) => {
			setTasks(prevTasks => {
				return prevTasks.map(t => {
					if ( t.gid === taskGID ) {
						return {
							...t,
							'processingStatus': processingStatus
						};
					} else {
						return { ...t };
					}
				});
			});
		},

		completeTask: async (taskGID, completed = true) => {

			const task = context.tasks.find(t => taskGID === t.gid);

			let data = {
				'action': 'ptc_update_task',
				'nonce': window.PTCCompletionist.api.nonce,
				'task_gid': taskGID,
				'completed': completed
			};

			const init = {
				'method': 'POST',
				'credentials': 'same-origin',
				'body': new URLSearchParams(data)
			};

			return await window.fetch(window.ajaxurl, init)
				.then( res => res.json() )
				.then( res => {
					console.log(res);

					if(res.status == 'success' && res.data) {
						task.completed = completed;
						context.updateTask(task);
						return true;
					} else if(res.status == 'error' && res.data) {
						console.error(res.data);
					} else {
						alert('[Completionist] Error '+res.code+': '+res.message);
					}

					return false;
				})
				.catch( err => {
					console.error('Promise catch:', err);
					alert('[Completionist] Failed to complete task.');
					return false;
				});
		},

		deleteTask: async (taskGID) => {

			const task = context.tasks.find(t => taskGID === t.gid);

			let data = {
				'action': 'ptc_delete_task',
				'nonce': window.PTCCompletionist.api.nonce,
				'task_gid': taskGID
			};

			if ( task.action_link.post_id ) {
				data.post_id = task.action_link.post_id;
			}

			const init = {
				'method': 'POST',
				'credentials': 'same-origin',
				'body': new URLSearchParams(data)
			};

			return await window.fetch(window.ajaxurl, init)
				.then( res => res.json() )
				.then( res => {
					console.log(res);

					if(res.status == 'success' && res.data) {
						context.removeTask(res.data);
						return true;
					} else if(res.status == 'error' && res.data) {
						console.error(res.data);
					} else {
						alert('[Completionist] Error '+res.code+': '+res.message);
					}

					return false;
				})
				.catch( err => {
					console.error('Promise catch:', err);
					alert('[Completionist] Failed to delete task.');
					return false;
				});
		},

		unpinTask: async (taskGID, postID = null) => {
			const task = context.tasks.find(t => taskGID === t.gid);

			let data = {
				'action': 'ptc_unpin_task',
				'nonce': window.PTCCompletionist.api.nonce,
				'task_gid': taskGID
			};

			if ( postID ) {
				data.post_id = postID;
			}

			const init = {
				'method': 'POST',
				'credentials': 'same-origin',
				'body': new URLSearchParams(data)
			};

			return await window.fetch(window.ajaxurl, init)
				.then( res => res.json() )
				.then( res => {
					console.log(res);

					if(res.status == 'success' && res.data) {
						context.removeTask(res.data);
						return true;
					} else if(res.status == 'error' && res.data) {
						console.error(res.data);
					} else {
						alert('[Completionist] Error '+res.code+': '+res.message);
					}

					return false;
				})
				.catch( err => {
					console.error('Promise catch:', err);
					alert('[Completionist] Failed to unpin task.');
					return false;
				});
		},

		/**
		 * @param object taskUpdates A task object containing the "gid" and only
		 * the necessary fields to override.
		 */
		updateTask: (taskUpdates) => {
			setTasks(prevTasks => {
				return prevTasks.map(t => {
					if ( t.gid === task.gid ) {
						return {
							...t,
							...taskUpdates
						};
					} else {
						return { ...t };
					}
				});
			});
		},

		removeTask: (taskGID) => {
			setTasks(prevTasks => {
				return prevTasks.map(t => {
					if ( t.gid != taskGID ) {
						return { ...t };
					}
				}).filter(t => !!t);
			});
		},

		getTaskUrl: (taskGID) => {
			return `https://app.asana.com/0/0/${taskGID}/f`;
		},

		isCriticalTask: (task) => {
			const DAY_IN_SECONDS = 86400;
			const limit = 7 * DAY_IN_SECONDS;
			return ( ( Date.parse(task.due_on) - Date.now() ) < limit );
		},

		filterIncompleteTasks: (tasks) => {
			return tasks.filter(t => !t.completed);
		},

		filterCriticalTasks: (tasks) => {
			return tasks.filter(t => context.isCriticalTask(t));
		},

		filterMyTasks: (userGID, tasks) => {
			return tasks.filter(t => {
				if ( t.assignee ) {
					return ( userGID === t.assignee.gid );
				}
				return false;
			});
		},

		filterGeneralTasks: (tasks) => {
			return tasks.filter(t => {
				if ( t.action_link && t.action_link.post_id > 0 ) {
					return false;
				}
				return true;
			});
		},

		filterPinnedTasks: (tasks) => {
			return tasks.filter(t => {
				if ( t.action_link && t.action_link.post_id > 0 ) {
					return true;
				}
				return false;
			});
		}
	};

	return (
		<TaskContext.Provider value={context}>
			{children}
		</TaskContext.Provider>
	);
}
