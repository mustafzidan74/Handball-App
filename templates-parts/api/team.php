<?php

// Add the custom API endpoint
add_action('rest_api_init', 'custom_team_api_endpoint');

function custom_team_api_endpoint() {
    register_rest_route('handball/v1', '/teams', array(
        'methods'  => 'GET',
        'callback' => 'get_team_posts',
        'args'     => array(
            'posts_per_page' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
            ),
            'page' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
            ),
            'lang' => array(
                'validate_callback' => function ($param, $request, $key) {
                    // Allow only 'ar' (Arabic) or 'en' (English)
                    return in_array($param, array('ar', 'en'));
                },
            ),
        ),
    ));
}

// Callback function for the custom API endpoint
function get_team_posts($data) {
    // Get the posts_per_page parameter from the request
    $posts_per_page = isset($data['posts_per_page']) ? intval($data['posts_per_page']) : -1;

    // Get the current page parameter from the request
    $current_page = isset($data['page']) ? intval($data['page']) : 1;

    // Get the lang parameter from the request
    $lang = isset($data['lang']) ? sanitize_text_field($data['lang']) : 'en';

	// Query to retrieve team posts
	$args = array(
		'post_type'      => 'sp_team',
		'posts_per_page' => $posts_per_page,
		'paged'          => $current_page,
		'meta_query'     => array(
			array(
				'key'   => 'view_mobile',
				'value' => 'checked',
				'compare' => '=',
			),
		),
	);


    // Language-specific query for WPML
    if (function_exists('icl_object_id')) {
        $args['lang'] = $lang;
    }

    $query = new WP_Query($args);

    // Initialize an empty array to store the results
    $results = array();

    // Loop through the query results
    while ($query->have_posts()) {
        $query->the_post();

        // Get post title and image
        $post_data = array(
            'id' => get_the_ID(),
            'title' => get_the_title(),
			'image' => (has_post_thumbnail()) ? get_the_post_thumbnail_url() : '',
			'order'         => get_post_field('menu_order', get_the_ID()),
			'view_mobile' => get_post_meta(get_the_ID(), 'view_mobile')[0],
		);

        // Add post data to the results array
        $results[] = $post_data;
    }
	
    // Sort results by 'order'
	usort($results, function ($a, $b) {
		// If 'order' is empty for both items, compare by name
		if (empty($a['order']) && empty($b['order'])) {
			return strcmp($a['title'], $b['title']);
		}

		// If 'order' is empty for one item, prioritize the one with non-empty 'order'
		if (empty($a['order'])) {
			return 1;
		}

		if (empty($b['order'])) {
			return -1;
		}

		// Compare 'order' numerically
		return $a['order'] - $b['order'];
	});


    // Reset post data
    wp_reset_postdata();

    // Get total post count for the team
    $total_posts = $query->found_posts;

    // Calculate total pages based on posts_per_page
    $total_pages = $total_posts > 0 ? ceil($total_posts / $posts_per_page) : 1;

    // Ensure total pages is not negative
    $total_pages = max($total_pages, 1);

    // Construct pagination data
    $pagination = array(
        'total_posts' => $total_posts,
        'total_pages' => $total_pages,
    );

    // Add navigation URLs if posts_per_page is set
    if ($posts_per_page > 0) {
        $current_page = isset($data['page']) ? absint($data['page']) : 1;

        // Initialize the pagination URLs
        $pagination['prev_page_url'] = null;
        $pagination['next_page_url'] = null;

        // Set the prev_page_url if the current page is greater than 1
        if ($current_page > 1) {
            $pagination['prev_page_url'] = get_rest_url(null, "/handball/v1/team?posts_per_page={$posts_per_page}&page=" . ($current_page - 1) . "&lang={$lang}");
        }

        // Set the next_page_url if the current page is less than the total pages
        if ($current_page < $total_pages) {
            $pagination['next_page_url'] = get_rest_url(null, "/handball/v1/team?posts_per_page={$posts_per_page}&page=" . ($current_page + 1) . "&lang={$lang}");
        }
    }

    // Return the results and pagination data as JSON
    return rest_ensure_response(array('results' => $results, 'pagination' => $pagination));
}
