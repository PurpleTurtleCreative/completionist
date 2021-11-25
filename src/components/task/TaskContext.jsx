const { createContext, useState } = wp.element;

export const TaskContext = createContext(false);

export function TaskContextProvider({ children }) {
	// const [tasks, setTasks] = useState({ ...window.PTCCompletionist.tasks });
	const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));

	const context = {

		"tasks": tasks,

		deleteTask: async (taskGID) => {
			console.warn(`@TODO: Delete task ${taskGID}`);
			return;//TESTING==============================

			const task = context.tasks[ taskGID ];

			let data = {
				'action': 'ptc_delete_task',
				'nonce': window.PTCCompletionist.api.nonce,
				'task_gid': taskGID
			};

			if ( task.action_link.post_id ) {
				data.post_id = task.action_link.post_id;
			}

			const init = {
				method: 'POST',
				body: data
			};

			await window.fetch(window.ajaxurl, init)
				.then( res => res.json() )
				.then( res => {

					if(res.status == 'success' && res.data != '') {
						context.removeTask(res.data);
					} else if(res.status == 'error' && res.data != '') {
						// display_alert_html(res.data);
					} else {
						alert('[Completionist] Error '+res.code+': '+res.message);
					}
				})
				.catch(function() {
					alert('[Completionist] Failed to delete task.');
				});
		},

		unpinTask: (taskGID) => {
			console.warn(`@TODO: Unpin task ${taskGID}`);
		},

		removeTask: (taskGID) => {
			const newTasks = tasks.filter(t => t.gid !== taskGID);
			setTasks(newTasks);
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
