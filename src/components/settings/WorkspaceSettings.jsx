import { Button, Card, CardBody, CardDivider, CardHeader, CardMedia, ComboboxControl, Flex, FlexBlock, FlexItem, SelectControl } from '@wordpress/components';

import { SettingsContext } from './SettingsContext';
import { useContext, useState } from '@wordpress/element';

export default function WorkspaceSettings() {
	const { settings } = useContext(SettingsContext);
	const [ asanaWorkspaceValue, setAsanaWorkspaceValue ] = useState(settings?.workspace?.asana_site_workspace?.gid || '');
	const [ asanaTagValue, setAsanaTagValue ] = useState(settings?.workspace?.asana_site_tag?.gid || '');
	const [ asanaTagOptions, setAsanaTagOptions ] = useState(() => {
		const options = [];
		if ( settings?.workspace?.asana_site_tag?.gid ) {
			options.push({
				label: settings?.workspace?.asana_site_tag?.name || '(Unknown)',
				value: settings?.workspace?.asana_site_tag?.gid || '',
			});
		}
		return options;
	});

	function handleAsanaTagFilterValueChange(value) {
		// @TODO setAsanaTagOptions() using Asana Typeahead search for tags.
		window.console.log(value);
	}

	const styleCollaboratorTH = { background: 'rgb(245, 245, 245)', padding: '8px 24px' };
	const styleCollaboratorTD = { verticalAlign: 'middle', padding: '16px 24px' };

	const asanaWorkspaceOptions = [];
	if ( ! settings?.workspace?.asana_site_workspace?.gid || ! settings?.user?.is_site_workspace_member ) {
		asanaWorkspaceOptions.push({
			label: settings?.workspace?.asana_site_workspace?.name || 'Choose a workspace...',
			value: settings?.workspace?.asana_site_workspace?.gid || '',
		});
	}
	if ( settings?.user?.asana_profile?.workspaces?.length > 0 ) {
		for ( const workspace of settings?.user?.asana_profile?.workspaces ) {
			asanaWorkspaceOptions.push({
				label: workspace?.name || '(Unknown)',
				value: workspace?.gid || '',
			});
		}
	}

	return (
		<Card>
			<CardHeader style={{ marginBottom: '16px' }}>
				<h2 style={{ margin: 0 }}>Workspace</h2>
			</CardHeader>
			<CardBody>
				<SelectControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label='Asana Workspace'
					help='The workspace associated with this WordPress website.'
					options={asanaWorkspaceOptions}
					value={asanaWorkspaceValue}
					required={true}
				/>
			</CardBody>
			<CardBody>
				<ComboboxControl
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					label='Asana Tag'
					help='The tag applied to Asana tasks which are managed on this WordPress website.'
					placeholder='Choose a tag or type to search...'
					options={asanaTagOptions}
					value={asanaTagValue}
					required={true}
					onChange={setAsanaTagValue}
					onFilterValueChange={handleAsanaTagFilterValueChange}
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
			<CardDivider style={{ marginTop: '16px' }} />
			<CardBody style={{ display: 'block' }}>
				<h3 style={{ marginBottom: 0 }}>Collaborators</h3>
				<p style={{ color: 'rgb(117, 117, 117)' }}>The table below shows WordPress users found with the same email address in the Asana Workspace.</p>
			</CardBody>
			<CardMedia>
				<table style={{ width: '100%', borderCollapse: 'collapse' }}>
					<thead style={{ borderTop: '1px solid rgb(229 229 229)' }}>
						<tr>
							<th style={styleCollaboratorTH}>User</th>
							<th style={styleCollaboratorTH}>Email</th>
							<th style={styleCollaboratorTH}>Status</th>
						</tr>
					</thead>
					<tbody>
						<tr style={{ borderTop: '1px solid rgb(229 229 229)' }}>
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
						<tr style={{ borderTop: '1px solid rgb(229 229 229)' }}>
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
		</Card>
	);
}