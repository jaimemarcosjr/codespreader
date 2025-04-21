<?php
// Custom CSS and Javscript code per page

// Add meta box in page/post editor
function codespreader_add_custom_css_metabox() {
    $screens = ['page', 'post'];
    foreach ($screens as $screen) {
        add_meta_box(
            'codespreader_custom_css',
            'CodeSpreader - Custom CSS and Javascript',
            'codespreader_custom_css_callback',
            $screen,
            'normal',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'codespreader_add_custom_css_metabox');

function codespreader_custom_css_callback($post) {
    $custom_css = get_post_meta($post->ID, '_codespreader_custom_css', true);
    $custom_js  = get_post_meta($post->ID, '_codespreader_custom_js', true);

    echo '<p><strong>Custom CSS</strong></p>';
    echo '<textarea id="codespreader_custom_css_editor" name="codespreader_custom_css" rows="10" style="width:100%;">' . esc_textarea($custom_css) . '</textarea>';

    echo '<p><strong>Custom JavaScript</strong></p>';
    echo '<textarea id="codespreader_custom_js_editor" name="codespreader_custom_js" rows="10" style="width:100%;">' . esc_textarea($custom_js) . '</textarea>';

    wp_nonce_field('codespreader_save_custom_code', 'codespreader_custom_code_nonce');
}

function codespreader_save_custom_css($post_id) {
    if (!isset($_POST['codespreader_custom_code_nonce']) || !wp_verify_nonce($_POST['codespreader_custom_code_nonce'], 'codespreader_save_custom_code')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $custom_css = isset($_POST['codespreader_custom_css']) ? wp_kses_post($_POST['codespreader_custom_css']) : '';
    $custom_js  = isset($_POST['codespreader_custom_js']) ? wp_kses($_POST['codespreader_custom_js'], []) : '';

    update_post_meta($post_id, '_codespreader_custom_css', $custom_css);
    update_post_meta($post_id, '_codespreader_custom_js', $custom_js);
}

add_action('save_post', 'codespreader_save_custom_css');



