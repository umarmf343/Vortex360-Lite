<?php
/**
 * Plugin Name: Vortex360 Lite
 * Description: Create simple 360° virtual tours. Lite = 1 tour, 5 scenes/tour, 5 hotspots/scene.
 * Version: 1.0.0
 * Author: AlFawz Qur’an Institute
 * License: GPLv2 or later
 * Text Domain: vortex360-lite
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VXLITE_VERSION', '1.0.0' );
define( 'VXLITE_FILE', __FILE__ );
define( 'VXLITE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VXLITE_URL', plugin_dir_url( __FILE__ ) );
define( 'VXLITE_SLUG', 'vortex360-lite' );
define( 'VXLITE_CPT',  'vortex_tour' );
define( 'VXLITE_META', '_vxlite_tour_data' ); // JSON blob: scenes + hotspots

// PSR-4-ish minimal autoloader for plugin classes.
spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'VX_' ) !== 0 ) return;
    $map = [
        'VX_Loader'          => 'includes/class-vx-loader.php',
        'VX'                 => 'includes/class-vx.php',
        'VX_I18n'            => 'includes/class-vx-i18n.php',
        'VX_CPT'             => 'includes/class-vx-cpt.php',
        'VX_Shortcode'       => 'includes/class-vx-shortcode.php',
        'VX_Public'          => 'public/class-vx-public.php',
        'VX_Render'          => 'public/classes/class-vx-render.php',
        'VX_Admin'           => 'admin/class-vx-admin.php',
        'VX_Admin_Metabox'   => 'admin/classes/class-vx-metabox-tour.php',
    ];
    if ( isset( $map[ $class ] ) ) {
        require_once VXLITE_DIR . $map[ $class ];
    }
});

require_once VXLITE_DIR . 'includes/helpers/vx-utils.php';

function vxlite_activate() {
    // Reserve rewrite for CPT.
    ( new VX_CPT() )->register_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vxlite_activate' );

function vxlite_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vxlite_deactivate' );

function vxlite_init() {
    $vx = VX::instance();
    $vx->run();
}
add_action( 'plugins_loaded', 'vxlite_init' );
