import { Flex } from "@wordpress/components";
import MissingPermissionsBadge from "./MissingPermissionsBadge";

export default function MissingPermissionsBadgeGroup({ badges, ...rest }) {

	const badgesToDisplay = [];
	for ( const { check, key, ...badgeProps } of badges ) {
		if ( check ) {
			badgesToDisplay.push(<MissingPermissionsBadge key={key} {...badgeProps} />);
		}
	}

	if ( ! badgesToDisplay.length ) {
		return null; // There are no missing permissions to display.
	}

	return (
		<Flex align='center' justify='start' gap='0.5em' wrap={true} {...rest}>
			{badgesToDisplay}
		</Flex>
	);
}
