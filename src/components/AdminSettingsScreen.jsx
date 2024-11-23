import { SettingsContext } from './settings/SettingsContext';

import AccountSettings from './settings/AccountSettings';
import FrontendSettings from './settings/FrontendSettings';
import WorkspaceSettings from './settings/WorkspaceSettings';

import { Button, Card, CardBody, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, SnackbarList, Spinner } from '@wordpress/components';
import { useContext, useEffect, useState } from '@wordpress/element';

export default function AdminSettingsScreen() {
	const { loadSettings, status, settings, notices, removeNotice } = useContext(SettingsContext);
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
					return <WorkspaceSettings />;
				case 'frontend':
					return <FrontendSettings />;
				default:
					return (
						<Card>
							<CardBody>
								<p>Please select an option from the left-hand menu.</p>
							</CardBody>
						</Card>
					);
			}
		} else if ( 'error' === status ) {
			return (
				<Card size='large'>
					<CardBody>
						<h2 style={{ margin: 0 }}>Failed to load your settings.</h2>
						<p style={{ marginBottom: '24px' }}>{settings}</p>
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
					<Card isBorderless={true} style={{ margin: '8px 0' }}>
						<CardBody>
							<p style={{ color: 'rgb(117, 117, 117)', margin: 0, fontSize: '0.8em', fontStyle: 'italic' }}>**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>
						</CardBody>
					</Card>
				</FlexBlock>
				<FlexItem style={{ flexBasis: '230px' }}></FlexItem>
			</Flex>
			<SnackbarList notices={notices} onRemove={removeNotice}></SnackbarList>
		</div>
	);
}
