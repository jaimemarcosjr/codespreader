<?php
// Allowed HTML tags and attributes
function codespreader_get_allowed_tags() {
    return array(
        'style' => array('type' => true, 'media' => true),
        'script' => array('type' => true, 'src' => true, 'async' => true, 'defer' => true),
        'link' => array('rel' => true, 'href' => true, 'type' => true),
        'meta' => array('charset' => true, 'name' => true, 'content' => true),
        'noscript' => array(),
    );
}

// Output Header Code
function codespreader_output_custom_header_code() {
    $header_code = get_option('codespreader_inject_header', '');

    if (!empty($header_code)) {
        echo "\n<!-- CodeSpreader Header Code -->\n";
        echo wp_kses($header_code, codespreader_get_allowed_tags()) . "\n";
    }
}
add_action('wp_head', 'codespreader_output_custom_header_code');

// Output Body Code
function codespreader_output_custom_body_code() {
    $body_code = get_option('codespreader_inject_body', '');

    if (!empty($body_code)) {
        echo "\n<!-- CodeSpreader Body Code -->\n";
        echo wp_kses($body_code, codespreader_get_allowed_tags()) . "\n";
    }
}
add_action('wp_body_open', 'codespreader_output_custom_body_code');

// Output Footer Code
function codespreader_output_custom_footer_code() {
    $footer_code = get_option('codespreader_inject_footer', '');

    if (!empty($footer_code)) {
        echo "\n<!-- CodeSpreader Footer Code -->\n";
        echo wp_kses($footer_code, codespreader_get_allowed_tags()) . "\n";
    }
}
add_action('wp_footer', 'codespreader_output_custom_footer_code');

// Output the custom CSS in <head>
function codespreader_output_custom_css() {
    if (is_singular(['post', 'page'])) {
        global $post;
        $custom_css = get_post_meta($post->ID, '_codespreader_custom_css', true);
        if ($custom_css) {
            echo '<style type="text/css">' . $custom_css . '</style>';
        }
    }
}
add_action('wp_head', 'codespreader_output_custom_css');

// Output the custom JS in footer
function codespreader_output_custom_js() {
    if (is_singular(['post', 'page'])) {
        global $post;
        $custom_js = get_post_meta($post->ID, '_codespreader_custom_js', true);
        if ($custom_js) {
            echo '<script id="codespreader-custom-js">' . $custom_js . '</script>';
        }
    }
}
add_action('wp_footer', 'codespreader_output_custom_js', 100); // Load late