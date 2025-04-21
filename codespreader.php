<?php
/**
 * Plugin Name:     CodeSpreader
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     One plugin to rule them all - globally add CSS and JS on every WordPress page.
 * Author:          Jaime Marcos
 * Author URI:      https://github.com/jaimemarcosjr/
 * Text Domain:     codespreader
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Codespreader
 */

// Your code starts here.

defined('ABSPATH') or die('No script kiddies please!');

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/helper-functions.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-types.php';
require_once plugin_dir_path(__FILE__) . 'public/output-hooks.php';

// Hooks

// Hook into admin_enqueue_scripts
add_action('admin_enqueue_scripts', 'codespreader_load_settings_css');

function codespreader_load_settings_css($hook) {
    // Check if we're on our plugin's settings page
    if ($hook !== 'settings_page_codespreader-settings') {
        return;
    }

    wp_enqueue_style(
        'codespreader-settings-style',
        plugin_dir_url(__FILE__) . 'assets/css/settings-style.css',
        array(),
        '1.0'
    );


}

// Enqueue CodeMirror for Custom CSS textarea
function codespreader_enqueue_codemirror_assets($hook) {
    global $post;

    // Only enqueue on post editor pages
    if (!in_array($hook, ['post.php', 'post-new.php'])) return;

    // Only for post types where the meta box appears
    $screen = get_current_screen();
    if (!in_array($screen->post_type, ['post', 'page'])) return;

    // Enqueue CodeMirror core
    wp_enqueue_code_editor([
        'type' => 'text/css',
    ]);
    
    wp_enqueue_script('wp-theme-plugin-editor');
    wp_enqueue_style('wp-codemirror');

    // Enqueue your own small script handle
    wp_register_script('custom-codespreader-editor-init', '', [], false, true);

    wp_enqueue_script('custom-codespreader-editor-init');
    wp_add_inline_script('custom-codespreader-editor-init', "
        jQuery(function($) {
            console.log('Codespreader init script running');

            const cssSettings = _.clone(wp.codeEditor.defaultSettings || {});
            cssSettings.codemirror = _.extend({}, cssSettings.codemirror, {
                mode: 'css',
                indentUnit: 2,
                tabSize: 2,
                lineNumbers: true
            });

            const jsSettings = _.clone(wp.codeEditor.defaultSettings || {});
            jsSettings.codemirror = _.extend({}, jsSettings.codemirror, {
                mode: 'javascript',
                indentUnit: 2,
                tabSize: 2,
                lineNumbers: true
            });

            function initializeCodeSpreaderEditors() {
                const cssEl = document.getElementById('codespreader_custom_css_editor');
                const jsEl = document.getElementById('codespreader_custom_js_editor');

                
                window.codespreaderEditor = wp.codeEditor.initialize(cssEl, cssSettings);
                window.codespreaderJSEditor = wp.codeEditor.initialize(jsEl, jsSettings);
                
            }

            setTimeout(initializeCodeSpreaderEditors, 100);

            if (window.wp && wp.data && wp.data.subscribe) {
                let wasSaving = false;
                wp.data.subscribe(function () {
                    const isSaving = wp.data.select('core/editor').isSavingPost();
                    const isAutosaving = wp.data.select('core/editor').isAutosavingPost();

                    if (!isAutosaving && !wasSaving && isSaving) {
                        if (window.codespreaderEditor?.codemirror) {
                            $('#codespreader_custom_css_editor').val(window.codespreaderEditor.codemirror.getValue());
                        }
                        if (window.codespreaderJSEditor?.codemirror) {
                            $('#codespreader_custom_js_editor').val(window.codespreaderJSEditor.codemirror.getValue());
                        }
                        console.log('Synced CSS & JS before save');
                    }

                    wasSaving = isSaving;
                });
            }
        });
    ");
}
add_action('admin_enqueue_scripts', 'codespreader_enqueue_codemirror_assets');

function codespreader_enqueue_codemirror_for_html($hook) {
    // Load only on your plugin settings page
    if ($hook === 'settings_page_codespreader-settings') {
        wp_enqueue_code_editor([
            'type' => 'text/html'
        ]);
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        add_action('admin_footer', 'codespreader_init_codemirror_on_settings');
    }
}

add_action('admin_enqueue_scripts', 'codespreader_enqueue_codemirror_for_html');

// Register settings page
add_action('admin_menu', 'codespreader_register_settings_page');

// Register settings
add_action('admin_init', 'codespreader_register_settings');