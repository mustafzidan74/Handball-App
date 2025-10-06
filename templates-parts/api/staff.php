<?php


add_action('rest_api_init', 'custom_api_endpoint');

function custom_api_endpoint() {
	register_rest_route('handball/v1', '/staff/', array(
		'methods'  => 'GET',
		'callback' => 'get_staff_by_role',
		'args'     => array(
			'role' => array(
				'validate_callback' => function ($param, $request, $key) {
					return is_numeric($param) || (is_string($param) && !empty($param));
				},
			),
			'team_id' => array(
				'validate_callback' => function ($param, $request, $key) {
					return is_numeric($param) && $param > 0;
				},
			),
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
function get_staff_by_role($data) {

    // Get the posts_per_page parameter from the request
    $posts_per_page = isset($data['posts_per_page']) ? intval($data['posts_per_page']) : -1;

    // Get the current page parameter from the request
    $current_page = isset($data['page']) ? intval($data['page']) : 1;

    // Get the lang parameter from the request
    $lang = isset($data['lang']) ? sanitize_text_field($data['lang']) : 'en';

    // Query to retrieve posts based on the custom taxonomy if a role is provided
    $args = array(
        'post_type'      => 'sp_staff',
        'posts_per_page' => $posts_per_page,
        'paged'          => $current_page,
    );
	
	
    // Get the team_id parameter from the request
    $team_id = isset($data['team_id']) ? intval($data['team_id']) : 0;

    // Add meta query for sp_team
    if ($team_id) {
        $args['meta_query'][] = array(
            'key'     => 'sp_team',
            'value'   => $team_id,
            'compare' => '=',
        );
    }

	$roles_array = array('coach', 'date_of_founding_of_the_club', 'referee', 'club_president', 'club_headquarters');

	// Check if $data['role'] matches any item in $roles_array
	if (!in_array($data['role'], $roles_array)) {
		// If not, set $role_id based on $data['role']
		$role_id = isset($data['role']) ? intval($data['role']) : '';
	} else {
		$role_id = get_option('event_selected_' . $data['role'] . '_id');
	} 

	// Add taxonomy query only if $role_id is provided
	if ($role_id) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'sp_role',
				'field'    => 'id', // Use 'id' instead of 'slug'
				'terms'    => $role_id,
			),
		);
	}
	
    // Add taxonomy query for sp_season
//     $season = get_option('app_season');

//     if ($season) {
//         $args['tax_query'][] = array(
//             'taxonomy' => 'sp_season',
//             'field'    => 'term_id',
//             'terms'    => $season,
//         );
//     }




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
        );
		
		if ($team_id) {
			// Get the terms for the 'sp_role' taxonomy
			$role_terms = wp_get_post_terms(get_the_ID(), 'sp_role');

			// Extract term names from the terms array
			foreach ($role_terms as $role_term) {
				$post_data['role_names'] = $role_term->name;
			}
		}
		
        // Add post data to the results array
        $results[] = $post_data;
    }

    // Reset post data
    wp_reset_postdata();

    // Get total post count for the given role
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
            $pagination['prev_page_url'] = get_rest_url(null, "/handball/v1/staff?posts_per_page={$posts_per_page}&page=" . ($current_page - 1) . "&lang={$lang}");
        }

        // Set the next_page_url if the current page is less than the total pages
        if ($current_page < $total_pages) {
            $pagination['next_page_url'] = get_rest_url(null, "/handball/v1/staff?posts_per_page={$posts_per_page}&page=" . ($current_page + 1) . "&lang={$lang}");
        }
    }

    // Return the results and pagination data as JSON
    return rest_ensure_response(array('results' => $results, 'pagination' => $pagination));
}
