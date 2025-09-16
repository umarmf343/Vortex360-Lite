<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_CPT {
    public function register_cpt() {
        $labels = [
            'name'               => __( 'Vortex Tours', 'vortex360-lite' ),
            'singular_name'      => __( 'Vortex Tour', 'vortex360-lite' ),
            'add_new'            => __( 'Add New', 'vortex360-lite' ),
            'add_new_item'       => __( 'Add New Tour', 'vortex360-lite' ),
            'edit_item'          => __( 'Edit Tour', 'vortex360-lite' ),
            'new_item'           => __( 'New Tour', 'vortex360-lite' ),
            'view_item'          => __( 'View Tour', 'vortex360-lite' ),
            'search_items'       => __( 'Search Tours', 'vortex360-lite' ),
            'not_found'          => __( 'No tours found', 'vortex360-lite' ),
            'not_found_in_trash' => __( 'No tours found in Trash', 'vortex360-lite' ),
            'menu_name'          => __( 'Vortex360', 'vortex360-lite' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-location-alt',
            'supports'           => [ 'title', 'thumbnail' ],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ];
        register_post_type( VXLITE_CPT, $args );
    }
}
