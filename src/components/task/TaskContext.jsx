const { createContext, useState } = wp.element;

export const TaskContext = createContext(false);

export function TaskContextProvider({ children }) {
	// const [tasks, setTasks] = useState({ ...window.PTCCompletionist.tasks });
	const [tasks, setTasks] = useState(Object.values(window.PTCCompletionist.tasks));

	const context = {

		"tasks": tasks,

		test: () => {
			console.log('Starting tasks:', tasks);
			const newTasks = tasks.slice(0, tasks.length - 1);
			console.log('After change:', newTasks);
			setTasks(newTasks);
		}
	};

	console.log('TaskContextProvider context:', context);

	return (
		<TaskContext.Provider value={context}>
			<button type="button" onClick={context.test}>Test Context</button>
			{children}
		}
		</TaskContext.Provider>
	);
}
