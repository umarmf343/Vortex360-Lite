<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Block {

    public function register() {
        // Register block.json
        register_block_type( VXLITE_DIR . 'blocks/tour', [
            'render_callback' => [ $this, 'render' ]
        ] );

        // Register assets
        wp_register_script(
            'vxlite-block-editor',
            VXLITE_URL . 'blocks/tour/edit.js',
            [ 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-block-editor' ],
            VXLITE_VERSION,
            true
        );
        wp_register_style(
            'vxlite-block-style',
            VXLITE_URL . 'blocks/tour/style.css',
            [],
            VXLITE_VERSION
        );
    }

    public function render( $attributes, $content ) {
        $post_id = isset( $attributes['postId'] ) ? absint( $attributes['postId'] ) : 0;
        if ( ! $post_id ) return '';
        $atts = [
            'id'         => $post_id,
            'width'      => isset( $attributes['width'] ) ? $attributes['width'] : '100%',
            'height'     => isset( $attributes['height'] ) ? $attributes['height'] : '520px',
            'autorotate' => ! empty( $attributes['autorotate'] ) ? 'true' : 'false',
            'fullscreen' => ! empty( $attributes['fullscreen'] ) ? 'true' : 'false',
            'compass'    => ! empty( $attributes['compass'] ) ? 'true' : 'false',
            'thumbnails' => ! empty( $attributes['thumbnails'] ) ? 'true' : 'false',
            'controls'   => ! empty( $attributes['controls'] ) ? 'true' : 'false',
        ];
        // Reuse shortcode renderer for consistency
        return do_shortcode( sprintf(
            '[vortex360 id="%d" width="%s" height="%s" autorotate="%s" fullscreen="%s" compass="%s" thumbnails="%s" controls="%s"]',
            $atts['id'], esc_attr($atts['width']), esc_attr($atts['height']),
            $atts['autorotate'], $atts['fullscreen'], $atts['compass'], $atts['thumbnails'], $atts['controls']
        ) );
    }
}
