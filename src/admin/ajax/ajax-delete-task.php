<?php
/**
 * Delete a task in Asana and unpin it.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
	if (
		isset( $_POST['task_gid'] )
		&& isset( $_POST['nonce'] )
		&& wp_verify_nonce( $_POST['nonce'], 'ptc_completionist' ) !== false//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		&& Asana_Interface::has_connected_asana()
	) {

		$task_gid = Options::sanitize( 'gid', $_POST['task_gid'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( '' === $task_gid ) {
			throw new \Exception( 'Invalid task gid.', 400 );
		}

		if ( isset( $_POST['post_id'] ) ) {
			$the_post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( $the_post_id < 1 ) {
				throw new \Exception( 'Invalid post identifier.', 400 );
			}
		} else {
			$the_post_id = Options::get_task_pin_post_id( $task_gid );
		}

		/* Delete the task */

		Asana_Interface::delete_task( $task_gid );

		/* Unpin from post */

		if ( isset( $the_post_id ) && $the_post_id > 0 ) {

			try {
				$did_unpin_task = Options::delete( Options::PINNED_TASK_GID, $the_post_id, $task_gid );
			} catch ( \Exception $e ) {
				$did_unpin_task = false;
			}

			if ( false === $did_unpin_task ) {
				error_log( "Failed to unpin the deleted task {$task_gid} from post {$the_post_id}." );
			}
		}

		$res['status'] = 'success';
		$res['code'] = 200;
		$res['message'] = "Successfully deleted task {$task_gid}.";
		$res['data'] = $task_gid;

	}//end validate form submission
} catch ( \Exception $e ) {
	$res['status'] = 'error';
	$res['code'] = $e->getCode();
	$res['message'] = $e->getMessage();
	$res['data'] = HTML_Builder::format_error_box( $e, 'Failed to delete task. ' );
}

echo json_encode( $res );
wp_die();
