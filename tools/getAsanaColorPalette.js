/**
 * Used to scrape Asana's latest color palette names and values
 * from their webapp UI so we can use their exact colors whenever referenced.
 * 
 * Navigate to Asana in the web browser, expose a color picker component
 * in the UI, and run this script in the browser console.
 * 
 * For example, view a project in Asana and open its setting to
 * "Set color & icon" which reveals a color picker.
 * 
 * @since 4.2.1
 */
main();

/**
 * Finds Asana's color palette values in the UI and provides them
 * as JSON and an SCSS map variable declaration.
 * 
 * Note that an Asana color palette picker must currently be displayed
 * on the page. You may need to interact with Asana to expose the palette.
 * 
 * @since 4.2.1
 */
function main() {

	const colors = findAsanaPaletteColors();
	window.console.log(colors);
	window.console.log(convertColorsToScssVars(colors));
	
	const apiColors = convertAsanaColorNamesToApiColorNames(colors);
	window.console.log(apiColors);
	window.console.log(convertColorsToScssVars(apiColors));
}

/**
 * Finds the Asana palette color values.
 * 
 * Note that an Asana color palette picker must currently be displayed
 * on the page. You may need to interact with Asana to expose the palette.
 * 
 * @since 4.2.1
 * 
 * @returns {object} Color names mapped to CSS color values.
 */
function findAsanaPaletteColors() {

	const colors = {};
	const rootComputedStyles = getComputedStyle(document.documentElement);

	const colorPickerCells = document.querySelectorAll('.ColorPicker-cellsContainer .ColorPickerCell input[aria-label]');
	if ( colorPickerCells?.length > 0 ) {
		colorPickerCells.forEach(el => {
			const colorSlug = el.ariaLabel.replace(' ', '-');
			if ( 'none' === colorSlug ) {
				colors[ colorSlug ] = rootComputedStyles.getPropertyValue(`--color-customization-background`);
			} else {
				colors[ colorSlug ] = rootComputedStyles.getPropertyValue(`--color-customization-${colorSlug}-background`);
			}
		});
	}

	return colors;
}

/**
 * Changes the Asana UI color palette names to their Asana API name counterparts.
 * 
 * @since 4.2.1
 * 
 * @param {object} colors Asana UI color palette names and values.
 * @returns {object} The Asana API-named color palette.
 */
function convertAsanaColorNamesToApiColorNames( colors ) {

	const asanaUiToApiColorNameMap = {
		"none"         : "none",
		"yellow"       : "dark-brown",
		"green"        : "dark-green",
		"orange"       : "dark-orange",
		"hot-pink"     : "dark-pink",
		"indigo"       : "dark-purple",
		"red"          : "dark-red",
		"aqua"         : "dark-teal",
		"blue"         : "light-blue",
		"yellow-green" : "light-green",
		"yellow-orange": "light-orange",
		"magenta"      : "light-pink",
		"purple"       : "light-purple",
		"pink"         : "light-red",
		"blue-green"   : "light-teal",
		"cool-gray"    : "light-warm-gray",
	};

	const convertedColors = {};
	for (const colorSlug in colors) {
		if ( asanaUiToApiColorNameMap?.[colorSlug] ) {
			convertedColors[ asanaUiToApiColorNameMap[colorSlug] ] = colors[colorSlug];
		} else {
			window.console.warn(`Color slug <${colorSlug}> found in UI is missing from API color names map.`);
		}
	}

	return convertedColors;
}

/**
 * Converts a color palette to a SCSS map variable declaration.
 * 
 * @since 4.2.1
 * 
 * @param {object} colors Color names mapped to CSS color values.
 * @returns {string} The SCSS map variable for the given color palette.
 */
function convertColorsToScssVars( colors ) {

	colors = ksort(colors);

	const scssPalette = [];

	for ( const colorSlug in colors ) {
		scssPalette.push(`${colorSlug}: ${colors[colorSlug]}`);
	}

	return `
$asana-palette: (
	${scssPalette.join(',\n\t')}
);
	`;
}

/**
 * Sorts an object by its key names.
 * 
 * @since 4.2.1
 * 
 * @param {object} obj Object to sort. 
 * @returns {object} Object sorted by key names.
 */
function ksort(obj) {

  const keys = Object.keys(obj);

  keys.sort();

  const sortedObject = {};
  for ( const key of keys ) {
    sortedObject[key] = obj[key];
  }

  return sortedObject;
}