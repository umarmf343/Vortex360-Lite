<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Capabilities {

    const CAP_EDIT_TOUR   = 'vxlite_edit_tour';
    const CAP_EDIT_TOURS  = 'vxlite_edit_tours';
    const CAP_DELETE_TOUR = 'vxlite_delete_tour';
    const CAP_READ_TOUR   = 'vxlite_read_tour';

    public static function install_caps() {
        // MapLite: admins & editors can manage; authors can edit own.
        $roles = [
            'administrator' => [
                self::CAP_EDIT_TOUR,
                self::CAP_EDIT_TOURS,
                self::CAP_DELETE_TOUR,
                self::CAP_READ_TOUR,
                'edit_posts',
                'edit_others_posts',
                'publish_posts',
                'delete_posts',
                'delete_others_posts'
            ],
            'editor' => [
                self::CAP_EDIT_TOUR,
                self::CAP_EDIT_TOURS,
                self::CAP_DELETE_TOUR,
                self::CAP_READ_TOUR,
                'edit_posts',
                'edit_others_posts',
                'publish_posts',
                'delete_posts',
                'delete_others_posts'
            ],
            'author' => [
                self::CAP_EDIT_TOUR,
                self::CAP_READ_TOUR,
                'edit_posts',
                'publish_posts',
                'delete_posts'
            ],
        ];
        foreach ( $roles as $role_key => $caps ) {
            $role = get_role( $role_key );
            if ( ! $role ) continue;
            foreach ( $caps as $cap ) {
                $role->add_cap( $cap );
            }
        }
    }

    public static function remove_caps() {
        $roles = [ 'administrator', 'editor', 'author' ];
        $caps = [
            self::CAP_EDIT_TOUR, self::CAP_EDIT_TOURS, self::CAP_DELETE_TOUR, self::CAP_READ_TOUR
        ];
        foreach ( $roles as $role_key ) {
            $role = get_role( $role_key );
            if ( ! $role ) continue;
            foreach ( $caps as $cap ) {
                $role->remove_cap( $cap );
            }
        }
    }
}
