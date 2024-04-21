<?php
/**
 * Admin Pages class
 *
 * Registers admin pages.
 *
 * @since 3.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

/**
 * Registers admin pages.
 */
class Admin_Pages {

	/**
	 * The name of the plugin menu's main parent page.
	 *
	 * @since 3.0.0
	 *
	 * @var string PARENT_PAGE_SLUG
	 */
	public const PARENT_PAGE_SLUG = 'ptc-completionist';

	/**
	 * The data for frontend scripts relating to tasks.
	 *
	 * @see get_frontend_task_data()
	 *
	 * @since 4.0.0
	 *
	 * @var array $frontend_task_data
	 */
	private static $frontend_task_data;

	/**
	 * The data for frontend scripts relating to API requests.
	 *
	 * @see get_frontend_api_data()
	 *
	 * @since 4.0.0
	 *
	 * @var array $frontend_api_data
	 */
	private static $frontend_api_data;

	/**
	 * Registers code.
	 *
	 * @since 3.0.0
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_pages' ) );
		add_filter( 'plugin_action_links_' . PLUGIN_BASENAME, array( __CLASS__, 'filter_plugin_action_links' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'register_block_editor_assets' ) );
	}

	/**
	 * Gets the settings admin page URL.
	 *
	 * @since 3.0.0
	 */
	public static function get_settings_url() {
		return admin_url( 'admin.php?page=' . static::PARENT_PAGE_SLUG );
	}

