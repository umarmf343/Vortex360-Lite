<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_CPT {

    /**
     * Register the Vortex Tour custom post type.
     * - Shows a top-level "Vortex360" menu with "Add New".
     * - Uses normal post capabilities (Admins/Editors see it by default).
     * - Hidden on the public site (no archives/single pages), since we render via shortcode/block.
     */
    public function register_cpt() {
        $labels = [
            'name'                  => __( 'Vortex Tours', 'vortex360-lite' ),
            'singular_name'         => __( 'Vortex Tour', 'vortex360-lite' ),
            'menu_name'             => __( 'Vortex360', 'vortex360-lite' ),
            'name_admin_bar'        => __( 'Vortex Tour', 'vortex360-lite' ),
            'add_new'               => __( 'Add New', 'vortex360-lite' ),
            'add_new_item'          => __( 'Add New Tour', 'vortex360-lite' ),
            'new_item'              => __( 'New Tour', 'vortex360-lite' ),
            'edit_item'             => __( 'Edit Tour', 'vortex360-lite' ),
            'view_item'             => __( 'View Tour', 'vortex360-lite' ),
            'all_items'             => __( 'All Tours', 'vortex360-lite' ),
            'search_items'          => __( 'Search Tours', 'vortex360-lite' ),
            'not_found'             => __( 'No tours found', 'vortex360-lite' ),
            'not_found_in_trash'    => __( 'No tours found in Trash', 'vortex360-lite' ),
        ];

        $args = [
            'labels'                => $labels,

            // Admin UI
            'show_ui'               => true,
            'show_in_menu'          => true,      // creates top-level "Vortex360"
            'menu_position'         => 26,        // ~under Comments
            'menu_icon'             => 'dashicons-location-alt',

            // Editing support
            'supports'              => [ 'title', 'thumbnail' ],

            // Capabilities (use post caps so Admin/Editor have access)
            'capability_type'       => 'post',
            'map_meta_cap'          => true,

            // Hide from public site; we render via shortcode/block instead
            'public'                => false,
            'publicly_queryable'    => false,
            'exclude_from_search'   => true,
            'has_archive'           => false,
            'rewrite'               => false,

            // REST off for Lite (we provide a small custom REST in includes/class-vx-rest.php)
            'show_in_rest'          => false,
        ];

        register_post_type( VXLITE_CPT, $args );

        // Safety net: ensure the admin bar "New" menu shows our CPT for quick access
        add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
            if ( ! current_user_can( 'edit_posts' ) ) return;
            $wp_admin_bar->add_node( [
                'id'    => 'new-' . VXLITE_CPT,
                'title' => __( 'Vortex Tour', 'vortex360-lite' ),
                'parent'=> 'new-content',
                'href'  => admin_url( 'post-new.php?post_type=' . VXLITE_CPT ),
            ] );
        }, 80 );
    }
}
