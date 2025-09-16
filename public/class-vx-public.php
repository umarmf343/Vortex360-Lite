<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Public {

    /**
     * Register public-facing styles & scripts.
     * Actual enqueue happens on demand from the shortcode/block render.
     */
    public function register_assets() {
        // Core UI for our wrapper / thumbs / tooltip
        wp_register_style(
            'vxlite-public',
            VXLITE_URL . 'public/css/vortex360.css',
            [],
            VXLITE_VERSION
        );

        // Pannellum (CDN) for 360° rendering
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

        // Our viewer glue (depends on jQuery + Pannellum)
        wp_register_script(
            'vxlite-viewer',
            VXLITE_URL . 'public/js/viewer.js',
            [ 'jquery', 'pannellum' ],
            VXLITE_VERSION,
            true
        );

        // Provide global AJAX vars (used as a fallback; per-instance cfg is localized in the shortcode)
        if ( ! wp_script_is( 'vxlite-viewer', 'registered' ) ) {
            // Safety (should be registered above, but guard in case of customization)
            wp_register_script(
                'vxlite-viewer',
                VXLITE_URL . 'public/js/viewer.js',
                [ 'jquery', 'pannellum' ],
                VXLITE_VERSION,
                true
            );
        }

        wp_localize_script( 'vxlite-viewer', 'vxliteVars', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'vxlite_ajax' ),
        ] );
    }
}
