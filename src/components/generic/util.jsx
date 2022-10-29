/**
 * Generic utility functions unrelated to application state or global data.
 *
 * @since [unreleased]
 */

/**
 * Gets the current user's locale representation for a temporal string.
 *
 * @since [unreleased]
 *
 * @param string dateTimeString A temporal data string in a format that is
 * compatible with JavaScript's built-in Date class's constructor.
 * @return string The date or time in the current user's locale.
 */
export function getLocaleString( dateTimeString ) {

	if ( dateTimeString.includes( 'T' ) ) {
		// Time (eg. "2022-10-28T18:11:41.859Z") should display in current user's timezone.
		return new Date( dateTimeString ).toLocaleString(
			undefined,
			{
				dateStyle: 'medium',
				timeStyle: 'short',
			}
		);
	}

	// Dates (eg. "2022-10-28") are considered midnight, so use UTC timezone for literal display.
	return new Date( dateTimeString ).toLocaleDateString(
		undefined,
		{
			month: 'short',
			day: 'numeric',
			timeZone: 'UTC',
		}
	);
}
