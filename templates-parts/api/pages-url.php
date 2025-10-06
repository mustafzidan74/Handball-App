<?php

function pages_api_endpoint() {
    register_rest_route('handball/v1', '/page-url/', array(
        'methods' => 'GET',
        'callback' => 'get_page_url',
    ));
}

add_action('rest_api_init', 'pages_api_endpoint');

function get_page_url($data) {
    $page = sanitize_text_field($data['page']);
    $lang = isset($data['lang']) ? sanitize_text_field($data['lang']) : 'ar';

    // Define your page-to-URL mapping
    $page_urls = array(
        'contact' => get_option('contact_us') . '?lang='.$lang,
        'about'   => get_option('about_us') . '?lang='.$lang,
        'season'  => get_option('competition') . '?lang='.$lang,
        'regulations'  => get_option('regulations') . '?lang='.$lang,
        'national_teams'  => get_option('national') . '?lang='.$lang,

	);

    // Check if the page parameter is valid
    if (array_key_exists($page, $page_urls)) {
        $url = $page_urls[$page];
        return array('pageUrl' => $url);
    } else {
        return array('error' => 'Invalid page parameter');
    }
}
