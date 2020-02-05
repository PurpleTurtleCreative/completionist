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

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';

echo '<h1>Completionist &ndash; Settings</h1>';
require_once $ptc_completionist->plugin_path . 'src/script-save-settings.php';

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

    <form class="ptc-asana-disconnect" method="POST">
      <div class="field-group">
        <input type="hidden" name="asana_disconnect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'disconnect_asana' ) ); ?>">
        <input type="submit" name="asana_disconnect" value="Deauthorize">
        <p class="disconnect-notice"><i class="fas fa-ban"></i>This will remove your Personal Access Token from this site, thus deauthorizing access to your Asana account. Until connecting your Asana account again, you will not have access to use Completionist's features or be recognized on tasks.</p>
      </div>
    </form>
  </section><!--close section#ptc-asana-user-->

  <section id="ptc-asana-workspace">
    <?php
    try {

      $can_manage_options = current_user_can( 'manage_options' );
      $chosen_workspace_gid = Options::get( Options::ASANA_WORKSPACE_GID );
      $is_workspace_member = Asana_Interface::is_workspace_member( $chosen_workspace_gid );

      $disabled = ( ! $can_manage_options ) ? ' disabled="disabled"' : '';

      if ( $can_manage_options || $is_workspace_member ) {
        /* User is able to view workspace details */
        ?>
        <form method="POST">
          <div class="field-group">
            <label for="asana-workspace"><i class="fas fa-building"></i>Workspace:</label>
            <select id="asana-workspace" name="asana_workspace" <?php echo $disabled; ?>>
              <?php
              if ( $chosen_workspace_gid === '' ) {
                echo  '<option value="' . esc_attr( $chosen_workspace_gid ) . '" selected="selected">' .
                        'Choose a Workspace...' .
                      '</option>';
              } elseif ( ! $is_workspace_member ) {
                echo  '<option value="' . esc_attr( $chosen_workspace_gid ) . '" selected="selected">' .
                        '(Unauthorized)' .
                      '</option>';
              }
              foreach ( $me->workspaces as $workspace ) {
                $selected = ( $chosen_workspace_gid === $workspace->gid ) ? ' selected="selected"' : '';
                echo  '<option value="' . esc_attr( $workspace->gid ) . '"' . $selected . '>' .
                        esc_html( $workspace->name ) .
                      '</option>';
              }
              ?>
            </select>
          </div>
          <div class="field-group">
            <input type="hidden" name="asana_workspace_save_nonce" value="<?php echo esc_attr( wp_create_nonce( 'asana_workspace_save' ) ); ?>">
            <input type="submit" name="asana_workspace_save" value="Save">
          </div>
        </form>

        <div id="ptc-asana-workspace-users">
        <?php
        if ( $chosen_workspace_gid === '' ) {
          if ( $can_manage_options ) {
            ?>
            <p class="nothing-to-see">A workspace has not been assigned to this site. Please choose an Asana workspace from the dropdown above to start collaborating on site tasks.</p>
            <?php
          } else {
            ?>
            <p class="nothing-to-see">A workspace has not been assigned to this site. Please ask an <a href="<?php echo esc_url( admin_url( 'users.php?role=administrator' ) ); ?>" target="_blank">administrator</a> to set an Asana workspace to begin collaborating on site tasks.</p>
            <?php
          }
        } elseif ( ! $is_workspace_member ) {
          ?>
          <p class="nothing-to-see">You are unauthorized to collaborate on this site's tasks. Please ask an <a href="<?php echo esc_url( admin_url( 'users.php?role=administrator' ) ); ?>" target="_blank">administrator</a> to invite you to this site's workspace in Asana.</p>
          <?php
        } else {

          $workspace_users = Asana_Interface::find_workspace_users();

          if ( empty( $workspace_users ) ) {
            ?>
            <p class="nothing-to-see">No collaborators were found by email.</p>
            <?php
          } else {
            foreach ( $workspace_users as $user ) {
              display_collaborator_row( $user );
            }
          }//end if empty collaborators
        }//end list recognized collaborators
      } else {
        /* User is unable to view workspace details */
        ?>
        <p id="ptc-asana-workspace-unauthorized"><i class="fas fa-ban"></i><strong>Unauthorized.</strong> To view workspace details, you must be a member of the designated Asana workspace or have administrative capabilities to manage options.</p>
        <?php
      }//end if can view workspace details

    } catch ( \Exception $e ) {
      /* An Asana API client exception occurred */
      ?>
      <p id="ptc-asana-workspace-error"><i class="fas fa-ban"></i><strong>Error.</strong> Unable to load workspace details.</p>
      <pre class="error-output">
        <p class="error-code"><?php echo esc_html( $e->getCode() ); ?></p>
        <p class="error-message"><?php echo esc_html( $e->getMessage() ); ?></p>
      </pre>
      <?php
    }
    ?>
  </section><!--close section#ptc-asana-workspace-->
  <?php

} catch ( \Exception $e ) {

  /* User is not authenticated for API usage. */
  // TODO: Create custom Exceptions to know if an authentication error occurred or if this was some other client error such as a rate limit or server issue.
  ?>
  <section id="ptc-asana-connect">

    <img src="<?php echo esc_url( $ptc_completionist->plugin_url . '/assets/images/asana_logo-horizontal-color.png' ); ?>" alt="Asana Logo" title="Asana">
    <h2>Connect Asana</h2>
    <p>To use Completionist, you must first connect your Asana account.</p>

    <form method="POST">
      <div class="field-group">
        <label for="asana-pat"><i class="fas fa-key"></i>Personal Access Token:</label>
        <input id="asana-pat" name="asana_pat" type="password">
        <p class="asana-pat-info"><i class="fas fa-question"></i>You may generate an access token from <a href="https://app.asana.com/0/developer-console" target="_blank">your Asana developer console</a>. Be sure to name it something identifiable like <em>My <?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?> WordPress Site</em>.</p>
      </div>
      <div class="field-group">
        <input type="hidden" name="asana_connect_nonce" value="<?php echo esc_attr( wp_create_nonce( 'connect_asana' ) ); ?>">
        <input type="submit" name="asana_connect" value="Authorize">
      </div>
    </form>

    <div class="additional-info">
      <p class="security"><i class="fas fa-lock"></i>Personal Access Tokens authenticate access to your Asana account just like a username and password, so we encrypt it when saving.</p>
      <p class="privacy"><i class="fas fa-mask"></i>Your personal data will not be stored. Completionist only works on your behalf via Asana's API when you are logged into this site.</p>
    </div>

  </section><!--close section#ptc-asana-connect-->

  <footer>
    <p>You are viewing this page because...</p>
    <pre class="error-output">
      <p class="error-code"><?php echo esc_html( $e->getCode() ); ?></p>
      <p class="error-message"><?php echo esc_html( $e->getMessage() ); ?></p>
    </pre>
  </footer>
  <?php

}//end try catch asana client

