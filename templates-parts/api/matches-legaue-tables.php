<?php
// Get league tables API endpoint
function get_league_tables_endpoint() {
    register_rest_route('handball/v1', '/league-tables/', array(
        'methods'  => 'GET',
        'callback' => 'get_league_tables',
        'args'     => array(
//             'season' => array(
//                 'validate_callback' => function ($param, $request, $key) {
//                     return is_string($param) && !empty($param);
//                 },
//             ),
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'get_league_tables_endpoint');

function get_league_tables($data) {
    // Get parameters
    $lang = isset($data['lang']) ? $data['lang'] : 'en';
    $table_id = isset($data['id']) ? (int) $data['id'] : null;
    $season = isset($data['season']) ? $data['season'] : null;

    // Query args
    $args = array(
        'post_type'      => 'sp_table',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => array(), // Initialize tax_query array
    );

    // Set the WPML language for the query
    if (function_exists('wpml_get_language')) {
        $args['lang'] = $lang;
    }

    // Add tax query for 'sp_season'
    if ($season) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sp_season',
            'terms'    => $season,
            'field'    => 'term_id',
        );
    }

    // Add tax query for 'sp_league'
    if ($table_id) {
        $args['tax_query'][] = array(
            'taxonomy' => 'sp_league',
            'terms'    => $table_id,
            'field'    => 'term_id',
        );
    }

    $tables = get_posts($args);

    // Initialize an array to store tables data
    $tables_data = array();

    foreach ($tables as $table) {
        // Get table title
        $table_title = get_the_title($table->ID);

        // Get team IDs for this table
        $table_teams = get_post_meta($table->ID)['sp_team'];

        // Initialize an array to store table data
        $teams_data = array();

        // Loop through each team ID
        foreach ($table_teams as $team_id) {
            // Get team post data with translation support
            $team_id = !empty($team_id) ? (int) $team_id : null;
            $team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true, $lang);

            // Check if the team post exists
            if ($team_post) {
                // Get team title
                $team_title = get_the_title($team_post);

                // Get table stats for this team
                $table_stats = (array) get_post_meta($table->ID, 'sp_teams', true);

                // Transform $table_stats[$team_id] to the desired structure
                $transformed_table_stats = array(
                    'games'           => (int) $table_stats[$team_id]['twofourzerosix'],
                    'win'             => (int) $table_stats[$team_id]['w'],
                    'draw'            => (int) $table_stats[$team_id]['twofourzeroeight'],
                    'lose'            => (int) $table_stats[$team_id]['twofourzeronine'],
                    'has_goals'       => (int) $table_stats[$team_id]['twofourten'],
                    'Goals_on_him'    => (int) $table_stats[$team_id]['twofouroneone'],
                    'Goal_difference' => (int) $table_stats[$team_id]['twofouronetwo'],
                    'points'          => (int) $table_stats[$team_id]['twofouronethree'],
                    'order' => 			0
// Initialize order to 0
                );

				$team_position = get_team_position($team_id, $table->ID);
                // Add team data to the array with table stats
                $teams_data[] = array(
                    'id'           => $team_id,
                    'title'        => $team_title,
                    'image'        => (has_post_thumbnail($team_id)) ? get_the_post_thumbnail_url($team_id) : '',
                    'table_stats'  => array_merge($transformed_table_stats, array('order' => $team_position)),
                );
            }
        }

		// Sort $teams_data by 'order' key
		usort($teams_data, function ($a, $b) {
			return $a['table_stats']['order'] - $b['table_stats']['order'];
		});
		
        // Add teams data to the array
        $tables_data[] = array(
            'id'           => $table->ID,
            'title'        => $table_title,
			'table_order' => get_post_meta($table->ID)['sp_order'],
			'table_sp_orderby' => get_post_meta($table->ID)['sp_orderby'],
            'teams'        => $teams_data,
        );
    }

    // Return the array of league tables
    return rest_ensure_response(array(
        'tables' => $tables_data,
    ));
}

function get_team_position($team_id, $table_id) {
    // Create an instance of the league table.
    $table = new SP_League_Table($table_id);

    // Get the data for the league table.
    $data = $table->data();

    // Loop through the teams to find the specified team.
    foreach ($data as $team_key => $team_data) {
        if ($team_key == $team_id) {
            // Return the 'pos' value for the specified team.
            return sp_array_value($team_data, 'pos', '');
        }
    }

    // Return an empty string if the team ID is not found.
    return '';
}
