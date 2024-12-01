import apiFetch from '@wordpress/api-fetch';
import { createContext, useState } from '@wordpress/element';

export const SettingsContext = createContext(false);

export function SettingsContextProvider({children}) {
	const [ status, setStatus ] = useState('idle'); // idle, loading, error, success.
	const [ settings, setSettings ] = useState(null);
	const [ notices, setNotices ] = useState([]);

	async function loadSettings() {
		setStatus('loading');
		apiFetch({
			url: `${window.ptc_completionist_settings.api.v1}/settings`,
			method: 'GET',
		}).then((data) => {

			if ( 'success' !== data?.status || ! data?.data ) {
				throw new Error(data);
			}

			setSettings(data.data);
			setStatus('success');
			addNotice({
				id: 'LOAD_SETTINGS',
				content: ( data?.message || 'Settings loaded successfully!' ),
			});
		}).catch((error) => {
			window.console.error('Error', error);

			let errorMessage = ( error?.message || error );
			if ( 'string' !== typeof errorMessage ) {
				errorMessage = 'Failed to load settings.';
			}

			setStatus('error');
			setSettings(errorMessage);
			addNotice({
				id: 'LOAD_SETTINGS',
				content: errorMessage,
				explicitDismiss: true,
				actions: [
					{
						"label": 'Refresh',
						"onClick": () => { window.location.reload(); },
					}
				],
			});
		});
	}

	async function updateSettings( action = '', args = {} ) {
		setStatus('loading');
		apiFetch({
			url: `${window.ptc_completionist_settings.api.v1}/settings`,
			method: 'PUT',
			data: {
				action,
				action_nonce: window?.ptc_completionist_settings?.api?.[`nonce_${action}`],
				...args,
			},
		}).then((data) => {

			if ( 'success' !== data?.status ) {
				throw new Error(data);
			}

			addNotice({
				id: 'UPDATE_SETTINGS',
				content: ( data?.message || 'Settings updated successfully!' ),
			});
			loadSettings(); // Reload settings with the latest changes.
		}).catch((error) => {
			window.console.error('Error', error);

			let errorMessage = ( error?.message || error );
			if ( 'string' !== typeof errorMessage ) {
				errorMessage = 'Failed to update settings.';
			}

			setStatus('error');
			setSettings(errorMessage);
			addNotice({
				id: 'UPDATE_SETTINGS',
				content: errorMessage,
				explicitDismiss: true,
				actions: [
					{
						"label": 'Retry',
						"onClick": () => { updateSettings( ...arguments ); },
					}
				],
			});
		});
	}

	//
	// Current user.
	//

	function isWorkspaceMember() {
		return ( true === settings?.user?.is_site_workspace_member );
	}

	function isFrontendAuthUser() {
		return ( settings?.frontend?.auth_user_id === settings?.user?.id );
	}

	function userCan( capability ) {
		return ( !! settings?.user?.capabilities?.[capability] );
	}

	function hasConnectedAsana() {
		return ( !! settings?.user?.asana_profile?.gid );
	}

	//
	// Generic data.
	//

	function getWorkspaceCollaborators() {
		const collaborators = {};
		for ( const user_gid in settings?.workspace?.connected_workspace_users ) {
			collaborators[ user_gid ] = {
				...settings.workspace.connected_workspace_users[ user_gid ],
				"hasConnectedAsana": true,
			}
		}
		for ( const user_gid in settings?.workspace?.found_workspace_users ) {
			if ( ! collaborators?.[ user_gid ] ) {
				collaborators[ user_gid ] = {
					...settings.workspace.found_workspace_users[ user_gid ],
					"hasConnectedAsana": false,
				}
			}
		}
		return collaborators;
	}

	//
	// Notices.
	//

	function addNotice(notice) {
		setNotices(prevState => [
			...prevState.filter(({id}) => id !== notice.id), // Prevent duplicate IDs.
			notice,
		]);
	}

	function removeNotice(id) {
		setNotices(prevState => [ ...prevState.filter((notice) => id !== notice.id)]);
	}

	//////////////////////////////////////////////

	const context = {
		status,
		settings,
		notices,
		loadSettings,
		updateSettings,
		isWorkspaceMember,
		isFrontendAuthUser,
		userCan,
		hasConnectedAsana,
		getWorkspaceCollaborators,
		addNotice,
		removeNotice,
	};

	return (
		<SettingsContext.Provider value={context}>
			{children}
		</SettingsContext.Provider>
	);
}