/* HELPERS */

function display_collaborator_row( \WP_User $user ) : void {

  $gravatar = get_avatar( $user->ID, 30, 'mystery' );
  $name = $user->display_name;
  $roles_csv = implode( ',', $user->roles );
  $email = $user->user_email;
  $has_connected_asana = Asana_Interface::has_connected_asana( $user->ID );
  $asana_user_link = Asana_Interface::get_task_list_external_link( $user->ID );
  ?>
  <div class="ptc-asana-collaborator-row" data-user-id="<?php echo esc_attr( $user->ID ); ?>">

    <div class="identity">
      <?php echo $gravatar; ?>
      <p><?php echo esc_html( $name ); ?></p>
    </div>

    <div class="roles">
      <p><?php echo esc_html( $roles_csv ); ?></p>
    </div>

    <div class="email">
      <p><a href="<?php echo esc_attr( "mailto:$email" ); ?>"><?php echo esc_html( $email ); ?></a></p>
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

    <div class="view-in-asana">
      <p>
        <?php
        if ( ! empty( $asana_user_link ) ) {
          echo  '<a href="' . esc_url( $asana_user_link ) . '" target="_blank">' .
                  'View in Asana<i class="fas fa-external-link-alt"></i>' .
                '</a>';
        } else {
          echo 'Unable to View';
        }
        ?>
      </p>
    </div>

  </div>
  <?php
}
