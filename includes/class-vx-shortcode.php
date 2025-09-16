<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode handler: [vortex360 id="123" width="100%" height="520px" autorotate="true" thumbnails="true" compass="true" controls="true" fullscreen="true"]
 * Renders a container and localizes data for the front-end viewer (Pannellum glue in public/js/viewer.js).
 */
class VX_Shortcode {

    /**
     * Register the shortcode.
     */
    public function register() {
        add_shortcode( 'vortex360', [ $this, 'render' ] );
    }

    /**
     * Render the tour container.
     *
     * @param array $atts
     * @return string
     */
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

        // Validate tour ID
        $post_id = absint( $atts['id'] );
        if ( ! $post_id || get_post_type( $post_id ) !== VXLITE_CPT ) {
            return vxlite_safe_html(
                '<div class="vxlite-error">' . esc_html__( 'Tour not found.', 'vortex360-lite' ) . '</div>'
            );
        }

        // Get tour data
        $data = get_post_meta( $post_id, VXLITE_META, true );
        if ( empty( $data ) || ! is_array( $data ) || empty( $data['scenes'] ) ) {
            return vxlite_safe_html(
                '<div class="vxlite-error">' . esc_html__( 'Tour has no scenes yet.', 'vortex360-lite' ) . '</div>'
            );
        }

        // Enqueue assets (our CSS, Pannellum, and glue JS)
        wp_enqueue_style( 'vxlite-public' );
        wp_enqueue_style( 'pannellum-css' );
        wp_enqueue_script( 'pannellum' );
        wp_enqueue_script( 'vxlite-viewer' );

        // Unique container ID
        $container = 'vxlite-' . $post_id . '-' . wp_generate_uuid4();

        // Build payload
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
            // Optional analytics ping endpoint (handled by VX_Analytics if present)
            'ajax'        => [
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'vxlite-ping' ),
                'pid'   => $post_id,
                'action'=> 'vxlite_ping',
            ],
        ];

        // Localize per-instance config into the viewer glue script
        wp_localize_script( 'vxlite-viewer', $container, $payload );

        // Inline style for container dimensions
        $style = sprintf( 'style="width:%s;height:%s;"',
            esc_attr( $atts['width'] ),
            esc_attr( $atts['height'] )
        );

        // Output wrapper + container + thumbnail row (the JS will populate)
        $html  = '<div class="vxlite-wrapper" data-vxlite-instance="' . esc_attr( $container ) . '">';
        $html .= '<div id="' . esc_attr( $container ) . '" class="vxlite-viewer" ' . $style . '></div>';
        $html .= '<div class="vxlite-thumbs" id="' . esc_attr( $container ) . '-thumbs"></div>';
        $html .= '</div>';

        return vxlite_safe_html( $html );
    }
}
