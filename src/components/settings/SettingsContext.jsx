import apiFetch from '@wordpress/api-fetch';
import { createContext, useState } from '@wordpress/element';

export const SettingsContext = createContext(false);

export function SettingsContextProvider({children}) {
	const [ status, setStatus ] = useState('idle'); // idle, loading, error, success.
	const [ settings, setSettings ] = useState(null);

	const context = {

		"status": status,
		"settings": settings,

		loadSettings: async () => {
			setStatus('loading');
			apiFetch({
				path: '/completionist/v1/settings',
				method: 'GET',
			}).then((data) => {
				setSettings(data);
				setStatus('success');
			}).catch((error) => {
				window.console.error('Error:', error);
				setStatus('error');
				setSettings(error);
			});
		},

		updateSettings: async ( action = '', args = {} ) => {
			setStatus('loading');
			apiFetch({
				path: '/completionist/v1/settings',
				method: 'PUT',
				data: {
					action,
					action_nonce: window?.ptc_completionist_settings?.auth?.[`nonce_${action}`],
					...args,
				},
			}).then((data) => {
				if ( 'success' === data?.status ) {
					window.console.log('Success', data);
					context.loadSettings(); // Reload settings with updates.
				} else {
					window.console.error('Error', data);
					throw data?.message;
				}
			}).catch((error) => {
				window.console.error('Fail', error);
				setStatus('error');
				setSettings(error);
			});
		}
	};

	return (
		<SettingsContext.Provider value={context}>
			{children}
		</SettingsContext.Provider>
	);
}
