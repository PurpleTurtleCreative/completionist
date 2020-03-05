<?php
/**
 * Build tag select option HTML for a workspace.
 *
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PTC_Completionist;

defined( 'ABSPATH' ) || die();

global $ptc_completionist;
require_once $ptc_completionist->plugin_path . 'src/class-asana-interface.php';
require_once $ptc_completionist->plugin_path . 'src/class-options.php';
require_once $ptc_completionist->plugin_path . 'src/class-html-builder.php';

$res['status'] = 'error';
$res['code'] = 400;
$res['message'] = 'Invalid submission';
$res['data'] = '';

try {
  if (
    isset( $_POST['workspace_gid'] )
    && isset( $_POST['nonce'] )
    && wp_verify_nonce( $_POST['nonce'], 'ptc_completionist_dashboard' ) !== FALSE//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    && Asana_Interface::has_connected_asana()
  ) {

    $workspace_gid = Options::sanitize( 'gid', $_POST['workspace_gid'] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput
    if ( '' === $workspace_gid ) {
      throw new \Exception( 'The provided workspace identifier is invalid.', 400 );
    }

    $asana = Asana_Interface::get_client();
    $workspace_tags = $asana->tags->findByWorkspace( $workspace_gid, [ 'opt_fields' => 'name' ] );

    $html = '';

    $saved_tag_gid = Options::get( Options::ASANA_TAG_GID );
    $found_saved_tag = FALSE;
    foreach ( $workspace_tags as $workspace_tag ) {
      $selected = '';
      if ( $saved_tag_gid === $workspace_tag->gid ) {
        $selected = ' selected="selected"';
        $found_saved_tag = TRUE;
      }
      $html .=  '<option value="' . esc_attr( $workspace_tag->gid ) . '"' . $selected . '>' .
                  esc_html( $workspace_tag->name ) .
                '</option>';
    }

    if ( ! $found_saved_tag && $saved_tag_gid !== '' ) {
      try {
        $saved_tag = $asana->tags->findById( $saved_tag_gid, [ 'opt_fields' => 'name,workspace' ] );
        if (
          isset( $saved_tag->workspace->gid )
          && $saved_tag->workspace->gid === $workspace_gid
          && isset( $saved_tag->gid )
          && isset( $saved_tag->name )
        ) {
          $saved_tag_option_html = '<option value="' . esc_attr( $saved_tag->gid ) . '" selected="selected">' .
                                      esc_html( $saved_tag->name ) .
                                    '</option>';
          $html = $saved_tag_option_html . $html;
        }
      } catch ( \Exception $e ) {
        error_log( HTML_Builder::format_error_string( $e, 'Failed to list saved tag option.' ) );
      }
    }

    $res['status'] = 'success';
    $res['code'] = 200;
    $res['message'] = 'Successfully built tag options for workspace.';
    $res['data'] = $html;

  }//end validate form submission
} catch ( \Exception $e ) {
  $res['status'] = 'error';
  $res['code'] = $e->getCode();
  $res['message'] = $e->getMessage();
  $res['data'] = 'Error ' . $e->getCode() . ': ' . $e->getMessage();
}

echo json_encode( $res );
wp_die();
