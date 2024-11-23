import { Button, Card, CardBody, CardDivider, CardHeader, CardMedia, ComboboxControl, Flex, FlexBlock, FlexItem, SelectControl } from '@wordpress/components';

import { useContext, useRef, useState } from '@wordpress/element';
import { SettingsContext } from './SettingsContext';

import apiFetch from '@wordpress/api-fetch';
import CollaboratorsTable from '../users/CollaboratorsTable';

export default function WorkspaceSettings() {
	const { settings, hasConnectedAsana, updateSettings, getWorkspaceCollaborators } = useContext(SettingsContext);
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
	const tagTypeaheadAbortControllerRef = useRef(null);
	const PREFIX_CREATE_TAG = '__create__';

	function handleAsanaTagFilterValueChange(value) {

		if ( 'function' === typeof tagTypeaheadAbortControllerRef.current?.abort ) {
			// Abort the previous request.
			tagTypeaheadAbortControllerRef.current.abort();
		}

		if ( ! value ) {
			return; // Avoid useless requests.
		}

		// Create new AbortController for this request.
		tagTypeaheadAbortControllerRef.current = new AbortController();

		// Perform the request.
		apiFetch({
			path: `/completionist/v1/tags/typeahead?workspace_gid=${asanaWorkspaceValue}&query=${value}&count=100`,
			method: 'GET',
			signal: tagTypeaheadAbortControllerRef.current?.signal,
		}).then( res => {
			window.console.log(res);
			if ( res?.data?.tags ) {
				setAsanaTagOptions( prevState => {
					const seenTags = new Set();
					const newState = [
						{
							label: `${value} (Create new tag)`,
							value: `${PREFIX_CREATE_TAG}${value}`,
						}
					];
					for ( const tagOption of prevState ) {
						if ( ! seenTags.has( tagOption?.value ) && ! tagOption.value.startsWith(PREFIX_CREATE_TAG) ) {
							newState.push(tagOption);
							seenTags.add(tagOption?.value);
						}
					}
					for ( const tag of res.data.tags ) {
						if ( ! seenTags.has( tag?.gid ) ) {
							newState.push({
								label: tag?.name,
								value: tag?.gid,
							});
							seenTags.add(tag?.gid);
						}
					}
					return newState;
				});
			}
		}).catch( error => {
			if ( 'AbortError' !== error?.name ) {
				window.console.error(error);
			}
		});
	}

	function handleUpdateWorkspaceTagSubmit(submitEvent) {
		submitEvent?.preventDefault();
		window.console.log(asanaWorkspaceValue, asanaTagValue);

		const data = { workspace_gid: asanaWorkspaceValue };
		if ( asanaTagValue.startsWith(PREFIX_CREATE_TAG) ) {
			data.tag_name = asanaTagValue.substr(PREFIX_CREATE_TAG.length);
		} else {
			data.tag_gid = asanaTagValue;
		}

		updateSettings('update_asana_workspace_tag', data);
	}

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
		<Card className='ptc-WorkspaceSettings'>
			<CardHeader style={{ marginBottom: '16px' }}>
				<h2 style={{ margin: 0 }}>Workspace</h2>
			</CardHeader>
			<form onSubmit={handleUpdateWorkspaceTagSubmit}>
				<CardBody>
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label='Asana Workspace'
							help='The workspace associated with this WordPress website.'
							options={asanaWorkspaceOptions}
							value={asanaWorkspaceValue}
							required={true}
							onChange={setAsanaWorkspaceValue}
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
						onChange={setAsanaTagValue}
						onFilterValueChange={handleAsanaTagFilterValueChange}
						required={true}
						disabled={ ! asanaWorkspaceValue || ! hasConnectedAsana() }
					/>
				</CardBody>
				<CardBody>
					<Button
						__next40pxDefaultSize
						type='submit'
						variant='primary'
						text='Update'
						style={{ paddingLeft: '2em', paddingRight: '2em' }}
					/>
				</CardBody>
			</form>
			<CardDivider style={{ marginTop: '16px' }} />
			<CardBody style={{ display: 'block' }}>
				<h3 style={{ marginBottom: 0 }}>Collaborators</h3>
				<p style={{ color: 'rgb(117, 117, 117)' }}>The table below shows WordPress users that have connected their Asana account or were found with the same email address in the Asana Workspace.</p>
			</CardBody>
			<CardMedia>
				<CollaboratorsTable collaborators={getWorkspaceCollaborators()} />
			</CardMedia>
		</Card>
	);
}
