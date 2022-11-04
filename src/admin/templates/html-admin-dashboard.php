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

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
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
				<p>This WordPress user's Asana connection will be used to render shortcodes on your website.</p>
			</div>
			<div class="note-box note-box-warning">
				<i class="fas fa-lightbulb"></i>
				<p>The user should have access to all tasks and projects in Asana that you wish to display on your website, so it's best to set this to someone such as your project manager. <a href="https://docs.purpleturtlecreative.com/completionist/getting-started/#set-a-frontend-authentication-user" target="_blank">Learn more.</a></p>
			</div>
		</div>
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

	/* User is not authenticated for API usage. */
	?>
	<section id="ptc-asana-connect">

		<img src="<?php echo esc_url( PLUGIN_URL . '/assets/images/asana_logo-vertical-color.png' ); ?>" alt="Asana Logo" title="Asana">
		<h2>Connect</h2>
		<p>To use Completionist, you must first connect your Asana account.</p>

		<form method="POST">
			<div class="field-group">
				<label for="asana-pat">Personal Access Token:</label>
				<div class="icon-input-field">
					<i class="fas fa-key"></i>
					<input id="asana-pat" name="asana_pat" type="password" autofocus>
				</div>
				<p class="help-link"><a href="https://app.asana.com/0/developer-console" target="_asana">Visit your Asana developer console.</a></p>
			</div>
			<div class="field-group">
				<p id="connection-agreement-text">
					I understand that I am granting this application, Completionist by Purple Turtle Creative, access to my Asana account so that it can perform actions on my behalf.
				</p>
				<input id="connection-agreement" name="connection_agreement" type="checkbox" value="yes" required>
				<label for="connection-agreement">I agree.</label>
			</div>
			<div class="field-group">
				<input type="hidden" name="asana_connect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'connect_asana' ) ); ?>">
				<input type="submit" name="asana_connect" value="Authorize">
			</div>
		</form>

	</section><!--close section#ptc-asana-connect-->

	<section id="ptc-asana-connect-info">
		<div class="note-box note-box-info">
			<i class="fas fa-question"></i>
			<p><a href="https://app.asana.com/0/developer-console" target="_asana">Visit your Asana developer console</a> to <b>create a Personal Access Token.</b> Be sure to name it something memorable like <em>My <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?> WordPress Site</em> in case you want to revoke it later.</p>
		</div>
		<div class="note-box">
			<i class="fas fa-lock"></i>
			<p class="security">Personal Access Tokens authenticate access to your Asana account just like a username and password, so <b>we encrypt it when saving.</b></p>
		</div>
		<div class="note-box">
			<i class="fas fa-mask"></i>
			<p class="privacy"><b>Your personal data will not be stored.</b> Completionist only acts on your behalf via Asana's API when you are logged into this site.</p>
		</div>
		<p class="footnote">**Completionist by Purple Turtle Creative is not associated with Asana. Asana is a trademark and service mark of Asana, Inc., registered in the U.S. and in other countries.</p>
	</section><!--close section#ptc-asana-connect-info-->
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
