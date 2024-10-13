import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardDivider, CardHeader, ExternalLink, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, TextControl } from '@wordpress/components';

export default function AdminSettingsScreen() {
	const [currentScreen, setCurrentScreen] = useState('account');

	const menuItems = [
		{ value: 'account', label: 'Asana Account' },
		{ value: 'workspace', label: 'Workspace' },
		{ value: 'frontend', label: 'Frontend' },
	];

	const renderScreenContent = () => {
		switch (currentScreen) {
			case 'account':
				return (
					<>
						<CardBody>
							<Flex gap={4} justify='start' align='center'>
								<FlexItem>
									<img
										src='https://s3.us-east-1.amazonaws.com/asana-user-private-us-east-1/assets/1154845361267017/profile_photos/1204227982288580/866566539988991.1154845361267018.dbGL5HVoVEVPaQsC0LHX_128x128.png'
										alt='Profile Image'
										height={80}
										width={80}
										style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
									/>
								</FlexItem>
								<FlexBlock>
									<h3 style={{ margin: '8px 0' }}>You're connected, Michelle Blanchette!</h3>
									<p style={{ color: 'rgb(117, 117, 117)', margin: '8px 0' }}>Your Asana account is successfully connected. Completionist is able to help you get stuff done on this WordPress website as long as you are a member of this site's assigned workspace.</p>
								</FlexBlock>
							</Flex>
						</CardBody>
						<CardBody>
							<Flex justify='start' align='top'>
								<FlexBlock>
									<TextControl
										__next40pxDefaultSize
										__nextHasNoMarginBottom
										type='password'
										label='Asana PAT'
										help='Connect your Asana account by entering your Personal Access Token (PAT).'
									/>
									<ExternalLink href='https://app.asana.com/0/developer-console'>Visit your Asana developer console</ExternalLink>
								</FlexBlock>
								<FlexItem>
									<Button
										__next40pxDefaultSize
										variant='primary'
										text='Update'
										style={{ marginTop: '23.39px', paddingLeft: '2em', paddingRight: '2em' }}
									/>
								</FlexItem>
							</Flex>
						</CardBody>
						<CardBody>
								<Button
									__next40pxDefaultSize
									variant='secondary'
									isDestructive={true}
									text='Disconnect'
								/>
								<p style={{ color: 'rgb(117, 117, 117)' }}>This will remove your encrypted Personal Access Token and Asana user id from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features.</p>
						</CardBody>
					</>
				);
			case 'workspace':
				return (
					<CardBody>
						<p>This is where you'll set the Asana workspace and Site Tag for this website.</p>
					</CardBody>
				);
			case 'frontend':
				return (
					<CardBody>
						<p>This is where settings for frontend displays are set.</p>
					</CardBody>
				);
			default:
				return (
					<CardBody>
						<p>Select an option from the menu.</p>
					</CardBody>
				);
		}
	};

	return (
		<div className='ptc-AdminSettingsScreen'>
			<Flex gap={6} align='top'>
				<FlexItem style={{ flexBasis: '230px' }}>
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
					<Card isBorderless={true} style={{ margin: '8px 0' }}>
						<CardBody>
							<p style={{ color: 'rgb(117, 117, 117)', margin: 0, fontSize: '0.8em' }}>**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>
						</CardBody>
					</Card>
				</FlexBlock>
				<FlexItem style={{ flexBasis: '230px' }}></FlexItem>
			</Flex>
		</div>
	);
}