	/**
	 * Adds the admin pages.
	 *
	 * @since 3.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 */
	public static function add_admin_pages() {

		add_menu_page(
			'Completionist &ndash; Settings',
			'Completionist',
			'edit_posts',
			static::PARENT_PAGE_SLUG,
			array( __CLASS__, 'display_admin_dashboard' ),
			'data:image/svg+xml;base64,' . base64_encode( '<svg width="20" height="20" aria-hidden="true" focusable="false" data-prefix="fas" data-icon="clipboard-check" class="svg-inline--fa fa-clipboard-check fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="white" d="M336 64h-80c0-35.3-28.7-64-64-64s-64 28.7-64 64H48C21.5 64 0 85.5 0 112v352c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM192 40c13.3 0 24 10.7 24 24s-10.7 24-24 24-24-10.7-24-24 10.7-24 24-24zm121.2 231.8l-143 141.8c-4.7 4.7-12.3 4.6-17-.1l-82.6-83.3c-4.7-4.7-4.6-12.3.1-17L99.1 285c4.7-4.7 12.3-4.6 17 .1l46 46.4 106-105.2c4.7-4.7 12.3-4.6 17 .1l28.2 28.4c4.7 4.8 4.6 12.3-.1 17z"></path></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			100 /* For default priorities, see https://developer.wordpress.org/reference/functions/add_menu_page/#default-bottom-of-menu-structure */
		);

		add_submenu_page(
			static::PARENT_PAGE_SLUG,
			'Completionist &ndash; Settings',
			'Settings', // parent page submenu title override.
			'edit_posts',
			static::PARENT_PAGE_SLUG,
			array( __CLASS__, 'display_admin_dashboard' ),
			null
		);

		add_submenu_page(
			static::PARENT_PAGE_SLUG,
			'Completionist &ndash; Automations',
			'Automations',
			'edit_posts',
			static::PARENT_PAGE_SLUG . '-automations',
			array( __CLASS__, 'display_automations_dashboard' ),
			null
		);
	}//end add_admin_pages()

	/**
	 * Edits the plugin row's action links.
	 *
	 * @since 4.0.0 Array keys are now named instead of numeric indices.
	 * @since 3.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 *
	 * @param string[] $links The plugin action link HTML items.
	 */
	public static function filter_plugin_action_links( $links ) {
		$links['ptc_docs']     = '<a href="https://docs.purpleturtlecreative.com/completionist/" target="_blank">Docs</a>';
		$links['ptc_settings'] = '<a href="' . esc_url( static::get_settings_url() ) . '">Settings</a>';
		return $links;
	}

	/**
	 * Registers and enqueues admin CSS and JS.
	 *
	 * @since 3.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public static function register_scripts( $hook_suffix ) {

		wp_register_style(
			'fontawesome-5',
			PLUGIN_URL . '/assets/vendor/fontawesome/css/all.min.css',
			array(),
			'5.12.1'
		);

		wp_register_style(
			'ptc-completionist_admin-theme-css',
			PLUGIN_URL . '/assets/styles/admin-theme.css',
			array(),
			PLUGIN_VERSION
		);

		$current_screen = get_current_screen();
		if (
			method_exists( $current_screen, 'is_block_editor' )
			&& $current_screen->is_block_editor()
		) {
			// Exit; assets are enqueued in enqueue_block_editor_assets.
			return;
		}

		switch ( $hook_suffix ) {

			case 'index.php':
				$asset_file = require_once PLUGIN_PATH . 'build/index_DashboardWidget.jsx.asset.php';
				wp_enqueue_script(
					'ptc-completionist_DashboardWidget',
					PLUGIN_URL . '/build/index_DashboardWidget.jsx.js',
					$asset_file['dependencies'],
					PLUGIN_VERSION,
					true
				);
				$js_data = wp_json_encode( static::get_frontend_task_data() );
				wp_add_inline_script(
					'ptc-completionist_DashboardWidget',
					"var PTCCompletionist = {$js_data};",
					'before'
				);
				wp_enqueue_style( 'fontawesome-5' );
				wp_enqueue_style(
					'ptc-completionist_DashboardWidget',
					PLUGIN_URL . '/build/index_DashboardWidget.jsx.css',
					array(),
					PLUGIN_VERSION
				);
				break;

			case 'post.php':
			case 'post-new.php':
				// Classic Editor metabox for backwards compatibility.
				$asset_file = require_once PLUGIN_PATH . 'build/index_PinnedTasksMetabox.jsx.asset.php';
				wp_enqueue_script(
					'ptc-completionist-pinned-tasks',
					PLUGIN_URL . '/build/index_PinnedTasksMetabox.jsx.js',
					$asset_file['dependencies'],
					PLUGIN_VERSION,
					true
				);
				wp_enqueue_style( 'fontawesome-5' );
				wp_enqueue_style(
					'ptc-completionist-pinned-tasks',
					PLUGIN_URL . '/build/index_PinnedTasksMetabox.jsx.css',
					array(),
					PLUGIN_VERSION
				);
				$js_data = wp_json_encode( static::get_frontend_task_data() );
				wp_add_inline_script(
					'ptc-completionist-pinned-tasks',
					"var PTCCompletionist = {$js_data};",
					'before'
				);
				break;

			case 'toplevel_page_ptc-completionist':
				wp_enqueue_style(
					'ptc-completionist_connect-asana-css',
					PLUGIN_URL . '/assets/styles/connect-asana.css',
					array( 'ptc-completionist_admin-theme-css' ),
					PLUGIN_VERSION
				);
				wp_enqueue_style(
					'ptc-completionist_admin-dashboard-css',
					PLUGIN_URL . '/assets/styles/admin-dashboard.css',
					array( 'ptc-completionist_admin-theme-css' ),
					PLUGIN_VERSION
				);
				wp_enqueue_style( 'fontawesome-5' );
				wp_enqueue_script(
					'ptc-completionist_admin-dashboard-js',
					PLUGIN_URL . '/assets/scripts/admin-dashboard.js',
					array( 'jquery' ),
					PLUGIN_VERSION,
					true
				);
				wp_localize_script(
					'ptc-completionist_admin-dashboard-js',
					'ptc_completionist_dashboard',
					array(
						'saved_workspace_gid' => Options::get( Options::ASANA_WORKSPACE_GID ),
						'saved_tag_gid'       => Options::get( Options::ASANA_TAG_GID ),
						'api'                 => array_intersect_key(
							static::get_frontend_api_data(),
							array(
								'auth_nonce'     => true,
								'nonce_get_tags' => true,
								'v1'             => true,
							)
						),
					)
				);
				break;

			case 'completionist_page_ptc-completionist-automations':
				try {
					Asana_Interface::require_settings();
					$has_required_settings = true;
				} catch ( \Exception $e ) {
					$has_required_settings = false;
				}
				if ( $has_required_settings && Asana_Interface::has_connected_asana() ) {
					$asset_file = require_once PLUGIN_PATH . 'build/index_Automations.jsx.asset.php';
					wp_enqueue_script(
						'ptc-completionist_Automations',
						PLUGIN_URL . '/build/index_Automations.jsx.js',
						$asset_file['dependencies'],
						PLUGIN_VERSION,
						true
					);
					wp_localize_script(
						'ptc-completionist_Automations',
						'ptc_completionist_automations',
						array(
							'action_options'            => Automations\Actions::ACTION_OPTIONS,
							'api'                       => array_intersect_key(
								static::get_frontend_api_data(),
								array(
									'auth_nonce'           => true,
									'nonce_create_automation' => true,
									'nonce_delete_automation' => true,
									'nonce_get_automation' => true,
									'nonce_get_post'       => true,
									'nonce_update_automation' => true,
									'v1'                   => true,
								)
							),
							'automations'               => Automations\Data::get_automation_overviews( null, true ),
							'connected_workspace_users' => Asana_Interface::get_connected_workspace_user_options(),
							'event_custom_options'      => Automations\Events::CUSTOM_OPTIONS,
							'event_post_options'        => Automations\Events::POST_OPTIONS,
							'event_user_options'        => Automations\Events::USER_OPTIONS,
							'field_comparison_methods'  => Automations\Fields::COMPARISON_METHODS,
							'field_post_options'        => Automations\Fields::POST_OPTIONS,
							'field_user_options'        => Automations\Fields::USER_OPTIONS,
							'workspace_projects'        => Asana_Interface::get_workspace_project_options(),
							'workspace_users'           => Asana_Interface::get_workspace_user_options(),
						)
					);
				}
				wp_enqueue_style( 'fontawesome-5' );
				wp_enqueue_style(
					'ptc-completionist_admin-automations-css',
					PLUGIN_URL . '/assets/styles/admin-automations.css',
					array(),
					PLUGIN_VERSION
				);
				break;
		}//end switch hook suffix
	}//end register_scripts()

	/**
	 * Registers assets for the Block Editor screen.
	 *
	 * @since 3.3.0
	 */
	public static function register_block_editor_assets() {
		$asset_file = require_once PLUGIN_PATH . 'build/index_BlockEditor.jsx.asset.php';
		wp_enqueue_script(
			'ptc-completionist-block-editor',
			PLUGIN_URL . '/build/index_BlockEditor.jsx.js',
			$asset_file['dependencies'],
			PLUGIN_VERSION,
			true
		);
		wp_enqueue_style( 'fontawesome-5' );
		wp_enqueue_style(
			'ptc-completionist-block-editor',
			PLUGIN_URL . '/build/index_BlockEditor.jsx.css',
			array(),
			PLUGIN_VERSION
		);
		$js_data = wp_json_encode( static::get_frontend_task_data() );
		wp_add_inline_script(
			'ptc-completionist-block-editor',
			"var PTCCompletionist = {$js_data};",
			'before'
		);
	}

	/**
	 * Gets the data for frontend script use relating to tasks.
	 *
	 * @see $frontend_task_data
	 *
	 * @since 4.0.0
	 *
	 * @return array The data. Remember to JSON encode for use
	 * on the frontend.
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	private static function get_frontend_task_data() {

		if ( ! empty( static::$frontend_task_data ) ) {
			return static::$frontend_task_data;
		}

		$js_data = array(
			'error' => array(
				'code'    => 500,
				'message' => 'An unexpected error occurred.',
			),
		);

		try {

			// Ensure required settings.
			Asana_Interface::require_settings();

			// Load Asana client context for current user.
			Asana_Interface::get_client();

			if ( ! Asana_Interface::is_workspace_member() ) {
				throw new \Exception( 'You are not a member of the assigned Asana Workspace.', 403 );
			}

			$all_site_tasks = Asana_Interface::maybe_get_all_site_tasks();

			$post_id       = get_the_ID();
			$display_tasks = array();

			if ( $post_id && is_int( $post_id ) ) {
				// Only display pinned tasks in post context.
				$pinned_task_gids = Options::get( Options::PINNED_TASK_GID, get_the_ID() );
				// Map pinned task gids to full task objects.
				foreach ( $pinned_task_gids as &$task_gid ) {
					// Ignore tasks this user doesn't have permission to view.
					if ( isset( $all_site_tasks[ $task_gid ] ) ) {
						$display_tasks[] = $all_site_tasks[ $task_gid ];
					}
				}
			} else {
				// Display all tasks outside of post context.
				$display_tasks = array_values( $all_site_tasks );
			}

			$js_data = array(
				'api'      => array_intersect_key(
					static::get_frontend_api_data(),
					array(
						'auth_nonce'        => true,
						'nonce_create_task' => true,
						'nonce_delete_task' => true,
						'nonce_pin_task'    => true,
						'nonce_unpin_task'  => true,
						'nonce_update_task' => true,
						'v1'                => true,
					)
				),
				'tasks'    => $display_tasks,
				'users'    => Asana_Interface::get_connected_workspace_users(),
				'projects' => Asana_Interface::get_workspace_project_options(),
				'me'       => Asana_Interface::get_me(),
				'tag_url'  => HTML_Builder::get_asana_tag_url(),
			);
		} catch ( \Exception $err ) {
			$js_data = array(
				'error' => array(
					'code'    => $err->getCode(),
					'message' => $err->getMessage(),
				),
			);
		}

		static::$frontend_task_data = $js_data;
		return $js_data;
	}

	/**
	 * Gets the data for frontend script use relating to API requests.
	 *
	 * @see $frontend_task_data
	 *
	 * @since 4.0.0
	 *
	 * @return array The data. Remember to JSON encode for use
	 * on the frontend.
	 */
	public static function get_frontend_api_data() : array {

		if ( ! empty( static::$frontend_api_data ) ) {
			return static::$frontend_api_data;
		}

		$api_data = array(
			'auth_nonce'              => wp_create_nonce( 'wp_rest' ),
			'nonce'                   => wp_create_nonce( 'ptc_completionist' ),
			'nonce_create_automation' => wp_create_nonce( 'ptc_completionist_create_automation' ),
			'nonce_create_task'       => wp_create_nonce( 'ptc_completionist_create_task' ),
			'nonce_delete_automation' => wp_create_nonce( 'ptc_completionist_delete_automation' ),
			'nonce_delete_task'       => wp_create_nonce( 'ptc_completionist_delete_task' ),
			'nonce_get_automation'    => wp_create_nonce( 'ptc_completionist_get_automation' ),
			'nonce_get_post'          => wp_create_nonce( 'ptc_completionist_get_post' ),
			'nonce_get_tags'          => wp_create_nonce( 'ptc_completionist_get_tags' ),
			'nonce_pin_task'          => wp_create_nonce( 'ptc_completionist_pin_task' ),
			'nonce_unpin_task'        => wp_create_nonce( 'ptc_completionist_unpin_task' ),
			'nonce_update_automation' => wp_create_nonce( 'ptc_completionist_update_automation' ),
			'nonce_update_task'       => wp_create_nonce( 'ptc_completionist_update_task' ),
			'url'                     => rest_url(),
			'v1'                      => rest_url( REST_API_NAMESPACE_V1 ),
		);

		static::$frontend_api_data = $api_data;
		return $api_data;
	}

	/**
	 * Displays the admin dashboard (plugin settings) admin page
	 * content, including processing any form submissions, if
	 * the current user is permitted.
	 *
	 * @since 4.0.0 Moved to Admin_Pages class from raw
	 * PHP+HTML template file inclusion to ensure variables
	 * are properly encapsulated from the global namespace.
	 * @since 1.0.0
	 */
	public static function display_admin_dashboard() {

		// Ensure user has permission to use this page.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
			return;
		}

		try {

			// Process the current submission and display related notices.
			// Must happen BEFORE checking Asana API authentication
			// because the user could have just submitted their PAT.
			static::process_save_settings_submit();

			// Check if user is authenticated for API usage.
			Asana_Interface::get_client();

			// Get the current user's Asana profile.
			$me = Asana_Interface::get_me();

			?>
			<section id="ptc-asana-user">
				<div class="asana-connected-profile-photo">
					<img src="<?php echo esc_url( $me->photo->image_128x128 ); ?>" alt="<?php echo esc_attr( $me->name ); ?> on Asana">
					<i class="fas fa-check"></i>
				</div>
				<h2>You're connected, <?php echo esc_html( $me->name ); ?>!</h2>
				<p>Your Asana account is successfully connected. Completionist is able to help you get stuff done on <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?> as long as you are a member of this site's assigned workspace.</p>
				<h3>Helpful Links</h3>
				<div class="ptc-button-group">
					<a class="ptc-asana-button" href="https://app.asana.com/" target="_asana">
						<img src="<?php echo esc_url( PLUGIN_URL . '/assets/images/asana_logo-horizontal-color.png' ); ?>" alt="Asana" title="Open Asana">
					</a>
					<a class="ptc-icon-button" href="https://docs.purpleturtlecreative.com/completionist/" target="_blank">
						<i class="fas fa-book"></i>
						Readme
					</a>
					<a class="ptc-icon-button" href="https://wordpress.org/support/plugin/completionist/" target="_blank">
						<i class="fas fa-comments"></i>
						Support
					</a>
					<a class="ptc-icon-button" href="https://purpleturtlecreative.com/completionist/plugin-info/#changelog" target="_blank">
						<i class="fas fa-file-code"></i>
						Changelog
					</a>
				</div>
			</section><!--close section#ptc-asana-user-->

			<section id="ptc-asana-workspace">
				<?php
				try {

					$can_manage_options   = current_user_can( 'manage_options' );
					$chosen_workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
					$is_workspace_member  = Asana_Interface::is_workspace_member( $chosen_workspace_gid );
					$chosen_tag_gid       = Options::get( Options::ASANA_TAG_GID );

					$pinned_tasks_count = Options::count_all_pinned_tasks();

					if ( $can_manage_options || $is_workspace_member ) {
						/* User is able to view workspace details */
						?>
						<form method="POST">
							<div class="field-group">
								<label for="asana-workspace"><i class="fas fa-building" title="Workspace"></i></label>
								<select id="asana-workspace" name="asana_workspace"<?php echo ( ( ! $can_manage_options ) ? ' disabled="disabled"' : '' ); ?> required="required">
									<?php
									if ( '' === $chosen_workspace_gid ) {
										echo '<option value="" selected="selected">Choose a Workspace...</option>';
									} elseif ( ! $is_workspace_member ) {
										echo '<option value="" selected="selected">(Unauthorized)</option>';
									}
									foreach ( $me->workspaces as $workspace ) {
										printf(
											'<option value="%s" %s>%s</option>',
											esc_attr( $workspace->gid ),
											( $chosen_workspace_gid === $workspace->gid ) ? 'selected="selected"' : '',
											esc_html( $workspace->name )
										);
									}
									?>
								</select>
								<label for="asana-tag"><i class="fas fa-tag" title="Tag"></i></label>
								<select id="asana-tag" name="asana_tag"<?php echo ( ( ! $can_manage_options ) ? ' disabled="disabled"' : '' ); ?> required="required">
									<option value="" <?php echo ( '' === $chosen_tag_gid ) ? 'selected="selected"' : ''; ?>>Choose a Tag...</option>
									<option value="create">(Create New Tag)</option>
									<?php
									if ( ! $is_workspace_member && '' !== $chosen_tag_gid ) {
										echo '<option value="" selected="selected">(Unauthorized)</option>';
									}
									/* Tag options loaded via JavaScript */
									?>
								</select>
								<input type="text" id="asana-tag-name" name="asana_tag_name" placeholder="Site tag name" value="website: <?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
								<?php if ( $can_manage_options ) { ?>
								<input type="hidden" name="asana_workspace_save_nonce" value="<?php echo esc_attr( wp_create_nonce( 'asana_workspace_save' ) ); ?>">
								<input type="submit" name="asana_workspace_save" value="Save">
								<?php }//end if can_manage_options show submit button ?>
							</div>
							<?php
							try {
								Asana_Interface::require_settings();
							} catch ( \Exception $e ) {
								$alert_msg = HTML_Builder::get_error_message( $e );
								echo '<p class="error-note"><i class="fas fa-exclamation-circle"></i>' . esc_html( $alert_msg ) . '</p>';
							}
							?>
							<p id="asana-workspace-warning" class="warning-note"><i class="fas fa-exclamation-triangle"></i><strong>WARNING:</strong> Changing workspaces will remove all <?php echo esc_html( $pinned_tasks_count ); ?> currently pinned tasks from this site.</p>
							<p id="asana-tag-warning" class="warning-note"><i class="fas fa-exclamation-triangle"></i><strong>WARNING:</strong> Changing the site's tag will remove any pinned tasks that do not have the new tag.</p>
						</form>

						<div id="ptc-asana-workspace-users">
						<?php
						if ( '' === $chosen_workspace_gid ) {
							if ( $can_manage_options ) {
								?>
								<p class="nothing-to-see">A workspace has not been assigned to this site.<br>Please choose an Asana workspace from the dropdown above to start collaborating on site tasks.</p>
								<?php
							} else {
								?>
								<p class="nothing-to-see">A workspace has not been assigned to this site.<br>Please ask an <a href="<?php echo esc_url( admin_url( 'users.php?role=administrator' ) ); ?>" target="_blank">administrator</a> to set an Asana workspace to begin collaborating on site tasks.</p>
								<?php
							}
						} elseif ( ! $is_workspace_member ) {
							?>
							<p class="nothing-to-see">You are unauthorized to collaborate on this site's tasks.<br>Please ask an <a href="<?php echo esc_url( admin_url( 'users.php?role=administrator' ) ); ?>" target="_blank">administrator</a> to invite you to this site's workspace in Asana.</p>
							<?php
						} else {

							$found_workspace_users     = Asana_Interface::find_workspace_users();
							$connected_workspace_users = Asana_Interface::get_connected_workspace_users();
							$workspace_users           = $found_workspace_users + $connected_workspace_users;

							if ( empty( $workspace_users ) ) {
								?>
								<p class="nothing-to-see">No collaborators were found by email.</p>
								<?php
							} else {
								$displayed_user_ids = array();
								foreach ( $workspace_users as $user ) {
									// Ensure users are only displayed once.
									if ( ! in_array( $user->ID, $displayed_user_ids, true ) ) {
										$displayed_user_ids[] = $user->ID;
										static::display_collaborator_row( $user );
									}
								}
							}//end if empty collaborators
						}//end list recognized collaborators
					} else {
						/* User is unable to view workspace details */
						?>
						<div id="ptc-asana-workspace-unauthorized" class="note-box note-box-error">
							<i class="fas fa-ban"></i>
							<p><strong>Unauthorized.</strong> To view workspace details, you must be a member of the designated Asana workspace or have administrative capabilities to manage options.</p>
						</div>
						<?php
					}//end if can view workspace details
				} catch ( \Exception $e ) {
					/* An Asana API client exception occurred */
					?>
					<div id="ptc-asana-workspace-error" class="note-box note-box-error">
						<i class="fas fa-times"></i>
						<p><strong>Error <?php echo esc_html( $e->getCode() ); ?>.</strong><br>Unable to load workspace details: <?php echo esc_html( $e->getMessage() ); ?></p>
					</div>
					<?php
				}
				?>
			</section><!--close section#ptc-asana-workspace-->

			<?php
			if (
				current_user_can( 'manage_options' ) &&
				! empty( $connected_workspace_users )
			) :
				?>
			<section id="ptc-frontend-auth-user">
				<label for="asana-frontend-user">Frontend Authentication User</label>
				<form method="POST">
					<div class="field-group">
						<select id="asana-frontend-user" name="wp_user_id" required>
							<option value="">Choose a user...</option>
							<?php
							$frontend_auth_user_id = Options::get( Options::FRONTEND_AUTH_USER_ID );
							foreach ( $connected_workspace_users as &$wp_user ) {
								printf(
									'<option value="%1$s"%3$s>%2$s</option>',
									esc_attr( $wp_user->ID ),
									esc_html( $wp_user->display_name . ' (' . $wp_user->user_email . ')' ),
									( $frontend_auth_user_id === $wp_user->ID ) ? ' selected' : ''
								);
							}
							?>
						</select>
						<input type="hidden" name="asana_frontend_user_save_nonce" value="<?php echo esc_attr( wp_create_nonce( 'asana_frontend_user_save' ) ); ?>">
						<input type="submit" name="asana_frontend_user_save" value="Save">
					</div>
				</form>
				<div class="help-notes">
					<div class="note-box note-box-info">
						<i class="fas fa-question"></i>
						<p>This WordPress user's Asana connection will be used to render <a href="https://docs.purpleturtlecreative.com/completionist/shortcodes/" target="_blank">shortcodes</a> on your website.</p>
					</div>
					<div class="note-box note-box-warning">
						<i class="fas fa-lightbulb"></i>
						<p>The user should have access to all tasks and projects in Asana that you wish to display on your website, so it's best to set this to someone such as your project manager. <a href="https://docs.purpleturtlecreative.com/completionist/getting-started/#set-a-frontend-authentication-user" target="_blank">Learn more.</a></p>
					</div>
				</div>
			</section>
			<?php endif; ?>

			<?php
			if (
				current_user_can( 'manage_options' ) ||
				current_user_can( 'edit_posts' )
			) :
				$saved_asana_cache_ttl = Options::get( Options::CACHE_TTL_SECONDS );
				?>
			<section id="ptc-asana-data-cache">
				<div class="section-title">Asana Data Cache</div>
				<form id="ptc-asana-cache-duration-ttl" method="POST">
					<div class="field-group">
						<label for="asana-cache-ttl">Cache Duration (TTL)</label>
						<div class="suffixed-input-field">
							<input id="asana-cache-ttl" name="asana_cache_ttl" type="number" min="0" step="1" value="<?php echo esc_attr( $saved_asana_cache_ttl ); ?>" <?php echo esc_attr( ( current_user_can( 'manage_options' ) ) ? 'required' : 'readonly' ); ?> />
							<span class="field-suffix">Seconds</span>
						</div>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
						<input type="hidden" name="asana_cache_ttl_save_nonce" value="<?php echo esc_attr( wp_create_nonce( 'asana_cache_ttl_save' ) ); ?>">
						<input type="submit" name="asana_cache_ttl_save" value="Save">
						<?php endif; ?>
					</div>
					<p>Currently set to <strong><?php echo esc_html( $saved_asana_cache_ttl ); ?> seconds</strong> which is <strong><?php echo esc_html( human_readable_duration( gmdate( 'H:i:s', $saved_asana_cache_ttl ) ) ); ?></strong>.</p>
				</form>
				<form id="ptc-asana-data-cache-purge" method="POST">
					<input type="hidden" name="purge_asana_cache_nonce" value="<?php echo esc_attr( wp_create_nonce( 'purge_asana_cache' ) ); ?>">
					<div class="note-box">
						<p class="cache-purge-notice">
							<input class="warning" type="submit" name="purge_asana_cache" value="Clear Cache">
							This will clear all cached Asana data such as projects, tasks, and media attachments. You can use this to ensure the latest information is fetched from Asana during the next load. <a href="https://docs.purpleturtlecreative.com/completionist/shortcodes/caching/" target="_blank">Learn more.</a>
						</p>
					</div>
				</form>
			</section>
			<?php endif; ?>

			<section id="ptc-disconnect-asana">
				<form method="POST">
					<input type="hidden" name="asana_disconnect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'disconnect_asana' ) ); ?>">
					<div class="note-box note-box-error">
						<p class="disconnect-notice">
							<input class="error" type="submit" name="asana_disconnect" value="Disconnect">
							This will remove your encrypted Personal Access Token and Asana user id from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features.
						</p>
					</div>
				</form>
			</section>
			<?php
		} catch ( Errors\No_Authorization $e ) {
			/* User is NOT authenticated for API usage. */
			?>
			<div id="ptc-asana-connect">

				<div class="connect-services">
					<div class="service-logo service-completionist">
						<img class="completionist-logo" src="<?php echo esc_url( PLUGIN_URL . '/assets/images/completionist_asana-for-wordpress_300x300.jpg' ); ?>" alt="Completionist" width="90" height="90" />
					</div>
					<div class="connect-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M438.6 150.6c12.5-12.5 12.5-32.8 0-45.3l-96-96c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.7 96 32 96C14.3 96 0 110.3 0 128s14.3 32 32 32l306.7 0-41.4 41.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l96-96zm-333.3 352c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 416 416 416c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0 41.4-41.4c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-96 96c-12.5 12.5-12.5 32.8 0 45.3l96 96z"/></svg>
					</div>
					<div class="service-logo service-asana">
						<img class="asana-logo" src="<?php echo esc_url( PLUGIN_URL . '/assets/images/asana_logo-vertical-color.png' ); ?>" alt="Asana" width="70" height="46" />
					</div>
				</div>

				<div class="content">

					<h2>Connect Asana</h2>

					<form method="POST">
						<div class="field-group connection-permissions">
							<p>To&nbsp;use&nbsp;<strong>Completionist</strong>, you must connect your&nbsp;Asana&nbsp;account to grant&nbsp;the&nbsp;plugin&nbsp;permission&nbsp;to:</p>
							<ul>
								<li>Access your name and email address for display purposes.</li>
								<li>Access your tasks, projects, and workspaces for display purposes.</li>
								<li>Create and modify tasks, projects, and comments on your behalf.</li>
							</ul>
						</div>
						<div class="field-group asana-pat">
							<label for="asana-pat">Personal Access Token:</label>
							<div class="input-submit-field">
								<div class="icon-input-field">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.8 8.3 53.7L7 391c-4.5 4.5-7 10.6-7 17v80c0 13.3 10.7 24 24 24h80c13.3 0 24-10.7 24-24V448h40c13.3 0 24-10.7 24-24V384h40c6.4 0 12.5-2.5 17-7l33.3-33.3c16.9 5.4 35 8.3 53.7 8.3zM376 96a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"/></svg>
									<input id="asana-pat" name="asana_pat" type="password" required autofocus />
								</div>
								<input type="hidden" name="asana_connect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'connect_asana' ) ); ?>" />
								<input type="submit" name="asana_connect" value="Authorize" />
							</div>
							<p class="help-link"><a href="https://app.asana.com/0/developer-console" target="_asana">Visit your Asana developer console<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M320 0c-17.7 0-32 14.3-32 32s14.3 32 32 32h82.7L201.4 265.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L448 109.3V192c0 17.7 14.3 32 32 32s32-14.3 32-32V32c0-17.7-14.3-32-32-32H320zM80 32C35.8 32 0 67.8 0 112V432c0 44.2 35.8 80 80 80H400c44.2 0 80-35.8 80-80V320c0-17.7-14.3-32-32-32s-32 14.3-32 32V432c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16H192c17.7 0 32-14.3 32-32s-14.3-32-32-32H80z"/></svg></a></p>
						</div>
					</form>

				</div>

				<p class="footnote">**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>

			</div>
			<?php
		} catch ( \Exception $e ) {
			?>
			<div id="ptc-asana-dashboard-error" class="note-box note-box-error">
				<i class="fas fa-times"></i>
				<p><strong>Error <?php echo esc_html( $e->getCode() ); ?>.</strong> <?php echo wp_kses_post( $e->getMessage() ); ?></p>
			</div>
			<?php
		}//end try catch asana client
	}//end display_admin_dashboard()

