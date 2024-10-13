import { Card, CardBody, CardHeader, Flex, FlexItem } from '@wordpress/components';
import AdminSettingsScreen from './components/screens/AdminSettingsScreen.jsx';

import { createRoot } from '@wordpress/element';

document.addEventListener('DOMContentLoaded', () => {
	const rootNode = document.getElementById('ptc-AdminSettingsScreen');
	if ( null !== rootNode ) {
		createRoot( rootNode ).render(
			<div className='ptc-AdminSettings'>
				<Card variant='secondary' isRounded={false}>
					<CardHeader>
						Completionist &bull; Settings
					</CardHeader>
				</Card>
				<Card isBorderless={true}>
					<CardBody>
						<AdminSettingsScreen />
					</CardBody>
				</Card>
			</div>
		);
	}
});
