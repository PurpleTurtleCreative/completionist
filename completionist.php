<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette
 * @copyright         2020 Michelle Blanchette
 * @license           https://purpleturtlecreative.com/terms-conditions/
 *
 * @wordpress-plugin
 * Plugin Name:       Completionist - Manage Asana Tasks on WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Pin and manage Asana tasks in your WordPress admin.
 * Version:           1.0.0
 * Requires at least: 4.7.1
 * Requires PHP:      7.0
 * Author:            Purple Turtle Creative
 * Author URI:        https://purpleturtlecreative.com/
 * License:           https://purpleturtlecreative.com/terms-conditions/
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

    /**
     * The WCAM library object.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $wcam;

    /**
     * The full url to this plugin's license page.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    public $license_url;

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

      if ( ! class_exists( 'WC_AM_Client_2_7' ) ) {
        require_once( plugin_dir_path( __FILE__ ) . 'wc-am-client.php' );
      }

      if ( class_exists( 'WC_AM_Client_2_7' ) ) {
        $this->wcam = new WC_AM_Client_2_7( __FILE__, '', '1.0.0', 'plugin', 'https://www.purpleturtlecreative.com/', 'Completionist' );
        $this->license_url = admin_url( 'admin.php?page=' . $this->wcam->wc_am_activation_tab_key );
      }

      add_filter( 'plugin_action_links_' . $this->plugin_title, [ $this, 'filter_plugin_action_links' ] );
      add_action( 'wp_ajax_ptc_get_tag_options', [ $this, 'ajax_get_tag_options' ] );

      add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
      add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );

      add_action( 'wp_ajax_ptc_pin_task', [ $this, 'ajax_pin_task' ] );
      add_action( 'wp_ajax_ptc_unpin_task', [ $this, 'ajax_unpin_task' ] );
      add_action( 'wp_ajax_ptc_get_pins', [ $this, 'ajax_get_pins' ] );
      add_action( 'wp_ajax_ptc_list_task', [ $this, 'ajax_list_task' ] );
      add_action( 'wp_ajax_ptc_list_tasks', [ $this, 'ajax_list_tasks' ] );
      add_action( 'wp_ajax_ptc_create_task', [ $this, 'ajax_create_task' ] );
      add_action( 'wp_ajax_ptc_delete_task', [ $this, 'ajax_delete_task' ] );
      add_action( 'wp_ajax_ptc_update_task', [ $this, 'ajax_update_task' ] );

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
        'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-check" class="svg-inline--fa fa-clipboard-check fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="white" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm121.2 231.8l-143 141.8c-4.7 4.7-12.3 4.6-17-.1l-82.6-83.3c-4.7-4.7-4.6-12.3.1-17L99.1 285c4.7-4.7 12.3-4.6 17 .1l46 46.4 106-105.2c4.7-4.7 12.3-4.6 17 .1l28.2 28.4c4.7 4.8 4.6 12.3-.1 17z"></path></svg>'),
        100 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
      );

    }//end add_admin_pages()

    /**
     * Add action links to the plugins page row.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function filter_plugin_action_links( $links ) {

      $links[] = '<a href="https://purpleturtlecreative.com/completionist/documentation/">Docs</a>';
      $links[] = '<a href="https://purpleturtlecreative.com/my-account/">Support</a>';
      $links[] = '<a href="' . esc_url( $this->settings_url ) . '">Settings</a>';
      $links[] = '<a href="' . esc_url( $this->license_url ) . '">License</a>';

      if ( is_object( $this->wcam ) && $this->wcam->get_api_key_status( FALSE ) ) {
        $links[] = '<span style="color:#399709;">&#10004; Activated</span>';
      } else {
        $links[] = '<span style="color:#E12D39;">&#10006; Inactive</span>';
      }

      return $links;

    }

    /**
     * AJAX handler to load tag options for a workspace.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_get_tag_options() {
      require_once $this->plugin_path . 'src/ajax-get-tag-options.php';
    }

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
     * Add admin dashboard widgets.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function add_dashboard_widgets() {
      wp_add_dashboard_widget(
        'ptc-completionist_site-tasks',
        'Completionist Tasks',
        [ $this, 'all_pinned_tasks_dashboard_widget_html' ]
      );
    }

    /**
     * Content for the Site Tasks admin dashboard widget.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function all_pinned_tasks_dashboard_widget_html() {
      include_once $this->plugin_path . 'view/html-dashboard-widget.php';
    }

    /**
     * AJAX handler to pin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_pin_task() {
      require_once $this->plugin_path . 'src/ajax-pin-task.php';
    }

    /**
     * AJAX handler to unpin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_unpin_task() {
      require_once $this->plugin_path . 'src/ajax-unpin-task.php';
    }

    /**
     * AJAX handler to load task HTML.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_get_pins() {
      require_once $this->plugin_path . 'src/ajax-get-pins.php';
    }

    /**
     * AJAX handler to load task HTML.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_list_task() {
      require_once $this->plugin_path . 'src/ajax-list-task.php';
    }

    /**
     * AJAX handler to load HTML for multiple tasks.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_list_tasks() {
      require_once $this->plugin_path . 'src/ajax-list-tasks.php';
    }

    /**
     * AJAX handler to create and pin a new task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_create_task() {
      require_once $this->plugin_path . 'src/ajax-create-task.php';
    }

    /**
     * AJAX handler to delete and unpin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_delete_task() {
      require_once $this->plugin_path . 'src/ajax-delete-task.php';
    }

    /**
     * AJAX handler to update a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_update_task() {
      require_once $this->plugin_path . 'src/ajax-update-task.php';
    }

    /**
     * Register and enqueue plugin CSS and JS.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function register_scripts( $hook_suffix ) {

      wp_register_script(
        'fontawesome-5',
        'https://kit.fontawesome.com/02ab9ff442.js',
        [],
        '5.12.1'
      );

      wp_register_style(
        'ptc-completionist_admin-theme-css',
        plugins_url( 'assets/css/admin-theme.css', __FILE__ ),
        [],
        '0.0.0'
      );

      switch ( $hook_suffix ) {

        case 'index.php':
          wp_enqueue_script(
            'ptc-completionist_dashboard-widget-js',
            plugins_url( 'assets/js/dashboard-widget.js', __FILE__ ),
            [ 'jquery', 'fontawesome-5' ],
            '0.0.0'
          );
          wp_localize_script(
            'ptc-completionist_dashboard-widget-js',
            'ptc_completionist_dashboard_widget',
            [
              'nonce_pin' => wp_create_nonce( 'ptc_completionist_pin_task' ),
              'nonce_list' => wp_create_nonce( 'ptc_completionist_list_task' ),
              'nonce_create' => wp_create_nonce( 'ptc_completionist_create_task' ),
              'nonce_delete' => wp_create_nonce( 'ptc_completionist_delete_task' ),
              'nonce_update' => wp_create_nonce( 'ptc_completionist_update_task' ),
              'page_size' => 10,
              'current_category' => 'all-site-tasks',
              'current_page' => 1,
            ]
          );
          wp_enqueue_style(
            'ptc-completionist_dashboard-widget-css',
            plugins_url( 'assets/css/dashboard-widget.css', __FILE__ ),
            [],
            '0.0.0'
          );
          break;

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
          wp_enqueue_script(
            'ptc-completionist_admin-dashboard-js',
            plugins_url( 'assets/js/admin-dashboard.js', __FILE__ ),
            [ 'jquery', 'fontawesome-5' ],
            '0.0.0'
          );
          require_once $this->plugin_path . 'src/class-options.php';
          wp_localize_script(
            'ptc-completionist_admin-dashboard-js',
            'ptc_completionist_dashboard',
            [
              'saved_workspace_gid' => \PTC_Completionist\Options::get( \PTC_Completionist\Options::ASANA_WORKSPACE_GID ),
              'saved_tag_gid' => \PTC_Completionist\Options::get( \PTC_Completionist\Options::ASANA_TAG_GID ),
              'nonce' => wp_create_nonce( 'ptc_completionist_dashboard' ),
            ]
          );
          break;

        case 'post.php':
        case 'post-new.php':
          require_once $this->plugin_path . 'src/class-options.php';
          wp_enqueue_script(
            'ptc-completionist_metabox-pinned-tasks-js',
            plugins_url( 'assets/js/metabox-pinned-tasks.js', __FILE__ ),
            [ 'jquery', 'fontawesome-5' ],
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
              'nonce_delete' => wp_create_nonce( 'ptc_completionist_delete_task' ),
              'nonce_update' => wp_create_nonce( 'ptc_completionist_update_task' ),
            ]
          );
          wp_enqueue_style(
            'ptc-completionist_metabox-pinned-tasks-css',
            plugins_url( 'assets/css/metabox-pinned-tasks.css', __FILE__ ),
            [],
            '0.0.0'
          );
          break;

      }//end switch hook suffix

    }//end register_scripts()

  }//end class

  $ptc_completionist = new PTC_Completionist();
  $ptc_completionist->register();

}//end if class_exists
