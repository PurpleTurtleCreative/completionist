import { ReactComponent as CheckmarkIcon } from '/assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '/assets/icons/fa-code-branch-solid.svg';

import '/assets/styles/scss/components/task/_TaskListItem.scss';

const { useState } = wp.element;

export default function TaskListItem({ task }) {

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
				<CheckmarkIcon title={label} />
			</div>
		);
	}

	let maybeSubtaskCount = null;
	if (
		'subtasks' in task &&
		task.subtasks &&
		Array.isArray( task.subtasks ) &&
		task.subtasks.length > 0
	) {
		maybeSubtaskCount = (
			<p className="subtask-count">
				{task.subtasks.length}
				<SubtasksIcon title="Subtasks" style={{ "transform": 'rotate(90deg)' }} width="16" height="16" />
			</p>
		);
	}

	let maybeName = null;
	if ( 'name' in task && task.name ) {
		maybeName = <p className="name">{task.name}{maybeSubtaskCount}</p>;
	}

	let maybeAssignee = null;
	if (
		'assignee' in task &&
		task.assignee &&
		'name' in task.assignee &&
		task.assignee.name
	) {
		let maybeAssigneeImg = null;
		if (
			'photo' in task.assignee &&
			task.assignee.photo &&
			'image_36x36' in task.assignee.photo &&
			task.assignee.photo.image_36x36
		) {
			maybeAssigneeImg = <img src={task.assignee.photo.image_36x36} width="36" height="36" />;
		}
		maybeAssignee = (
			<p className="assignee">
				{maybeAssigneeImg}
				<span className="assignee-name">{task.assignee.name}</span>
			</p>
		);
	}

	let maybeDueDate = null;
	if ( 'due_on' in task && task.due_on ) {
		const dueOnDateString = new Date(task.due_on).toLocaleDateString(undefined, {month: 'short', day: 'numeric', timeZone: 'UTC'});
		maybeDueDate = <p className="due">{dueOnDateString}</p>;
	}

	return (
		<li className={"ptc-TaskListItem"+extraClassNames}>
			{maybeCompleted}
			{maybeName}
			{maybeAssignee}
			{maybeDueDate}
		</li>
	);
}
