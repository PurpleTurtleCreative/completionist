import { useState } from '@wordpress/element';
import { Button, Card, CardBody, CardHeader, CardMedia, ComboboxControl, ExternalLink, Flex, FlexBlock, FlexItem, MenuGroup, MenuItem, SelectControl, TextControl } from '@wordpress/components';

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
				const styleCollaboratorTH = { background: 'rgb(245, 245, 245)', padding: '8px 24px' };
				const styleCollaboratorTD = { verticalAlign: 'middle', padding: '16px 24px' };
				return (
					<>
						<CardBody>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label='Asana Workspace'
								help='The workspace associated with this WordPress website.'
								options={[
									{
										label: 'purpleturtlecreative.com',
										value: '123abc456xyz789',
									},
								]}
							/>
						</CardBody>
						<CardBody>
							<ComboboxControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label='Asana Tag'
								help='The tag applied to Asana tasks which are managed on this WordPress website.'
								placeholder='Please choose a Workspace first...'
								options={[]}
								value={null}
							/>
						</CardBody>
						<CardBody>
							<Button
								__next40pxDefaultSize
								variant='primary'
								text='Update'
								style={{ paddingLeft: '2em', paddingRight: '2em' }}
							/>
						</CardBody>
						<CardHeader style={{ display: 'block' }}>
							<h3 style={{ marginBottom: 0 }}>Collaborators</h3>
							<p style={{ color: 'rgb(117, 117, 117)' }}>The table below shows WordPress users found with the same email address in the Asana Workspace.</p>
						</CardHeader>
						<CardMedia>
							<table style={{ width: '100%', borderCollapse: 'collapse' }}>
								<thead style={{ borderBottom: '1px solid rgb(229 229 229)' }}>
									<tr>
										<th style={styleCollaboratorTH}>User</th>
										<th style={styleCollaboratorTH}>Email</th>
										<th style={styleCollaboratorTH}>Status</th>
									</tr>
								</thead>
								<tbody>
									<tr style={{ borderBottom: '1px solid rgb(229 229 229)' }}>
										<td style={styleCollaboratorTD}>
											<Flex>
												<FlexItem>
													<img
														src='https://s3.us-east-1.amazonaws.com/asana-user-private-us-east-1/assets/1154845361267017/profile_photos/1204227982288580/866566539988991.1154845361267018.dbGL5HVoVEVPaQsC0LHX_128x128.png'
														height={40}
														width={40}
														style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
													/>
												</FlexItem>
												<FlexBlock style={{ textAlign: 'left' }}>
													<p style={{ fontWeight: 'bold', margin: 0 }}>Michelle Blanchette</p>
													<p style={{ fontStyle: 'italic', margin: 0 }}>Administrator</p>
												</FlexBlock>
											</Flex>
										</td>
										<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
											<p><a href='mailto:michelle@purpleturtlecreative.com'>michelle@purpleturtlecreative.com</a></p>
										</td>
										<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
											<p style={{ color: '#4ab866', fontWeight: 'bold' }}>Connected Asana</p>
										</td>
									</tr>
									<tr>
										<td style={styleCollaboratorTD}>
											<Flex>
												<FlexItem>
													<img
														src='https://gravatar.com/avatar/?d=mp&s=40'
														height={40}
														width={40}
														style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
													/>
												</FlexItem>
												<FlexBlock style={{ textAlign: 'left' }}>
													<p style={{ fontWeight: 'bold', margin: 0 }}>John Smith</p>
													<p style={{ fontStyle: 'italic', margin: 0 }}>Editor</p>
												</FlexBlock>
											</Flex>
										</td>
										<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
											<p><a href='mailto:jsmith@example.com'>jsmith@example.com</a></p>
										</td>
										<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
											<p style={{ color: '#757575' }}>Not Connected</p>
										</td>
									</tr>
								</tbody>
							</table>
						</CardMedia>
					</>
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
					<Card>
						<CardHeader>
							<h2 style={{ margin: 0 }}>{menuItems.find((item) => item.value === currentScreen)?.label}</h2>
						</CardHeader>
						{renderScreenContent()}
					</Card>
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
