<?php
/**
 * The main administrative dashboard for a user to view and manage plugin and
 * API settings.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';

// Begin output.
require_once PLUGIN_PATH . 'src/admin/script-save-settings.php';

try {

	$asana = Asana_Interface::get_client();

	/* User is authenticated for API usage. */

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
				Documentation
			</a>
			<a class="ptc-icon-button" href="https://purpleturtlecreative.com/completionist/plugin-info/#changelog" target="_blank">
				<i class="fas fa-file-code"></i>
				Changelog
			</a>
		</div>
		<p>Please send feedback to <a href="mailto:michelle@purpleturtlecreative.com" target="_blank">michelle@purpleturtlecreative.com</a></p>
	</section><!--close section#ptc-asana-user-->

	<section id="ptc-asana-workspace">
		<?php
		try {

			$can_manage_options = current_user_can( 'manage_options' );
			$chosen_workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
			$is_workspace_member = Asana_Interface::is_workspace_member( $chosen_workspace_gid );
			$chosen_tag_gid = Options::get( Options::ASANA_TAG_GID );

			$pinned_tasks_count = Options::count_all_pinned_tasks();
			$disabled = ( ! $can_manage_options ) ? ' disabled="disabled"' : '';

			if ( $can_manage_options || $is_workspace_member ) {
				/* User is able to view workspace details */
				?>
				<form method="POST">
					<div class="field-group">
						<label for="asana-workspace"><i class="fas fa-building" title="Workspace"></i></label>
						<select id="asana-workspace" name="asana_workspace" <?php echo $disabled; ?> required="required">
							<?php
							if ( $chosen_workspace_gid === '' ) {
								echo '<option value="" selected="selected">Choose a Workspace...</option>';
							} elseif ( ! $is_workspace_member ) {
								echo '<option value="" selected="selected">(Unauthorized)</option>';
							}
							foreach ( $me->workspaces as $workspace ) {
								$selected = ( $chosen_workspace_gid === $workspace->gid ) ? ' selected="selected"' : '';
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
						<select id="asana-tag" name="asana_tag" <?php echo $disabled; ?> required="required">
							<option value="" <?php echo ( $chosen_tag_gid === '' ) ? 'selected="selected"' : ''; ?>>Choose a Tag...</option>
							<option value="create">(Create New Tag)</option>
							<?php
							if ( ! $is_workspace_member && $chosen_tag_gid !== '' ) {
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
						require_once PLUGIN_PATH . 'src/includes/class-html-builder.php';
						$alert_msg = HTML_Builder::get_error_message( $e );
						echo '<p class="error-note"><i class="fas fa-exclamation-circle"></i>' . esc_html( $alert_msg ) . '</p>';
					}
					?>
					<p id="asana-workspace-warning" class="warning-note"><i class="fas fa-exclamation-triangle"></i><strong>WARNING:</strong> Changing workspaces will remove all <?php echo esc_html( $pinned_tasks_count ); ?> currently pinned tasks from this site.</p>
					<p id="asana-tag-warning" class="warning-note"><i class="fas fa-exclamation-triangle"></i><strong>WARNING:</strong> Changing the site's tag will remove any pinned tasks that do not have the new tag.</p>
				</form>

				<div id="ptc-asana-workspace-users">
				<?php
				if ( $chosen_workspace_gid === '' ) {
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

					$found_workspace_users = Asana_Interface::find_workspace_users();
					$connected_workspace_users = Asana_Interface::get_connected_workspace_users();
					$workspace_users = $found_workspace_users + $connected_workspace_users;

					if ( empty( $workspace_users ) ) {
						?>
						<p class="nothing-to-see">No collaborators were found by email.</p>
						<?php
					} else {
						$displayed_user_ids = [];
						foreach ( $workspace_users as $user ) {
							// Ensure users are only displayed once.
							if ( ! in_array( $user->ID, $displayed_user_ids, true ) ) {
								$displayed_user_ids[] = $user->ID;
								display_collaborator_row( $user );
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
					foreach ( $connected_workspace_users as $gid => $wp_user ) {
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
	?>
	<section id="ptc-asana-data-cache">
		<div class="section-title">Asana Data Cache</div>
		<form id="ptc-asana-cache-duration-ttl" method="POST">
			<div class="field-group">
				<label for="asana-cache-ttl">Cache Duration (TTL)</label>
				<div class="suffixed-input-field">
					<input id="asana-cache-ttl" name="asana_cache_ttl" type="number" min="0" step="1" value="<?php echo esc_attr( Options::get( Options::CACHE_TTL_SECONDS ) ); ?>" <?php echo esc_attr( ( current_user_can( 'manage_options' ) ) ? 'required' : 'readonly' ); ?> />
					<span>Seconds</span>
				</div>
				<?php if ( current_user_can( 'manage_options' ) ) : ?>
				<input type="hidden" name="asana_cache_ttl_save_nonce" value="<?php echo esc_attr( wp_create_nonce( 'asana_cache_ttl_save' ) ); ?>">
				<input type="submit" name="asana_cache_ttl_save" value="Save">
				<?php endif; ?>
			</div>
		</form>
		<form id="ptc-asana-data-cache-purge" method="POST">
			<div class="field-group">
				<input type="hidden" name="purge_asana_cache_nonce" value="<?php echo esc_attr( wp_create_nonce( 'purge_asana_cache' ) ); ?>">
				<div class="note-box">
					<p class="cache-purge-notice">
						<input class="warning" type="submit" name="purge_asana_cache" value="Clear Cache">
						This will clear all cached Asana data such as projects, tasks, and media attachments. You can use this to ensure the latest information is fetched from Asana.
					</p>
				</div>
			</div>
		</form>
	</section>
	<?php endif; ?>

	<section id="ptc-disconnect-asana">
		<form method="POST">
			<div class="field-group">
				<input type="hidden" name="asana_disconnect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'disconnect_asana' ) ); ?>">
				<div class="note-box note-box-error">
					<p class="disconnect-notice">
						<input class="error" type="submit" name="asana_disconnect" value="Disconnect">
						This will remove your encrypted Personal Access Token and Asana user id from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features.
					</p>
				</div>
			</div>
		</form>
	</section>
	<?php
} catch ( Errors\NoAuthorization $e ) {
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

/* HELPERS */

/**
 * Outputs collaborator row for the passed user.
 *
 * @since 1.0.0
 *
 * @param \WP_User $user The collaborator to display.
 */
function display_collaborator_row( \WP_User $user ) {

	$gravatar = get_avatar( $user->ID, 30, 'mystery' );
	$name = $user->display_name;
	$roles_csv = implode( ',', $user->roles );
	$email = $user->user_email;
	$has_connected_asana = Asana_Interface::has_connected_asana( $user->ID );
	?>
	<div class="ptc-asana-collaborator-row" data-user-id="<?php echo esc_attr( $user->ID ); ?>">

		<div class="user">
			<?php echo $gravatar; ?>
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
