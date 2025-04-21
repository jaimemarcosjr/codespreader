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
    echo '<div  class="codespreader-tabs">
    <button class="tab-button active" data-tab="css">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#1e1e1e" d="M480 32l-64 368-223.3 80L0 400l19.6-94.8h82l-8 40.6L210 390.2l134.1-44.4 18.8-97.1H29.5l16-82h333.7l10.5-52.7H56.3l16.3-82H480z"/></svg>
      Custom CSS
    </button>
    <button class="tab-button" data-tab="js">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#1e1e1e" d="M0 32v448h448V32H0zm243.8 349.4c0 43.6-25.6 63.5-62.9 63.5-33.7 0-53.2-17.4-63.2-38.5l34.3-20.7c6.6 11.7 12.6 21.6 27.1 21.6 13.8 0 22.6-5.4 22.6-26.5V237.7h42.1v143.7zm99.6 63.5c-39.1 0-64.4-18.6-76.7-43l34.3-19.8c9 14.7 20.8 25.6 41.5 25.6 17.4 0 28.6-8.7 28.6-20.8 0-14.4-11.4-19.5-30.7-28l-10.5-4.5c-30.4-12.9-50.5-29.2-50.5-63.5 0-31.6 24.1-55.6 61.6-55.6 26.8 0 46 9.3 59.8 33.7L368 290c-7.2-12.9-15-18-27.1-18-12.3 0-20.1 7.8-20.1 18 0 12.6 7.8 17.7 25.9 25.6l10.5 4.5c35.8 15.3 55.9 31 55.9 66.2 0 37.8-29.8 58.6-69.7 58.6z"/></svg>
      Custom JS
    </button>
  </div>

  <div id="css" class="codespreader-custom-code active">
    <h3>Your Custom CSS</h3>
    <textarea id="codespreader_custom_css_editor" name="codespreader_custom_css" rows="10" style="width:100%;">' . esc_textarea($custom_css) . '</textarea>
  </div>

  <div id="js"  class="codespreader-custom-code">
    <h3>Your Custom JavaScript</h3>
    <textarea id="codespreader_custom_js_editor" name="codespreader_custom_js" rows="10" style="width:100%;">' . esc_textarea($custom_js) . '</textarea>
  </div>';

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