<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function vxlite_safe_html( $html ) {
    // Very permissive for our needs; tighten if you add rich HTML.
    $allowed = [
        'div' => [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'span'=> [ 'id'=>[], 'class'=>[], 'style'=>[] ],
        'p'   => [ 'class'=>[], 'style'=>[] ],
    ];
    return wp_kses( $html, $allowed );
}
