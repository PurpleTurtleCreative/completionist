import { Button, Card, CardBody, CardHeader, ExternalLink, Flex, FlexBlock, FlexItem, TextControl } from '@wordpress/components';

import { SettingsContext } from './SettingsContext';
import { useContext } from '@wordpress/element';

export default function AccountSettings() {
	const { settings } = useContext(SettingsContext);

	const hasConnectedAsana = ( !! settings?.user?.asana_profile?.gid );

	return (
		<Card>
			<CardHeader style={{ marginBottom: '16px' }}>
				<h2 style={{ margin: 0 }}>Asana Account</h2>
			</CardHeader>
			<CardBody>
				<Flex gap={4} justify='start' align='center'>
					{
						( hasConnectedAsana ) &&
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
							( hasConnectedAsana ) ?
							(<>
								<h3 style={{ margin: '8px 0' }}>
									{
										( settings?.user?.asana_profile?.name ) ?
										`You're connected, ${settings?.user?.asana_profile?.name}!` :
										"Your Asana account is connected!"
									}
								</h3>
								<p style={{ color: 'rgb(117, 117, 117)', margin: '8px 0' }}>Your Asana account is successfully connected. Completionist is able to boost your productivity on this WordPress website as long as you are a member of this site's assigned workspace.</p>
							</>) :
							(<>
								<h3 style={{ margin: '8px 0' }}>Connect Asana</h3>
								<p style={{ color: 'rgb(117, 117, 117)' }}>Access Completionist's collaborative features and appear in Asana-related options.</p>
								<p>By connecting your Asana account, <strong>Completionist</strong> will have permission to:</p>
								<ul style={{ listStyle: 'initial', listStylePosition: 'inside' }}>
									<li>Access your profile's name and email address for display purposes.</li>
									<li>Access your tasks, projects, and workspaces for display purposes.</li>
									<li>Create and modify tasks, projects, and comments on your behalf.</li>
								</ul>
							</>)
						}
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
							label='Personal Access Token'
							help='Connect your Asana account by entering your Personal Access Token (PAT).'
							value={ settings?.user?.asana_personal_access_token || '' }
						/>
						<ExternalLink href='https://app.asana.com/0/developer-console'>Visit your Asana developer console</ExternalLink>
					</FlexBlock>
					<FlexItem>
						<Button
							__next40pxDefaultSize
							variant='primary'
							text={ ( hasConnectedAsana ) ? 'Update' : 'Authorize' }
							style={{ marginTop: '23.39px', paddingLeft: '2em', paddingRight: '2em' }}
						/>
					</FlexItem>
				</Flex>
			</CardBody>
			<CardBody>
			{
				( hasConnectedAsana ) &&
				(<>
					<Button
						__next40pxDefaultSize
						variant='secondary'
						isDestructive={true}
						text='Disconnect'
						style={{ paddingLeft: '2em', paddingRight: '2em' }}
					/>
					<p style={{ color: 'rgb(117, 117, 117)' }}>This will remove your encrypted Personal Access Token and Asana user id from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features. <ExternalLink href="https://docs.purpleturtlecreative.com/completionist/disconnect-asana/">Learn more</ExternalLink></p>
				</>)
			}
			</CardBody>
		</Card>
	);
}
