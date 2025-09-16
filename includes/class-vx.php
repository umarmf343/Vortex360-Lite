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

        $i18n       = new VX_I18n();
        $cpt        = new VX_CPT();
        $shortcode  = new VX_Shortcode();
        $public     = new VX_Public();
        $admin      = is_admin() ? new VX_Admin() : null;

        // I18n
        $this->loader->add_action( 'init', $i18n, 'load_textdomain' );

        // Core
        $this->loader->add_action( 'init', $cpt, 'register_cpt' );

        // Admin
        if ( $admin ) {
            $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue' );
            $this->loader->add_action( 'add_meta_boxes', [ 'VX_Admin_Metabox', 'register' ] );
            $this->loader->add_action( 'save_post_' . VXLITE_CPT, [ 'VX_Admin_Metabox', 'save' ] );
        }

        // Front
        $this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue' );

        // Shortcode
        $this->loader->add_action( 'init', $shortcode, 'register' );
    }

    public function run() {
        $this->loader->run();
    }
}
