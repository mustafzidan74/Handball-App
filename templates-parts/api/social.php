<?php

// Register the custom API endpoint
function register_social_media_api_endpoint() {
    register_rest_route('handball/v1', '/social-media/', array(
        'methods' => 'GET',
        'callback' => 'get_social_media_urls',
    ));
}
add_action('rest_api_init', 'register_social_media_api_endpoint');

// Callback function to return social media URLs
function get_social_media_urls() {
    $social_media_urls = array(
		'instagram' => get_option('instagram_url_option'),
		'instagram_view' => get_option('instagram_url_option_view'),
		'x' => get_option('x_url_option'),
		'x_view' => get_option('x_url_option_view'),
		'youtube' => get_option('youtube_url_option'),
		'youtube_view' => get_option('youtube_url_option_view'),
        'facebook' => get_option('facebook_url_option'),
        'facebook_view' => get_option('facebook_url_option_view'),
        'website' => get_option('website_url_option'),
        'website_view' => get_option('website_url_option_view'),
        'email' => get_option('email_option'),
        'email_view' => get_option('email_option_view'),
        'tiktok' => get_option('tiktok_url_option'),
        'tiktok_view' => get_option('tiktok_url_option_view'),
    );

    return rest_ensure_response($social_media_urls);
}
