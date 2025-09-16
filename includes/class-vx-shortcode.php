<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Shortcode {
    public function register() {
        add_shortcode( 'vortex360', [ $this, 'render' ] );
    }

    public function render( $atts ) {
        $atts = shortcode_atts( [
            'id'         => 0,
            'width'      => '100%',
            'height'     => '520px',
            'autorotate' => 'true',
            'fullscreen' => 'true',
            'compass'    => 'true',
            'thumbnails' => 'true',
            'controls'   => 'true',
        ], $atts, 'vortex360' );

        $post_id = absint( $atts['id'] );
        if ( ! $post_id || get_post_type( $post_id ) !== VXLITE_CPT ) {
            return vxlite_safe_html('<div class="vxlite-error">'. esc_html__( 'Tour not found.', 'vortex360-lite' ) .'</div>');
        }

        $data = get_post_meta( $post_id, VXLITE_META, true );
        if ( empty( $data ) || ! is_array( $data ) || empty( $data['scenes'] ) ) {
            return vxlite_safe_html('<div class="vxlite-error">'. esc_html__( 'Tour has no scenes yet.', 'vortex360-lite' ) .'</div>');
        }

        // Register + enqueue public assets (Pannellum + our CSS/JS)
        wp_enqueue_style( 'vxlite-public' );
        wp_enqueue_script( 'pannellum' );
        wp_enqueue_style( 'pannellum-css' );
        wp_enqueue_script( 'vxlite-viewer' );

        $container = 'vxlite-' . $post_id . '-' . wp_generate_uuid4();
        $payload = [
            'containerId' => $container,
            'tour'        => $data,
            'options'     => [
                'autorotate' => filter_var( $atts['autorotate'], FILTER_VALIDATE_BOOLEAN ),
                'fullscreen' => filter_var( $atts['fullscreen'], FILTER_VALIDATE_BOOLEAN ),
                'compass'    => filter_var( $atts['compass'], FILTER_VALIDATE_BOOLEAN ),
                'thumbnails' => filter_var( $atts['thumbnails'], FILTER_VALIDATE_BOOLEAN ),
                'controls'   => filter_var( $atts['controls'], FILTER_VALIDATE_BOOLEAN ),
            ],
        ];
        wp_localize_script( 'vxlite-viewer', $container, $payload );

        $style = sprintf( 'style="width:%s;height:%s;"', esc_attr( $atts['width'] ), esc_attr( $atts['height'] ) );
        $html  = '<div class="vxlite-wrapper">';
        $html .= '<div id="'. esc_attr( $container ) .'" class="vxlite-viewer" '. $style .'></div>';
        $html .= '<div class="vxlite-thumbs" id="'. esc_attr( $container ) .'-thumbs"></div>';
        $html .= '</div>';

        return vxlite_safe_html( $html );
    }
}
