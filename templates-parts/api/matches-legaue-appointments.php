<?php
// Get matches API endpoint
function get_matches_legaue_appointments_endpoint() {
    register_rest_route('handball/v1', '/matches-league-appointments/', array(
        'methods'  => 'GET',
        'callback' => 'get_matches_appointments_legaue',
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
add_action('rest_api_init', 'get_matches_legaue_appointments_endpoint');


function get_matches_appointments_legaue($data) {
    // Get parameters
    $lang = isset($data['lang']) ? $data['lang'] : 'en';
    $season = get_option('app_season');
	$id = isset($data['id']) ? $data['id'] : null;
	
    // Query args
    $args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'post_status'    => array('publish', 'future'),
        'tax_query'      => array(),
// 		'meta_query' => array(
// 			'relation' => 'OR',
// 			array(
// 				'key'     => 'sp_results',
// 				'compare' => 'NOT EXISTS',
// 			),
// 			array(
// 				'key'     => 'sp_results',
// 				'value'   => ':"goals";s:1:"0";',
// 				'compare' => 'LIKE',
// 			),
// 		),
        'date_query'     => array(
            array(
                'after'     => date('Y-m-d'), // Get posts after the current date
                'inclusive' => true,
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
	
    // Initialize an array to store leagues with matches
    $leagues_with_matches = array();

    foreach ($events as $event) {
        // Get specific post meta 'sp_day'
        $sp_day = get_post_meta($event->ID, 'sp_day', true);
        // Get custom taxonomy 'sp_venue'
        $venue = wp_get_post_terms($event->ID, 'sp_venue', array('fields' => 'names'));
        // Get custom taxonomy 'sp_league'
        $leagues = wp_get_post_terms($event->ID, 'sp_league', array('fields' => 'all'));

        // Compare event date with current date
        $event_date = date('Y-m-d', strtotime($event->post_date));
        // Loop through each league
        foreach ($leagues as $league) {
            // If the league is not already a key in the leagues array, initialize it
            if (!isset($leagues_array[$league->term_id])) {
                $leagues_array[$league->term_id] = array(
                    'id'    => $league->term_id,
                    'name'  => $league->name,
                    'dates' => array(),
                );
            }

            // Split the date and time
            $date_time = explode(' ', $event->post_date);
            $date      = $date_time[0];

            // Check if the date already exists in the league's dates array
            $date_index = array_search($date, array_column($leagues_array[$league->term_id]['dates'], 'date'));

            // If the date doesn't exist, add it to the league's dates array
            if ($date_index === false) {
                $leagues_array[$league->term_id]['dates'][] = array(
                    'date'    => $date,
                    'day'     => date('d', strtotime($date)),
                    'day_name' => date('l', strtotime($date)),
                    'month'   => date('m', strtotime($date)),
                    'month_name' => date('F', strtotime($date)),
                    'year'    => date('Y', strtotime($date)),
                    'matches' => array(),
                );
                $date_index = count($leagues_array[$league->term_id]['dates']) - 1;
            }
        // Get team data and resultMatch from get_teams_data function
        $teams_data_and_result = get_teams_data($event->ID, $lang);
        $team_data = $teams_data_and_result['team_data'];
        $resultMatch = $teams_data_and_result['resultMatch'];

            // Add match data to the league's dates array
//             if ($resultMatch === null || $resultMatch['goals'] == 0) {
                $time      = $date_time[1];
                $match_data = array(
                    'id'       => $event->ID,
                    'title'    => $event->post_title,
                    'date'     => $date,
                    'time'     => $time,
                    'status'   => get_post_meta($event->ID,'match_live', true),
                    'week'     => !empty($sp_day) ? $sp_day : null,
                    'venue'    => !empty($venue) ? $venue : null,
                    'teams'    => $team_data,
                    'resultMatch' => $resultMatch, // Add resultMatch to the match data
                );

                // Check if the league is not already in the array of leagues with matches
                if (!isset($leagues_with_matches[$league->term_id])) {
                    $leagues_with_matches[$league->term_id] = array(
                        'id'    => $league->term_id,
                        'name'  => $league->name,
                        'dates' => array(),
                    );
                }

                // Check if the date exists in the league's dates array
                $date_index = array_search($date, array_column($leagues_with_matches[$league->term_id]['dates'], 'date'));

                // If the date doesn't exist, add it to the league's dates array
                if ($date_index === false) {
                    $leagues_with_matches[$league->term_id]['dates'][] = array(
                        'date'    => $date,
                        'day'     => date('d', strtotime($date)),
                        'day_name' => date('l', strtotime($date)),
                        'month'   => date('m', strtotime($date)),
                        'month_name' => date('F', strtotime($date)),
                        'year'    => date('Y', strtotime($date)),
                        'matches' => array($match_data),
                    );
                } else {
                    // Add match data to the existing date in the league's dates array
                    $leagues_with_matches[$league->term_id]['dates'][$date_index]['matches'][] = $match_data;
                }
//             }
        }
    }

    // Remove leagues without matches
    $leagues_with_matches = array_filter($leagues_with_matches, function($league) {
        return !empty($league['dates']);
    });

    // Return the array of leagues with matches
    return rest_ensure_response(array(
        'leagues' => array_values($leagues_with_matches), // Convert associative array to indexed array
    ));
}

function get_teams_data($event_id, $lang) {
    // Get team IDs
    $team_ids = get_post_meta($event_id)['sp_team'];

    // Initialize an array to store team data
    $team_data = array();

    // Results
    $results = get_post_meta($event_id, 'sp_results', true);

    // Initialize resultMatch
    $resultMatch = null;

    // Loop through each team ID
    foreach ($team_ids as $index => $team_id) {
        // Get team post data with translation support
        $team_id = !empty($team_id) ? (int)$team_id : null;
        $team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true, $lang);

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
            );

            // Set resultMatch if available
            if (!empty($results[$team_id])) {
                $resultMatch = $results[$team_id];
            }
        }
    }

    // Return both team data and resultMatch
    return array('team_data' => $team_data, 'resultMatch' => $resultMatch);
}
