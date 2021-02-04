<?php
/**
 * Admin Pages class
 *
 * Registers admin pages.
 *
 * @since 2.0.1
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

if ( ! class_exists( __NAMESPACE__ . '\Admin_Pages' ) ) {
	/**
	 * Registers admin pages.
	 */
	class Admin_Pages {

		/**
		 * The name of the plugin menu's main parent page.
		 *
		 * @since 2.0.1
		 */
		public const PARENT_PAGE_SLUG = 'ptc-completionist';

		/**
		 * Registers code.
		 *
		 * @since 2.0.1
		 */
		public static function register() {
			add_action( 'admin_menu', [ __CLASS__, 'add_admin_pages' ] );
			add_filter( 'plugin_action_links_' . PLUGIN_BASENAME, [ __CLASS__, 'filter_plugin_action_links' ] );
		}

		/**
		 * Gets the settings admin page URL.
		 *
		 * @since 2.0.1
		 */
		public static function get_settings_url() {
			return admin_url( 'admin.php?page=' . static::PARENT_PAGE_SLUG );
		}

		/**
		 * Adds the admin pages.
		 *
		 * @since 2.0.1 Moved to Admin_Pages class
		 * @since 1.0.0
		 */
		public static function add_admin_pages() {

			add_menu_page(
				'Completionist &ndash; Settings',
				'Completionist',
				'edit_posts',
				static::PARENT_PAGE_SLUG,
				function() {
					if ( current_user_can( 'edit_posts' ) ) {
						include_once PLUGIN_PATH . 'src/admin/templates/html-admin-dashboard.php';
					} else {
						wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
					}
				},
				'data:image/svg+xml;base64,' . base64_encode( '<svg width="20" height="20" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-check" class="svg-inline--fa fa-clipboard-check fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="white" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm121.2 231.8l-143 141.8c-4.7 4.7-12.3 4.6-17-.1l-82.6-83.3c-4.7-4.7-4.6-12.3.1-17L99.1 285c4.7-4.7 12.3-4.6 17 .1l46 46.4 106-105.2c4.7-4.7 12.3-4.6 17 .1l28.2 28.4c4.7 4.8 4.6 12.3-.1 17z"></path></svg>' ),
				100 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
			);

			add_submenu_page(
				static::PARENT_PAGE_SLUG,
				'Completionist &ndash; Automations',
				'Automations',
				'edit_posts',
				static::PARENT_PAGE_SLUG . '-automations',
				function() {
					if ( current_user_can( 'edit_posts' ) ) {
						include_once PLUGIN_PATH . 'src/admin/templates/html-admin-automations.php';
					} else {
						wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
					}
				},
				null
			);
		}//end add_admin_pages()

		/**
		 * Edits the plugin row's action links.
		 *
		 * @since 2.0.1 Moved to Admin_Pages class
		 * @since 1.0.0
		 *
		 * @param string[] $links The plugin action link HTML items.
		 */
		public static function filter_plugin_action_links( $links ) {
			$links[] = '<a href="https://docs.purpleturtlecreative.com/completionist/">Docs</a>';
			$links[] = '<a href="' . esc_url( static::get_settings_url() ) . '">Settings</a>';
			return $links;
		}
	}//end class
}
