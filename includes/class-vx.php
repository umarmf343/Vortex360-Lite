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

        // Load i18n early
        $this->loader->add_action( 'init', $i18n, 'load_textdomain' );

        // Register CPT
        $this->loader->add_action( 'init', $cpt, 'register_cpt' );

        // Shortcode
        $this->loader->add_action( 'init', $shortcode, 'register' );

        // Front assets (Phase 2 wires viewer + rendering)
        if ( class_exists( 'VX_Public' ) ) {
            $public = new VX_Public();
            $this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_assets' );
        }

        // Admin assets + metabox (Phase 2 adds full builder)
        if ( is_admin() && class_exists( 'VX_Admin' ) ) {
            $admin = new VX_Admin();
            $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'register_assets' );
        }
        if ( is_admin() && class_exists( 'VX_Admin_Metabox' ) ) {
            $this->loader->add_action( 'add_meta_boxes', [ 'VX_Admin_Metabox', 'register' ] );
            $this->loader->add_action( 'save_post_' . VXLITE_CPT, [ 'VX_Admin_Metabox', 'save' ] );
        }
    }

    public function run() {
        $this->loader->run();
    }
}
