<?php

// Register the custom API endpoint
function register_hide_options_api_endpoint() {
    register_rest_route('handball/v1', '/menu-objects-visibility/', array(
        'methods' => 'GET',
        'callback' => 'get_hide_options',
    ));
}
add_action('rest_api_init', 'register_hide_options_api_endpoint');

// Callback function to return hide options
function get_hide_options() {
    $hide_options = array(
        'show_players' => get_boolean_option('hide_players'),
        'show_home_menu' => get_boolean_option('hide_home_menu'),
        'show_local_competition_menu' => get_boolean_option('hide_local_competition_menu'),
        'show_news_menu' => get_boolean_option('hide_news_menu'),
        'show_clubs_menu' => get_boolean_option('hide_clubs_menu'),
        'show_refree_menu' => get_boolean_option('hide_refree_menu'),
        'show_competition_menu' => get_boolean_option('hide_competition_menu'),
        'show_live_menu' => get_boolean_option('hide_live_menu'),
        'show_about_menu' => get_boolean_option('hide_about_menu'),
        'show_sponsor_menu' => get_boolean_option('hide_sponsor_menu'),
        'show_contact_menu' => get_boolean_option('hide_contact_menu'),
        'show_regulations_docs_menu' => get_boolean_option('hide_regulations_docs_menu'),
        'show_national_teams' => get_boolean_option('hide_national_teams'),
    );

    return rest_ensure_response($hide_options);
}

// Helper function to convert 'yes'/'no' to boolean
function get_boolean_option($option_name) {
    return (get_option($option_name) === 'yes') ? true : false;
}
