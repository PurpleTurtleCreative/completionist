import { Button, Card, CardBody, CardHeader, ExternalLink, Flex, FlexBlock, FlexItem, Modal, Notice, TextControl } from '@wordpress/components';

import completionistLogoSrc from '../../../assets/images/completionist_asana-for-wordpress_300x300.jpg';
import asanaLogoSrc from '../../../assets/images/asana_logo-vertical-color.png';

import { useContext, useState } from '@wordpress/element';
import { SettingsContext } from './SettingsContext';

export default function AccountSettings() {
	const { settings, updateSettings, isFrontendAuthUser, hasConnectedAsana } = useContext(SettingsContext);
	const [ asanaPAT, setAsanaPAT ] = useState(settings?.user?.asana_personal_access_token || '');
	const [ disconnectModalIsOpen, setDisconnectModalIsOpen ] = useState(false);

	const serviceLogoStyle = {
		overflow: 'hidden',
		borderRadius: '50%',
		background: '#fff',
	};

	const asanaServiceLogoStyle = {
		...serviceLogoStyle,
		width: '94px',
		height: '94px',
		boxSizing: 'border-box',
		border: '2px solid rgba(0, 0, 0, 0.1)',
		display: 'flex',
		alignItems: 'center',
		justifyContent: 'center',
		padding: '10px 10px 15px',
		transform: 'rotate(10deg)',
		boxShadow: '2px 2px 15px -5px #949494',
	};

	const completionistServiceLogoStyle = {
		...serviceLogoStyle,
		transform: 'rotate(350deg)',
		boxShadow: '-2px 2px 10px 0px #949494',
	};

	function handleUpdateAsanaPAT(submitEvent) {
		submitEvent.preventDefault();
		updateSettings('connect_asana', { asana_pat: asanaPAT });
	}

	function handleRequestDisconnectAsana() {
		setDisconnectModalIsOpen(true);
	}

	function handleDisconnectAsana() {
		updateSettings('disconnect_asana');
		setDisconnectModalIsOpen(false);
	}

	return (
		<>
			<Card className='ptc-AccountSettings'>
				<CardHeader style={{ marginBottom: '16px' }}>
					<h2 style={{ margin: 0 }}>Asana Account</h2>
				</CardHeader>
				<CardBody>
					<Flex gap={4} justify='start' align='center'>
						{
							( hasConnectedAsana() ) &&
							(
								<FlexItem>
									<img
										src={ settings?.user?.asana_profile?.photo?.image_128x128 || 'https://gravatar.com/avatar/?d=mp&s=128' }
										height={128}
										width={128}
										style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
									/>
								</FlexItem>
							)
						}
						<FlexBlock>
							{
								( hasConnectedAsana() ) ?
								(<>
									<h3 style={{ margin: '8px 0' }}>
										{
											( settings?.user?.asana_profile?.name ) ?
											`You're connected, ${settings?.user?.asana_profile?.name}!` :
											'Your Asana account is connected!'
										}
									</h3>
									<p style={{ color: 'rgb(117, 117, 117)', margin: '8px 0' }}>Your Asana account is successfully connected. Completionist is able to boost your productivity on this WordPress website as long as you are a member of this site's assigned workspace.</p>
								</>) :
								(<div style={{ textAlign: 'center' }}>
									<Flex gap={6} justify='center' align='center'>
										<FlexItem style={completionistServiceLogoStyle}>
											<img src={completionistLogoSrc} alt='Completionist logo' height={90} width={90} style={{ display: 'block' }} />
										</FlexItem>
										<FlexItem>
											<h3 style={{ margin: '1em 0 0', fontSize: '32px', lineHeight: '1.5' }}>Connect Asana</h3>
										</FlexItem>
										<FlexItem style={asanaServiceLogoStyle}>
											<img src={asanaLogoSrc} alt='Asana logo' width={70} height={46} style={{ display: 'block' }} />
										</FlexItem>
									</Flex>
									<p style={{ color: 'rgb(117, 117, 117)', fontSize: '1.3em' }}>Access Completionist's collaborative features and appear in Asana-related options.</p>
									<p>By connecting your Asana account, <strong>Completionist</strong> will have permission to:</p>
									<ul style={{ listStyle: 'initial', listStylePosition: 'inside', textAlign: 'left', width: 'fit-content', margin: '1em auto' }}>
										<li>Access your profile's name and email address for display purposes.</li>
										<li>Access your tasks, projects, and workspaces for display purposes.</li>
										<li>Create and modify tasks, projects, and comments on your behalf.</li>
									</ul>
								</div>)
							}
						</FlexBlock>
					</Flex>
				</CardBody>
				<CardBody>
					<form onSubmit={handleUpdateAsanaPAT}>
						<Flex justify='start' align='top'>
							<FlexBlock>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									type='password'
									label='Personal Access Token'
									help='Connect your Asana account by entering your Personal Access Token (PAT).'
									value={asanaPAT}
									onChange={setAsanaPAT}
									required={true}
								/>
								<ExternalLink href='https://app.asana.com/0/developer-console'>Visit your Asana developer console</ExternalLink>
							</FlexBlock>
							<FlexItem>
								<Button
									__next40pxDefaultSize
									type='submit'
									variant='primary'
									text={ ( hasConnectedAsana() ) ? 'Update' : 'Authorize' }
									style={{ marginTop: '23.39px', paddingLeft: '2em', paddingRight: '2em' }}
								/>
							</FlexItem>
						</Flex>
					</form>
				</CardBody>
				<CardBody>
				{
					( hasConnectedAsana() ) &&
					(<>
						<Button
							__next40pxDefaultSize
							variant='secondary'
							isDestructive={true}
							text='Disconnect'
							style={{ paddingLeft: '2em', paddingRight: '2em' }}
							onClick={handleRequestDisconnectAsana}
						/>
						<p style={{ color: 'rgb(117, 117, 117)' }}>This will remove your encrypted Personal Access Token and Asana user ID from this site, cancelling access to your Asana account. Please understand the consequences before disconnecting your account. <ExternalLink href="https://docs.purpleturtlecreative.com/completionist/disconnect-asana/">Learn more</ExternalLink></p>
					</>)
				}
				{
					( disconnectModalIsOpen ) && (
						<Modal
							title='Are you sure?'
							size='large'
							onRequestClose={() => setDisconnectModalIsOpen(false)}
						>
							<p>Disconnecting your Asana account means you will lose access to Completionist's collaborative features and no longer appear in Asana-related options.</p>
							{/* @TODO - Make the following warning dynamic by checking Automation Actions, etc. */}
							<p>Automations which reference your Asana account may also stop working.</p>
							{
								isFrontendAuthUser() && (
									<Notice status='warning' isDismissible={false}>
										<h2>Warning: Shortcodes may stop working!</h2>
										<p><strong>You are currently the default frontend authentication user,</strong> so Completionist's shortcodes may not work if you disconnect your Asana account!</p>
										<p>Please consider updating the frontend authentication user for this WordPress website before disconnecting your Asana account to prevent interruptions.</p>
									</Notice>
								)
							}
							<Flex style={{ marginTop: '24px' }} gap={4} align='center' justify='center'>
								<Button
									__next40pxDefaultSize
									variant='secondary'
									text='Cancel'
									style={{ paddingLeft: '2em', paddingRight: '2em' }}
									onClick={() => setDisconnectModalIsOpen(false)}
								/>
								<Button
									__next40pxDefaultSize
									variant='primary'
									isDestructive={true}
									text='Yes, Disconnect'
									style={{ paddingLeft: '2em', paddingRight: '2em' }}
									onClick={handleDisconnectAsana}
								/>
							</Flex>
						</Modal>
					)
				}
				</CardBody>
			</Card>
			<Card isBorderless={true} style={{ margin: '8px 0' }}>
				<CardBody>
					<p style={{ color: 'rgb(117, 117, 117)',fontSize: '0.8em', fontStyle: 'italic', margin: '0 auto', maxWidth: '50em', textAlign: 'center' }}>**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>
				</CardBody>
			</Card>
		</>
	);
}
