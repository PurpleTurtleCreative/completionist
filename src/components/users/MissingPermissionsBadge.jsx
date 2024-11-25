import { ReactComponent as LockIcon } from '../../../assets/icons/lock-solid.svg';

export default function MissingPermissionsBadge({ label, style }) {

	const badgeStyle = {
		display: 'inline-flex',
		alignItems: 'center',
		justifyContent: 'center',
		flexWrap: 'nowrap',
		gap: '0.5em',
		whiteSpace: 'nowrap',
		padding: '0.5em 1em',
		borderRadius: '0.3em',
		background: '#f0f0f0',
		color: '#757575',
		fontSize: '1em',
		...style,
	};

	return (
		<span className='ptc-MissingPermissionsBadge' style={badgeStyle}>
			<LockIcon style={{ fill: '#757575', height: '1em', width: '1em' }} />{label}
		</span>
	);
}
