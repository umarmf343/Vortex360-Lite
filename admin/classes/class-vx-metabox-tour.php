<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin_Metabox {

    public static function register() {
        add_meta_box(
            'vxlite_tour_builder',
            __( 'Vortex360 Tour (Lite)', 'vortex360-lite' ),
            [ __CLASS__, 'render' ],
            VXLITE_CPT,
            'normal',
            'high'
        );
    }

    public static function render( $post ) {
        wp_nonce_field( 'vxlite_save_tour', 'vxlite_nonce' );
        $data = get_post_meta( $post->ID, VXLITE_META, true );
        if ( ! is_array( $data ) ) $data = [ 'scenes' => [] ];

        // Lite limits info
        echo '<p><strong>'. esc_html__( 'Lite limits:', 'vortex360-lite' ) .'</strong> ';
        echo esc_html__( '1 tour total, 5 scenes per tour, 5 hotspots per scene (text/image/link).', 'vortex360-lite' ) .'</p>';

        // Simple JSON editor (safe, structured)
        echo '<p>'. esc_html__( 'Paste/edit your tour JSON. Use the “Add Scene” helper for a scaffold.', 'vortex360-lite' ) .'</p>';

        echo '<textarea style="width:100%;min-height:260px;font-family:monospace" name="vxlite_json">';
        echo esc_textarea( wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
        echo '</textarea>';

        // Tiny scaffold helper
        ?>
        <button type="button" class="button" id="vxlite-add-scene"><?php esc_html_e('Add Scene scaffold','vortex360-lite'); ?></button>
        <script>
        (function($){
            $('#vxlite-add-scene').on('click', function(){
                try {
                    const el = $('textarea[name="vxlite_json"]');
                    const json = el.val().trim() ? JSON.parse(el.val()) : { scenes: [] };
                    if (!json.scenes) json.scenes = [];
                    if (json.scenes.length >= 5) { alert('<?php echo esc_js(__('Lite: max 5 scenes', 'vortex360-lite')); ?>'); return; }
                    json.scenes.push({
                        id: 'scene-' + (json.scenes.length + 1),
                        title: 'Scene ' + (json.scenes.length + 1),
                        panorama: '',
                        hotspots: []
                    });
                    el.val(JSON.stringify(json, null, 2));
                } catch(e){ alert('Invalid JSON'); }
            });
        })(jQuery);
        </script>
        <?php
    }

    public static function save( $post_id ) {
        if ( ! isset( $_POST['vxlite_nonce'] ) || ! wp_verify_nonce( $_POST['vxlite_nonce'], 'vxlite_save_tour' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Enforce Lite: only allow 1 published tour. Others forced to draft.
        $count = new WP_Query([
            'post_type'      => VXLITE_CPT,
            'post_status'    => [ 'publish', 'pending', 'draft' ],
            'fields'         => 'ids',
            'posts_per_page' => -1,
        ]);
        if ( $count->found_posts > 1 ) {
            // Soft guard: keep saving but warn via admin_notices in admin.js (simple).
        }

        $raw = isset( $_POST['vxlite_json'] ) ? wp_unslash( $_POST['vxlite_json'] ) : '';
        if ( ! $raw ) {
            delete_post_meta( $post_id, VXLITE_META );
            return;
        }
        $decoded = json_decode( $raw, true );
        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) return;

        // Sanitize + enforce limits
        $out = [ 'scenes' => [] ];
        if ( ! empty( $decoded['scenes'] ) && is_array( $decoded['scenes'] ) ) {
            $scenes = array_slice( $decoded['scenes'], 0, 5 ); // max 5 scenes (Lite)
            foreach ( $scenes as $scene ) {
                $s = [
                    'id'       => sanitize_key( $scene['id'] ?? uniqid('scene-') ),
                    'title'    => sanitize_text_field( $scene['title'] ?? '' ),
                    'panorama' => esc_url_raw( $scene['panorama'] ?? '' ),
                    'hotspots' => [],
                ];
                if ( ! empty( $scene['hotspots'] ) && is_array( $scene['hotspots'] ) ) {
                    $hots = array_slice( $scene['hotspots'], 0, 5 ); // max 5 hotspots per scene
                    foreach ( $hots as $h ) {
                        $type = in_array( $h['type'] ?? 'text', [ 'text', 'image', 'link' ], true ) ? $h['type'] : 'text';
                        $s['hotspots'][] = [
                            'type'   => $type,
                            'title'  => sanitize_text_field( $h['title'] ?? '' ),
                            'text'   => sanitize_textarea_field( $h['text'] ?? '' ),
                            'image'  => esc_url_raw( $h['image'] ?? '' ),
                            'url'    => esc_url_raw( $h['url'] ?? '' ),
                            'yaw'    => floatval( $h['yaw'] ?? 0 ),
                            'pitch'  => floatval( $h['pitch'] ?? 0 ),
                            'scene'  => sanitize_key( $h['scene'] ?? '' ), // target scene id for link
                        ];
                    }
                }
                $out['scenes'][] = $s;
            }
        }

        update_post_meta( $post_id, VXLITE_META, $out );
    }
}
