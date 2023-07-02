/**
 * TaskListItem component
 *
 * @since 3.4.0
 */

import {
	getTaskCompleted,
	getTaskAssignee,
	getTaskDueOn,
	getTaskHtmlNotes,
	getTaskSubtasks,
	getTaskAttachments,
	getTaskTags,
	getTaskStories,
} from './util.js';

import { getLocaleString } from '../generic/util.jsx';

import { ReactComponent as CheckmarkIcon } from '../../../assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '../../../assets/icons/fa-code-branch-solid.svg';
import { ReactComponent as ToggleIcon } from '../../../assets/icons/fa-caret-right-solid.svg';
import { ReactComponent as StoriesIcon } from '../../../assets/icons/fa-comment-regular.svg';

import AttachmentThumbnail from '../attachment/AttachmentThumbnail.jsx';

import TaskStoriesModal from './TaskStoriesModal.jsx';

import '../../../assets/styles/scss/components/task/_TaskListItem.scss';

const { useState } = wp.element;

export default function TaskListItem({ task, rowNumber = null }) {
	const [ isExpanded, setIsExpanded ] = useState(false);
	const [ showTaskStoriesModal, setShowTaskStoriesModal ] = useState(false);

	let extraClassNames = '';

	let renderToggle = false;
	let allowToggle = false;

	let maybeCompleted = null;
	if ( 'completed' in task ) {
		const [ isCompleted, label ] = getTaskCompleted(task);
		maybeCompleted = (
			<div className="completed" data-completed={isCompleted}>
				<CheckmarkIcon aria-label={label} preserveAspectRatio="xMidYMid meet" />
			</div>
		);
	}

	let maybeSubtaskCount = null;
	let maybeSubtaskList = null;
	if ( 'subtasks' in task ) {

		renderToggle = true;

		let maybeSubtaskCountContent = null;
		const taskSubtasks = getTaskSubtasks(task);
		if ( taskSubtasks.length > 0 ) {
			allowToggle = true;
			maybeSubtaskCountContent = (
				<>
					{taskSubtasks.length}
					<SubtasksIcon aria-label="Subtasks" style={{ "transform": 'rotate(90deg)' }} preserveAspectRatio="xMidYMid meet" />
				</>
			);
			maybeSubtaskList = (
				<div className="subtasks">
					<p className="small-label">Subtasks</p>
					<ol className="tasks">
						{
							taskSubtasks.map((subtask, index) => {
								// Remove "subtasks" to prevent deeper recursion.
								const { subtasks, ...task } = subtask;
								return <TaskListItem key={JSON.stringify(task)} task={task} rowNumber={index+1} />;
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
		const [ maybeAssigneeName, maybeAssigneeImg ] = getTaskAssignee(task);
		maybeAssignee = (
			<p className="assignee">
				{ maybeAssigneeImg && <img src={maybeAssigneeImg} width="36" height="36" /> }
				{ maybeAssigneeName && <span className="assignee-name">{maybeAssigneeName}</span> }
			</p>
		);
	}

	let maybeDueDate = null;
	if ( 'due_on' in task ) {
		const taskDueOn = getTaskDueOn(task);
		maybeDueDate = <p className="due">{taskDueOn && getLocaleString(taskDueOn)}</p>;
	}

	let maybeDescription = null;
	if ( 'html_notes' in task ) {
		renderToggle = true;
		const taskHtmlNotes = getTaskHtmlNotes(task);
		if ( taskHtmlNotes ) {
			allowToggle = true;
			maybeDescription = (
				<div className="task-notes">
					<p className="small-label">Description</p>
					<div
						className="description"
						dangerouslySetInnerHTML={ { __html: taskHtmlNotes } }
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

	let maybeTags = null;
	if ( 'html_notes' in task ) {
		renderToggle = true;
		const taskTags = getTaskTags(task);
		if ( taskTags.length > 0 ) {
			allowToggle = true;
			maybeTags = (
				<div className="task-tags">
					<p className="small-label">Tags</p>
					<ol className="tags-list">
						{
							taskTags.map((tag, index) => {
								return <li className={"--has-asana-palette-color-"+tag.color}>{tag.name}</li>;
							})
						}
					</ol>
				</div>
			);
		}
	}

	let maybeTaskStoriesModalButton = null;
	const taskStories = getTaskStories(task);
	if ( taskStories.length > 0 ) {
		renderToggle = true;
		allowToggle = true;
		maybeTaskStoriesModalButton = (
			<>
				<button className="task-stories-modal-button" type="button" onClick={() => setShowTaskStoriesModal(true)}>
					<StoriesIcon preserveAspectRatio="xMidYMid meet" />
					<span>{`See Comments (${taskStories.length})`}</span>
				</button>
				{ showTaskStoriesModal && <TaskStoriesModal stories={taskStories} onCloseClick={() => setShowTaskStoriesModal(false)} /> }
			</>
		);
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
					{maybeTags}
					{maybeDescription}
					{maybeTaskStoriesModalButton}
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
