<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Public {
    public function enqueue() {
        wp_register_style(
            'vxlite-public',
            VXLITE_URL . 'public/css/vortex360.css',
            [],
            VXLITE_VERSION
        );
        wp_register_script(
            'vxlite-viewer',
            VXLITE_URL . 'public/js/viewer.js',
            [ 'jquery' ],
            VXLITE_VERSION,
            true
        );
    }
}
