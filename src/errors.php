<?php
/**
 * All custom error types.
 *
 * @since 1.0.0
 */

namespace PTC_Completionist\Errors;

/**
 * Invalid Asana API authorization error.
 */
class NoAuthorization extends \Exception {}

/**
 * The plugin's license is not Activated.
 */
class NoLicense extends \Exception {}
