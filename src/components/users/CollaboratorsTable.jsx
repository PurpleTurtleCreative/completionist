import { Flex, FlexItem, FlexBlock } from "@wordpress/components";

export default function CollaboratorsTable({ collaborators }) {

	window.console.log( collaborators );

	const styleCollaboratorTH = { background: 'rgb(245, 245, 245)', padding: '8px 24px' };
	const styleCollaboratorTR = { borderTop: '1px solid rgb(229 229 229)' };
	const styleCollaboratorTD = { verticalAlign: 'middle', padding: '16px 24px' };

	const tableRows = [];
	for ( const asana_gid in collaborators ) {
		const collaborator = collaborators[ asana_gid ];
		tableRows.push((
			<tr style={styleCollaboratorTR}>
				<td style={styleCollaboratorTD}>
					<Flex>
						<FlexItem>
							<img
								src={collaborator?.avatar_url || 'https://gravatar.com/avatar/?d=mp&s=42'}
								height={40}
								width={40}
								style={{ borderRadius: '50%', border: '1px solid rgba(0, 0, 0, 0.1)' }}
							/>
						</FlexItem>
						<FlexBlock style={{ textAlign: 'left' }}>
							<p style={{ fontWeight: 'bold', margin: 0 }}>{collaborator?.display_name || '(No Name)'}</p>
							<p style={{ fontStyle: 'italic', margin: 0 }}>{collaborator?.roles?.join(', ') || '(Unknown Role)'}</p>
						</FlexBlock>
					</Flex>
				</td>
				<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
					{
						collaborator?.user_email ?
						<p><a href={`mailto:${collaborator.user_email}`}>{collaborator.user_email}</a></p> :
						<p>(Unknown Email)</p>
					}
				</td>
				<td style={{ ...styleCollaboratorTD, textAlign: 'center' }}>
					{
						collaborator?.hasConnectedAsana ?
						<p style={{ color: '#4ab866', fontWeight: 'bold' }}>Connected Asana</p> :
						<p style={{ color: '#757575' }}>Not Connected</p>
					}
				</td>
			</tr>
		));
	}

	if ( ! tableRows?.length ) {
		tableRows.push()
	}

	return (
		<table className='ptc-CollaboratorsTable' style={{ width: '100%', borderCollapse: 'collapse' }}>
			<thead style={{ borderTop: '1px solid rgb(229 229 229)' }}>
				<tr>
					<th style={styleCollaboratorTH}>User</th>
					<th style={styleCollaboratorTH}>Email</th>
					<th style={styleCollaboratorTH}>Status</th>
				</tr>
			</thead>
			<tbody>
				{
					tableRows?.length > 0 ?
					tableRows :
					(
						<tr style={styleCollaboratorTR}>
							<td colSpan={3} style={styleCollaboratorTD}>
								<p style={{ color: '#757575', textAlign: 'center' }}><em>No collaborators were found</em></p>
							</td>
						</tr>
					)
				}
			</tbody>
		</table>
	);
}
