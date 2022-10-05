import { NoticeContext } from './NoticeContext.jsx';

// import '/assets/styles/scss/components/notice/_NoteBox.scss';

const { useContext, useEffect } = wp.element;

export default function NoticesDisplay() {
	const { addNotice, getRenderedNotices } = useContext(NoticeContext);

	useEffect(() => {
		window.console.log('!! NoticesDisplay mounted !!');
		addNotice(
			'First notice for testing unique IDs and functionality.',
			'info',
			true,
			[]
		);
		addNotice(
			'Second notice with warning status.',
			'warning',
			false,
			[]
		);
		addNotice(
			'Third notice with error status.',
			'error',
			false,
			[]
		);
		addNotice(
			'Fourth notice with success status.',
			'success',
			true,
			[]
		);

		return () => {
			window.console.log('>> NoticesDisplay unmounted <<');
		}
	}, []);

	return (
		<div className={"ptc-NoticesDisplay"}>
			{getRenderedNotices()}
		</div>
	);
}
