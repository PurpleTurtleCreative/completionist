import { SettingsContext } from './settings/SettingsContext';

import AccountSettings from './settings/AccountSettings';
import FrontendSettings from './settings/FrontendSettings';
import WorkspaceSettings from './settings/WorkspaceSettings';

import { Card, CardBody, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, Spinner } from '@wordpress/components';
import { useContext, useEffect, useState } from '@wordpress/element';

export default function AdminSettingsScreen() {
	const { loadSettings, status } = useContext(SettingsContext);
	const [currentScreen, setCurrentScreen] = useState('account');

	useEffect(() => {
		loadSettings();
	}, []);

	const menuItems = [
		{ value: 'account', label: 'Asana Account' },
		{ value: 'workspace', label: 'Workspace' },
		{ value: 'frontend', label: 'Frontend' },
	];

	const renderScreenContent = () => {
		if ( 'success' !== status ) {
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
		} else {
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
		}
	};

	return (
		<div className='ptc-AdminSettingsScreen'>
			<Flex gap={6} align='top'>
				<FlexItem style={{ flexBasis: '230px' }}>
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
				<FlexBlock>
					{renderScreenContent()}
					<Card isBorderless={true} style={{ margin: '8px 0' }}>
						<CardBody>
							<p style={{ color: 'rgb(117, 117, 117)', margin: 0, fontSize: '0.8em', fontStyle: 'italic' }}>**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>
						</CardBody>
					</Card>
				</FlexBlock>
				<FlexItem style={{ flexBasis: '230px' }}></FlexItem>
			</Flex>
		</div>
	);
}
