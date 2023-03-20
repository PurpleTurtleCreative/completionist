/**
 * Generic utility functions unrelated to application state or global data.
 *
 * @since 3.4.0
 */

/**
 * Gets the current user's locale representation for a temporal string.
 *
 * @since 3.4.0
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

export function areOnSameDate( date1, date2 ) {
	return (
		date1.getUTCFullYear() === date2.getUTCFullYear() &&
		date1.getUTCMonth() === date2.getUTCMonth() &&
		date1.getUTCDate() === date2.getUTCDate()
	);
}
