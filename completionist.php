<?php
/**
 * Completionist
 *
 * @author            Michelle Blanchette
 * @copyright         2020 Michelle Blanchette
 * @license           https://purpleturtlecreative.com/terms-conditions/
 *
 * @wordpress-plugin
 * Plugin Name:       Completionist - Asana for WordPress
 * Plugin URI:        https://purpleturtlecreative.com/completionist/
 * Description:       Manage, pin, and automate Asana tasks in relevant areas of your WordPress admin.
 * Version:           2.0.0
 * Requires at least: 5.0.0
 * Requires PHP:      7.1
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
     * This plugin's current version.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    public $plugin_version;

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
      $this->plugin_version = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' )['Version'];
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

      register_activation_hook( __FILE__, [ $this, 'install_tables' ] );
      add_action( 'plugins_loaded', [ $this, 'install_tables' ] );

      add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );
      add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );

      add_filter( 'plugin_action_links_' . $this->plugin_title, [ $this, 'filter_plugin_action_links' ] );
      add_action( 'wp_ajax_ptc_get_tag_options', [ $this, 'ajax_get_tag_options' ] );

      /* Admin Widgets */
      add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
      add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widgets' ] );

      /* Task AJAX Handlers */
      add_action( 'wp_ajax_ptc_pin_task', [ $this, 'ajax_pin_task' ] );
      add_action( 'wp_ajax_ptc_unpin_task', [ $this, 'ajax_unpin_task' ] );
      add_action( 'wp_ajax_ptc_get_pins', [ $this, 'ajax_get_pins' ] );
      add_action( 'wp_ajax_ptc_list_task', [ $this, 'ajax_list_task' ] );
      add_action( 'wp_ajax_ptc_list_tasks', [ $this, 'ajax_list_tasks' ] );
      add_action( 'wp_ajax_ptc_create_task', [ $this, 'ajax_create_task' ] );
      add_action( 'wp_ajax_ptc_delete_task', [ $this, 'ajax_delete_task' ] );
      add_action( 'wp_ajax_ptc_update_task', [ $this, 'ajax_update_task' ] );
      /* Generic AJAX Handlers */
      add_action( 'wp_ajax_ptc_get_post_options_by_title', [ $this, 'ajax_ptc_get_post_options_by_title' ] );
      add_action( 'wp_ajax_ptc_get_post_title_by_id', [ $this, 'ajax_ptc_get_post_title_by_id' ] );
      /* Automation AJAX Handlers */
      add_action( 'wp_ajax_ptc_save_automation', [ $this, 'ajax_ptc_save_automation' ] );
      add_action( 'wp_ajax_ptc_get_automation', [ $this, 'ajax_ptc_get_automation' ] );
      add_action( 'wp_ajax_ptc_get_automation_overviews', [ $this, 'ajax_ptc_get_automation_overviews' ] );
      add_action( 'wp_ajax_ptc_delete_automation', [ $this, 'ajax_ptc_delete_automation' ] );

      /* Enqueue Automation Actions */
      add_action( 'plugins_loaded', function() {
        require_once $this->plugin_path . 'src/automations/class-events.php';
        \PTC_Completionist\Automations\Events::add_actions();
      });

    }

    /**
     * Install all database tables for the current site.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function install_tables() {
      require_once $this->plugin_path . 'src/class-database-manager.php';
      \PTC_Completionist\Database_Manager::init();
      \PTC_Completionist\Database_Manager::install_all_tables();
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
            wp_die('<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.');
          }
        },
        'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-check" class="svg-inline--fa fa-clipboard-check fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="white" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm121.2 231.8l-143 141.8c-4.7 4.7-12.3 4.6-17-.1l-82.6-83.3c-4.7-4.7-4.6-12.3.1-17L99.1 285c4.7-4.7 12.3-4.6 17 .1l46 46.4 106-105.2c4.7-4.7 12.3-4.6 17 .1l28.2 28.4c4.7 4.8 4.6 12.3-.1 17z"></path></svg>'),
        100 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
      );

      add_submenu_page(
        'ptc-completionist',
        'Completionist &ndash; Automations',
        'Automations',
        'edit_posts',
        'ptc-completionist-automations',
        function() {
          if ( current_user_can( 'edit_posts' ) ) {
            require_once $this->plugin_path . 'view/html-admin-automations.php';
          } else {
            wp_die('<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.');
          }
        },
        NULL
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
      $links[] = '<a href="' . esc_url( $this->settings_url ) . '">Settings</a>';
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
      require_once $this->plugin_path . 'src/ajax/ajax-get-tag-options.php';
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
      require_once $this->plugin_path . 'src/ajax/ajax-pin-task.php';
    }

    /**
     * AJAX handler to unpin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_unpin_task() {
      require_once $this->plugin_path . 'src/ajax/ajax-unpin-task.php';
    }

    /**
     * AJAX handler to load task HTML.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_get_pins() {
      require_once $this->plugin_path . 'src/ajax/ajax-get-pins.php';
    }

    /**
     * AJAX handler to load task HTML.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_list_task() {
      require_once $this->plugin_path . 'src/ajax/ajax-list-task.php';
    }

    /**
     * AJAX handler to load HTML for multiple tasks.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_list_tasks() {
      require_once $this->plugin_path . 'src/ajax/ajax-list-tasks.php';
    }

    /**
     * AJAX handler to create and pin a new task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_create_task() {
      require_once $this->plugin_path . 'src/ajax/ajax-create-task.php';
    }

    /**
     * AJAX handler to delete and unpin a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_delete_task() {
      require_once $this->plugin_path . 'src/ajax/ajax-delete-task.php';
    }

    /**
     * AJAX handler to update a task.
     *
     * @since 1.0.0
     *
     * @ignore
     */
    function ajax_update_task() {
      require_once $this->plugin_path . 'src/ajax/ajax-update-task.php';
    }

    /**
     * AJAX handler to get post options by like title.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_get_post_options_by_title() {
      require_once $this->plugin_path . 'src/ajax/ajax-get-post-options-by-title.php';
    }

    /**
     * AJAX handler to get post title by ID.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_get_post_title_by_id() {
      require_once $this->plugin_path . 'src/ajax/ajax-get-post-title-by-id.php';
    }

    /**
     * AJAX handler to save automation data.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_save_automation() {
      require_once $this->plugin_path . 'src/ajax/ajax-save-automation.php';
    }

    /**
     * AJAX handler to get automation by ID.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_get_automation() {
      require_once $this->plugin_path . 'src/ajax/ajax-get-automation.php';
    }

    /**
     * AJAX handler to get overview data for all automations.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_get_automation_overviews() {
      require_once $this->plugin_path . 'src/ajax/ajax-get-automation-overviews.php';
    }

    /**
     * AJAX handler to delete an automation by ID.
     *
     * @since 1.1.0
     *
     * @ignore
     */
    function ajax_ptc_delete_automation() {
      require_once $this->plugin_path . 'src/ajax/ajax-delete-automation.php';
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
        $this->plugin_version
      );

      switch ( $hook_suffix ) {

        case 'index.php':
          wp_enqueue_script(
            'ptc-completionist_dashboard-widget-js',
            plugins_url( 'assets/js/dashboard-widget.js', __FILE__ ),
            [ 'jquery', 'fontawesome-5' ],
            $this->plugin_version
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
            $this->plugin_version
          );
          break;

        case 'toplevel_page_ptc-completionist':
          wp_enqueue_style(
            'ptc-completionist_connect-asana-css',
            plugins_url( 'assets/css/connect-asana.css', __FILE__ ),
            [ 'ptc-completionist_admin-theme-css' ],
            $this->plugin_version
          );
          wp_enqueue_style(
            'ptc-completionist_admin-dashboard-css',
            plugins_url( 'assets/css/admin-dashboard.css', __FILE__ ),
            [ 'ptc-completionist_admin-theme-css' ],
            $this->plugin_version
          );
          wp_enqueue_script(
            'ptc-completionist_admin-dashboard-js',
            plugins_url( 'assets/js/admin-dashboard.js', __FILE__ ),
            [ 'jquery', 'fontawesome-5' ],
            $this->plugin_version
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
            $this->plugin_version
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
            $this->plugin_version
          );
          break;

        case 'completionist_page_ptc-completionist-automations':
          require_once $this->plugin_path . 'src/class-asana-interface.php';
          try {
            \PTC_Completionist\Asana_Interface::require_settings();
            $has_required_settings = TRUE;
          } catch ( \Exception $e ) {
            $has_required_settings = FALSE;
          }
          if ( $has_required_settings && \PTC_Completionist\Asana_Interface::has_connected_asana() ) {
            $asset_file = require_once( $this->plugin_path . 'build/index.asset.php' );
            wp_enqueue_script(
              'ptc-completionist_build-index-js',
              plugins_url( 'build/index.js', __FILE__ ),
              $asset_file['dependencies'],
              $this->plugin_version
            );
            require_once $this->plugin_path . 'src/automations/class-events.php';
            require_once $this->plugin_path . 'src/automations/class-fields.php';
            require_once $this->plugin_path . 'src/automations/class-actions.php';
            require_once $this->plugin_path . 'src/automations/class-data.php';
            wp_localize_script(
              'ptc-completionist_build-index-js',
              'ptc_completionist_automations',
              [
                'automations' => \PTC_Completionist\Automations\Data::get_automation_overviews(),
                'event_user_options' => \PTC_Completionist\Automations\Events::USER_OPTIONS,
                'event_post_options' => \PTC_Completionist\Automations\Events::POST_OPTIONS,
                'field_user_options' => \PTC_Completionist\Automations\Fields::USER_OPTIONS,
                'field_post_options' => \PTC_Completionist\Automations\Fields::POST_OPTIONS,
                'field_comparison_methods' => \PTC_Completionist\Automations\Fields::COMPARISON_METHODS,
                'action_options' => \PTC_Completionist\Automations\Actions::ACTION_OPTIONS,
                'workspace_users' => \PTC_Completionist\Asana_Interface::get_workspace_user_options(),
                'connected_workspace_users' => \PTC_Completionist\Asana_Interface::get_connected_workspace_user_options(),
                'workspace_projects' => \PTC_Completionist\Asana_Interface::get_workspace_project_options(),
                'nonce' => wp_create_nonce( 'ptc_completionist_automations' ),
              ]
            );
          }
          wp_enqueue_script( 'fontawesome-5' );
          wp_enqueue_style(
            'ptc-completionist_admin-automations-css',
            plugins_url( 'assets/css/admin-automations.css', __FILE__ ),
            [],
            $this->plugin_version
          );
          break;

      }//end switch hook suffix

    }//end register_scripts()

  }//end class

  $ptc_completionist = new PTC_Completionist();
  $ptc_completionist->register();

}//end if class_exists
