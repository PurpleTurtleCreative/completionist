import { NoticeContext } from './NoticeContext.jsx';

import '/assets/styles/scss/components/notice/_NoticesContainer.scss';

const { useContext, useEffect } = wp.element;

export default function NoticesContainer() {
	const { addNotice, getRenderedNotices } = useContext(NoticeContext);

	useEffect(() => {
		addNotice( 'The NoticesContainer just mounted!' );
	}, []);

	return (
		<div className={"ptc-NoticesContainer"}>
			{getRenderedNotices()}
		</div>
	);
}
