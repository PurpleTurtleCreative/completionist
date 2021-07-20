<?php
/**
 * Processes settings form submissions and displays process notices.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once PLUGIN_PATH . 'src/includes/class-options.php';
require_once PLUGIN_PATH . 'src/includes/class-asana-interface.php';

if (
	isset( $_POST['asana_connect'] )
	&& isset( $_POST['asana_pat'] )
	&& isset( $_POST['connection_agreement'] )
	&& isset( $_POST['asana_connect_nonce'] )
	&& wp_verify_nonce( $_POST['asana_connect_nonce'], 'connect_asana' ) !== false
) {

	if ( ! filter_var( wp_unslash( $_POST['connection_agreement'] ), FILTER_VALIDATE_BOOLEAN ) ) {
		echo '<p class="notice notice-error">To use Completionist, you must accept the agreement.</p>';
		return;
	}

	try {
		$did_save_pat = Options::save( Options::ASANA_PAT, $_POST['asana_pat'] );
	} catch ( \Exception $e ) {
		echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
		$did_save_pat = false;
	}

	if ( $did_save_pat === true ) {

		try {
			$asana = Asana_Interface::get_client();
			$me = Asana_Interface::get_me();
			$did_save_gid = Options::save( Options::ASANA_USER_GID, $me->gid );
			$did_delete_pat = false;
			$did_delete_gid = false;
		} catch ( \Exception $e ) {
			echo '<p class="notice notice-error">' . esc_html( $e->getMessage() ) . '</p>';
			$did_save_gid = false;
			$did_delete_pat = Options::delete( Options::ASANA_PAT );
			$did_delete_gid = Options::delete( Options::ASANA_USER_GID );
		}

		if ( $did_delete_pat === true ) {
			echo '<p class="notice notice-error">An error occurred, causing your Personal Access Token to not be saved.</p>';
		} elseif ( $did_save_gid === true ) {
			echo '<p class="notice notice-success">Your Asana account was successfully connected!</p>';
		}
	}//end if did_save_pat
}//end if asana_connect

if (
	isset( $_POST['asana_disconnect'] )
	&& isset( $_POST['asana_disconnect_nonce'] )
	&& wp_verify_nonce( $_POST['asana_disconnect_nonce'], 'disconnect_asana' ) !== false
) {

	$did_delete_pat = Options::delete( Options::ASANA_PAT );
	$did_delete_gid = Options::delete( Options::ASANA_USER_GID );

	if (
		$did_delete_pat === true
		&& $did_delete_gid === true
	) {
		echo '<p class="notice notice-success">Your Asana account was successfully forgotten!</p>';
	} elseif ( $did_delete_pat === true ) {
		echo '<p class="notice notice-success">Your Asana account was successfully disconnected.</p>';
	} else {
		echo '<p class="notice notice-error">Your Asana account could not be disconnected.</p>';
	}
}//end if asana_disconnect

try {
	if (
		isset( $_POST['asana_workspace_save'] )
		&& isset( $_POST['asana_workspace'] )
		&& isset( $_POST['asana_tag'] )
		&& isset( $_POST['asana_workspace_save_nonce'] )
		&& wp_verify_nonce( $_POST['asana_workspace_save_nonce'], 'asana_workspace_save' ) !== false//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
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

				$asana = Asana_Interface::get_client();
				$params = [
					'name' => $tag_name,
					'workspace' => Options::get( Options::ASANA_WORKSPACE_GID ),
				];
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
				$asana = Asana_Interface::get_client();
				$the_tag = $asana->tags->findById( $tag_gid );
				$tag_gid = $the_tag->gid;
				if (
					isset( $the_tag->workspace->gid )
					&& $the_tag->workspace->gid !== Options::get( Options::ASANA_WORKSPACE_GID )
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
