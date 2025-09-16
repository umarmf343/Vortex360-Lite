<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin {
    public function enqueue( $hook ) {
        $screen = get_current_screen();
        if ( isset( $screen->post_type ) && $screen->post_type === VXLITE_CPT ) {
            wp_enqueue_style( 'vxlite-admin', VXLITE_URL . 'admin/css/admin.css', [], VXLITE_VERSION );
            wp_enqueue_script( 'vxlite-admin', VXLITE_URL . 'admin/js/admin.js', [ 'jquery' ], VXLITE_VERSION, true );
        }
    }
}
