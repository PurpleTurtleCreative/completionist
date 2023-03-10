import '../../../assets/styles/scss/components/notice/_NoteBox.scss';

export default function NoteBox({type, message, code}) {

	let titleText = null;
	if ( 'error' === type ) {
		titleText = 'Error';
		if ( !! code ) {
			titleText += ` ${code}`;
		}
	}

	let title = null;
	if ( !! titleText ) {
		title = <strong>{titleText+'. '}</strong>;
	}

	let extraClassNames = '';
	if ( !! type ) {
		extraClassNames += ` --has-type-${type}`;
	}

	return (
		<div className={"ptc-NoteBox"+extraClassNames}>
			<p>{title}{message}</p>
		</div>
	);
}
