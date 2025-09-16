<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin_Ajax {

    public function __construct() {
        add_filter( 'post_row_actions', [ $this, 'row_actions' ], 10, 2 );
    }

    public function row_actions( $actions, $post ) {
        if ( $post->post_type !== VXLITE_CPT ) return $actions;
        if ( ! current_user_can( 'edit_posts' ) ) return $actions;

        $url = wp_nonce_url(
            admin_url( 'admin.php?action=vxlite_duplicate&post=' . $post->ID ),
            'vxlite_duplicate_' . $post->ID
        );
        $actions['vxlite_duplicate'] = '<a href="'. esc_url( $url ) .'">'. esc_html__( 'Duplicate', 'vortex360-lite' ) .'</a>';
        return $actions;
    }

    public function duplicate_tour() {
        if ( ! current_user_can( 'edit_posts' ) ) wp_die();
        $post_id = absint( $_GET['post'] ?? 0 );
        check_admin_referer( 'vxlite_duplicate_' . $post_id );

        $src = get_post( $post_id );
        if ( ! $src || $src->post_type !== VXLITE_CPT ) wp_die( esc_html__( 'Invalid source', 'vortex360-lite' ) );

        $new_id = wp_insert_post( [
            'post_type'   => VXLITE_CPT,
            'post_title'  => $src->post_title . ' (Copy)',
            'post_status' => 'draft'
        ], true );
        if ( is_wp_error( $new_id ) ) wp_die( esc_html__( 'Duplication failed', 'vortex360-lite' ) );

        $data = get_post_meta( $post_id, VXLITE_META, true );
        if ( is_array( $data ) ) update_post_meta( $new_id, VXLITE_META, $data );

        wp_safe_redirect( admin_url( 'post.php?post=' . $new_id . '&action=edit' ) );
        exit;
    }
}
