import { useState } from '@wordpress/element';
import { Button, Card, CardBody, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem } from '@wordpress/components';

export default function AdminSettingsScreen() {
	const [currentScreen, setCurrentScreen] = useState('dashboard');

	const menuItems = [
		{ value: 'dashboard', label: 'Dashboard' },
		{ value: 'workspace', label: 'Workspace' },
		{ value: 'frontend', label: 'Frontend' },
	];

	const renderScreenContent = () => {
		switch (currentScreen) {
			case 'dashboard':
				return <p>Welcome to the Dashboard. Overview of the plugin functionality goes here.</p>;
			case 'workspace':
				return <p>Adjust your plugin settings on this screen.</p>;
			case 'frontend':
				return <p>View reports and statistics for your plugin here.</p>;
			default:
				return <p>Select an option from the menu.</p>;
		}
	};

	return (
		<div className='ptc-AdminSettingsScreen'>
			<Flex gap={6} align='top'>
				<FlexItem>
					<MenuGroup>
						{menuItems.map(item => (
							<MenuItem
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
					<Card>
						<CardBody>
							<h2>{menuItems.find((item) => item.value === currentScreen)?.label}</h2>
							<div>{renderScreenContent()}</div>
						</CardBody>
					</Card>
				</FlexBlock>
			</Flex>
		</div>
	);
}
