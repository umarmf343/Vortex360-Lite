<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Render {
    // Kept for forward-compatibility; complex config assembly could live here.
    public static function normalize_tour( $data ) {
        return is_array( $data ) ? $data : [];
    }
}
