<?php
/**
 * Plugin Name: Handball Plugin
 * Description: Custom functionality extracted from theme (matches, notifications, dashboard, etc.)
 * Version: 1.0
 * Author: Super Coding
 */

if (!defined('ABSPATH')) {
    exit;
}

// ================= Include main files =================
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/matches.php';
require_once plugin_dir_path(__FILE__) . 'includes/plugins.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-options.php';

// ================= Enqueue styles and scripts =================
add_action('admin_enqueue_scripts', function (): void {
    wp_enqueue_style(
        'handball-dashboard',
        plugin_dir_url(__FILE__) . 'assets/custom-dashboard-styles.css'
    );
    wp_enqueue_script(
        'handball-ajax',
        plugin_dir_url(__FILE__) . 'assets/custom-ajax-script.js',
        ['jquery'],
        null,
        true
    );
});

// ================= Constants =================
define('HANDBALL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HANDBALL_PLUGIN_URL', plugin_dir_url(__FILE__));

// ================= Settings helpers =================
function handball_get_settings() {
    $defaults = [
        'project_id' => 'handball-notifications',
        'topic_ar'   => 'new_matches_ar_demo',
        'topic_en'   => 'new_matches_en_demo',
        'apis_enabled' => []
    ];
    $opt = get_option('handball_options', []);
    return wp_parse_args($opt, $defaults);
}

function handball_get_topic() {
    $s = handball_get_settings();
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    return (strpos($locale, 'ar') === 0) ? $s['topic_ar'] : $s['topic_en'];
}

function handball_get_service_json_path() {
    $filename = 'handball-notifications-firebase-adminsdk-djle4-ebd601aedf.json';
    $path = HANDBALL_PLUGIN_PATH . 'notifications/' . $filename;
    return file_exists($path) ? $path : '';
}

// ================= Handle JSON upload =================
function handball_handle_json_upload() {
    if (empty($_FILES['handball_service_json']['tmp_name'])) {
        add_settings_error('handball_options', 'handball_json_missing', __('No file uploaded.', 'handball'), 'error');
        return;
    }

    $file = $_FILES['handball_service_json'];
    $required = 'handball-notifications-firebase-adminsdk-djle4-ebd601aedf.json';

    if ($file['name'] !== $required) {
        add_settings_error('handball_options', 'handball_json_name', __('Incorrect file name. It must be exactly: ', 'handball') . $required, 'error');
        return;
    }

    $dest_dir = HANDBALL_PLUGIN_PATH . 'notifications/';
    if (!file_exists($dest_dir)) {
        wp_mkdir_p($dest_dir);
    }

    $dest = $dest_dir . $required;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        add_settings_error('handball_options', 'handball_json_move', __('Failed to move JSON file to plugin notifications folder.', 'handball'), 'error');
        return;
    }

    add_settings_error('handball_options', 'handball_json_ok', __('JSON uploaded successfully to plugin notifications folder.', 'handball'), 'updated');
}

// ================= Include APIs conditionally =================
add_action('init', function() {
    $s = handball_get_settings();
    $enabled = isset($s['apis_enabled']) ? (array)$s['apis_enabled'] : [];

    $api_map = [
        'hide-options' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/hide-options.php',
        'legaue' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/legaue.php',
        'match-results' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/match-results.php',
        'matches-legaue-appointments' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/matches-legaue-appointments.php',
        'matches-legaue-past' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/matches-legaue-past.php',
        'matches-legaue-tables' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/matches-legaue-tables.php',
        'matches-legaue' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/matches-legaue.php',
        'matches' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/matches.php',
        'pages-url' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/pages-url.php',
        'posts' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/posts.php',
        'single-post' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/single-post.php',
        'social' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/social.php',
        'staff' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/staff.php',
        'team-details' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/team-details.php',
        'team' => HANDBALL_PLUGIN_PATH . 'templates-parts/api/team.php'
    ];

    foreach ($api_map as $slug => $file) {
        if (!empty($enabled[$slug]) && file_exists($file)) {
            require_once $file;
        }
    }
}, 5);


// ================= Register settings =================
add_action('admin_init', function() {
    register_setting('handball_options_group', 'handball_options');
});

// ================= Options page renderer =================

