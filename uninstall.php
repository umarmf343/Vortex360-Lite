<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Remove all tour posts + meta
$posts = get_posts([
    'post_type'      => 'vortex_tour',
    'post_status'    => 'any',
    'numberposts'    => -1,
    'fields'         => 'ids',
    'suppress_filters' => true,
]);
foreach ( $posts as $pid ) {
    wp_delete_post( $pid, true );
}
