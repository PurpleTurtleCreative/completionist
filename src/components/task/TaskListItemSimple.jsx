/**
 * TaskListItemSimple component
 *
 * @since [unreleased]
 */

import {
	getTaskCompleted,
	getTaskName,
	getTaskAssignee,
	getTaskDueOn,
	getTaskSubtasks
} from './util.js';

import { getLocaleString } from '../generic/util.jsx';

import { ReactComponent as CheckmarkIcon } from '../../../assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '../../../assets/icons/fa-code-branch-solid.svg';

import '../../../assets/styles/scss/components/task/_TaskListItemSimple.scss';

export default function TaskListItemSimple({ task, onClick }) {

	let extraClassNames = '';

	let maybeCompleted = <div className="spacer completed"></div>;
	if ( 'completed' in task ) {

		const [ taskCompleted, taskCompletedLabel ] = getTaskCompleted(task);

		if ( true === taskCompleted ) {
			extraClassNames += ' --is-completed';
		}

		maybeCompleted = (
			<div className="completed" data-completed={taskCompleted} aria-label={taskCompletedLabel}>
				<CheckmarkIcon preserveAspectRatio="xMidYMid meet" />
			</div>
		);
	} else {
		maybeCompleted = null;
	}

	let maybeSubtaskCount = <div className="spacer subtask-count"></div>;
	if ( 'subtasks' in task ) {
		const taskSubtasks = getTaskSubtasks(task);
		if ( taskSubtasks.length > 0 ) {
			maybeSubtaskCount = (
				<p className="subtask-count">
					{taskSubtasks.length}
					<SubtasksIcon aria-label="Subtasks" style={{ "transform": 'rotate(90deg)' }} preserveAspectRatio="xMidYMid meet" />
				</p>
			);
		}
	} else {
		maybeSubtaskCount = null;
	}

	let maybeName = <div className="spacer task-name"></div>;
	if ( 'name' in task ) {
		let taskName = getTaskName(task);
		if ( taskName ) {
			maybeName = <p className="task-name">{task.name}</p>;
		}
	} else {
		maybeName = null;
	}

	let maybeAssignee = <div className="spacer assignee"></div>;
	if ( 'assignee' in task ) {
		const [ _, taskAssigneeImage ] = getTaskAssignee(task);
		if ( taskAssigneeImage ) {
			maybeAssignee = <img className="assignee" src={taskAssigneeImage} width="36" height="36" />;
		}
	} else {
		maybeAssignee = null;
	}

	let maybeDueDate = <div className="spacer due"></div>;
	if ( 'due_on' in task ) {
		const taskDueOn = getTaskDueOn(task);
		if ( taskDueOn ) {
			maybeDueDate = (
				<p className="due">{getLocaleString(taskDueOn)}</p>
			);
		}
	} else {
		maybeDueDate = null;
	}

	return (
		<li className={"ptc-TaskListItemSimple"+extraClassNames} onClick={onClick}>
			{maybeCompleted}
			{maybeName}
			{maybeSubtaskCount}
			{maybeDueDate}
			{maybeAssignee}
		</li>
	);
}
