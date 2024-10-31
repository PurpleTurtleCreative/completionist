import apiFetch from '@wordpress/api-fetch';
import { createContext, useState } from '@wordpress/element';

export const SettingsContext = createContext(false);

export function SettingsContextProvider({children}) {
	const [ status, setStatus ] = useState('idle'); // idle, loading, error, success.
	const [ settings, setSettings ] = useState(null);

	async function loadSettings() {
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
	};

	async function updateSettings( action = '', args = {} ) {
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
				loadSettings(); // Reload settings with updates.
			} else {
				window.console.error('Error', data);
				throw data?.message;
			}
		}).catch((error) => {
			window.console.error('Fail', error);
			setStatus('error');
			setSettings(error);
		});
	};

	function isFrontendAuthUser() {
		return ( settings?.frontend?.auth_user_id === settings?.user?.id );
	};

	function userCan( capability ) {
		return ( !! settings?.user?.capabilities?.[capability] );
	};

	function hasConnectedAsana() {
		return ( !! settings?.user?.asana_profile?.gid );
	};

	const context = {
		status,
		settings,
		loadSettings,
		updateSettings,
		isFrontendAuthUser,
		userCan,
		hasConnectedAsana,
	};

	return (
		<SettingsContext.Provider value={context}>
			{children}
		</SettingsContext.Provider>
	);
}
