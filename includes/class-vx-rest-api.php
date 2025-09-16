<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_REST {

    public function register_routes() {
        register_rest_route( 'vxlite/v1', '/tour/(?P<id>\d+)', [
            'methods'  => 'GET',
            'callback' => [ $this, 'get_tour' ],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function( $v ){ return is_numeric( $v ); }
                ]
            ]
        ] );
    }

    public function get_tour( WP_REST_Request $req ) {
        $id = absint( $req['id'] );
        if ( ! $id || get_post_type( $id ) !== VXLITE_CPT ) {
            return new WP_REST_Response( [ 'error' => 'Not found' ], 404 );
        }
        $data = get_post_meta( $id, VXLITE_META, true );
        if ( ! is_array( $data ) ) $data = [ 'scenes' => [] ];
        return new WP_REST_Response( [
            'id'   => $id,
            'data' => $data
        ], 200 );
    }
}
