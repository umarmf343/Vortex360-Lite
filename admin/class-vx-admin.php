<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin {

    /**
     * Register + enqueue admin assets on Vortex Tour screens.
     */
    public function register_assets( $hook ) {
        $screen = get_current_screen();
        if ( isset( $screen->post_type ) && $screen->post_type === VXLITE_CPT ) {
            wp_register_style( 'vxlite-admin', VXLITE_URL . 'admin/css/admin.css', [], VXLITE_VERSION );
            wp_register_script( 'vxlite-admin', VXLITE_URL . 'admin/js/admin.js', [ 'jquery' ], VXLITE_VERSION, true );

            wp_enqueue_style( 'vxlite-admin' );
            wp_enqueue_script( 'vxlite-admin' );
        }
    }

    /**
     * Add a "Views" column (if analytics is enabled) and a "Shortcode" column for convenience.
     * Wired from VX only if VX_Analytics exists for views.
     */
    public function columns( $cols ) {
        // Insert Shortcode after Title
        $new = [];
        foreach ( $cols as $k => $v ) {
            $new[ $k ] = $v;
            if ( 'title' === $k ) {
                $new['vxlite_shortcode'] = __( 'Shortcode', 'vortex360-lite' );
            }
        }
        // Add Views if analytics present
        if ( class_exists( 'VX_Analytics' ) ) {
            $new['vxlite_views'] = __( 'Views', 'vortex360-lite' );
        }
        return $new;
    }

    /**
     * Render custom column content.
     */
    public function column_content( $column, $post_id ) {
        if ( 'vxlite_shortcode' === $column ) {
            $sc = sprintf( '[vortex360 id="%d"]', $post_id );
            echo '<code style="user-select:all;">' . esc_html( $sc ) . '</code>';
            return;
        }

        if ( 'vxlite_views' === $column && class_exists( 'VX_Analytics' ) ) {
            $views = (int) get_post_meta( $post_id, '_vxlite_views', true );
            echo esc_html( $views );
            return;
        }
    }
}
