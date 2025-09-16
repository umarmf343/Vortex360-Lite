<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Output safe HTML for small container fragments.
 */
function vxlite_safe_html( $html ) {
    $allowed = [
        'div'  => [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'span' => [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'p'    => [ 'class'=>[], 'style'=>[] ],
        'button'=> [ 'type'=>[], 'class'=>[], 'aria-label'=>[] ],
        'svg'  => [ 'class'=>[], 'viewBox'=>[], 'width'=>[], 'height'=>[] ],
        'path' => [ 'd'=>[], 'fill'=>[], 'stroke'=>[], 'stroke-width'=>[] ],
    ];
    return wp_kses( $html, $allowed );
}

/**
 * Sanitize a scenes/hotspots data array (Lite limits enforced in Phase 2 save handler).
 */
function vxlite_sanitize_tour_array( $data ) {
    if ( ! is_array( $data ) ) return [ 'scenes' => [] ];
    $out = [ 'scenes' => [] ];
    if ( empty( $data['scenes'] ) || ! is_array( $data['scenes'] ) ) return $out;
    foreach ( array_slice( $data['scenes'], 0, 5 ) as $scene ) {
        $s = [
            'id'       => sanitize_key( $scene['id'] ?? uniqid( 'scene-' ) ),
            'title'    => sanitize_text_field( $scene['title'] ?? '' ),
            'panorama' => esc_url_raw( $scene['panorama'] ?? '' ),
            'hotspots' => [],
        ];
        if ( ! empty( $scene['hotspots'] ) && is_array( $scene['hotspots'] ) ) {
            foreach ( array_slice( $scene['hotspots'], 0, 5 ) as $h ) {
                $type = in_array( $h['type'] ?? 'text', [ 'text', 'image', 'link' ], true ) ? $h['type'] : 'text';
                $s['hotspots'][] = [
                    'type'  => $type,
                    'title' => sanitize_text_field( $h['title'] ?? '' ),
                    'text'  => sanitize_textarea_field( $h['text'] ?? '' ),
                    'image' => esc_url_raw( $h['image'] ?? '' ),
                    'url'   => esc_url_raw( $h['url'] ?? '' ),
                    'yaw'   => floatval( $h['yaw'] ?? 0 ),
                    'pitch' => floatval( $h['pitch'] ?? 0 ),
                    'scene' => sanitize_key( $h['scene'] ?? '' ),
                ];
            }
        }
        $out['scenes'][] = $s;
    }
    return $out;
}
