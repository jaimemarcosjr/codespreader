<?php

function codespreader_init_codemirror_on_settings() {
    ?>
    <script>
    jQuery(function($){
        const fields = [
            'codespreader_inject_header',
            'codespreader_inject_body',
            'codespreader_inject_footer'
        ];

        fields.forEach(function(fieldId) {
            const textarea = document.querySelector('[name="' + fieldId + '"]');
            if (textarea) {
                wp.codeEditor.initialize(textarea, {
                    codemirror: {
                        mode: 'htmlmixed',
                        lineNumbers: true,
                        indentUnit: 2,
                        tabSize: 2,
                        lineWrapping: true
                    }
                });
            }
        });
    });
    </script>
    <?php
}

function codespreader_sanitize_custom_code($input) {
    $allowed_tags = array(
        'script' => array(
            'type' => true,
            'src'  => true,
            'async' => true,
            'defer' => true,
        ),
        'style' => array(
            'type' => true,
            'media' => true,
        ),
        'link' => array(
            'rel' => true,
            'href' => true,
            'type' => true,
        ),
        'meta' => array(
            'charset' => true,
            'name' => true,
            'content' => true,
        ),
        'noscript' => array(),
    );

    return wp_kses($input, $allowed_tags);
}


function codespreader_register_settings_page() {
    add_options_page(
        'CodeSpreader - Insert Global Codes Here (Header, Body, Script)',
        'CodeSpreader',
        'manage_options',
        'codespreader-settings',
        'codespreader_settings_page_html'
    );
}

function codespreader_register_settings() {
    register_setting('codespreader_settings_group', 'codespreader_inject_header', [
        'sanitize_callback' => 'codespreader_sanitize_custom_code'
    ]);
    register_setting('codespreader_settings_group', 'codespreader_inject_body', [
        'sanitize_callback' => 'codespreader_sanitize_custom_code'
    ]);
    register_setting('codespreader_settings_group', 'codespreader_inject_footer', [
        'sanitize_callback' => 'codespreader_sanitize_custom_code'
    ]);

    add_settings_section(
        'codespreader_main_section',
        'Main Settings',
        null,
        'codespreader-settings'
    );

    add_settings_field(
        'codespreader_inject_header',
        '<h3>Header </h3> <p>this will be added before &lt;/head&gt;</p>',
        'codespreader_inject_header_field_html',
        'codespreader-settings',
        'codespreader_main_section'
    );

    add_settings_field(
        'codespreader_inject_body',
        '<h3>Body </h3> <p>this will be added after &lt;body&gt;</p>',
        'codespreader_inject_body_field_html',
        'codespreader-settings',
        'codespreader_main_section'
    );

    add_settings_field(
        'codespreader_inject_footer',
        '<h3>Footer </h3><p>this will be added in footer</p>',
        'codespreader_inject_footer_field_html',
        'codespreader-settings',
        'codespreader_main_section'
    );

   
}

function codespreader_inject_header_field_html() {
    $value = get_option('codespreader_inject_header', '');
    echo '<textarea id="codespreader_inject_header" name="codespreader_inject_header" rows="10" style="width:100%;">' . esc_textarea($value) . '</textarea>';
}

function codespreader_inject_body_field_html() {
    $value = get_option('codespreader_inject_body', '');
    echo '<textarea id="codespreader_inject_body" name="codespreader_inject_body" rows="10" style="width:100%;">' . esc_textarea($value) . '</textarea>';
}

function codespreader_inject_footer_field_html() {
    $value = get_option('codespreader_inject_footer', '');
    echo '<textarea id="codespreader_inject_footer" name="codespreader_inject_footer" rows="10" style="width:100%;">' . esc_textarea($value) . '</textarea>';
}



function codespreader_settings_page_html() {
    ?>
    <div class="wrap-codespreader">
        <h1>CodeSpreader - Insert Global Codes Here (Header, Body, Script)</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('codespreader_settings_group');
            do_settings_sections('codespreader-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

