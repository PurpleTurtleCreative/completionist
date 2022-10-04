import TaskActions from './TaskActions.jsx';

import { TaskContext } from './TaskContext.jsx';
import { isCriticalTask, getAssigneeDisplayName } from './util';

import '/assets/styles/scss/components/task/_TaskRow.scss';

const { useState, useCallback, useContext } = wp.element;

export default function TaskRow({task}) {
	const [showDescription, setShowDescription] = useState(false);
	const { completeTask, setTaskProcessingStatus } = useContext(TaskContext);

	const handleMarkComplete = useCallback((taskGID) => {
		if ( task.processingStatus ) {
			console.error(`Rejected handleMarkComplete. Currently ${task.processingStatus} task ${taskGID}.`);
			return;
		}
		setTaskProcessingStatus(taskGID, 'completing');
		completeTask(taskGID, !task.completed).then(success => {
			setTaskProcessingStatus(taskGID, false);
		});
	}, [task.processingStatus, setTaskProcessingStatus, completeTask]);

	const handleToggleDescription = useCallback(() => {
		if ( ! task.notes ) {
			return;
		}
		setShowDescription(!showDescription);
	}, [task, showDescription, setShowDescription]);

	const notesIconClassName = ( showDescription ) ? 'fas' : 'far';

	let assigneeDisplayName = getAssigneeDisplayName(task);

	let extraClassNames = '';
	if ( isCriticalTask(task) ) {
		extraClassNames += ' --is-critical';
	}
	if ( true === task.completed ) {
		extraClassNames += ' --is-complete';
	}
	if ( task.processingStatus ) {
		extraClassNames += ` --is-processing --is-${task.processingStatus}`;
	}
	if ( !!task.notes ) {
		extraClassNames += ' --has-description';
	}

	const markCompleteIcon = ('completing' === task.processingStatus) ? 'fa-sync-alt fa-spin' : 'fa-check';

	/**
	 * Note `timeZone` is set to UTC so no time conversion takes place
	 * since this is simply a date (which gets interpreted as midnight UTC).
	 * The displayed date should directly match what was set within Asana.
	 *
	 * However, Asana does claim their times are dynamic per the user's
	 * computer. This leads me to think they get the client's timezone code,
	 * then properly store the date/time in their database in UTC. Perhaps they
	 * didn't actually do that for simple date fields, though. Not sure how
	 * that works for companies spread across Europe and America, though many
	 * seem to regularly complain about this in the Asana forums.
	 *
	 * @link https://asana.com/guide/help/faq/common-questions#gl-timezone
	 * @link https://forum.asana.com/t/what-time-zone-is-being-used/2701/16
	 */
	const dueOnDateString = new Date(task.due_on).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC'});

	return (
		<div className={"ptc-TaskRow"+extraClassNames}>

			<button title="Mark Complete" className="mark-complete" type="button" onClick={() => handleMarkComplete(task.gid)} disabled={!!task.processingStatus}>
				<i className={`fas ${markCompleteIcon}`}></i>
			</button>

			<div className="body">

				<p className="name" onClick={handleToggleDescription}>{task.name}{!!task.notes && <i className={`${notesIconClassName} fa-sticky-note`}></i>}</p>

				<div className="details">
					{assigneeDisplayName && <p className="assignee"><i class="fas fa-user"></i> {assigneeDisplayName}</p>}
					{task.due_on && <p className="due"><i className="fas fa-clock"></i> {dueOnDateString}</p>}
				</div>

				{showDescription && <p className="description">{task.notes}</p>}

			</div>

			<div className="actions">
				<a className="cta-button" href={task.action_link.href} target={task.action_link.target}>{task.action_link.label} <i className="fas fa-long-arrow-alt-right"></i></a>
				<TaskActions taskGID={task.gid} processingStatus={task.processingStatus} />
			</div>

		</div>
	);
}
