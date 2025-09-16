<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin_Pages {

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=' . VXLITE_CPT,
            __( 'Tools (Import/Export)', 'vortex360-lite' ),
            __( 'Tools', 'vortex360-lite' ),
            'manage_options',
            'vxlite-tools',
            [ $this, 'render_tools' ]
        );
    }

    public function render_tools() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Vortex360 Lite — Tools', 'vortex360-lite' ); ?></h1>
            <h2><?php esc_html_e( 'Export', 'vortex360-lite' ); ?></h2>
            <p><?php esc_html_e( 'Export a single tour as JSON.', 'vortex360-lite' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'vxlite_export' ); ?>
                <input type="hidden" name="action" value="vxlite_export" />
                <label><?php esc_html_e('Tour ID', 'vortex360-lite'); ?>:
                    <input type="number" name="tour_id" min="1" required />
                </label>
                <button class="button button-primary" type="submit"><?php esc_html_e('Export JSON','vortex360-lite'); ?></button>
            </form>

            <hr/>

            <h2><?php esc_html_e( 'Import', 'vortex360-lite' ); ?></h2>
            <p><?php esc_html_e( 'Import a tour JSON into a new tour post. Lite allows only 1 tour overall.', 'vortex360-lite' ); ?></p>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'vxlite_import' ); ?>
                <input type="hidden" name="action" value="vxlite_import" />
                <p><label><?php esc_html_e('New Tour Title', 'vortex360-lite'); ?>:
                    <input type="text" name="title" required />
                </label></p>
                <p><label><?php esc_html_e('Tour JSON', 'vortex360-lite'); ?>:</label><br/>
                    <textarea name="json" rows="12" cols="80" style="font-family:monospace" required></textarea>
                </p>
                <button class="button button-primary" type="submit"><?php esc_html_e('Import JSON','vortex360-lite'); ?></button>
            </form>
        </div>
        <?php
    }

    public function handle_export() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        check_admin_referer( 'vxlite_export' );
        $id = isset( $_POST['tour_id'] ) ? absint( $_POST['tour_id'] ) : 0;
        if ( ! $id || get_post_type( $id ) !== VXLITE_CPT ) {
            wp_die( esc_html__( 'Invalid tour ID', 'vortex360-lite' ) );
        }
        $data = get_post_meta( $id, VXLITE_META, true );
        if ( ! is_array( $data ) ) $data = [ 'scenes' => [] ];
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="vortex360-tour-'.$id.'.json"' );
        echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
        exit;
    }

    public function handle_import() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die();
        check_admin_referer( 'vxlite_import' );

        $title = sanitize_text_field( $_POST['title'] ?? '' );
        $raw   = wp_unslash( $_POST['json'] ?? '' );
        $arr   = json_decode( $raw, true );
        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $arr ) ) {
            wp_die( esc_html__( 'Invalid JSON', 'vortex360-lite' ) );
        }

        $clean = vxlite_sanitize_tour_array( $arr );

        // Create post
        $post_id = wp_insert_post( [
            'post_type'   => VXLITE_CPT,
            'post_title'  => $title ? $title : __( 'Imported Tour', 'vortex360-lite' ),
            'post_status' => 'publish'
        ], true );

        if ( is_wp_error( $post_id ) ) {
            wp_die( esc_html__( 'Failed to create tour', 'vortex360-lite' ) );
        }

        update_post_meta( $post_id, VXLITE_META, $clean );

        wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
        exit;
    }
}
