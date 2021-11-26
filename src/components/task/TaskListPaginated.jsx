import TaskList from './TaskList.jsx';

const { useState, useCallback, useMemo, useContext, useEffect } = wp.element;

export default function TaskListPaginated({limit, tasks}) {
	const [currentPage, setCurrentPage] = useState(1);
	const totalPages = useMemo(() => Math.ceil( tasks.length / limit ), [tasks, limit]);

	const goToPage = useCallback((page) => {
		if ( page <= 1 ) {
			setCurrentPage(1);
		} else if ( page >= totalPages ) {
			setCurrentPage(totalPages);
		} else {
			setCurrentPage(page);
		}
	}, [currentPage, setCurrentPage, totalPages]);

	useEffect(() => {
		setCurrentPage(1);
	}, [tasks]);

	const start = Math.max(0, (currentPage - 1) * limit);
	const currentTasks = tasks.slice(start, currentPage * limit);

	const renderedPageButtons = [];
	for ( let i = 1; i <= totalPages; ++i ) {
		renderedPageButtons.push(
			<button className="num" type="button" title={`Page ${i}`} disabled={i === currentPage} onClick={() => goToPage(i)}>{i}</button>
		);
	}

	return (
		<div className="ptc-TaskListPaginated">

			<TaskList tasks={currentTasks} />

			<nav className="pagination">

				<button className="prev" type="button" title="Previous Page" disabled={1 === currentPage} onClick={() => goToPage(currentPage - 1)}>
					<i className="fas fa-angle-left"></i>
				</button>

				{renderedPageButtons}

				<button className="next" type="button" title="Next Page" disabled={totalPages === currentPage} onClick={() => goToPage(currentPage + 1)}>
					<i className="fas fa-angle-right"></i>
				</button>

			</nav>

		</div>
	);
}
