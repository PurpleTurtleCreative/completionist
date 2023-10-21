<?php
/**
 * Admin Ajax class
 *
 * Registers AJAX endpoints requiring privileges.
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Registers AJAX endpoints requiring privileges.
 */
class Admin_Ajax {

	/**
	 * Registers code.
	 *
	 * @since 3.0.0 Moved to Admin_Ajax class.
	 * @since 1.0.0
	 */
	public static function register() {
		/* Generic AJAX Handlers */
		add_action( 'wp_ajax_ptc_get_tag_options', [ __CLASS__, 'ajax_get_tag_options' ] );
		add_action( 'wp_ajax_ptc_get_post_options_by_title', [ __CLASS__, 'ajax_ptc_get_post_options_by_title' ] );
		add_action( 'wp_ajax_ptc_get_post_title_by_id', [ __CLASS__, 'ajax_ptc_get_post_title_by_id' ] );
	}

	/**
	 * AJAX handler to load tag options for a workspace.
	 *
	 * @since 3.0.0 Moved to Admin_Ajax class.
	 * @since 1.0.0
	 */
	public static function ajax_get_tag_options() {
		require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-tag-options.php';
	}

	/**
	 * AJAX handler to get post options by like title.
	 *
	 * @since 3.0.0 Moved to Admin_Ajax class.
	 * @since 1.1.0
	 */
	public static function ajax_ptc_get_post_options_by_title() {
		require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-post-options-by-title.php';
	}

	/**
	 * AJAX handler to get post title by ID.
	 *
	 * @since 3.0.0 Moved to Admin_Ajax class.
	 * @since 1.1.0
	 */
	public static function ajax_ptc_get_post_title_by_id() {
		require_once PLUGIN_PATH . 'src/admin/ajax/ajax-get-post-title-by-id.php';
	}
}//end class
