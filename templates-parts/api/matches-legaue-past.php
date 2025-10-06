<?php
// Get matches API endpoint
function get_matches_legaue_past_endpoint() {
    register_rest_route('handball/v1', '/matches-legaue-past/', array(
        'methods'  => 'GET',
        'callback' => 'get_matches_past_legaue',
        'args'     => array(
			'lang' => array(
				'validate_callback' => function($param, $request, $key) {
					// You may want to perform additional validation for the 'lang' parameter
					return is_string($param) && !empty($param);
				},
			),
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    // You may want to perform additional validation for the 'id' parameter
                    return is_numeric($param) && $param > 0;
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'get_matches_legaue_past_endpoint');

function get_matches_past_legaue($data) {
    // Get parameters
    $lang = isset($data['lang']) ? $data['lang'] : 'en';
    $season = get_option('app_season');
	$id = isset($data['id']) ? $data['id'] : null;
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
	
    // Add taxonomy query for 'sp_league' if id parameter is provided
    if ($id) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sp_league',
            'field'    => 'term_id',
            'terms'    => $id,
        );
    }

    // Set the WPML language for the query
    if (function_exists('wpml_get_language')) {
        $args['lang'] = $lang;
    }

	$query = new WP_Query($args);
	$events = $query->posts;

    // Initialize an array to store leagues
    $leagues_array = array();

    foreach ($events as $event) {
		
        // Get specific post meta 'sp_day'
        $sp_day = get_post_meta($event->ID, 'sp_day', true);

        // Get custom taxonomy 'sp_venue'
        $venue = wp_get_post_terms($event->ID, 'sp_venue', array('fields' => 'names'));

        // Get custom taxonomy 'sp_league'
        $leagues = wp_get_post_terms($event->ID, 'sp_league', array('fields' => 'all'));
        // Check if the 'id' parameter is provided and matches the league ID
//         if ($id && !in_array($id, wp_list_pluck($leagues, 'term_id'))) {
//             // Skip to the next event if the league ID does not match
//             continue;
//         }

        // Loop through each league
        foreach ($leagues as $league) {
            // If the league is not already a key in the leagues array, initialize it
            if (!isset($leagues_array[$league->term_id])) {
                $leagues_array[$league->term_id] = array(
                    'id'      => $league->term_id,
                    'name'    => $league->name,
                    'matches' => array(),
                );
            }

            // Get team IDs
            $team_ids = get_post_meta($event->ID)['sp_team'];
			
            // Initialize an array to store team data
            $team_data = array();

            // Results
            $results = get_post_meta($event->ID, 'sp_results', true);

            // Get tables associated with the league
            $tables = get_posts(array(
                'post_type'      => 'sp_table',
                'posts_per_page' => -1,  // Retrieve all tables
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'sp_league',
                        'field'    => 'term_id',
                        'terms'    => $league->term_id,
                    ),
                ),
            ));			
			
            // Loop through each team ID
            foreach ($team_ids as $index => $team_id) {
                // Get team post data with translation support
				$team_id = !empty($team_id) ? (int) $team_id : null;
                $team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true, $lang);

                $resultMatch = $results[$team_id];

				$resultMatch = !empty($results[$team_id]) ? $results[$team_id] : null;
								
                // Check if the team post exists
                if ($team_post) {
                    // Get team title
                    $team_title = get_the_title($team_post);

                    // Get team image URL (assuming the image is stored as a featured image)
                    $team_image = get_the_post_thumbnail_url($team_post, 'full'); // Change 'full' to the desired image size

                    // Check if the team has a featured image
                    $team_image = $team_image ? $team_image : ''; // Set to an empty string if no featured image

                    // Add team data to the array with ordinal index
                    $team_type = ($index == 0) ? 'first_team' : 'second_team';
                    $team_data[$team_type] = array(
                        'id'      => $team_id,
                        'title'   => $team_title,
                        'image'   => $team_image,
						'results' => array(
							'firsthalf'  => isset($resultMatch['firsthalf']) ? strval($resultMatch['firsthalf']) : '',
							'secondhalf' => isset($resultMatch['secondhalf']) ? strval($resultMatch['secondhalf']) : '',
							'goals'      => isset($resultMatch['goals']) ? strval($resultMatch['goals']) : '',
							'outcome' => isset($resultMatch['outcome']) ? strval($resultMatch['outcome']) : '',
						),

						
                    );
                }
            }

            // Split the date and time
            $date_time = explode(' ', $event->post_date);
            $date      = $date_time[0];
            $time      = $date_time[1];

			if ($skipEvent) {
				continue;
			}
			
            // Add match data to the league's matches array
			$leagues_array[$league->term_id]['matches'][] = array(
				'id'       => $event->ID,
				'title'    => $event->post_title,
				'date'     => $date,
				'time'     => $time,
				'status'   => get_post_meta($event->ID,'match_live', true),
				'week'     => !empty($sp_day) ? $sp_day : null,
				'venue'    => !empty($venue) ? $venue : null,
				'teams'    => !empty($team_data) ? $team_data : null,
			);
        }
    }

    // Return the array of leagues with matches
    return rest_ensure_response(array(
        'leagues' => array_values($leagues_array), // Convert associative array to indexed array
    ));
}
