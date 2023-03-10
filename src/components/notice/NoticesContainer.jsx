import { NoticeContext } from './NoticeContext.jsx';

import '../../../assets/styles/scss/components/notice/_NoticesContainer.scss';

const { useContext } = wp.element;

export default function NoticesContainer() {
	const { addNotice, getRenderedNotices } = useContext(NoticeContext);

	return (
		<div className="ptc-NoticesContainer">
			{getRenderedNotices()}
		</div>
	);
}
