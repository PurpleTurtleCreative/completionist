const { createContext, useState } = wp.element;

export const TaskContext = createContext(false);

export function TaskContextProvider({ children }) {
	// const [tasks, setTasks] = useState({ ...window.PTCCompletionist.tasks });
	const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));

	const context = {

		"tasks": tasks,

		deleteTask: (taskGID) => {
			console.warn(`@TODO: Delete task ${taskGID}`);
		},

		unpinTask: (taskGID) => {
			console.warn(`@TODO: Unpin task ${taskGID}`);
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