	/**
	 * Outputs a row of Asana workspace collaborator information
	 * for the provided user.
	 *
	 * @since 4.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 *
	 * @param \WP_User $user The collaborator to display.
	 */
	private static function display_collaborator_row( \WP_User $user ) {

		$gravatar            = get_avatar( $user->ID, 30, 'mystery' );
		$name                = $user->display_name;
		$roles_csv           = implode( ',', $user->roles );
		$email               = $user->user_email;
		$has_connected_asana = Asana_Interface::has_connected_asana( $user->ID );
		?>
		<div class="ptc-asana-collaborator-row" data-user-id="<?php echo esc_attr( $user->ID ); ?>">

			<div class="user">
				<?php echo wp_kses_post( $gravatar ); ?>
				<div class="identity">
					<p><?php echo esc_html( $name ); ?></p>
					<p class="roles"><?php echo esc_html( $roles_csv ); ?></p>
				</div>
			</div>

			<div class="email">
				<p><a href="<?php echo esc_url( "mailto:$email" ); ?>"><?php echo esc_html( $email ); ?></a></p>
			</div>

			<div class="connection-status" data-status="<?php echo $has_connected_asana ? 'yes' : 'no'; ?>">
				<p>
					<?php
					if ( $has_connected_asana ) {
						echo '<i class="fas fa-check-circle"></i>Connected Asana';
					} else {
						echo '<i class="fas fa-times-circle"></i>Not Connected';
					}
					?>
				</p>
			</div>

		</div>
		<?php
	}

