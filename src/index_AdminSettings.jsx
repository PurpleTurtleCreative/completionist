import AdminSettingsScreen from './components/AdminSettingsScreen.jsx';
import { SettingsContextProvider } from './components/settings/SettingsContext.jsx';

import { Button, Card, CardBody, CardHeader, ExternalLink, Flex } from '@wordpress/components';

import initGlobalNamespace from './components/GlobalNamespace.jsx';

import { createRoot } from '@wordpress/element';

initGlobalNamespace();

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-AdminSettings-root');
	if ( null !== rootNode ) {
		createRoot( rootNode ).render(
			<div className='ptc-AdminSettings'>
				<Card variant='secondary' isRounded={false}>
					<CardHeader>
						<h1 style={{ fontSize: '1.3em', margin: '0.3em 0', whiteSpace: 'nowrap', minWidth: 'auto' }}>Completionist &bull; Settings</h1>
						<Flex gap={6} justify='end' align='center'>
							<ExternalLink href='https://app.asana.com/'>Asana</ExternalLink>
							<ExternalLink href='https://docs.purpleturtlecreative.com/completionist/'>Docs</ExternalLink>
							<ExternalLink href='https://wordpress.org/support/plugin/completionist/'>Support</ExternalLink>
							<ExternalLink href='https://purpleturtlecreative.com/completionist/plugin-info/#changelog'>Changelog</ExternalLink>
							{
								ptc_completionist_settings?.deprecated_url && (
									<Button
										__next40pxDefaultSize
										variant='secondary'
										icon='undo'
										text='Go to old screen'
										href={ptc_completionist_settings.deprecated_url}
									/>
								)
							}
						</Flex>
					</CardHeader>
				</Card>
				<Card isBorderless={true}>
					<CardBody>
						<SettingsContextProvider>
							<AdminSettingsScreen />
						</SettingsContextProvider>
					</CardBody>
				</Card>
			</div>
		);
	}
});
