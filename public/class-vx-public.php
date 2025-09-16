<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Public {
    public function register_assets() {
        // Our UI
        wp_register_style(
            'vxlite-public',
            VXLITE_URL . 'public/css/vortex360.css',
            [],
            VXLITE_VERSION
        );

        // Pannellum (CDN)
        wp_register_style(
            'pannellum-css',
            'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.css',
            [],
            '2.5.6'
        );
        wp_register_script(
            'pannellum',
            'https://cdn.jsdelivr.net/npm/pannellum@2.5.6/build/pannellum.js',
            [],
            '2.5.6',
            true
        );

        // Our viewer glue
        wp_register_script(
            'vxlite-viewer',
            VXLITE_URL . 'public/js/viewer.js',
            [ 'jquery', 'pannellum' ],
            VXLITE_VERSION,
            true
        );

        // Global AJAX vars for analytics ping
        wp_localize_script( 'vxlite-viewer', 'vxliteVars', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'vxlite_ajax' ),
        ] );
    }
}
