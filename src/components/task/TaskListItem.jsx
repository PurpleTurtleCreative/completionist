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
} from './util.js';

import { getLocaleString } from '../generic/util.jsx';

import { findAndMonitorLoadingMedia } from '../attachment/util.jsx';

import { ReactComponent as CheckmarkIcon } from '../../../assets/icons/fa-check-solid.svg';
import { ReactComponent as SubtasksIcon } from '../../../assets/icons/fa-code-branch-solid.svg';
import { ReactComponent as ToggleIcon } from '../../../assets/icons/fa-caret-right-solid.svg';

import AttachmentThumbnail from '../attachment/AttachmentThumbnail.jsx';

import '../../../assets/styles/scss/components/task/_TaskListItem.scss';

const { useState, useEffect, useRef } = wp.element;

export default function TaskListItem({ task, rowNumber = null }) {
	const [ isExpanded, setIsExpanded ] = useState(false);
	const rootRef = useRef(null);

	useEffect(() => {
		if ( rootRef.current ) {
			findAndMonitorLoadingMedia(rootRef.current);
		}
	}, [ rootRef.current, isExpanded ]);

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

	/**
	 * Filters whether the toggle column should be rendered
	 * in the TaskListItem.
	 *
	 * @since [unreleased]
	 *
	 * @param {boolean} renderToggle If to render the toggle column.
	 * @param {Object}  task The represented task.
	 */
	renderToggle = window.Completionist.hooks.applyFilters(
		'TaskListItem_if_render_toggle',
		renderToggle,
		task
	);

	/**
	 * Filters whether the TaskListItem can be toggled (expanded).
	 *
	 * @since [unreleased]
	 *
	 * @param {boolean} allowToggle If to allow toggling (expansion).
	 * @param {Object}  task The represented task.
	 */
	allowToggle = window.Completionist.hooks.applyFilters(
		'TaskListItem_if_allow_toggle',
		allowToggle,
		task
	);

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

		/**
		 * Filters content to display after the task
		 * description within a TaskListItem.
		 *
		 * Note that a task does not require a description
		 * for this content to be displayed.
		 *
		 * @since [unreleased]
		 *
		 * @param {Object[]} content An array of React
		 * components or JSX elements to be rendered.
		 * @param {Object}   task The represented task.
		 */
		const maybeContentAfterDescription = window.Completionist.hooks.applyFilters(
			'TaskListItem_content_after_description',
			[],
			task
		);

		maybeExpandedDetails = (
			<div className="expanded-details">
				{ rowNumber && <div className="spacer row-number"></div> }
				{ maybeToggle && <div className="spacer toggle"></div>}
				{ maybeCompleted && <div className="spacer completed"></div>}
				<div className="details">
					{maybeTags}
					{maybeDescription}
					{maybeContentAfterDescription}
					{maybeSubtaskList}
					{maybeAttachments}
				</div>
			</div>
		);
	}

	return (
		<li className={"ptc-TaskListItem"+extraClassNames} ref={rootRef}>
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