	/**
	 * Processes settings form submissions and displays process notices.
	 *
	 * @since 4.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	private static function process_save_settings_submit() {

		if (
			isset( $_POST['asana_connect'] )
			&& isset( $_POST['asana_pat'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asana_connect_nonce'] ) ), 'connect_asana' ) !== false
		) {

			try {
				$did_save_pat = Options::save( Options::ASANA_PAT, $_POST['asana_pat'] );
			} catch ( \Exception $e ) {
				echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
				$did_save_pat = false;
			}

			if ( true === $did_save_pat ) {

				try {
					$asana          = Asana_Interface::get_client();
					$me             = Asana_Interface::get_me();
					$did_save_gid   = Options::save( Options::ASANA_USER_GID, $me->gid );
					$did_delete_pat = false;
					$did_delete_gid = false;
				} catch ( \Exception $e ) {
					echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
					$did_save_gid   = false;
					$did_delete_pat = Options::delete( Options::ASANA_PAT );
					$did_delete_gid = Options::delete( Options::ASANA_USER_GID );
				}

				if ( true === $did_delete_pat ) {
					echo '<p class="notice notice-error">An error occurred, so your Personal Access Token could not be saved.</p>';
				} elseif ( true === $did_save_gid ) {
					echo '<p class="notice notice-success">Your Asana account was successfully connected!</p>';
				}
			}//end if did_save_pat
		}//end if asana_connect

		if (
			isset( $_POST['asana_disconnect'] )
			&& isset( $_POST['asana_disconnect_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asana_disconnect_nonce'] ) ), 'disconnect_asana' ) !== false
		) {

			$user_id = (int) get_current_user_id();

			$did_delete_pat = Options::delete( Options::ASANA_PAT, $user_id );
			$did_delete_gid = Options::delete( Options::ASANA_USER_GID, $user_id );

			if ( $did_delete_pat || $did_delete_gid ) {

				echo '<p class="notice notice-success">Your Asana account was successfully forgotten!</p>';

				$frontend_auth_user_id = (int) Options::get( Options::FRONTEND_AUTH_USER_ID );
				if ( $user_id === $frontend_auth_user_id ) {
					Options::delete( Options::FRONTEND_AUTH_USER_ID );
					echo '<p class="notice notice-warning">You were the default frontend authentication user. Completionist shortcodes may not work until a new frontend authentication user is saved!</p>';
				}
			} else {
				echo '<p class="notice notice-error">Your Asana account could not be disconnected.</p>';
			}
		}//end if asana_disconnect

		if (
			isset( $_POST['asana_frontend_user_save'] )
			&& ! empty( $_POST['wp_user_id'] )
			&& isset( $_POST['asana_frontend_user_save_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asana_frontend_user_save_nonce'] ) ), 'asana_frontend_user_save' ) !== false
			&& current_user_can( 'manage_options' )
		) {

			$submitted_wp_user_id = (int) Options::sanitize( Options::FRONTEND_AUTH_USER_ID, $_POST['wp_user_id'] );

			// Save the frontend authentication user ID.
			Options::save(
				Options::FRONTEND_AUTH_USER_ID,
				(string) $submitted_wp_user_id,
				true
			);

			// Get the saved and validated user ID.
			$retrieved_wp_user_id = (int) Options::get( Options::FRONTEND_AUTH_USER_ID );

			// Confirm that it was saved successfully.
			if ( $retrieved_wp_user_id === $submitted_wp_user_id ) {
				echo '<p class="notice notice-success">The frontend authentication user was successfully saved!</p>';
			} else {
				echo '<p class="notice notice-error">Failed to save the frontend authentication user.</p>';
			}
		}//end if asana_frontend_user_save

		if (
			isset( $_POST['asana_cache_ttl_save'] )
			&& current_user_can( 'manage_options' )
			&& isset( $_POST['asana_cache_ttl'] )
			&& isset( $_POST['asana_cache_ttl_save_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asana_cache_ttl_save_nonce'] ) ), 'asana_cache_ttl_save' ) !== false
		) {

			// Check if numeric.
			if ( ! is_numeric( $_POST['asana_cache_ttl'] ) ) {
				echo '<p class="notice notice-error">Failed to save non-numeric Asana data cache duration. Please provide the number of seconds as an integer value.</p>';
			} else {

				// Sanitize submitted value.
				$submitted_ttl = (int) Options::sanitize( Options::CACHE_TTL_SECONDS, $_POST['asana_cache_ttl'] );

				// Save the value.
				Options::save(
					Options::CACHE_TTL_SECONDS,
					(string) $submitted_ttl,
					true
				);

				// Get the saved and validated value.
				$retrieved_ttl = (int) Options::get( Options::CACHE_TTL_SECONDS );

				// Confirm that it was saved successfully.
				if ( $retrieved_ttl === $submitted_ttl ) {
					echo '<p class="notice notice-success">The Asana data cache duration was successfully saved!</p>';
				} else {
					echo '<p class="notice notice-error">Failed to save the Asana data cache duration.</p>';
				}
			}
		}//end if asana_cache_ttl_save

		if (
			isset( $_POST['purge_asana_cache'] ) &&
			(
				current_user_can( 'manage_options' ) ||
				current_user_can( 'edit_posts' )
			) &&
			isset( $_POST['purge_asana_cache_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['purge_asana_cache_nonce'] ) ), 'purge_asana_cache' ) !== false
		) {
			$rows_affected = Request_Token::clear_cache_data();
			printf(
				'<p class="notice notice-info">Cleared %s cache record(s) &mdash; Fresh data will be fetched from Asana on the next page load.</p>',
				esc_html( $rows_affected )
			);
		}//end if purge_asana_cache

		try {
			if (
				isset( $_POST['asana_workspace_save'] )
				&& isset( $_POST['asana_workspace'] )
				&& isset( $_POST['asana_tag'] )
				&& isset( $_POST['asana_workspace_save_nonce'] )
				&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['asana_workspace_save_nonce'] ) ), 'asana_workspace_save' ) !== false//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				&& current_user_can( 'manage_options' )
			) {

				/* Save workspace */

