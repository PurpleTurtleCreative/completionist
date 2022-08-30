const { createContext, useState } = wp.element;

export const TaskContext = createContext(false);

export function TaskContextProvider({children}) {
	const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));

	const context = {

		"tasks": tasks,

		setTaskProcessingStatus: (taskGID, processingStatus) => {
			setTasks(prevTasks => {
				return prevTasks.map(t => {
					if ( t.gid == taskGID ) {
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
						context.updateTask({
							"gid": task.gid,
							"completed": completed
						});
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
		 * @param string taskLink The Asana task link.
		 * @param int postID The WordPress post ID to pin the task.
		 */
		pinTask: async (taskLink, postID) => {

			let data = {
				'action': 'ptc_pin_task',
				'nonce': window.PTCCompletionist.api.nonce_pin,
				'task_link': taskLink,
				'post_id': postID
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
						context.addTask(res.data);
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
					alert('[Completionist] Failed to pin task.');
					return false;
				});
		},

		/**
		 * @param object taskData The new task's data.
		 * @param int postID (Optional) The WordPress post ID
		 * to pin the new task. Default null to simply create the task.
		 */
		createTask: async (taskData, postID = null) => {

			let data = {
				'action': 'ptc_create_task',
				'nonce': window.PTCCompletionist.api.nonce_create,
				...taskData
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
						context.addTask(res.data);
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
					alert('[Completionist] Failed to pin task.');
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
					if ( t.gid == taskUpdates.gid ) {
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

		addTask: (task) => {
			setTasks(prevTasks => {
				return [
					{ ...task },
					...prevTasks
				]
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
		}
	};

	return (
		<TaskContext.Provider value={context}>
			{children}
		</TaskContext.Provider>
	);
}
