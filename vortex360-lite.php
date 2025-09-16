<?php
/**
 * Plugin Name: Vortex360 Lite
 * Description: 360° virtual tour builder (Lite): 1 tour, 5 scenes per tour, 5 hotspots per scene, shortcode + block + Elementor (basic).
 * Version: 1.0.0
 * Author: AlFawz Qur’an Institute
 * License: GPLv2 or later
 * Text Domain: vortex360-lite
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// -----------------------------------------------------------------------------
// Constants
// -----------------------------------------------------------------------------
define( 'VXLITE_VERSION', '1.0.0' );
define( 'VXLITE_FILE', __FILE__ );
define( 'VXLITE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VXLITE_URL', plugin_dir_url( __FILE__ ) );
define( 'VXLITE_SLUG', 'vortex360-lite' );
define( 'VXLITE_CPT',  'vortex_tour' );
define( 'VXLITE_META', '_vxlite_tour_data' ); // JSON blob: scenes + hotspots

// -----------------------------------------------------------------------------
// Autoloader (classes prefixed with VX_)
// -----------------------------------------------------------------------------
spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'VX_' ) !== 0 ) {
        return;
    }

    $map = [
        // Core
        'VX_Loader'           => 'includes/class-vx-loader.php',
        'VX'                  => 'includes/class-vx.php',
        'VX_I18n'             => 'includes/class-vx-i18n.php',
        'VX_CPT'              => 'includes/class-vx-cpt.php',
        'VX_Shortcode'        => 'includes/class-vx-shortcode.php',

        // Public/Admin
        'VX_Public'           => 'public/class-vx-public.php',
        'VX_Render'           => 'public/classes/class-vx-render.php',
        'VX_Admin'            => 'admin/class-vx-admin.php',
        'VX_Admin_Metabox'    => 'admin/classes/class-vx-metabox-tour.php',

        // Phase 3 modules
        'VX_REST'             => 'includes/class-vx-rest.php',
        'VX_Admin_Pages'      => 'admin/classes/class-vx-admin-pages.php',
        'VX_Admin_Ajax'       => 'admin/classes/class-vx-ajax.php',
        'VX_Block'            => 'includes/class-vx-block.php',
        'VX_Elementor'        => 'elementor/elementor.php',

        // Phase 4 (optional enhancements)
        'VX_Capabilities'     => 'includes/class-vx-capabilities.php',
        'VX_Settings'         => 'admin/classes/class-vx-settings.php',
        'VX_Analytics'        => 'includes/class-vx-analytics.php',
        'VX_Upgrade_Notices'  => 'upgrade/class-vx-upgrade-notices.php',
    ];

    $rel = isset( $map[ $class ] ) ? $map[ $class ] : '';
    if ( $rel && file_exists( VXLITE_DIR . $rel ) ) {
        require_once VXLITE_DIR . $rel;
    }
});

// -----------------------------------------------------------------------------
// Always-available helpers
// -----------------------------------------------------------------------------
require_once VXLITE_DIR . 'includes/helpers/vx-utils.php';

// -----------------------------------------------------------------------------
// Activation / Deactivation (single, non-duplicated definitions)
// -----------------------------------------------------------------------------
function vxlite_activate() {
    // Ensure CPT exists for rewrite rules
    if ( class_exists( 'VX_CPT' ) ) {
        ( new VX_CPT() )->register_cpt();
    }
    // Optional: install custom capabilities if class exists
    if ( class_exists( 'VX_Capabilities' ) ) {
        VX_Capabilities::install_caps();
    }
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vxlite_activate' );

function vxlite_deactivate() {
    // Optional: remove custom capabilities if class exists
    if ( class_exists( 'VX_Capabilities' ) ) {
        VX_Capabilities::remove_caps();
    }
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'vxlite_deactivate' );

// -----------------------------------------------------------------------------
// Bootstrap
// -----------------------------------------------------------------------------
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'VX' ) ) {
        // Fail-safe notice if a core class failed to load (bad upload, missing files, etc.)
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p><strong>Vortex360 Lite:</strong> Core class missing. Please re-upload the plugin.</p></div>';
        } );
        return;
    }

    // Kick off the plugin
    VX::instance()->run();
} );