				$workspace_gid = Options::sanitize( Options::ASANA_WORKSPACE_GID, $_POST['asana_workspace'] );
				if ( '' === $workspace_gid ) {
					throw new \Exception( 'Invalid workspace identifier', 400 );
				}

				if ( Options::save( Options::ASANA_WORKSPACE_GID, $workspace_gid ) ) {
					echo '<p class="notice notice-success">This site\'s workspace was updated successfully.</p>';
					if ( Options::delete( Options::PINNED_TASK_GID, -1 ) ) {
						echo '<p class="notice notice-success">All pinned tasks were removed from this site.</p>';
					}
				}

				/* Save site tag */

				if ( 'create' === Options::sanitize( 'string', $_POST['asana_tag'] ) ) {

					if ( isset( $_POST['asana_tag_name'] ) ) {
						$tag_name = Options::sanitize( 'string', $_POST['asana_tag_name'] );
						if ( empty( $tag_name ) ) {
							throw new \Exception( 'Invalid name for new tag.', 400 );
						}
					} else {
						throw new \Exception( 'A tag name is required to create a new tag.', 400 );
					}

					try {

						$asana   = Asana_Interface::get_client();
						$params  = array(
							'name'      => $tag_name,
							'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
						);
						$new_tag = $asana->tags->create( $params );
						$tag_gid = $new_tag->gid;

					} catch ( \Asana\Errors\NotFoundError $e ) {
						if ( Options::delete( Options::ASANA_WORKSPACE_GID ) ) {
							throw new \Exception( 'The saved workspace does not exist, so it was reset. Please save a different workspace and tag.', 404 );
						} else {
							throw new \Exception( 'The specified workspace does not exist.', 404 );
						}
					}
				} else {

					$tag_gid = Options::sanitize( Options::ASANA_TAG_GID, $_POST['asana_tag'] );

					try {
						$asana   = Asana_Interface::get_client();
						$the_tag = $asana->tags->findById( $tag_gid );
						$tag_gid = $the_tag->gid;
						if (
							isset( $the_tag->workspace->gid )
							&& Options::get( Options::ASANA_WORKSPACE_GID ) !== $the_tag->workspace->gid
						) {
							throw new \Exception( 'Tag does not belong to the saved workspace.', 409 );
						}
					} catch ( \Asana\Errors\NotFoundError $e ) {
						throw new \Exception( 'Tag does not exist.', 404 );
					}
				}//end if create new tag

