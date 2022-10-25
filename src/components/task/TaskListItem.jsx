import { countIncompleteTasks } from './util';

import { ReactComponent as CheckmarkIcon } from '/assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '/assets/icons/fa-code-branch-solid.svg';

import '/assets/styles/scss/components/task/_TaskListItem.scss';

const { useState } = wp.element;

export default function TaskListItem({ task, rowNumber = null }) {

	let extraClassNames = '';

	let maybeCompleted = null;
	if ( 'completed' in task ) {
		let label = 'Incomplete';
		if ( true === task.completed ) {
			extraClassNames += ' --is-complete';
			label = 'Completed';
		}
		maybeCompleted = (
			<div className="completed" data-completed={task.completed}>
				<CheckmarkIcon title={label} preserveAspectRatio="xMidYMid meet" />
			</div>
		);
	}

	let maybeSubtaskCount = null;
	if ( 'subtasks' in task ) {

		let maybeSubtaskContent = null;
		if (
			task.subtasks &&
			Array.isArray( task.subtasks )
		) {
			const incompleteSubtasksCount = countIncompleteTasks(task.subtasks);
			if ( incompleteSubtasksCount > 0 ){
				maybeSubtaskContent = (
					<>
						{incompleteSubtasksCount}
						<SubtasksIcon title="Subtasks" style={{ "transform": 'rotate(90deg)' }} preserveAspectRatio="xMidYMid meet" />
					</>
				);
			}
		}

		maybeSubtaskCount = <p className="subtask-count">{maybeSubtaskContent}</p>;
	}

	let maybeName = null;
	if ( 'name' in task && task.name ) {
		maybeName = <p className="task-name">{task.name}</p>;
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
		if ( 'due_on' in task && task.due_on ) {
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

	return (
		<li className={"ptc-TaskListItem"+extraClassNames}>
			{ rowNumber && <div className="row-number">{rowNumber}</div> }
			{maybeCompleted}
			<div className="body">
				{maybeName}
				{maybeSubtaskCount}
				{maybeAssignee}
				{maybeDueDate}
			</div>
		</li>
	);
}
