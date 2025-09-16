<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core orchestrator for Vortex360 Lite
 * - Wires all subsystems using VX_Loader
 * - Keeps optional modules guarded with class_exists / function checks
 * - Avoids fatals when Elementor / Gutenberg / optional modules are not present
 */
class VX {
    private static $instance = null;
    /** @var VX_Loader */
    public  $loader;

    /**
     * Singleton
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Wire everything through the loader
     */
    private function __construct() {
        $this->loader = new VX_Loader();

        // ---------------------------------------------------------------------
        // Core subsystems
        // ---------------------------------------------------------------------
        $i18n      = new VX_I18n();
        $cpt       = new VX_CPT();
        $shortcode = new VX_Shortcode();

        // i18n
        $this->loader->add_action( 'init', $i18n, 'load_textdomain' );

        // CPT
        $this->loader->add_action( 'init', $cpt, 'register_cpt' );

        // Capabilities (roles) — optional enhancement
        if ( class_exists( 'VX_Capabilities' ) ) {
            $caps = new VX_Capabilities();
            $this->loader->add_action( 'init', $caps, 'add_caps' );
            // Safety: if theme switches, re-ensure caps
            $this->loader->add_action( 'switch_theme', $caps, 'add_caps' );
        }

        // Settings page (Lite) — optional enhancement
        if ( is_admin() && class_exists( 'VX_Settings' ) ) {
            $settings = new VX_Settings();
            $this->loader->add_action( 'admin_init',  $settings, 'register_settings' );
            $this->loader->add_action( 'admin_menu',  $settings, 'register_menu' );
        }

        // Gentle upgrade notices — optional enhancement
        if ( is_admin() && class_exists( 'VX_Upgrade_Notices' ) ) {
            $up = new VX_Upgrade_Notices();
            $this->loader->add_action( 'admin_notices', $up, 'maybe_show' );
        }

        // Shortcode
        $this->loader->add_action( 'init', $shortcode, 'register' );

        // ---------------------------------------------------------------------
        // Frontend
        // ---------------------------------------------------------------------
        if ( class_exists( 'VX_Public' ) ) {
            $public = new VX_Public();
            // Register (and allow on-demand enqueue inside shortcode)
            $this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_assets' );
        }

        // REST API (fetch tour JSON)
        if ( class_exists( 'VX_REST' ) ) {
            $rest = new VX_REST();
            $this->loader->add_action( 'rest_api_init', $rest, 'register_routes' );
        }

        // Frontend analytics ping (optional)
        if ( class_exists( 'VX_Analytics' ) ) {
            $an = new VX_Analytics();
            $this->loader->add_action( 'wp_ajax_vxlite_ping',        $an, 'ping' );
            $this->loader->add_action( 'wp_ajax_nopriv_vxlite_ping', $an, 'ping' );
        }

        // ---------------------------------------------------------------------
        // Admin area
        // ---------------------------------------------------------------------
        if ( is_admin() ) {
            if ( class_exists( 'VX_Admin' ) ) {
                $admin = new VX_Admin();
                $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'register_assets' );

                // Optional: add "Views" column (analytics) if VX_Analytics exists
                if ( class_exists( 'VX_Analytics' ) ) {
                    $this->loader->add_filter( 'manage_' . VXLITE_CPT . '_posts_columns', $admin, 'columns' );
                    $this->loader->add_action( 'manage_' . VXLITE_CPT . '_posts_custom_column', $admin, 'column_content', 10, 2 );
                }
            }

            if ( class_exists( 'VX_Admin_Metabox' ) ) {
                $this->loader->add_action( 'add_meta_boxes', function() {
                    // Ensure meta box only appears on our CPT
                    add_meta_box(
                        'vxlite_tour_builder',
                        __( 'Vortex360 Tour (Lite)', 'vortex360-lite' ),
                        [ 'VX_Admin_Metabox', 'render' ],
                        VXLITE_CPT,
                        'normal',
                        'high'
                    );
                } );
                $this->loader->add_action( 'save_post_' . VXLITE_CPT, [ 'VX_Admin_Metabox', 'save' ] );
            }

            // Tools page: Import/Export handlers
            if ( class_exists( 'VX_Admin_Pages' ) ) {
                $pages = new VX_Admin_Pages();
                $this->loader->add_action( 'admin_menu',                $pages, 'register_menu' );
                $this->loader->add_action( 'admin_post_vxlite_export',  $pages, 'handle_export' );
                $this->loader->add_action( 'admin_post_vxlite_import',  $pages, 'handle_import' );
            }

            // Duplicate tour action in row actions
            if ( class_exists( 'VX_Admin_Ajax' ) ) {
                $ajax = new VX_Admin_Ajax();
                $this->loader->add_action( 'admin_action_vxlite_duplicate', $ajax, 'duplicate_tour' );
            }
        }

        // ---------------------------------------------------------------------
        // Builders
        // ---------------------------------------------------------------------

        // Gutenberg block (optional — only if available)
        if ( function_exists( 'register_block_type' ) && class_exists( 'VX_Block' ) ) {
            $block = new VX_Block();
            $this->loader->add_action( 'init', $block, 'register' );
        }

        // Elementor widget (optional — only if Elementor is loaded)
        if ( did_action( 'elementor/loaded' ) && class_exists( 'VX_Elementor' ) ) {
            $el = new VX_Elementor();
            $this->loader->add_action( 'elementor/widgets/register', $el, 'register_widgets' );
        }
    }

    /**
     * Execute all registered hooks
     */
    public function run() {
        $this->loader->run();
    }
}
