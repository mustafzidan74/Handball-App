<?php

// Get Posts API endpoint
function posts_api_route() {
    register_rest_route('handball/v1', '/posts/', array(
        'methods' => 'GET',
        'callback' => 'get_handball_posts',
        'args' => array(
            'posts_per_page' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
            'category_id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) || is_string($param);
                }
            ),
            'lang' => array(
                'validate_callback' => function($param, $request, $key) {
                    return preg_match('/^[a-z]{2}$/', $param);
                },
//                 'required' => true, // Make 'lang' parameter mandatory
            ),
        ),
    ));
}
add_action('rest_api_init', 'posts_api_route');

// Get Posts API endpoint callback function
function get_handball_posts($data) {
    // Get parameters
    $posts_per_page = isset($data['posts_per_page']) ? $data['posts_per_page'] : -1;
    $category_id = isset($data['category_id']) ? $data['category_id'] : '';
    $lang = isset($data['lang']) ? $data['lang'] : '';
    $page = isset($data['page']) ? max(1, intval($data['page'])) : 1;

    // Calculate offset for pagination
    $offset = ($page - 1) * $posts_per_page;

    // Query arguments
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => ($page === 0) ? -1 : $posts_per_page, // If page is 0, get all posts
        'offset' => ($page === 0) ? 0 : $offset,
        'tax_query' => array(),
        'orderby' => 'date',  // Order by date
        'order' => 'DESC',    // Order in descending order (newest first)
        'suppress_filters' => false, // Make sure WPML filters are not suppressed
    );
	
    // New variable to store category name
    $category_name = '';	

    // Modify $category_id if it is 'slider'
    if ($category_id === 'top_news') {
        $category_id = get_option('event_selected_category_id');
		$category_name = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
    }
    if ($category_id === 'news') {
        $category_id = get_option('event_selected_last_news_id');
		$category_name_id = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
		$category_name = get_cat_name($category_name_id);
	}
    if ($category_id === 'live') {
        $category_id = get_option('event_selected_live_news_id');
		$category_name_id = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
		$category_name = get_cat_name($category_name_id);
    }
    if ($category_id === 'diamond_sponsors') {
        $category_id = get_option('event_selected_diamond_sponsors_id');
		$category_name_id = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
		$category_name = get_cat_name($category_name_id);
    }
    if ($category_id === 'gold_sponsors') {
        $category_id = get_option('event_selected_gold_sponsors_id');
		$category_name_id = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
		$category_name = get_cat_name($category_name_id);
    }
    if ($category_id === 'union_sponsors') {
        $category_id = get_option('event_selected_union_sponsors_id');
		$category_name_id = apply_filters('wpml_object_id', $category_id, 'category', true, $lang);
		$category_name = get_cat_name($category_name_id);
    }

    // Add taxonomy query if category_id parameter is provided
    if (!empty($category_id)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'category',
            'field' => 'term_id',
            'terms' => $category_id,
        );
    }
	

	

    // Add language query if lang parameter is provided
    if (!empty($lang)) {
        $args['lang'] = $lang;
    }

    // Query posts
    $query = new WP_Query($args);

    // Prepare data
    $posts = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            // Get categories for the post
            $categories = get_the_category($post_id);
            $category_names = wp_list_pluck($categories, 'name');
            // Prepare post data
            $post_data = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'content' => get_the_content(),
                'excerpt' => get_the_excerpt(),
                'image' => (has_post_thumbnail()) ? get_the_post_thumbnail_url() : '', // Check if post has thumbnail
                'categories' => $category_names,
                'date' => get_the_date('d M Y'),
                'day' => get_the_date('d'),
                'month' => get_the_date('M'),
                'year' => get_the_date('Y'),
            );
            $posts[] = $post_data;
        }
        wp_reset_postdata();
    }

    // Get total post count for the specified language
    $total_posts = $query->found_posts;

    // Calculate total pages based on posts_per_page
    $total_pages = ($posts_per_page === -1) ? 1 : ceil($total_posts / $posts_per_page);

    // Add pagination information to the response
    $pagination = array(
        'total_posts' => $total_posts,
        'total_pages' => $total_pages,
    );

    // Add navigation URLs with lang parameter
    if ($total_pages > 1) {
        if ($page > 1) {
            $pagination['prev_page_url'] = get_rest_url(null, "/handball/v1/posts?page=" . ($page - 1) . "&lang=" . $lang . "&posts_per_page=" . $posts_per_page);
        }

        if ($page < $total_pages) {
            $pagination['next_page_url'] = get_rest_url(null, "/handball/v1/posts?page=" . ($page + 1) . "&lang=" . $lang . "&posts_per_page=" . $posts_per_page);
        }
    }

	// Return data along with pagination information
	$return_data = array('posts' => $posts, 'pagination' => $pagination);

	// Add category_name to the return array if it is not empty
	if (!empty($category_name)) {
		$return_data['category_name'] = $category_name;
	}

	return $return_data;
}
