<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class VX_Admin_Metabox {

    /**
     * OPTIONAL: If some environments still call ::register(), this keeps compatibility.
     * Your orchestrator already adds the main builder metabox directly.
     */
    public static function register() {
        add_meta_box(
            'vxlite_tour_builder',
            __( 'Vortex360 Tour (Lite)', 'vortex360-lite' ),
            [ __CLASS__, 'render' ],
            VXLITE_CPT,
            'normal',
            'high'
        );
        add_meta_box(
            'vxlite_tour_help',
            __( 'Lite Limits & Tips', 'vortex360-lite' ),
            [ __CLASS__, 'render_help' ],
            VXLITE_CPT,
            'side',
            'default'
        );
    }

    /**
     * Main builder metabox.
     */
    public static function render( $post ) {
        wp_nonce_field( 'vxlite_save_tour', 'vxlite_nonce' );

        $data = get_post_meta( $post->ID, VXLITE_META, true );
        if ( ! is_array( $data ) ) {
            $data = [ 'scenes' => [] ];
        }

        $exceeded = get_post_meta( $post->ID, '_vxlite_exceeded_single_tour', true );
        ?>
        <div class="vxlite-metabox">
            <?php if ( $exceeded ) : ?>
                <div class="notice notice-warning" style="margin:0 0 10px;">
                    <p><?php esc_html_e( 'Lite note: You have more than one tour in total. Vortex360 Lite is designed for a single tour.', 'vortex360-lite' ); ?></p>
                </div>
            <?php endif; ?>

            <p class="description">
                <?php esc_html_e( 'Edit your tour JSON or use the scaffold buttons. Lite allows up to 5 scenes per tour and 5 hotspots per scene (types: text, image, link).', 'vortex360-lite' ); ?>
            </p>

            <div class="vxlite-toolbar">
                <button type="button" class="button" id="vxlite-add-scene"><?php esc_html_e('Add Scene','vortex360-lite'); ?></button>
                <button type="button" class="button" id="vxlite-add-hotspot"><?php esc_html_e('Add Hotspot to Last Scene','vortex360-lite'); ?></button>
                <button type="button" class="button" id="vxlite-pretty"><?php esc_html_e('Prettify JSON','vortex360-lite'); ?></button>
                <button type="button" class="button button-secondary" id="vxlite-validate"><?php esc_html_e('Validate','vortex360-lite'); ?></button>
            </div>

            <textarea class="vxlite-json" name="vxlite_json" spellcheck="false"><?php
                echo esc_textarea( wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
            ?></textarea>

            <div class="vxlite-examples">
                <p class="description" style="margin-top:10px;">
                    <strong><?php esc_html_e('Scene shape:', 'vortex360-lite'); ?></strong><br/>
                    <code>{"id":"scene-1","title":"Lobby","panorama":"https://.../pano.jpg","thumb":"https://.../thumb.jpg","hfov":110,"pitch":0,"yaw":0,"hotspots":[ ... ]}</code>
                </p>
                <p class="description">
                    <strong><?php esc_html_e('Hotspot (text/image/link):', 'vortex360-lite'); ?></strong><br/>
                    <code>{"type":"text","title":"Info","text":"Welcome!","yaw":0,"pitch":0}</code><br/>
                    <code>{"type":"image","title":"Poster","image":"https://.../poster.jpg","yaw":15,"pitch":-5}</code><br/>
                    <code>{"type":"link","title":"Go Lobby","scene":"scene-1","yaw":45,"pitch":-2}</code>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Side help metabox (shown only if ::register() is used by some setups).
     */
    public static function render_help( $post ) {
        $count = new WP_Query( [
            'post_type'      => VXLITE_CPT,
            'post_status'    => [ 'publish', 'pending', 'draft', 'future', 'private' ],
            'fields'         => 'ids',
            'posts_per_page' => -1,
        ] );
        $total = (int) $count->found_posts;
        ?>
        <ul class="vxlite-help">
            <li><?php esc_html_e('Lite allows 1 tour in total.', 'vortex360-lite'); ?>
                <?php printf( esc_html__( 'Current total: %d', 'vortex360-lite' ), $total ); ?>
            </li>
            <li><?php esc_html_e('Max 5 scenes per tour; each scene max 5 hotspots.', 'vortex360-lite'); ?></li>
            <li><?php esc_html_e('Use the scene "id" to link between scenes with a link hotspot.', 'vortex360-lite'); ?></li>
            <li><?php esc_html_e('Add "thumb" (URL) per scene for thumbnail navigation row.', 'vortex360-lite'); ?></li>
            <li><?php esc_html_e('Optional: "hfov", "pitch", "yaw" to set initial view.', 'vortex360-lite'); ?></li>
        </ul>
        <?php
    }

    /**
     * Save handler for tour JSON.
     */
    public static function save( $post_id ) {
        // Nonce / autosave / caps
        if ( ! isset( $_POST['vxlite_nonce'] ) || ! wp_verify_nonce( $_POST['vxlite_nonce'], 'vxlite_save_tour' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Get raw JSON
        $raw = isset( $_POST['vxlite_json'] ) ? wp_unslash( $_POST['vxlite_json'] ) : '';
        if ( $raw === '' ) {
            delete_post_meta( $post_id, VXLITE_META );
            return;
        }

        $decoded = json_decode( $raw, true );
        if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
            // Keep previous valid meta if JSON invalid
            return;
        }

        // Sanitize and enforce Lite limits
        $clean = vxlite_sanitize_tour_array( $decoded );
        update_post_meta( $post_id, VXLITE_META, $clean );

        // Soft-flag if more than 1 tour exists overall
        $count = new WP_Query( [
            'post_type'      => VXLITE_CPT,
            'post_status'    => [ 'publish', 'pending', 'draft', 'future', 'private' ],
            'fields'         => 'ids',
            'posts_per_page' => -1,
        ] );
        if ( $count->found_posts > 1 ) {
            update_post_meta( $post_id, '_vxlite_exceeded_single_tour', 1 );
        } else {
            delete_post_meta( $post_id, '_vxlite_exceeded_single_tour' );
        }
    }
}
