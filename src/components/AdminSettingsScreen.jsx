import { SettingsContext } from './settings/SettingsContext';

import AccountSettings from './settings/AccountSettings';
import FrontendSettings from './settings/FrontendSettings';
import WorkspaceSettings from './settings/WorkspaceSettings';

import { Button, Card, CardBody, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, SnackbarList, Spinner } from '@wordpress/components';
import { useContext, useEffect, useState } from '@wordpress/element';
import MissingPermissionsBadge from './users/MissingPermissionsBadge';

export default function AdminSettingsScreen() {
	const { loadSettings, status, settings, notices, removeNotice, hasConnectedAsana, isWorkspaceMember, userCan } = useContext(SettingsContext);
	const [currentScreen, setCurrentScreen] = useState(() => {
		const params = new URLSearchParams(window.location.search);
		return params.get('tab') || 'account';
	});

	useEffect(() => {
		loadSettings();
	}, []);

	useEffect(() => {
		// Update the query parameter in the URL when `currentScreen` changes.
		const url = new URL(window.location.href);
		url.searchParams.set('tab', currentScreen);
		window.history.replaceState({}, '', url.toString());
	}, [currentScreen]);

	const menuItems = [
		{ value: 'account', label: 'Asana Account' },
		{ value: 'workspace', label: 'Workspace' },
		{ value: 'frontend', label: 'Frontend' },
	];

	const renderScreenContent = () => {
		if ( 'success' === status ) {
			switch (currentScreen) {
				case 'account':
					return <AccountSettings />;
				case 'workspace':
					return (
						hasConnectedAsana() ?
						(
							( isWorkspaceMember() || userCan('manage_options') ) ?
							<WorkspaceSettings /> :
							<Card style={{ textAlign: 'center', padding: '64px' }}>
								<CardBody>
									<MissingPermissionsBadge label='Missing permissions' />
									<h2 style={{ margin: '1em', fontSize: '20px' }}>You are not a member of this site's Asana workspace</h2>
									<p style={{ margin: '0 auto 2em', maxWidth: '40em' }}>To view workspace details, you must be a member of the designated Asana workspace or have administrative capabilities to manage options.</p>
								</CardBody>
							</Card>
						) :
						<Card style={{ textAlign: 'center', padding: '64px' }}>
							<CardBody>
								<MissingPermissionsBadge label='Requires Asana account' />
								<h2 style={{ margin: '1em', fontSize: '20px' }}>Track relevant Asana tasks</h2>
								<p style={{ margin: '0 auto 2em', maxWidth: '40em' }}>Completionist uses the Asana workspace and associated site tag to determine relevant tasks to display in wp-admin on this site.</p>
								<Button
									__next40pxDefaultSize
									variant='primary'
									text='Connect Asana'
									onClick={() => { setCurrentScreen('account'); }}
									style={{ paddingLeft: '2em', paddingRight: '2em' }}
								/>
							</CardBody>
						</Card>
					);
				case 'frontend':
					return <FrontendSettings />;
				default:
					return (
						<Card style={{ textAlign: 'center', padding: '64px' }}>
							<CardBody>
								<p>Please select an option from the left-hand menu.</p>
							</CardBody>
						</Card>
					);
			}
		} else if ( 'error' === status ) {
			return (
				<Card style={{ textAlign: 'center', padding: '64px' }}>
					<CardBody>
						<h2 style={{ margin: 0 }}>Failed to load your settings.</h2>
						<p style={{ margin: '1em auto 2em', maxWidth: '40em' }}>{settings}</p>
						<Button
							__next40pxDefaultSize
							variant='primary'
							icon='update'
							text='Reload Settings'
							onClick={loadSettings}
						/>
					</CardBody>
				</Card>
			);
		} else {
			return (
				<Card size='large'>
					<CardBody>
						<Flex gap={0} align='center' justify='center'>
							<Spinner />
							<p style={{ margin: '5px 0 0' }}>Loading your settings...</p>
						</Flex>
					</CardBody>
				</Card>
			);
		}
	};

	return (
		<div className='ptc-AdminSettingsScreen' style={{ position: 'relative' }}>
			<Flex gap={6} justify='space-between' align='top'>
				<FlexItem style={{ flexBasis: '200px' }}>
					<MenuGroup>
						{menuItems.map(item => (
							<MenuItem
								key={item.value}
								role='menuitemradio'
								isSelected={currentScreen === item.value}
								onClick={() => setCurrentScreen(item.value)}
								style={{
									display: 'block',
									borderColor: 'transparent',
									borderLeft: '4px solid transparent',
									...( currentScreen === item.value ? {
										fontWeight: 'bold',
										color: 'var(--wp-admin-theme-color)',
										borderLeft: '4px solid var(--wp-admin-theme-color)',
									} : {})
								}}
							>
								{item.label}
							</MenuItem>
						))}
					</MenuGroup>
				</FlexItem>
				<FlexBlock style={{ maxWidth: '850px' }}>
					{renderScreenContent()}
				</FlexBlock>
				<FlexItem style={{ flexBasis: '230px' }}></FlexItem>
			</Flex>
			<SnackbarList notices={notices} onRemove={removeNotice}></SnackbarList>
		</div>
	);
}
