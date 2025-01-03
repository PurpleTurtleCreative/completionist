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

/**
 * Returns a human-readable duration string.
 *
 * @since 4.5.0
 *
 * @param {integer} seconds The duration in seconds.
 * @returns The duration string.
 */
export function humanReadableDuration( seconds ) {

	if ( ! seconds ) {
		return '0 seconds';
	}

	// Define units to segment by.
	const units = {
		year  : 365 * 24 * 60 * 60,    // 1 year in seconds.
		month : 30.5 * 24 * 60 * 60,   // 1 month (approx) in seconds.
		week  : 7 * 24 * 60 * 60,      // 1 week in seconds.
		day   : 24 * 60 * 60,          // 1 day in seconds.
		hour  : 60 * 60,               // 1 hour in seconds.
		minute: 60,                    // 1 minute in seconds.
		second: 1                      // 1 second.
	};

	// Loop through units and calculate their values.
	let remainingSeconds = seconds;
	const result = [];
	for (const [unit, valueInSeconds] of Object.entries(units)) {
		if (remainingSeconds >= valueInSeconds) {
			const amount = Math.floor(remainingSeconds / valueInSeconds);
			remainingSeconds %= valueInSeconds;
			result.push(`${amount} ${unit}${amount > 1 ? 's' : ''}`); // Pluralize if needed.
		}
	}

	// Join all parts with commas.
	if (result.length > 1) {
		return result.join(', ');
	}

	// Fallback on failure.
	return result[0] || '0 seconds';
}
