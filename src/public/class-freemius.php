<?php
/**
 * Freemius class
 *
 * @link https://github.com/Freemius/wordpress-sdk/blob/47aaeb6611c8f4c7a0a88793c0d79eafdebfbf4f/start.php#L314 Available hooks in the Freemius SDK.
 *
 * @since 4.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/admin/class-admin-pages.php';

/**
 * A static class for managing the Freemius integration.
 */
class Freemius {

	/**
	 * The Freemius SDK instance.
	 *
	 * @since 4.0.0
	 *
	 * @var \Freemius $freemius
	 */
	private static $freemius;

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since 4.0.0
	 */
	public static function register() {
		static::instance(); // Immediately load the Freemius SDK.
	}

	/**
	 * Gets the Freemius SDK instance for this plugin.
	 *
	 * @since 4.0.0
	 *
	 * @return \Freemius The Freemius SDK instance.
	 */
	public static function instance() {

		if ( ! isset( static::$freemius ) ) {

			// Include Freemius SDK for remote updates.
			require_once PLUGIN_PATH . 'vendor/freemius/wordpress-sdk/start.php';

			/**
			 * Filters the arguments for initializing the Freemius SDK.
			 *
			 * @since 4.0.0
			 *
			 * @param array $freemius_init_args Freemius SDK init args.
			 */
			$freemius_init_args = apply_filters(
				'ptc_completionist_freemius_init_args',
				array(
					'id'                  => '13144',
					'slug'                => 'completionist',
					'premium_slug'        => 'completionist-pro',
					'type'                => 'plugin',
					'public_key'          => 'pk_bc2f099a7701a0c4646ebb19511b9',
					'is_premium'          => false,
					'premium_suffix'      => '(Pro)',
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'menu'                => array(
						'slug'    => Admin_Pages::PARENT_PAGE_SLUG,
						'contact' => false,
						'support' => false,
					),
				)
			);

			// Initialize the Freemius SDK object.
			static::$freemius = fs_dynamic_init( $freemius_init_args );

			// Add customizations.
			static::$freemius->add_filter( 'plugin_icon', __CLASS__ . '::get_plugin_icon' );

			/**
			 * Runs after the Freemius SDK is initialized for this plugin.
			 *
			 * @since 4.0.0
			 *
			 * @param \Freemius The Freemius SDK instance.
			 */
			do_action( 'ptc_completionist_freemius_loaded', static::$freemius );
		}

		return static::$freemius;
	}

	/**
	 * Gets the plugin icon file.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public static function get_plugin_icon() : string {
		return PLUGIN_PATH . 'assets/images/completionist_asana-for-wordpress_300x300.jpg';
	}
}//end class
