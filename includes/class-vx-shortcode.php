<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Shortcode {
    public function register() {
        add_shortcode( 'vortex360', [ $this, 'render' ] );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( [
            'id'        => 0,
            'width'     => '100%',
            'height'    => '500px',
            'autoload'  => 'true',
            'controls'  => 'true',
            'compass'   => 'true',
            'fullscreen'=> 'true',
        ], $atts, 'vortex360' );

        $post_id = absint( $atts['id'] );
        if ( ! $post_id || get_post_type( $post_id ) !== VXLITE_CPT ) {
            return vxlite_safe_html('<div class="vxlite-error">'. esc_html__( 'Tour not found.', 'vortex360-lite' ) .'</div>');
        }

        $data = get_post_meta( $post_id, VXLITE_META, true );
        if ( empty( $data ) || ! is_array( $data ) ) {
            return vxlite_safe_html('<div class="vxlite-error">'. esc_html__( 'Tour has no scenes yet.', 'vortex360-lite' ) .'</div>');
        }

        $container_id = 'vxlite-viewer-' . $post_id . '-' . wp_generate_uuid4();

        wp_enqueue_style( 'vxlite-public' );
        wp_enqueue_script( 'vxlite-viewer' );

        $payload = [
            'containerId' => $container_id,
            'tour'        => $data,
            'options'     => [
                'controls'   => filter_var( $atts['controls'], FILTER_VALIDATE_BOOLEAN ),
                'compass'    => filter_var( $atts['compass'], FILTER_VALIDATE_BOOLEAN ),
                'fullscreen' => filter_var( $atts['fullscreen'], FILTER_VALIDATE_BOOLEAN ),
                'autoload'   => filter_var( $atts['autoload'], FILTER_VALIDATE_BOOLEAN ),
            ],
        ];
        wp_localize_script( 'vxlite-viewer', $container_id, $payload );

        $style = sprintf( 'style="width:%s;height:%s;"', esc_attr( $atts['width'] ), esc_attr( $atts['height'] ) );
        return vxlite_safe_html( '<div class="vxlite-viewer" id="'. esc_attr( $container_id ) .'" '. $style .'></div>' );
    }
}
