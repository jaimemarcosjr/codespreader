<?php
/**
 * Plugin Name:     CodeSpreader
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     One plugin to rule them all - globally add CSS and JS on every WordPress page.
 * Author:          Jaime Marcos
 * Author URI:      https://github.com/jaimemarcosjr/
 * Text Domain:     codespreader
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         Codespreader
 */

// Your code starts here.

defined('ABSPATH') or die('No script kiddies please!');

// Includes
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

function codespreader_enqueue_editor_styles() {
    $editor_css_url  = plugin_dir_url(__FILE__) . 'assets/css/editor-style.css';
    $editor_css_path = plugin_dir_path(__FILE__) . 'assets/css/editor-style.css';
    $version         = file_exists($editor_css_path) ? filemtime($editor_css_path) : false;

    // Gutenberg
    add_action('enqueue_block_editor_assets', function () use ($editor_css_url, $version) {
        wp_enqueue_style('codespreader-editor-style', $editor_css_url, array(), $version);
    });

    // Classic Editor (TinyMCE)
    add_filter('mce_css', function ($mce_css) use ($editor_css_url) {
        if (!empty($mce_css)) {
            $mce_css .= ',' . $editor_css_url;
        } else {
            $mce_css = $editor_css_url;
        }
        return $mce_css;
    });
}
add_action('init', 'codespreader_enqueue_editor_styles');

function codespreader_enqueue_editor_scripts($hook) {
    $editor_js_url  = plugin_dir_url(__FILE__) . 'assets/js/editor-script.js';
    $editor_js_path = plugin_dir_path(__FILE__) . 'assets/js/editor-script.js';
    $version        = file_exists($editor_js_path) ? filemtime($editor_js_path) : false;

    // Gutenberg (Block Editor)
    add_action('enqueue_block_editor_assets', function () use ($editor_js_url, $version) {
        wp_enqueue_script(
            'codespreader-editor-js',
            $editor_js_url,
            array('wp-blocks', 'wp-element', 'wp-editor'), // adjust based on usage
            $version,
            true
        );
    });

    // Classic Editor
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        wp_enqueue_script(
            'codespreader-classic-js',
            $editor_js_url,
            array('jquery'), // adjust based on your JS
            $version,
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'codespreader_enqueue_editor_scripts');

// Register settings page
add_action('admin_menu', 'codespreader_register_settings_page');

// Register settings
add_action('admin_init', 'codespreader_register_settings');