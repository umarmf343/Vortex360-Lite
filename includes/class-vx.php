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
        $i18n      = new VX_I18n();
        $cpt       = new VX_CPT();
        $shortcode = new VX_Shortcode();

        // Load i18n
        $this->loader->add_action( 'init', $i18n, 'load_textdomain' );

        // Register CPT
        $this->loader->add_action( 'init', $cpt, 'register_cpt' );

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

        // Admin
        if ( is_admin() ) {
            if ( class_exists( 'VX_Admin' ) ) {
                $admin = new VX_Admin();
                $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'register_assets' );
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
                $this->loader->add_action( 'admin_action_vxlite_duplicate', $ajax, 'duplicate_tour' ); // row action link
            }
        }

        // Block (Gutenberg)
        if ( function_exists( 'register_block_type' ) && class_exists( 'VX_Block' ) ) {
            $block = new VX_Block();
            $this->loader->add_action( 'init', $block, 'register' );
        }

        // Elementor
        if ( did_action('elementor/loaded') && class_exists( 'VX_Elementor' ) ) {
            $el = new VX_Elementor();
            $this->loader->add_action( 'elementor/widgets/register', $el, 'register_widgets' );
        }
    }

    public function run() {
        $this->loader->run();
    }
}
