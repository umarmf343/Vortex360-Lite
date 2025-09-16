<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Analytics {

    const META_VIEWS = '_vxlite_views';

    public function __construct() {
        add_action( 'wp_ajax_vxlite_ping',    [ $this, 'ping' ] );
        add_action( 'wp_ajax_nopriv_vxlite_ping', [ $this, 'ping' ] );
    }

    public function ping() {
        $id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
        if ( ! $id || get_post_type( $id ) !== VXLITE_CPT ) {
            wp_send_json_error( [ 'message' => 'Invalid' ], 400 );
        }
        $views = (int) get_post_meta( $id, self::META_VIEWS, true );
        $views++;
        update_post_meta( $id, self::META_VIEWS, $views );
        wp_send_json_success( [ 'views' => $views ] );
    }

    public static function get_views( $post_id ) {
        return (int) get_post_meta( $post_id, self::META_VIEWS, true );
    }
}
