<?php
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class VX_Widget_Tour extends Widget_Base {

    public function get_name() { return 'vxlite_tour'; }
    public function get_title() { return __( 'Vortex360 Tour (Lite)', 'vortex360-lite' ); }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return [ 'general' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'section_content', [
            'label' => __( 'Content', 'vortex360-lite' )
        ] );

        $this->add_control( 'post_id', [
            'label' => __( 'Tour ID', 'vortex360-lite' ),
            'type'  => Controls_Manager::NUMBER,
            'min'   => 1,
            'default' => 0,
        ] );

        $this->add_control( 'width', [
            'label' => __( 'Width', 'vortex360-lite' ),
            'type'  => Controls_Manager::TEXT,
            'default' => '100%'
        ] );

        $this->add_control( 'height', [
            'label' => __( 'Height', 'vortex360-lite' ),
            'type'  => Controls_Manager::TEXT,
            'default' => '520px'
        ] );

        $this->add_control( 'thumbnails', [
            'label' => __( 'Thumbnails', 'vortex360-lite' ),
            'type'  => Controls_Manager::SWITCHER,
            'default' => 'yes'
        ] );

        $this->add_control( 'autorotate', [
            'label' => __( 'Autorotate', 'vortex360-lite' ),
            'type'  => Controls_Manager::SWITCHER,
            'default' => 'yes'
        ] );

        $this->add_control( 'compass', [
            'label' => __( 'Compass', 'vortex360-lite' ),
            'type'  => Controls_Manager::SWITCHER,
            'default' => 'yes'
        ] );

        $this->add_control( 'controls', [
            'label' => __( 'Controls', 'vortex360-lite' ),
            'type'  => Controls_Manager::SWITCHER,
            'default' => 'yes'
        ] );

        $this->add_control( 'fullscreen', [
            'label' => __( 'Fullscreen', 'vortex360-lite' ),
            'type'  => Controls_Manager::SWITCHER,
            'default' => 'yes'
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $id = absint( $settings['post_id'] );
        if ( ! $id ) {
            echo '<div class="vxlite-error">'. esc_html__( 'Select a Tour ID.', 'vortex360-lite' ) .'</div>';
            return;
        }

        echo do_shortcode( sprintf(
            '[vortex360 id="%d" width="%s" height="%s" autorotate="%s" fullscreen="%s" compass="%s" thumbnails="%s" controls="%s"]',
            $id,
            esc_attr( $settings['width'] ?: '100%' ),
            esc_attr( $settings['height'] ?: '520px' ),
            ( ! empty( $settings['autorotate'] ) && $settings['autorotate'] === 'yes' ) ? 'true' : 'false',
            ( ! empty( $settings['fullscreen'] ) && $settings['fullscreen'] === 'yes' ) ? 'true' : 'false',
            ( ! empty( $settings['compass'] ) && $settings['compass'] === 'yes' ) ? 'true' : 'false',
            ( ! empty( $settings['thumbnails'] ) && $settings['thumbnails'] === 'yes' ) ? 'true' : 'false',
            ( ! empty( $settings['controls'] ) && $settings['controls'] === 'yes' ) ? 'true' : 'false'
        ) );
    }
}
