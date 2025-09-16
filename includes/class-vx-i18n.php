<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_I18n {
    public function load_textdomain() {
        load_plugin_textdomain(
            'vortex360-lite',
            false,
            dirname( plugin_basename( VXLITE_FILE ) ) . '/languages'
        );
    }
}
