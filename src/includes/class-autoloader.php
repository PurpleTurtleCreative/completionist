<?php
/**
 * Autoloader class
 *
 * @since 4.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Autoloads class files that are included in this plugin.
 */
class Autoloader {

	/**
	 * An array of class name keys mapped to their respective
	 * class file.
	 *
	 * Note that the class names are relative to this plugin's
	 * root namespace, which this class belongs to. Class file
	 * paths are relative to this plugin's root directory.
	 *
	 * @since 4.0.0
	 *
	 * @var array CLASS_FILE_MAP
	 */
	private const CLASS_FILE_MAP = array(

		// Admin.
		'Admin_Pages'                      => 'src/admin/class-admin-pages.php',
		'Admin_Widgets'                    => 'src/admin/class-admin-widgets.php',

		// Includes.
		'Asana_Batch'                      => 'src/includes/class-asana-batch.php',
		'Asana_Interface'                  => 'src/includes/class-asana-interface.php',
		'Database_Manager'                 => 'src/includes/class-database-manager.php',
		'HTML_Builder'                     => 'src/includes/class-html-builder.php',
		'Options'                          => 'src/includes/class-options.php',
		'Util'                             => 'src/includes/class-util.php',

		// Includes/Abstracts.
		'Abstracts\Plugin_Version_Checker' => 'src/includes/abstracts/class-plugin-version-checker.php',

		// Includes/Automations.
		'Automation'                       => 'src/includes/automations/class-automation.php',
		'Automations\Actions'              => 'src/includes/automations/class-actions.php',
		'Automations\Data'                 => 'src/includes/automations/class-data.php',
		'Automations\Events'               => 'src/includes/automations/class-events.php',
		'Automations\Fields'               => 'src/includes/automations/class-fields.php',

		// Includes/Errors.
		'Errors\No_Authorization'          => 'src/includes/errors/class-no-authorization.php',

		// Public.
		'Admin_Notices'                    => 'src/public/class-admin-notices.php',
		'Request_Token'                    => 'src/public/class-request-token.php',
		'REST_Server'                      => 'src/public/class-rest-server.php',
		'Shortcodes'                       => 'src/public/class-shortcodes.php',
		'Uninstaller'                      => 'src/public/class-uninstaller.php',
		'Upgrader'                         => 'src/public/class-upgrader.php',

		// Public/REST_API.
		'REST_API\Attachments'             => 'src/public/rest-api/class-attachments.php',
		'REST_API\Projects'                => 'src/public/rest-api/class-projects.php',
		'REST_API\Tasks'                   => 'src/public/rest-api/class-tasks.php',
		'REST_API\Automations'             => 'src/public/rest-api/class-automations.php',
		'REST_API\Tags'                    => 'src/public/rest-api/class-tags.php',
		'REST_API\Posts'                   => 'src/public/rest-api/class-posts.php',

	);

	/**
	 * Registers the autoloaders for class files.
	 *
	 * @since 4.0.0
	 */
	public static function register() {
		// Autoload third-party dependencies.
		require_once PLUGIN_PATH . '/php-asana/vendor/autoload.php';
		// Autoload our own class files.
		spl_autoload_register( __CLASS__ . '::load_class_file' );
	}

	/**
	 * Loads the file for the given class name.
	 *
	 * @since 4.0.0
	 *
	 * @param string $class_name The fully-qualified class name.
	 */
	public static function load_class_file( string $class_name ) {

		// Ignore if not in root namespace.
		if ( 0 !== strpos( $class_name, __NAMESPACE__ ) ) {
			return;
		}

		// Trim root namespace.
		$class_name = substr( $class_name, strlen( __NAMESPACE__ ) + 1 );

		// Load class file if recognized.
		if ( ! empty( static::CLASS_FILE_MAP[ $class_name ] ) ) {
			require_once PLUGIN_PATH . static::CLASS_FILE_MAP[ $class_name ];
		}
	}
}//end class
