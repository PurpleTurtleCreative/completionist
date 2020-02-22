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
 * Description:       Pin and manage Asana tasks in relevant areas of your WordPress admin.
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

    /**
     * The full url to this plugin's admin page.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $settings_url;

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
      $this->settings_url = admin_url( 'admin.php?page=ptc-completionist' );
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
      add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );

      add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
      add_action( 'wp_ajax_ptc_pin_task', [ $this, 'metabox_pin_task' ] );
      add_action( 'wp_ajax_ptc_list_task', [ $this, 'metabox_list_task' ] );
      add_action( 'wp_ajax_ptc_create_task', [ $this, 'metabox_create_task' ] );

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
        3 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
      );

    }//end add_admin_pages()

    /**
     * Add metaboxes.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function add_meta_boxes() {
      add_meta_box(
        'ptc-completionist_pinned-tasks',
        'Tasks',
        [ $this, 'pinned_tasks_metabox_html' ],
        NULL,
        'side'
      );
    }

    /**
     * Content for the Pinned Tasks metabox.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function pinned_tasks_metabox_html() {
      include_once $this->plugin_path . 'view/html-metabox-pinned-tasks.php';
    }

    /**
     * AJAX handler to pin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function metabox_pin_task() {
      require_once $this->plugin_path . 'src/ajax-pin-task.php';
    }

    /**
     * AJAX handler to load task HTML.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function metabox_list_task() {
      require_once $this->plugin_path . 'src/ajax-list-task.php';
    }

    /**
     * AJAX handler to create and pin a new task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function metabox_create_task() {
      require_once $this->plugin_path . 'src/ajax-create-task.php';
    }

    /**
     * Register and enqueue plugin CSS and JS.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function register_scripts( $hook_suffix ) {

      wp_register_style(
        'fontawesome-5',
        'https://kit.fontawesome.com/02ab9ff442.js',
        [],
        '5.12.1'
      );

      wp_register_style(
        'ptc-completionist_admin-theme-css',
        plugins_url( 'assets/css/admin-theme.css', __FILE__ ),
        [ 'fontawesome-5' ],
        '0.0.0'
      );

      switch ( $hook_suffix ) {

        case 'toplevel_page_ptc-completionist':
          wp_enqueue_style(
            'ptc-completionist_connect-asana-css',
            plugins_url( 'assets/css/connect-asana.css', __FILE__ ),
            [ 'ptc-completionist_admin-theme-css' ],
            '0.0.0'
          );
          wp_enqueue_style(
            'ptc-completionist_admin-dashboard-css',
            plugins_url( 'assets/css/admin-dashboard.css', __FILE__ ),
            [ 'ptc-completionist_admin-theme-css' ],
            '0.0.0'
          );
          break;

        case 'post.php':
          require_once $this->plugin_path . 'src/class-options.php';
          wp_enqueue_script(
            'ptc-completionist_metabox-pinned-tasks-js',
            plugins_url( 'assets/js/metabox-pinned-tasks.js', __FILE__ ),
            [ 'jquery' ],
            '0.0.0'
          );
          wp_localize_script(
            'ptc-completionist_metabox-pinned-tasks-js',
            'ptc_completionist_pinned_tasks',
            [
              'post_id' => get_the_ID(),
              'pinned_task_gids' => \PTC_Completionist\Options::get( \PTC_Completionist\Options::PINNED_TASK_GID, get_the_ID() ),
              'nonce_pin' => wp_create_nonce( 'ptc_completionist_pin_task' ),
              'nonce_list' => wp_create_nonce( 'ptc_completionist_list_task' ),
              'nonce_create' => wp_create_nonce( 'ptc_completionist_create_task' ),
            ]
          );
          break;

      }//end switch hook suffix

    }//end register_scripts()

  }//end class

  $ptc_completionist = new PTC_Completionist();
  $ptc_completionist->register();

}//end if class_exists
