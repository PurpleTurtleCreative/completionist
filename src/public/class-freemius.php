<?php
/**
 * Freemius class
 *
 * @since [unreleased]
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * A static class for managing the Freemius integration.
 */
class Freemius {

	/**
	 * The Freemius SDK instance.
	 *
	 * @since [unreleased]
	 *
	 * @var \Freemius $freemius
	 */
	private static $freemius;

	/**
	 * Hooks functionality into the WordPress execution flow.
	 *
	 * @since [unreleased]
	 */
	public static function register() {
		add_action( 'plugins_loaded', __CLASS__ . '::instance' );
	}

	/**
	 * Gets the Freemius SDK instance for this plugin.
	 *
	 * @since [unreleased]
	 *
	 * @return \Freemius The Freemius SDK instance.
	 */
	public static function instance() {

		if ( ! isset( static::$freemius ) ) {

			// Include Freemius SDK for remote updates.
			require_once PLUGIN_PATH . 'vendor/freemius/wordpress-sdk/start.php';

			// Initialize the Freemius SDK object.
			static::$freemius = fs_dynamic_init(
				array(
					'id'                  => '13144',
					'slug'                => 'completionist',
					'premium_slug'        => 'completionist-pro',
					'type'                => 'plugin',
					'public_key'          => 'pk_bc2f099a7701a0c4646ebb19511b9',
					'is_premium'          => false,
					'premium_suffix'      => 'Pro',
					'has_premium_version' => true,
					'has_addons'          => false,
					'has_paid_plans'      => true,
					'menu'                => array(
						'slug'    => 'ptc-completionist',
						'contact' => false,
						'support' => false,
					),
				)
			);

			/**
			 * Runs after the Freemius SDK is initialized for this plugin.
			 *
			 * @since [unreleased]
			 *
			 * @param \Freemius The Freemius SDK instance.
			 */
			do_action( 'ptc_completionist_freemius_loaded', static::$freemius );
		}

		return static::$freemius;
	}
}//end class
