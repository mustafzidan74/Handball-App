<?php

// Add a custom endpoint for post data
function custom_post_api_endpoint() {
    register_rest_route('handball/v1', '/post', array(
        'methods' => 'GET',
        'callback' => 'get_custom_post_data',
        'args' => array(
            'post_id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && intval($param) > 0;
                }
            ),
        ),
    ));
}

add_action('rest_api_init', 'custom_post_api_endpoint');

// Callback function to get post data
function get_custom_post_data($data) {
    $original_post_id = isset($data['post_id']) ? intval($data['post_id']) : 0;

    // Get the translated post ID based on the original post ID and the current language
    $translated_post_id = apply_filters('wpml_object_id', $original_post_id, 'post', false);

    if (!$translated_post_id) {
        return new WP_Error('not_found', 'Translated post not found', array('status' => 404));
    }

    $post = get_post($translated_post_id);

    if (!$post) {
        return new WP_Error('not_found', 'Post not found', array('status' => 404));
    }

    // Get post data
    $post_data = array(
        'id' => $post->ID,
        'title' => get_the_title($translated_post_id),
		'image' => (has_post_thumbnail()) ? get_the_post_thumbnail_url($translated_post_id) : '',
        'content' => apply_filters('the_content', $post->post_content),
        'date' => date('d M Y', strtotime($post->post_date)),
        'day' => date('d', strtotime($post->post_date)),
        'month' => date('M', strtotime($post->post_date)),
        'year' => date('Y', strtotime($post->post_date)),
    );

    // Get category and tags
    $post_data['category'] = wp_get_post_terms($translated_post_id, 'category', array('fields' => 'names'));
    $post_data['post_tags'] = wp_get_post_terms($translated_post_id, 'post_tag', array('fields' => 'names'));

    return rest_ensure_response($post_data);
}
