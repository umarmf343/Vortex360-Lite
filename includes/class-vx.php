<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX {
    private static $instance = null;
    public  $loader;

    public static function instance() {
        if ( null === self::$instance ) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        $this->loader = new VX_Loader();

        // Subsystems
        $i18n       = new VX_I18n();
        $cpt        = new VX_CPT();
        $shortcode  = new VX_Shortcode();

        // i18n
        $this->loader->add_action( 'init', $i18n, 'load_textdomain' );

        // CPT
        $this->loader->add_action( 'init', $cpt, 'register_cpt' );

        // Capabilities (roles)
        if ( class_exists( 'VX_Capabilities' ) ) {
            $caps = new VX_Capabilities();
            $this->loader->add_action( 'init', $caps, 'add_caps' );
            $this->loader->add_action( 'switch_theme', $caps, 'add_caps' ); // safety
        }

        // Settings page (Lite)
        if ( is_admin() && class_exists( 'VX_Settings' ) ) {
            $settings = new VX_Settings();
            $this->loader->add_action( 'admin_init', $settings, 'register_settings' );
            $this->loader->add_action( 'admin_menu', $settings, 'register_menu' );
        }

        // Upgrade notices (gentle)
        if ( is_admin() && class_exists( 'VX_Upgrade_Notices' ) ) {
            $up = new VX_Upgrade_Notices();
            $this->loader->add_action( 'admin_notices', $up, 'maybe_show' );
        }

        // Shortcode
        $this->loader->add_action( 'init', $shortcode, 'register' );

        // Public assets
        if ( class_exists( 'VX_Public' ) ) {
            $public = new VX_Public();
            $this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_assets' );
        }

        // REST
        if ( class_exists( 'VX_REST' ) ) {
            $rest = new VX_REST();
            $this->loader->add_action( 'rest_api_init', $rest, 'register_routes' );
        }

        if ( is_admin() ) {
            if ( class_exists( 'VX_Admin' ) ) {
                $admin = new VX_Admin();
                $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'register_assets' );
                // Add "Views" column for analytics
                $this->loader->add_filter( 'manage_'.VXLITE_CPT.'_posts_columns', $admin, 'columns' );
                $this->loader->add_action( 'manage_'.VXLITE_CPT.'_posts_custom_column', $admin, 'column_content', 10, 2 );
            }
            if ( class_exists( 'VX_Admin_Metabox' ) ) {
                $this->loader->add_action( 'add_meta_boxes', [ 'VX_Admin_Metabox', 'register' ] );
                $this->loader->add_action( 'save_post_' . VXLITE_CPT, [ 'VX_Admin_Metabox', 'save' ] );
            }
            if ( class_exists( 'VX_Admin_Pages' ) ) {
                $pages = new VX_Admin_Pages();
                $this->loader->add_action( 'admin_menu', $pages, 'register_menu' );
                $this->loader->add_action( 'admin_post_vxlite_export', $pages, 'handle_export' );
                $this->loader->add_action( 'admin_post_vxlite_import', $pages, 'handle_import' );
            }
            if ( class_exists( 'VX_Admin_Ajax' ) ) {
                $ajax = new VX_Admin_Ajax();
                $this->loader->add_action( 'admin_action_vxlite_duplicate', $ajax, 'duplicate_tour' );
            }
        }

        // Gutenberg
        if ( function_exists( 'register_block_type' ) && class_exists( 'VX_Block' ) ) {
            $block = new VX_Block();
            $this->loader->add_action( 'init', $block, 'register' );
        }

        // Elementor
        if ( did_action('elementor/loaded') && class_exists( 'VX_Elementor' ) ) {
            $el = new VX_Elementor();
            $this->loader->add_action( 'elementor/widgets/register', $el, 'register_widgets' );
        }

        // Analytics (AJAX ping)
        if ( class_exists( 'VX_Analytics' ) ) {
            $an = new VX_Analytics();
            $this->loader->add_action( 'wp_ajax_vxlite_ping', $an, 'ping' );
            $this->loader->add_action( 'wp_ajax_nopriv_vxlite_ping', $an, 'ping' );
        }
    }

    public function run() {
        $this->loader->run();
    }
}
