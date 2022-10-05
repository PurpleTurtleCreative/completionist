import { Notice } from '@wordpress/components';

const { createContext, useState, useEffect } = wp.element;

export const NoticeContext = createContext(false);

export function NoticeContextProvider({children}) {
	/*
	Note that only ONE NoticeContextProvider may be used per page load
	due to storing state in the top-level global variable.
	*/
	const [notices, setNotices] = useState(window.PTCCompletionist.notices ?? []);

	useEffect(() => {
		/*
		Save the current state on unmount,
		so it can be correctly restored on re-mount.
		*/
		return () => {
			window.PTCCompletionist.notices = notices;
		};
	}, [notices]);

	const context = {

		"notices": notices,

		/**
		 * Adds a new WordPress Notice component.
		 *
		 * See the linked documentation to understand component parameters.
		 *
		 * @link https://developer.wordpress.org/block-editor/reference-guides/components/notice/
		 */
		addNotice: (children, status = 'info', isDismissible = true, actions = []) => {
			setNotices(prevNotices => {
				return [
					...prevNotices,
					{
						"key": `${Date.now()}${prevNotices.length}`,
						"children": children,
						"status": status,
						"isDismissible": isDismissible,
						"actions": actions
					}
				]
			});
		},

		removeNotice: (notice) => {
			setNotices(prevNotices => {
				return prevNotices.filter(n => ( n !== notice ));
			});
		},

		getRenderedNotices: () => {
			return context.notices.map(n => {
				const { key, children, ...props } = n;
				return <Notice key={key} {...props} onRemove={() => context.removeNotice(n)}>{children}</Notice>;
			});
		}
	};

	return (
		<NoticeContext.Provider value={context}>
			{children}
		</NoticeContext.Provider>
	);
}
