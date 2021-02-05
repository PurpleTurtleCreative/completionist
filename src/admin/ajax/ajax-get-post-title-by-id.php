<?php
/**
 * Get WordPress post ID and title options by searching for like titles.
 *
 * @since 1.1.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

require_once __DIR__ . '/../class-html-builder.php';
require_once __DIR__ . '/../class-options.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['post_id'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_automations' ) !== FALSE
  ) {

    $post_id = (int) Options::sanitize( 'gid', $_POST['post_id'] );

    $the_post = get_post( $post_id, OBJECT, 'display' );

    if ( is_object( $the_post ) && is_a( $the_post, '\WP_Post' ) ) {
      if ( isset( $the_post->post_title ) ) {
        $res['status'] = 'success';
        $res['code'] = 200;
        $res['message'] = "Found post {$post_id}.";
        $res['data'] = $the_post->post_title;
      } else {
        throw new \Exception( "Post {$post_id} title not set.", 404 );
      }
    } else {
      throw new \Exception( "Post {$post_id} not found.", 404 );
    }

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = HTML_Builder::get_error_code( $e );
  $res['message'] = HTML_Builder::get_error_message( $e );
  $res['data'] = '';
}

echo json_encode( $res );
wp_die();
