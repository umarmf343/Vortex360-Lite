<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Settings {

    const OPTION = 'vxlite_settings';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
    }

    public static function get() {
        $defaults = [
            'default_autorotate' => 1,
            'default_compass'    => 1,
            'default_controls'   => 1,
            'default_fullscreen' => 1,
            'default_thumbnails' => 1,
            'brand_logo'         => '',
        ];
        $opt = get_option( self::OPTION, [] );
        return wp_parse_args( $opt, $defaults );
    }

    public function register_settings() {
        register_setting( 'vxlite_settings_group', self::OPTION, [ $this, 'sanitize' ] );

        add_settings_section(
            'vxlite_defaults',
            __( 'Default Viewer Options', 'vortex360-lite' ),
            function(){ echo '<p>'. esc_html__('These apply when shortcode/block options are not specified explicitly.', 'vortex360-lite') .'</p>'; },
            'vxlite_settings'
        );

        $fields = [
            'default_autorotate' => __( 'Autorotate by default', 'vortex360-lite' ),
            'default_compass'    => __( 'Show compass by default', 'vortex360-lite' ),
            'default_controls'   => __( 'Show controls by default', 'vortex360-lite' ),
            'default_fullscreen' => __( 'Enable fullscreen button by default', 'vortex360-lite' ),
            'default_thumbnails' => __( 'Show thumbnails row by default', 'vortex360-lite' ),
        ];
        foreach ( $fields as $key => $label ) {
            add_settings_field(
                $key,
                esc_html( $label ),
                [ $this, 'render_checkbox' ],
                'vxlite_settings',
                'vxlite_defaults',
                [ 'key' => $key ]
            );
        }

        add_settings_section(
            'vxlite_brand',
            __( 'Branding', 'vortex360-lite' ),
            function(){ echo '<p>'. esc_html__('Basic branding settings for Lite.', 'vortex360-lite') .'</p>'; },
            'vxlite_settings'
        );

        add_settings_field(
            'brand_logo',
            __( 'Brand Logo URL', 'vortex360-lite' ),
            [ $this, 'render_text' ],
            'vxlite_settings',
            'vxlite_brand',
            [ 'key' => 'brand_logo', 'placeholder' => 'https://...' ]
        );
    }

    public function sanitize( $input ) {
        $out = [];
        $out['default_autorotate'] = empty( $input['default_autorotate'] ) ? 0 : 1;
        $out['default_compass']    = empty( $input['default_compass'] ) ? 0 : 1;
        $out['default_controls']   = empty( $input['default_controls'] ) ? 0 : 1;
        $out['default_fullscreen'] = empty( $input['default_fullscreen'] ) ? 0 : 1;
        $out['default_thumbnails'] = empty( $input['default_thumbnails'] ) ? 0 : 1;
        $out['brand_logo']         = esc_url_raw( $input['brand_logo'] ?? '' );
        return $out;
    }

    public function render_checkbox( $args ) {
        $opt = self::get();
        $key = $args['key'];
        printf(
            '<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s> %4$s</label>',
            esc_attr( self::OPTION ),
            esc_attr( $key ),
            checked( 1, ! empty( $opt[ $key ] ), false ),
            ''
        );
    }

    public function render_text( $args ) {
        $opt = self::get();
        $key = $args['key'];
        $ph  = $args['placeholder'] ?? '';
        printf(
            '<input type="url" name="%1$s[%2$s]" value="%3$s" class="regular-text" placeholder="%4$s" />',
            esc_attr( self::OPTION ),
            esc_attr( $key ),
            esc_attr( $opt[ $key ] ),
            esc_attr( $ph )
        );
    }

    public function register_menu() {
        add_submenu_page(
            'edit.php?post_type=' . VXLITE_CPT,
            __( 'Settings', 'vortex360-lite' ),
            __( 'Settings', 'vortex360-lite' ),
            'manage_options',
            'vxlite-settings',
            [ $this, 'render_page' ]
        );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Vortex360 Lite — Settings', 'vortex360-lite' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'vxlite_settings_group' );
                do_settings_sections( 'vxlite_settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
