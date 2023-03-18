/**
 * TaskListItem component
 *
 * @since 3.4.0
 */

import {
	countIncompleteTasks,
	getTaskCompleted,
	getTaskName,
	getTaskAssignee,
	getTaskDueOn,
	getTaskHtmlNotes,
	getTaskSubtasks,
	getTaskAttachments
} from './util.js';

import { getLocaleString } from '../generic/util.jsx';

import { ReactComponent as CheckmarkIcon } from '../../../assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '../../../assets/icons/fa-code-branch-solid.svg';
import { ReactComponent as ToggleIcon } from '../../../assets/icons/fa-caret-right-solid.svg';

import AttachmentThumbnail from '../attachment/AttachmentThumbnail.jsx';

import '../../../assets/styles/scss/components/task/_TaskListItem.scss';

const { useState } = wp.element;

export default function TaskListItem({ task, rowNumber = null }) {
	const [ isExpanded, setIsExpanded ] = useState(false);

	let extraClassNames = '';

	let renderToggle = false;
	let allowToggle = false;

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

		let maybeSubtaskCountContent = null;
		if (
			task.subtasks &&
			Array.isArray( task.subtasks ) &&
			task.subtasks.length > 0
		) {
			allowToggle = true;
			maybeSubtaskCountContent = (
				<>
					{task.subtasks.length}
					<SubtasksIcon aria-label="Subtasks" style={{ "transform": 'rotate(90deg)' }} preserveAspectRatio="xMidYMid meet" />
				</>
			);
			maybeSubtaskList = (
				<div className="subtasks">
					<p className="small-label">Subtasks</p>
					<ol className="tasks">
						{
							task.subtasks.map((subtask, index) => {
								// Remove "subtasks" to prevent deeper recursion.
								const { subtasks, ...task } = subtask;
								return <TaskListItem task={task} rowNumber={index+1} />;
							})
						}
					</ol>
				</div>
			);
		}

		maybeSubtaskCount = <p className="subtask-count">{maybeSubtaskCountContent}</p>;
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
			maybeDueDateString = getLocaleString(task.due_on);
		}

		maybeDueDate = <p className="due">{maybeDueDateString}</p>;
	}

	let maybeDescription = null;
	if ( 'html_notes' in task ) {
		renderToggle = true;
		if ( task.html_notes ) {
			allowToggle = true;
			maybeDescription = (
				<div className="task-notes">
					<p className="small-label">Description</p>
					<div
						className="description"
						dangerouslySetInnerHTML={ { __html: task.html_notes } }
					/>
				</div>
			);
		}
	}

	let maybeAttachments = null;
	if ( 'attachments' in task ) {
		renderToggle = true;
		let taskAttachments = getTaskAttachments(task);
		// List the additional attachments, if any.
		window.console.log(task, taskAttachments);
		if ( taskAttachments.length > 0 ) {
			allowToggle = true;
			maybeAttachments = (
				<div className="task-attachments">
					<p className="small-label">Attachments</p>
					<ul className="attachments-list">
						{
							taskAttachments.map(attachment => (
								<li key={JSON.stringify(attachment)}><AttachmentThumbnail attachment={attachment} /></li>
							))
						}
					</ul>
				</div>
			);
		}
	}

	let maybeToggle = null;
	if ( renderToggle ) {
		let maybeToggleIcon = null;
		if ( allowToggle ) {
			extraClassNames += ' --can-expand';
			maybeToggleIcon = <ToggleIcon preserveAspectRatio="xMidYMid meet" />
		}
		maybeToggle = <div className="toggle">{maybeToggleIcon}</div>;
	}

	let maybeExpandedDetails = null;
	if ( isExpanded ) {
		extraClassNames += ' --is-expanded';
		maybeExpandedDetails = (
			<div className="expanded-details">
				{ rowNumber && <div className="spacer row-number"></div> }
				{ maybeToggle && <div className="spacer toggle"></div>}
				{ maybeCompleted && <div className="spacer completed"></div>}
				<div className="details">
					{maybeDescription}
					{maybeSubtaskList}
					{maybeAttachments}
				</div>
			</div>
		);
	}

	return (
		<li className={"ptc-TaskListItem"+extraClassNames}>
			<div
				className="main"
				onClick={
					allowToggle ?
					() => setIsExpanded(!isExpanded) :
					undefined
				}
			>
				{ rowNumber && <div className="row-number">{rowNumber}</div> }
				{maybeToggle}
				{maybeCompleted}
				<div className="body">
					{maybeName}
					{maybeSubtaskCount}
					{maybeAssignee}
					{maybeDueDate}
				</div>
			</div>
			{maybeExpandedDetails}
		</li>
	);
}
