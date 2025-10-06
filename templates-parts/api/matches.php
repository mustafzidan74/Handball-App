<?php
// Get matches API endpoint
function get_matches_endpoint() {
    register_rest_route('handball/v1', '/matches/', array(
        'methods'  => 'GET',
        'callback' => 'get_matches',
        'args'     => array(
            'posts_per_page' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param) && $param > 0; // Ensure it's a positive number
                },
            ),
            'lang' => array(
                'validate_callback' => function($param, $request, $key) {
                    // You may want to perform additional validation for the 'lang' parameter
                    return is_string($param) && !empty($param);
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'get_matches_endpoint');

function get_matches($data) {
    // Get parameters
    $posts_per_page = isset($data['posts_per_page']) ? $data['posts_per_page'] : -1;
    $lang = isset($data['lang']) ? $data['lang'] : 'en';
    $season = get_option('app_season');
	
    // Calculate offset based on the page number
    $offset = ($page - 1) * $posts_per_page;

    // Current time
    $yesterday = date('Y-m-d');

    // Query args
    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => $posts_per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => array('publish', 'future'),
        'tax_query'      => array(),
		'date_query'     => array(
			'before' => $yesterday, // Filter events before the current date
		),
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key' => 'sp_results',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key' => 'sp_results',
				'value' => ':"goals";s:1:"0";',
				'compare' => 'NOT LIKE',
			),

		),

    );

    // Add taxonomy query for 'sp_season' if season parameter is provided
    if ($season) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sp_season',
            'field'    => 'term_id',
            'terms'    => $season,
        );
    }

    // Set the WPML language for the query
    if (function_exists('wpml_get_language')) {
        $args['lang'] = $lang;
    }

	$query = new WP_Query($args);
	$events = $query->posts;

    $formatted_events = array();

    foreach ($events as $event) {
        // Get specific post meta 'sp_day'
        $sp_day = get_post_meta($event->ID, 'sp_day', true);

        // Get custom taxonomy 'sp_venue'
        $venue = wp_get_post_terms($event->ID, 'sp_venue', array('fields' => 'names'));

        // Get custom taxonomy 'sp_league'
        $leagues = wp_get_post_terms($event->ID, 'sp_league', array('fields' => 'names'));

        // Results
        $results = get_post_meta($event->ID, 'sp_results', true);

        // Initialize arrays to store team data
        $team_data = array();

        // Get team IDs
        $team_ids = !empty($results) ? array_keys($results) : array();

        // Loop through each team ID
        foreach ($team_ids as $index => $team_id) {
            $team_id = !empty($team_id) ? (int) $team_id : null;

            // Get team post data with translation support
            $team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true, $lang);

            $resultMatch = !empty($results[$team_id]) ? $results[$team_id] : null;

            // Check if the team post exists
            if ($team_post) {
                // Get team title
                $team_title = get_the_title($team_post);

                // Get team image URL (assuming the image is stored as a featured image)
                $team_image = get_the_post_thumbnail_url($team_post, 'thumbnail'); // Change 'full' to the desired image size

                // Check if the team has a featured image
                $team_image = $team_image ? $team_image : '';

                // Add team data to the array with ordinal index
                $team_type = ($index == 0) ? 'first_team' : 'second_team';

                $team_data[$team_type] = array(
                    'id' => $team_id,
                    'title' => $team_title,
                    'image' => $team_image,
						'results' => array(
							'firsthalf'  => isset($resultMatch['firsthalf']) ? strval($resultMatch['firsthalf']) : '',
							'secondhalf' => isset($resultMatch['secondhalf']) ? strval($resultMatch['secondhalf']) : '',
							'goals'      => isset($resultMatch['goals']) ? strval($resultMatch['goals']) : '',
							'outcome'    => isset($resultMatch['outcome']) ? 
                        (is_array($resultMatch['outcome']) ? 
                            strval(current($resultMatch['outcome'])) : 
                            strval($resultMatch['outcome'])
                        ) : '',

						),
                );
            }
        }

        // Get post date and time
        $post_date = $event->post_date;

        // Split the date and time
        $date_time = explode(' ', $post_date);
        $date = $date_time[0];
        $time = $date_time[1];

        // Get custom taxonomy 'sp_league'
        $league_terms = wp_get_post_terms($event->ID, 'sp_league', array('fields' => 'ids'));

        // Check if there are league terms assigned
        $league_id = !empty($league_terms) ? $league_terms[0] : null;
		
        $formatted_events[] = array(
            'id' => $event->ID,
            'title' => $event->post_title,
            'date' => $date,
            'time' => $time,
            'status' => get_post_meta($event->ID,'match_live', true),
            'week' => !empty($sp_day) ? $sp_day : null,
            'venue' => !empty($venue) ? $venue : null,
            'teams' => !empty($team_data) ? $team_data : null,
            'leagues' => !empty($leagues) ? $leagues : null,
			'league_id' => $league_id, // Add league_id
        );
    }

    // Count of formatted events
    $formatted_events_count = count($formatted_events);

    return rest_ensure_response(array(
        'matches' => $formatted_events,
        'matches_count' => $formatted_events_count,
    ));
}
