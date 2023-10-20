<?php
/**
 * Get WordPress post ID and title options by searching for like titles.
 *
 * @todo Should be able to replace this functionality with WordPress
 * REST API's /search endpoint like http://localhost/wp-json/wp/v2/search?type=post&context=view&search=Sample+Page
 *
 * @since 1.1.0
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
    isset( $_POST['title'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_automations' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
  ) {

    $post_title = Options::sanitize( 'string', htmlentities( wp_unslash( $_POST['title'] ) ) );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput

    global $wpdb;
    $rows = $wpdb->get_results(
      $wpdb->prepare(
        "
        SELECT ID, post_title FROM {$wpdb->posts}
        WHERE post_type <> 'revision'
          AND post_title LIKE %s
        ",
        '%' . $post_title . '%'
      )
    );

    if ( ! $rows ) {
      throw new \Exception( 'No posts found', 404 );
    }

    foreach ( $rows as $i => $post_data ) {
      $rows[ $i ]->post_title = html_entity_decode( $post_data->post_title, ENT_HTML5 );
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = "Found {$wpdb->num_rows} post options.";
    $res['data'] = $rows;

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = HTML_Builder::get_error_code( $e );
  $res['message'] = HTML_Builder::get_error_message( $e );
  $res['data'] = '';
}

echo json_encode( $res );
wp_die();
