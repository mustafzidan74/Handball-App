<?php
// Register REST API routes
add_action('rest_api_init', 'register_custom_api_endpoints');

function register_custom_api_endpoints() {
    // Get team results endpoint
    register_rest_route('handball/v1', '/update-match/', array(
        'methods'  => 'GET',
        'callback' => 'get_match_results',
        'args'     => array(
            'match_id' => array(
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

// Callback function for getting team results
function get_match_results($data) {
	global $wpdb;

    $match_id = $data['match_id'];

    // Set the WPML language for the query
    if (function_exists('wpml_get_language')) {
        $args['lang'] = $lang;
    }	
	
    // Use $wpdb to perform a custom SQL query
    $results = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta 
             WHERE post_id = %d AND meta_key = %s",
            $match_id,
            'sp_results'
        )
    );

    $results = !empty($results) ? maybe_unserialize($results) : array();


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
// 			$team_title = get_the_title($team_post);

// 			// Get team image URL (assuming the image is stored as a featured image)
// 			$team_image = get_the_post_thumbnail_url($team_post, 'thumbnail'); // Change 'full' to the desired image size

// 			// Check if the team has a featured image
// 			$team_image = $team_image ? $team_image : '';

			// Add team data to the array with ordinal index
			$team_type = ($index == 0) ? 'first_team' : 'second_team';

			$team_data[$team_type] = array(
				'id' => $team_id,
// 				'title' => $team_title,
// 				'image' => $team_image,
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


	
    return rest_ensure_response(array(
        'match_id' => $team_id,
        'results' => !empty($team_data) ? $team_data : null,
    ));
}