				if ( ! isset( $tag_gid ) || '' === $tag_gid ) {
					throw new \Exception( 'Invalid tag identifier.', 400 );
				}

				if ( Options::save( Options::ASANA_TAG_GID, $tag_gid ) ) {
					echo '<p class="notice notice-success">This site\'s tag was updated successfully.</p>';
				}
			}//end if asana_workspace_save
		} catch ( \Exception $e ) {
			$err_code = $e->getCode();
			if (
				0 === $err_code
				&& isset( $e->status )
				&& $e->status > 0
			) {
				$err_code = $e->status;
			}
			echo '<p class="notice notice-error">Error ' . esc_html( $err_code ) . ': ' . esc_html( $e->getMessage() ) . '</p>';
		}
	}

	/**
	 * Displays the Automations admin page.
	 *
	 * @since 4.0.0 Moved to Admin_Pages class.
	 * @since 1.0.0
	 *
	 * @throws \Exception Handled in try-catch block.
	 */
	public static function display_automations_dashboard() {

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( '<strong>Error: Unauthorized.</strong> You must have post editing capabilities to use Completionist.' );
			return;
		}

		try {

			Asana_Interface::require_settings();

			if ( Asana_Interface::has_connected_asana() ) {
				?>
				<div id="ptc-PTCCompletionistAutomations">
					<p class="ptc-loading"><i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i>Loading...</p>
				</div>
				<?php
			} else {
				throw new \Exception( 'Not Authorized. Please connect your Asana account to use Completionist.', 401 );
			}
		} catch ( \Exception $e ) {
			?>
			<div class="ptc-error-screen">
				<p>
					<strong><?php echo esc_html( 'Error ' . HTML_Builder::get_error_code( $e ) . '.' ); ?></strong>
					<br />
					<?php echo esc_html( HTML_Builder::get_error_message( $e ) ); ?>
					<br />
					<a href="<?php echo esc_url( static::get_settings_url() ); ?>">
						Go to Settings
						<i class="fas fa-long-arrow-alt-right"></i>
					</a>
				</p>
			</div>
			<?php
		}
	}
}//end class
