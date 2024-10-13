import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardDivider, CardHeader, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, TextControl } from '@wordpress/components';

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
				return (
					<>
						<CardBody>
							<Flex justify='start' align='center'>
								<img
									src='https://www.gravatar.com/avatar/?d=mp'
									alt='Profile Image'
									height={40}
									width={40}
									style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
								/>
								<div>
									<h3>You're connected, Michelle!</h3>
								</div>
							</Flex>
						</CardBody>
						<CardBody>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								type='password'
								label='Asana PAT'
								help='Connect your Asana account by entering your Personal Access Token (PAT).'
							/>
						</CardBody>
						<CardBody>
							<Button
								__next40pxDefaultSize
								variant='primary'
								isDestructive={true}
								text='Disconnect'
							/>
						</CardBody>
					</>
				);
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
						<CardHeader>
							<h2 style={{ margin: 0 }}>{menuItems.find((item) => item.value === currentScreen)?.label}</h2>
						</CardHeader>
						{renderScreenContent()}
					</Card>
				</FlexBlock>
			</Flex>
		</div>
	);
}
