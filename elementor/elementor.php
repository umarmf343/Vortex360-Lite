<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Elementor {
    public function register_widgets( $widgets_manager ) {
        require_once VXLITE_DIR . 'elementor/widgets/class-vx-widget-tour.php';
        $widgets_manager->register( new \VX_Widget_Tour() );
    }
}
