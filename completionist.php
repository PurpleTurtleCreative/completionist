<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette
 * @copyright         2020 Michelle Blanchette
 * @license           MIT
 *
 * Plugin Name:       Completionist - Asana Task Management for WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Manage site-specific Asana tasks in relevant areas of your WordPress admin.
 * Version:           1.0.0
 * Requires PHP:      7.0
 * Author:            Purple Turtle Creative
 * Author URI:        https://purpleturtlecreative.com/
 * License:           MIT
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( '\PTC_Completionist' ) ) {
  /**
   * Maintains data and functions related to plugin data and registration.
   *
   * @since 1.0.0
   */
  class PTC_Completionist {

    /**
     * This plugin's basename.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $plugin_title;

    /**
     * The full file path to this plugin's directory ending with a slash.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $plugin_path;

    /**
     * The full url to this plugin's directory.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $plugin_url;

    /* Plugin Initialization */

    /**
     * Sets plugin member variables.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function __construct() {
      $this->plugin_title = plugin_basename( __FILE__ );
      $this->plugin_path = plugin_dir_path( __FILE__ );
      $this->plugin_url = plugins_url( '', __FILE__ );
    }

    /**
     * Hooks code into WordPress.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function register() {

      add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );
      // add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );

      // add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
      // add_action( 'wp_ajax_refresh_page_relatives', [ $this, 'related_content_metabox_html_ajax_refresh' ] );

    }

    /**
     * Add the administrative pages.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function add_admin_pages() {

      add_menu_page(
        'Completionist &ndash; Settings',
        'Completionist',
        'edit_posts',
        'ptc-completionist',
        function() {
          if ( current_user_can( 'edit_posts' ) ) {
            require_once $this->plugin_path . 'view/html-admin-dashboard.php';
          } else {
            wp_die('<strong>Error: Unauthorized.</strong> You must have post editing capabilities in order to use Completionist.');
          }
        },
        'dashicons-yes-alt',
        3 /* Pages menu item is priority 20, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
      );

    }//end add_admin_pages()

    // /**
    //  * Add metaboxes.
    //  *
    //  * @since 1.0.0
    //  *
    //  * @ignore
    //  */
    // function add_meta_boxes() {
    //   add_meta_box(
    //     'ptc-grouped-content',
    //     'Page Relatives',
    //     [ $this, 'related_content_metabox_html' ],
    //     'page',
    //     'side'
    //   );
    // }

    // /**
    //  * Content for the Page Relatives metabox.
    //  *
    //  * @since 1.0.0
    //  *
    //  * @ignore
    //  */
    // function related_content_metabox_html() {
    //   include_once $this->plugin_path . 'view/html-metabox-page-relatives.php';
    // }

    // /**
    //  * AJAX handler for refreshing the Page Relatives metabox in Gutenberg.
    //  *
    //  * @since 1.2.0
    //  *
    //  * @ignore
    //  */
    // function related_content_metabox_html_ajax_refresh() {
    //   require_once $this->plugin_path . 'src/ajax-refresh-metabox-page-relatives.php';
    // }

    // /**
    //  * Register and enqueue plugin CSS and JS.
    //  *
    //  * @since 1.0.0
    //  *
    //  * @ignore
    //  */
    // function register_scripts( $hook_suffix ) {

    //   wp_register_style(
    //     'fontawesome-5',
    //     plugins_url( '/assets/fonts/fontawesome-free-5.12.0-web/css/all.min.css', __FILE__ ),
    //     [],
    //     '5.12.0'
    //   );

    //   switch ( $hook_suffix ) {
    //     case 'toplevel_page_ptc-grouped-content':
    //       wp_enqueue_style(
    //         'ptc-grouped-content_view-groups-css',
    //         plugins_url( 'assets/css/view-groups.css', __FILE__ ),
    //         [ 'fontawesome-5' ],
    //         '1.0.0'
    //       );
    //       break;
    //     case 'post.php' && get_post_type() === 'page':
    //       wp_enqueue_style(
    //         'ptc-grouped-content_metabox-page-relatives-css',
    //         plugins_url( 'assets/css/metabox_page-relatives.css', __FILE__ ),
    //         [ 'fontawesome-5' ],
    //         '1.0.0'
    //       );
    //       wp_enqueue_script(
    //         'ptc-grouped-content_metabox-page-relatives-js',
    //         plugins_url( 'assets/js/metabox-page-relatives.js', __FILE__ ),
    //         [ 'jquery' ],
    //         '0.0.1'
    //       );
    //       wp_localize_script(
    //         'ptc-grouped-content_metabox-page-relatives-js',
    //         'ptc_page_relatives',
    //         [ 'nonce' => wp_create_nonce( 'ptc_page_relatives' ) ]
    //       );
    //       break;
    //     case 'groups_page_ptc-grouped-content_generator':
    //       wp_enqueue_style(
    //         'ptc-grouped-content_content-generator-css',
    //         plugins_url( 'assets/css/content-generator.css', __FILE__ ),
    //         [ 'fontawesome-5' ],
    //         '1.0.0'
    //       );
    //       wp_enqueue_script(
    //         'ptc-grouped-content_content-generator-js',
    //         plugins_url( 'assets/js/content-generator.js', __FILE__ ),
    //         [ 'jquery' ],
    //         '1.0.0'
    //       );
    //       break;
    //   }

    // }//end register_scripts()

  }//end class

  $ptc_completionist = new PTC_Completionist();
  $ptc_completionist->register();

}//end if class_exists
