<?php
// Get leagues API endpoint
function get_leagues_endpoint() {
    register_rest_route('handball/v1', '/league/', array(
        'methods'  => 'GET',
        'callback' => 'get_leagues',
        'args'     => array(
            'lang' => array(
                'validate_callback' => function ($param, $request, $key) {
                    // Validate the language parameter if needed
                    // You may need to adjust this validation based on your specific requirements
                    return is_string($param) && !empty($param);
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'get_leagues_endpoint');

function get_leagues($data) {
    $leagues_array = array();

    // Get all terms from the 'sp_league' taxonomy
    $terms = get_terms(array(
        'taxonomy'   => 'sp_league',
        'hide_empty' => false,
    ));

    $language = sanitize_text_field($data['lang']);

    if ($terms && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            // Check if the term has a translation for the specified language
            $translated_name = apply_filters('wpml_translate_single_string', $term->name, 'taxonomy', 'sp_league_' . $term->term_id, $language);
			
            $leagues_array[$term->term_id] = array(
                'id'   => $term->term_id,
                'name' => $translated_name,
                'order' => $result->sp_order ? (int) $result->sp_order : 1000,
            );
        }
    }

	// Custom sorting based on the 'order' value or by name
	usort($leagues_array, function ($a, $b) {
		// If 'order' is not present for both items, compare by name
		if (!isset($a['order']) && !isset($b['order'])) {
			return strcmp($a['name'], $b['name']);
		}

		// If 'order' is not present for one item, prioritize the one with 'order'
		if (!isset($a['order'])) {
			return 1;
		}

		if (!isset($b['order'])) {
			return -1;
		}

		// Compare 'order' numerically
		return $a['order'] - $b['order'];
	});
	
	
    $response = array('leagues' => array_values($leagues_array));

    return rest_ensure_response($response);
}
