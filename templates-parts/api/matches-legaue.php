<?php
// Get matches API endpoint
function get_matches_legaue_endpoint() {
    register_rest_route('handball/v1', '/matches-legaue/', array(
        'methods'  => 'GET',
        'callback' => 'get_matches_legaue',
        'args'     => array(
            'lang' => array(
                'validate_callback' => function($param, $request, $key) {
                    // You may want to perform additional validation for the 'lang' parameter
                    return is_string($param) && !empty($param);
                },
            ),
        ),
    ));
}

add_action('rest_api_init', 'get_matches_legaue_endpoint');

function get_matches_legaue($data) {
    // Get parameters
    $posts_per_page = isset($data['posts_per_page']) ? $data['posts_per_page'] : -1;
    $lang = isset($data['lang']) ? $data['lang'] : 'en';
    $season = get_option('app_season');

    // Initialize data field in response
    $response_data = array();

    // Date query for today's posts
    $today_args = array(
        'post_type'      => 'sp_event',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'post_status'    => array('publish', 'future'),
        'date_query'     => array(
            array(
                'after'     => date('Y-m-d 00:00:00'),
                'before'    => date('Y-m-d 23:59:59'),
                'inclusive' => true,
            ),
        ),
    );

    $today_query = new WP_Query($today_args);
    $today_events = $today_query->posts;

    // Check if today has posts
    if (!empty($today_events)) {
        // Add today's data to the response
        $response_data['data'] = 'one_day';
        $response_data['matches'] = get_matches_data($today_events, $lang);
    } else {
        // Date query for 1 week's posts
        $week_args = array(
            'post_type'      => 'sp_event',
            'posts_per_page' => $posts_per_page,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => array('publish', 'future'),
            'date_query'     => array(
                array(
                    'after'     => date('Y-m-d 00:00:00'),
                    'before'    => date('Y-m-d 23:59:59', strtotime('+1 week')),
                    'inclusive' => true,
                ),
            ),
        );

        $week_query = new WP_Query($week_args);
        $week_events = $week_query->posts;

        // Check if 1 week has posts
        if (!empty($week_events)) {
            // Add 1 week's data to the response
            $response_data['data'] = 'one_week';
            $response_data['matches'] = get_matches_data($week_events, $lang);
        } else {
            // Date query for 2 weeks' posts
            $two_week_args = array(
                'post_type'      => 'sp_event',
                'posts_per_page' => $posts_per_page,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'post_status'    => array('publish', 'future'),
                'date_query'     => array(
                    array(
                        'after'     => date('Y-m-d 00:00:00'),
                        'before'    => date('Y-m-d 23:59:59', strtotime('+2 weeks')),
                        'inclusive' => true,
                    ),
                ),
            );

            $two_week_query = new WP_Query($two_week_args);
            $two_week_events = $two_week_query->posts;

            // Check if 2 weeks have posts
            if (!empty($two_week_events)) {
                // Add 2 weeks' data to the response
                $response_data['data'] = 'two_week';
                $response_data['matches'] = get_matches_data($two_week_events, $lang);
            } else {
                // No matches found
                $response_data['data'] = 'no_have_matches';
                $response_data['matches'] = array();
            }
        }
    }

    // Return the response data
    return rest_ensure_response($response_data);
}

// Helper function to extract match data
function get_matches_data($events, $lang) {
    // Initialize an array to store leagues
    $leagues_array = array();

    foreach ($events as $event) {
        // Get specific post meta 'sp_day'
        $sp_day = get_post_meta($event->ID, 'sp_day', true);

        // Get custom taxonomy 'sp_venue'
        $venue = wp_get_post_terms($event->ID, 'sp_venue', array('fields' => 'names'));

        // Get custom taxonomy 'sp_league'
        $leagues = wp_get_post_terms($event->ID, 'sp_league', array('fields' => 'all'));

        // Loop through each league
        foreach ($leagues as $league) {
            // If the league is not already a key in the leagues array, initialize it
            if (!isset($leagues_array[$league->term_id])) {
                $leagues_array[$league->term_id] = array(
                    'id'      => $league->term_id,
                    'name'    => $league->name,
					'order' => get_term_meta($league->term_id, 'sp_order', true) ? (int) get_term_meta($league->term_id, 'sp_order', true) : 1000,
                    'matches' => array(),
                );
            }

            // Get team IDs
            $team_ids = get_post_meta($event->ID)['sp_team'];

            // Initialize an array to store team data
            $team_data = array();

            // Results
            $results = get_post_meta($event->ID, 'sp_results', true);

            // Loop through each team ID
            foreach ($team_ids as $index => $team_id) {
                // Get team post data with translation support
                $team_id = !empty($team_id) ? (int) $team_id : null;
                $team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true, $lang);

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
                            'outcome'    => isset($resultMatch['outcome']) ?
                            (is_array($resultMatch['outcome']) ?
                                strval(current($resultMatch['outcome'])) :
                                strval($resultMatch['outcome'])
                            ) : '',
                        ),
                    );
                }
            }

            // Split the date and time
            $date_time = explode(' ', $event->post_date);
            $date      = $date_time[0];
            $time      = $date_time[1];

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

    // Return the match data
    return array(
        'leagues' => array_values($leagues_array), // Convert associative array to indexed array
    );
}
