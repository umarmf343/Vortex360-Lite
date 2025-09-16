<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Allowlist HTML for small UI fragments we render (containers, messages, simple tips).
 * Keep this tight; expand only when necessary.
 */
function vxlite_safe_html( $html ) {
    $allowed = [
        'div'    => [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'span'   => [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'p'      => [ 'class'=>[], 'style'=>[] ],
        'strong' => [],
        'em'     => [],
        'br'     => [],
        'button' => [ 'type'=>[], 'class'=>[], 'aria-label'=>[] ],
        'img'    => [ 'src'=>[], 'alt'=>[], 'width'=>[], 'height'=>[], 'class'=>[], 'style'=>[] ],
        'a'      => [ 'href'=>[], 'target'=>[], 'rel'=>[], 'class'=>[], 'title'=>[] ],
        'svg'    => [ 'class'=>[], 'viewBox'=>[], 'width'=>[], 'height'=>[] ],
        'path'   => [ 'd'=>[], 'fill'=>[], 'stroke'=>[], 'stroke-width'=>[] ],
        'code'   => [],
    ];
    return wp_kses( $html, $allowed );
}

/**
 * Cast-ish boolean from shortcode or settings input.
 */
function vxlite_is_true( $val ) {
    if ( is_bool( $val ) ) return $val;
    $val = is_string( $val ) ? strtolower( trim( $val ) ) : $val;
    return in_array( $val, [ true, 1, '1', 'true', 'yes', 'on' ], true );
}

/**
 * Numeric clamp helper.
 */
function vxlite_clamp( $num, $min, $max ) {
    $num = floatval( $num );
    return max( $min, min( $max, $num ) );
}

/**
 * Safe getter.
 */
function vxlite_get( $arr, $key, $default = null ) {
    return ( is_array( $arr ) && array_key_exists( $key, $arr ) ) ? $arr[ $key ] : $default;
}

/**
 * Sanitize a scenes/hotspots data array and ENFORCE Lite limits.
 *
 * Structure:
 * $data = [
 *   'scenes' => [
 *     [
 *       'id'       => 'scene-1',
 *       'title'    => 'Lobby',
 *       'panorama' => 'https://...',
 *       'thumb'    => 'https://...',        // optional thumbnail (used by thumbs row)
 *       'hfov'     => 110,                  // 30..120 typical
 *       'pitch'    => 0,                    // -90..90
 *       'yaw'      => 0,                    // -180..180
 *       'hotspots' => [
 *          [ 'type'=>'text','title'=>'..','text'=>'..','yaw'=>0,'pitch'=>0 ],
 *          [ 'type'=>'image','title'=>'..','image'=>'https://..','yaw'=>0,'pitch'=>0 ],
 *          [ 'type'=>'link','title'=>'..','scene'=>'scene-2','yaw'=>0,'pitch'=>0 ],
 *       ]
 *     ]
 *   ]
 * ];
 */
function vxlite_sanitize_tour_array( $data ) {
    $out = [ 'scenes' => [] ];
    if ( ! is_array( $data ) ) {
        return $out;
    }

    $scenes_in  = vxlite_get( $data, 'scenes', [] );
    if ( ! is_array( $scenes_in ) ) {
        return $out;
    }

    $ids_used = [];
    $scenes_in = array_slice( $scenes_in, 0, 5 ); // Lite: max 5 scenes total

    foreach ( $scenes_in as $i => $scene ) {
        if ( ! is_array( $scene ) ) continue;

        // Scene id (unique, slug-like)
        $raw_id = isset( $scene['id'] ) ? $scene['id'] : ( 'scene-' . ( $i + 1 ) );
        $sid    = sanitize_key( $raw_id );
        if ( ! $sid ) {
            $sid = 'scene-' . ( $i + 1 );
        }
        // Ensure uniqueness
        if ( isset( $ids_used[ $sid ] ) ) {
            $n = 2;
            while ( isset( $ids_used[ $sid . '-' . $n ] ) ) { $n++; }
            $sid = $sid . '-' . $n;
        }
        $ids_used[ $sid ] = true;

        // Scene fields
        $title    = sanitize_text_field( vxlite_get( $scene, 'title', '' ) );
        $panorama = esc_url_raw( vxlite_get( $scene, 'panorama', '' ) );
        $thumb    = esc_url_raw( vxlite_get( $scene, 'thumb', '' ) );
        $hfov     = vxlite_clamp( vxlite_get( $scene, 'hfov', 110 ), 30, 120 );
        $pitch    = vxlite_clamp( vxlite_get( $scene, 'pitch', 0 ), -90, 90 );
        $yaw      = vxlite_clamp( vxlite_get( $scene, 'yaw', 0 ), -180, 180 );

        $scene_out = [
            'id'       => $sid,
            'title'    => $title,
            'panorama' => $panorama,
            'thumb'    => $thumb,
            'hfov'     => floatval( $hfov ),
            'pitch'    => floatval( $pitch ),
            'yaw'      => floatval( $yaw ),
            'hotspots' => [],
        ];

        // Hotspots (Lite: max 5 per scene)
        $hots_in = vxlite_get( $scene, 'hotspots', [] );
        if ( is_array( $hots_in ) ) {
            $hots_in = array_slice( $hots_in, 0, 5 );
            foreach ( $hots_in as $h ) {
                if ( ! is_array( $h ) ) continue;

                $type = vxlite_get( $h, 'type', 'text' );
                $type = in_array( $type, [ 'text', 'image', 'link' ], true ) ? $type : 'text';

                $title_h = sanitize_text_field( vxlite_get( $h, 'title', '' ) );
                $text    = sanitize_textarea_field( vxlite_get( $h, 'text', '' ) );
                $image   = esc_url_raw( vxlite_get( $h, 'image', '' ) );
                $url     = esc_url_raw( vxlite_get( $h, 'url', '' ) );
                $toScene = sanitize_key( vxlite_get( $h, 'scene', '' ) );
                $yaw_h   = vxlite_clamp( vxlite_get( $h, 'yaw', 0 ), -180, 180 );
                $pit_h   = vxlite_clamp( vxlite_get( $h, 'pitch', 0 ), -90, 90 );

                $hs = [
                    'type'  => $type,
                    'title' => $title_h,
                    'text'  => $text,
                    'image' => $image,
                    'url'   => $url,
                    'yaw'   => floatval( $yaw_h ),
                    'pitch' => floatval( $pit_h ),
                ];

                if ( $type === 'link' ) {
                    $hs['scene'] = $toScene;
                }

                $scene_out['hotspots'][] = $hs;
            }
        }

        $out['scenes'][] = $scene_out;
    }

    return $out;
}
