import { countIncompleteTasks } from './util';

import { ReactComponent as CheckmarkIcon } from '/assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '/assets/icons/fa-code-branch-solid.svg';
import { ReactComponent as ToggleIcon } from '/assets/icons/fa-caret-right-solid.svg';

import '/assets/styles/scss/components/task/_TaskListItem.scss';

const { useState } = wp.element;

export default function TaskListItem({ task, rowNumber = null }) {
	const [ isExpanded, setIsExpanded ] = useState(false);

	let extraClassNames = '';

	let renderToggle = false;
	let showToggle = false;

	let maybeCompleted = null;
	if ( 'completed' in task ) {
		let label = 'Incomplete';
		if ( true === task.completed ) {
			extraClassNames += ' --is-completed';
			label = 'Completed';
		}
		maybeCompleted = (
			<div className="completed" data-completed={task.completed}>
				<CheckmarkIcon aria-label={label} preserveAspectRatio="xMidYMid meet" />
			</div>
		);
	}

	let maybeSubtaskCount = null;
	let maybeSubtaskList = null;
	if ( 'subtasks' in task ) {

		renderToggle = true;

		let maybeSubtaskContent = null;
		if (
			task.subtasks &&
			Array.isArray( task.subtasks )
		) {
			const incompleteSubtasksCount = countIncompleteTasks(task.subtasks);
			if ( incompleteSubtasksCount > 0 ) {
				showToggle = true;
				maybeSubtaskContent = (
					<>
						{incompleteSubtasksCount}
						<SubtasksIcon aria-label="Subtasks" style={{ "transform": 'rotate(90deg)' }} preserveAspectRatio="xMidYMid meet" />
					</>
				);
				maybeSubtaskList = (
					<ul className="subtasks">
						{
							task.subtasks.map((subtask, index) => {
								// Remove "subtasks" to prevent infinite recursion.
								const { subtasks, ...task } = subtask;
								return <TaskListItem task={task} rowNumber={index+1} />;
							})
						}
					</ul>
				);
			}
		}

		maybeSubtaskCount = <p className="subtask-count">{maybeSubtaskContent}</p>;
	}

	let maybeName = null;
	if ( 'name' in task ) {
		maybeName = <p className="task-name">{task.name ?? ''}</p>;
	}

	let maybeAssignee = null;
	if ( 'assignee' in task ) {

		let maybeAssigneeName = null;
		let maybeAssigneeImg = null;

		if (
			task.assignee &&
			'name' in task.assignee &&
			task.assignee.name
		) {

			maybeAssigneeName = task.assignee.name;

			if (
				'photo' in task.assignee &&
				task.assignee.photo &&
				'image_36x36' in task.assignee.photo &&
				task.assignee.photo.image_36x36
			) {
				maybeAssigneeImg = <img src={task.assignee.photo.image_36x36} width="36" height="36" />;
			}
		}

		maybeAssignee = (
			<p className="assignee">
				{ maybeAssigneeImg }
				{ maybeAssigneeName && <span className="assignee-name">{maybeAssigneeName}</span> }
			</p>
		);
	}

	let maybeDueDate = null;
	if ( 'due_on' in task ) {

		let maybeDueDateString = null;
		if ( task.due_on ) {
			maybeDueDateString = new Date(task.due_on).toLocaleDateString(
				undefined,
				{
					month: 'short',
					day: 'numeric',
					timeZone: 'UTC'
				}
			);
		}

		maybeDueDate = <p className="due">{maybeDueDateString}</p>;
	}

	let maybeDescription = null;
	if ( 'html_notes' in task ) {
		renderToggle = true;
		if ( task.html_notes ) {
			showToggle = true;
			maybeDescription = (
			<div
				className="description"
				dangerouslySetInnerHTML={ { __html: task.html_notes } }
			/>
		);
		}
	}

	let maybeToggle = null;
	if ( renderToggle ) {
		let maybeToggleIcon = null;
		if ( showToggle ) {
			maybeToggleIcon = <ToggleIcon preserveAspectRatio="xMidYMid meet" onClick={() => setIsExpanded(!isExpanded)} />
		}
		maybeToggle = <div className="toggle">{maybeToggleIcon}</div>;
	}

	let maybeExpandedDetails = null;
	if ( isExpanded ) {
		maybeExpandedDetails = (
			<div className="expanded-details">
				{maybeDescription}
				{maybeSubtaskList}
			</div>
		);
	}

	return (
		<li className={"ptc-TaskListItem"+extraClassNames}>
			{ rowNumber && <div className="row-number">{rowNumber}</div> }
			{maybeToggle}
			{maybeCompleted}
			<div className="body">
				{maybeName}
				{maybeSubtaskCount}
				{maybeAssignee}
				{maybeDueDate}
				{maybeExpandedDetails}
			</div>
		</li>
	);
}
