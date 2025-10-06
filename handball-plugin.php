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
// Functions are defined in includes/admin-options.php

// ================= Handle JSON upload =================
// Function is defined in includes/admin-options.php

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
// Settings registration is handled in includes/admin-options.php

