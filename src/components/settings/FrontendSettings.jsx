import { Button, Card, CardBody, CardDivider, CardHeader, ExternalLink, SelectControl, TextControl, Tip } from '@wordpress/components';

import { SettingsContext } from './SettingsContext';
import { useContext } from '@wordpress/element';

export default function FrontendSettings() {
	const { settings } = useContext(SettingsContext);

	const frontendAuthenticationUserSelectOptions = [
		{
			label: 'Choose a user...',
			value: '',
		},
	];
	if ( settings?.workspace?.connected_workspace_users ) {
		for ( const gid in settings.workspace.connected_workspace_users ) {
			const wp_user = settings.workspace.connected_workspace_users[ gid ];
			frontendAuthenticationUserSelectOptions.push({
				label: `${wp_user.display_name} (${wp_user.user_email})`,
				value: wp_user.ID,
				selected: ( wp_user.ID === settings?.frontend?.auth_user_id ),
			});
		}
	}

	return (
		<Card>
			<CardHeader style={{ marginBottom: '16px' }}>
				<h2 style={{ margin: 0 }}>Frontend</h2>
			</CardHeader>
			<CardBody>
				<SelectControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label='Frontend Authentication User'
					help="The connected Asana user which will be used to display projects and tasks on this website's frontend."
					options={ frontendAuthenticationUserSelectOptions }
				/>
			</CardBody>
			<CardBody style={{ paddingTop: 0 }}>
				<Tip>The user should have access to all tasks and projects in Asana that you wish to display on your website, so it's best to set this to someone such as your project manager. <ExternalLink href='https://docs.purpleturtlecreative.com/completionist/getting-started/#set-a-frontend-authentication-user'>Learn more</ExternalLink></Tip>
			</CardBody>
			<CardDivider style={{ marginTop: '16px' }} />
			<CardBody style={{ display: 'block' }}>
				<h3 style={{ marginBottom: 0 }}>Asana Data Cache</h3>
				<p style={{ color: 'rgb(117, 117, 117)', marginBottom: 0 }}>Completionist efficiently loads Asana projects and tasks on your website's frontend by caching the associated data for a period of time.</p>
			</CardBody>
			<CardBody>
				<TextControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					type='number'
					label='Cache Duration (TTL)'
					help='The number of seconds until new data is fetched from Asana for display on this website.'
					value={ settings?.frontend?.cache_ttl || 900 }
				/>
			</CardBody>
			<CardBody>
				<Button
					__next40pxDefaultSize
					variant='secondary'
					isDestructive={true}
					text='Clear Cache'
					style={{ paddingLeft: '2em', paddingRight: '2em' }}
				/>
				<p style={{ color: 'rgb(117, 117, 117)' }}>This will clear all cached Asana data such as projects, tasks, and media attachments. You can use this to ensure the latest information is fetched from Asana during the next load. <ExternalLink href='https://docs.purpleturtlecreative.com/completionist/shortcodes/caching/'>Learn more</ExternalLink></p>
			</CardBody>
		</Card>
	);
}
