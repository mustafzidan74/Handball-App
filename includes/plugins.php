<?php
function add_language_based_alert_script() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if it's an admin page
            if (typeof wp !== 'undefined' && wp.hooks && document.location.href.includes('wp-admin')) {
                // Check if it's an allowed admin page
                var currentPage = "<?php echo isset($_GET['page']) ? sanitize_text_field($_GET['page']) : ''; ?>";

                // Check if the URL contains import parameters
                var isImportPage = document.location.href.includes('import=sp_fixture_csv') || document.location.href.includes('import=sp_event_csv');

                // Check if it's a specific step of the import process
                var isImportStep = document.location.href.includes('import=sp_event_csv&step=1&_wpnonce=f89691b38b');

                if (['event-options', 'event-matches', 'hide-options', 'send-notifications', 'events-duplication', 'sp_fixture_csv', 'sp_event_csv'].includes(currentPage) || isImportPage || isImportStep) {
                    // Check the current language using WPML
                    var currentLanguage = "<?php echo defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en'; ?>";

                    // Display language-specific alert
                    if (currentLanguage === 'ar') {
                    } else {
                        // Append the alert HTML to the element with id="wpcontent"
                        var wpcontentElement = document.getElementById('wpcontent');
                        var alertMessage = '"Just a heads up, when you\'re uploading, importing, or applying any content for the mobile app, make sure you\'re in Arabic language mode. You can do this by setting the language to Arabic from the top bar."';
                        if (wpcontentElement) {
                            wpcontentElement.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger" role="alert">' + alertMessage + '</div>');
                        }

                        // Disable all input[type="submit"], input[type="button"], and button elements and add class "not-active"
                        var buttons = document.querySelectorAll('input[type="submit"], input[type="button"], button');
                        buttons.forEach(function(button) {
                            button.setAttribute('disabled', true);
                            button.classList.add('not-active');
                        });
                    }
                }
            }
        });
    </script>
    <?php
}
add_action('admin_footer', 'add_language_based_alert_script');


// Add a new admin menu
function event_options_menu() {
    add_menu_page(
        'App Options',      // Page title
        'App Options',      // Menu title
        'manage_options',     // Capability
        'event-options',      // Menu slug
        'event_options_page',  // Callback function
		''.get_site_url().'/wp-content/uploads/2024/04/apps.png'
    );

    // Add a submenu under the custom admin page
    add_submenu_page(
        'event-options',      // Parent menu slug
        'Display Options',            // Page title
        'Display Options',            // Menu title
        'manage_options',     // Capability
        'hide-options',      // Menu slug
        'hide_options'      // Callback function
    );
    // Add a submenu under the custom admin page
    add_submenu_page(
        'event-options',      // Parent menu slug
        'Results update',            // Page title
        'Results update',            // Menu title
        'manage_options',     // Capability
        'event-matches',      // Menu slug
        'global_matches'      // Callback function
    );
    // Add a submenu under the custom admin page
    add_submenu_page(
        'event-options',      // Parent menu slug
        'Send Notifications',            // Page title
        'Send Notifications',            // Menu title
        'manage_options',     // Capability
        'send-notifications',      // Menu slug
        'send_notifications'      // Callback function
    );
    // Add a submenu under the custom admin page
    add_submenu_page(
        'event-options',      // Parent menu slug
        'Events Duplication',            // Page title
        'Events Duplication',            // Menu title
        'manage_options',     // Capability
        'events-duplication',      // Menu slug
        'events_duplication'      // Callback function
    );
    // Add a submenu under the custom admin page
    add_submenu_page(
        'event-options',      // Parent menu slug
        'Standings Update',            // Page title
        'Standings Update',            // Menu title
        'manage_options',     // Capability
        'standings-update',      // Menu slug
        'standings_update'      // Callback function
    );
	 add_submenu_page(
        'event-options',                          // Parent menu slug
        'Match Meta Translations',                // Page title (in the <title>)
        'Match Translations',                     // Menu title (in the sidebar)
        'manage_options',                         // Capability
        'match-meta-translations',                // Unique slug for this page
        'render_match_translations_page'          // Callback function
    );
}
add_action('admin_menu', 'event_options_menu');
// Callback function for event_options_page
function event_options_page() {
    // Check if the form is submitted
    if (isset($_POST['submit_event_options']) || isset($_POST['submit_options'])) {
		
    // Update champions page meta
    if (isset($_POST['champions'])) {
        $champions_page_url = $_POST['champions'];
        $champions_page_id = url_to_postid($champions_page_url);
        update_post_meta($champions_page_id, '_hide_header', isset($_POST['hide_header_champions']) ? 'on' : 'off');
        update_post_meta($champions_page_id, '_hide_footer', isset($_POST['hide_footer_champions']) ? 'on' : 'off');
    }

    // Update about us page meta
    if (isset($_POST['about_us'])) {
        $about_us_page_url = $_POST['about_us'];
        $about_us_page_id = url_to_postid($about_us_page_url);
        update_post_meta($about_us_page_id, '_hide_header', isset($_POST['hide_header_about_us']) ? 'on' : 'off');
        update_post_meta($about_us_page_id, '_hide_footer', isset($_POST['hide_footer_about_us']) ? 'on' : 'off');
    }

    // Update contact us page meta
    if (isset($_POST['contact_us'])) {
        $contact_us_page_url = $_POST['contact_us'];
        $contact_us_page_id = url_to_postid($contact_us_page_url);
        update_post_meta($contact_us_page_id, '_hide_header', isset($_POST['hide_header_contact_us']) ? 'on' : 'off');
        update_post_meta($contact_us_page_id, '_hide_footer', isset($_POST['hide_footer_contact_us']) ? 'on' : 'off');
    }

    // Update regulations & docs page meta
    if (isset($_POST['regulations'])) {
        $regulations_page_url = $_POST['regulations'];
        $regulations_page_id = url_to_postid($regulations_page_url);
        update_post_meta($regulations_page_id, '_hide_header', isset($_POST['hide_header_regulations']) ? 'on' : 'off');
        update_post_meta($regulations_page_id, '_hide_footer', isset($_POST['hide_footer_regulations']) ? 'on' : 'off');
    }

    // Update national teams page meta
    if (isset($_POST['national'])) {
        $national_page_url = $_POST['national'];
        $national_page_id = url_to_postid($national_page_url);
        update_post_meta($national_page_id, '_hide_header', isset($_POST['hide_header_national']) ? 'on' : 'off');
        update_post_meta($national_page_id, '_hide_footer', isset($_POST['hide_footer_national']) ? 'on' : 'off');
    }
		
		
        // Save the Facebook URL option to WordPress options
        $facebook_url = esc_url($_POST['facebook_url']);
        update_option('facebook_url_option', $facebook_url);

        // Save the X URL option to WordPress options
        $x_url = esc_url($_POST['x_url']);
        update_option('x_url_option', $x_url);

        // Save the Instagram URL option to WordPress options
        $instagram_url = esc_url($_POST['instagram_url']);
        update_option('instagram_url_option', $instagram_url);

        // Save the LinkedIn URL option to WordPress options
        $linkedin_url = esc_url($_POST['linkedin_url']);
        update_option('linkedin_url_option', $linkedin_url);

        // Save the YouTube URL option to WordPress options
        $youtube_url = esc_url($_POST['youtube_url']);
        update_option('youtube_url_option', $youtube_url);
		
		$website_url = esc_url($_POST['website_url']);
		update_option('website_url_option', $website_url);

		$email = sanitize_email($_POST['email']);
		update_option('email_option', $email);

		$tiktok_url = esc_url($_POST['tiktok_url']);
		update_option('tiktok_url_option', $tiktok_url);

    // //////////////////////
    // Update the hidden checkbox state
    update_option('instagram_url_option_view', isset($_POST['instagram_url_view_hidden']) ? intval($_POST['instagram_url_view_hidden']) : 0);

	// Update the Facebook URL option to WordPress options
	$facebook_url_view = isset($_POST['facebook_url_view']) ? 'checked' : ''; // Check if the checkbox is checked
	update_option('facebook_url_option_view', $facebook_url_view);

    // Update the X URL option to WordPress options
    $x_url_view = esc_url($_POST['x_url_view']) ? 'checked' : '';
    update_option('x_url_option_view', $x_url_view);

    // Update the Instagram URL option to WordPress options
    $instagram_url_view = esc_url($_POST['instagram_url_view']) ? 'checked' : '';
    update_option('instagram_url_option_view', $instagram_url_view);

    // Update the LinkedIn URL option to WordPress options
    $linkedin_url_view = esc_url($_POST['linkedin_url_view']) ? 'checked' : '';
    update_option('linkedin_url_option_view', $linkedin_url_view);

    // Update the YouTube URL option to WordPress options
    $youtube_url_view = esc_url($_POST['youtube_url_view']) ? 'checked' : '';
    update_option('youtube_url_option_view', $youtube_url_view);

    // Update the Website URL option to WordPress options
    $website_url_view = esc_url($_POST['website_url_view']) ? 'checked' : '';
    update_option('website_url_option_view', $website_url_view);

    // Update the Email option to WordPress options
	$email_view = isset($_POST['email_view']) ? 'checked' : '';
	update_option('email_option_view', $email_view);

		
    // Update the TikTok URL option to WordPress options
    $tiktok_url_view = esc_url($_POST['tiktok_url_view']) ? 'checked' : '';
    update_option('tiktok_url_option_view', $tiktok_url_view);
    // //////////////////////
		
        // Save the selected options for season and order
        $selected_season = intval($_POST['selected_season']); 
        update_option('app_season', $selected_season);

        $selected_order = $_POST['selected_order'];
        update_option('app_order', $selected_order);

        // Save the selected category option to WordPress options
        $selected_category_id = intval($_POST['selected_category']); // Ensure it's an integer
        update_option('event_selected_category_id', $selected_category_id);

        // Save the champions option to WordPress options
        $champions_text = sanitize_text_field($_POST['champions']); // Sanitize text input
        update_option('champions', $champions_text);

        // Save the about us option to WordPress options
        $about_us_text = sanitize_text_field($_POST['about_us']); // Sanitize text input
        update_option('about_us', $about_us_text);

        // Save the contact us option to WordPress options
        $contact_us_text = sanitize_text_field($_POST['contact_us']); // Sanitize text input
        update_option('contact_us', $contact_us_text);
		
        // Save the contact us option to WordPress options
        $contact_us_text = sanitize_text_field($_POST['regulations']); // Sanitize text input
        update_option('regulations', $contact_us_text);

        // Save the contact us option to WordPress options
        $contact_us_text = sanitize_text_field($_POST['national']); // Sanitize text input
        update_option('national', $contact_us_text);
		
		
        // Save the selected last news option to WordPress options
        $selected_last_news_id = intval($_POST['selected_last_news']); // Ensure it's an integer
        update_option('event_selected_last_news_id', $selected_last_news_id);

        // Save the selected Diamond Sponsors option to WordPress options
        $selected_diamond_sponsors_id = intval($_POST['selected_diamond_sponsors']); // Ensure it's an integer
        update_option('event_selected_diamond_sponsors_id', $selected_diamond_sponsors_id);

        // Save the selected Gold Sponsors option to WordPress options
        $selected_gold_sponsors_id = intval($_POST['selected_gold_sponsors']); // Ensure it's an integer
        update_option('event_selected_gold_sponsors_id', $selected_gold_sponsors_id);

        // Save the selected Union Sponsors option to WordPress options
        $selected_union_sponsors_id = intval($_POST['selected_union_sponsors']); // Ensure it's an integer
        update_option('event_selected_union_sponsors_id', $selected_union_sponsors_id);

        // Save the selected Live News option to WordPress options
        $selected_live_news_id = intval($_POST['selected_live_news']); // Ensure it's an integer
        update_option('event_selected_live_news_id', $selected_live_news_id);

		// Save the selected options for sp_role
		$role_options = array('coach', 'date_of_founding_of_the_club', 'referee', 'club_president', 'club_headquarters');

		foreach ($role_options as $role_option) {
			$selected_role_id = intval($_POST['selected_' . $role_option]); // Ensure it's an integer
			update_option('event_selected_' . $role_option . '_id', $selected_role_id);
		}
		
		
		
        echo '<div class="updated-notice"><p>All options saved.</p></div>';
    }

    // Retrieve the saved options
    $facebook_url_option = get_option('facebook_url_option');
    $x_url_option = get_option('x_url_option');
    $instagram_url_option = get_option('instagram_url_option');
    $linkedin_url_option = get_option('linkedin_url_option');
    $youtube_url_option = get_option('youtube_url_option');
	$website_url_option = get_option('website_url_option');
	$email_option = get_option('email_option');
	$tiktok_url_option = get_option('tiktok_url_option');
	$facebook_url_option_view = get_option('facebook_url_option_view');
	$x_url_option_view = get_option('x_url_option_view');
	$instagram_url_option_view = get_option('instagram_url_option_view');
	$linkedin_url_option_view = get_option('linkedin_url_option_view');
	$youtube_url_option_view = get_option('youtube_url_option_view');
	$website_url_option_view = get_option('website_url_option_view');
	$email_option_view = get_option('email_option_view');
	$tiktok_url_option_view = get_option('tiktok_url_option_view');
    $app_season_option = get_option('app_season');
    $app_order_option = get_option('app_order');
    $saved_category_id = get_option('event_selected_category_id');
    $saved_champions = get_option('champions');
    $saved_about_us = get_option('about_us');
    $saved_contact_us = get_option('contact_us');
    $saved_regulations = get_option('regulations');
	$saved_national = get_option('national');
    $saved_last_news_id = get_option('event_selected_last_news_id');
    $saved_diamond_sponsors_id = get_option('event_selected_diamond_sponsors_id');
    $saved_gold_sponsors_id = get_option('event_selected_gold_sponsors_id');
    $saved_union_sponsors_id = get_option('event_selected_union_sponsors_id');
    $saved_live_news_id = get_option('event_selected_live_news_id');
	
	
    // Get the terms for the custom taxonomy (sp_season)
    $season_terms = get_terms(array(
        'taxonomy' => 'sp_season',
        'hide_empty' => false,
    ));

    // Get the categories for the category select dropdown
	$categories = get_categories(array(
		'hide_empty' => false
	));

    // Content for the custom admin page goes here
    echo '<div class="wrap-events"><h1>Global Options</h1>';
    // Form for entering Facebook URL
    echo '<form method="post" action="">';
	
    // Form for selecting season
    echo '<div class="groupe-posts"><h5>Season Options</h5>';
    echo '<label for="selected_season">Select Current Season:</label>';
	echo '<select name="selected_season">';

	// Add a new option for "Current Season"
	$current_season_id = get_option('sportspress_season');
	echo '<option value="' . esc_attr($current_season_id) . '" ' . selected($app_season_option, $current_season_id, false) . '>Current Season</option>';

	foreach ($season_terms as $term) {
		$selected = ($app_season_option == $term->term_id) ? 'selected' : '';
		echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
	}

	echo '</select>';
    
    // Form for selecting Order
		echo '<label for="selected_order">Select League Position:</label>'; 
		$current_app_order = get_option('app_order');
	?>
	<select name="selected_order">
		<option <?php echo ($current_app_order == 'head_to_head') ? 'selected' : '';  ?> value="head_to_head">Head To Head</option>
		<option <?php echo ($current_app_order == 'goal_difference') ? 'selected' : '';  ?> value="goal_difference">Goal Difference</option>
	</select></div>
    <?php
    echo '<div class="groupe-posts groupe-page-url"><h5>Pages URL</h5>';
    
// Form for entering champions page
echo '<div><label for="champions">Champions Page:</label>';
echo '<select name="champions">';
$english_pages = get_pages();

$champions_page_url = $saved_champions; // Get the saved page URL
foreach ($english_pages as $page) {
    $page_permalink = get_permalink($page);
    $selected = ($champions_page_url == $page_permalink) ? 'selected' : '';
    echo '<option value="' . $page_permalink . '" ' . $selected . '>' . $page->post_title . '</option>';
}
echo '</select>';
echo '<input type="checkbox" name="hide_header_champions" ' . (get_post_meta(url_to_postid($champions_page_url), '_hide_header', true) == 'on' ? 'checked' : '') . '> Hide Header';
echo '<input type="checkbox" name="hide_footer_champions" ' . (get_post_meta(url_to_postid($champions_page_url), '_hide_footer', true) == 'on' ? 'checked' : '') . '> Hide Footer</div>';

// Form for entering about us page
echo '<div><label for="about_us">About Us Page:</label>';
echo '<select name="about_us">';
$about_us_page_url = $saved_about_us; // Get the saved page URL
foreach ($english_pages as $page) {
    $page_permalink = get_permalink($page);
    $selected = ($about_us_page_url == $page_permalink) ? 'selected' : '';
    echo '<option value="' . $page_permalink . '" ' . $selected . '>' . $page->post_title . '</option>';
}
echo '</select>';
echo '<input type="checkbox" name="hide_header_about_us" ' . (get_post_meta(url_to_postid($about_us_page_url), '_hide_header', true) === 'on' ? 'checked' : '') . '> Hide Header';
echo '<input type="checkbox" name="hide_footer_about_us" ' . (get_post_meta(url_to_postid($about_us_page_url), '_hide_footer', true) === 'on' ? 'checked' : '') . '> Hide Footer</div>';

// Form for entering contact us page
echo '<div><label for="contact_us">Contact Us Page:</label>';
echo '<select name="contact_us">';
$contact_us_page_url = $saved_contact_us; // Get the saved page URL
foreach ($english_pages as $page) {
    $page_permalink = get_permalink($page);
    $selected = ($contact_us_page_url == $page_permalink) ? 'selected' : '';
    echo '<option value="' . $page_permalink . '" ' . $selected . '>' . $page->post_title . '</option>';
}
echo '</select>';
echo '<input type="checkbox" name="hide_header_contact_us" ' . (get_post_meta(url_to_postid($contact_us_page_url), '_hide_header', true) === 'on' ? 'checked' : '') . '> Hide Header';
echo '<input type="checkbox" name="hide_footer_contact_us" ' . (get_post_meta(url_to_postid($contact_us_page_url), '_hide_footer', true) === 'on' ? 'checked' : '') . '> Hide Footer</div>';

// Form for entering regulations & docs page
echo '<div><label for="regulations">Regulations & Docs Page:</label>';
echo '<select name="regulations">';
$regulations_page_url = $saved_regulations; // Get the saved page URL
foreach ($english_pages as $page) {
    $page_permalink = get_permalink($page);
    $selected = ($regulations_page_url == $page_permalink) ? 'selected' : '';
    echo '<option value="' . $page_permalink . '" ' . $selected . '>' . $page->post_title . '</option>';
}
echo '</select>';
echo '<input type="checkbox" name="hide_header_regulations" ' . (get_post_meta(url_to_postid($regulations_page_url), '_hide_header', true) === 'on' ? 'checked' : '') . '> Hide Header';
echo '<input type="checkbox" name="hide_footer_regulations" ' . (get_post_meta(url_to_postid($regulations_page_url), '_hide_footer', true) === 'on' ? 'checked' : '') . '> Hide Footer</div>';

// Form for entering national teams page
echo '<div><label for="national">National Teams Page:</label>';
echo '<select name="national">';
$national_page_url = $saved_national; // Get the saved page URL
foreach ($english_pages as $page) {
    $page_permalink = get_permalink($page);
    $selected = ($national_page_url == $page_permalink) ? 'selected' : '';
    echo '<option value="' . $page_permalink . '" ' . $selected . '>' . $page->post_title . '</option>';
}
echo '</select>';
echo '<input type="checkbox" name="hide_header_national" ' . (get_post_meta(url_to_postid($national_page_url), '_hide_header', true) === 'on' ? 'checked' : '') . '> Hide Header';
echo '<input type="checkbox" name="hide_footer_national" ' . (get_post_meta(url_to_postid($national_page_url), '_hide_footer', true) === 'on' ? 'checked' : '') . '> Hide Footer</div></div>';
    
    // Form for selecting category
    echo '<h2>Posts Categories</h2><div class="groupe-posts"><h5>Posts</h5><label for="selected_category">Top News:</label>';
    echo '<select name="selected_category">';
    foreach ($categories as $category) {
        $selected = ($saved_category_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select>';
    
    // Form for selecting last news
    echo '<label for="selected_last_news">News:</label>';
    echo '<select name="selected_last_news">';
    foreach ($categories as $category) {
        $selected = ($saved_last_news_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select>';
    
    // Form for selecting Live News
    echo '<label for="selected_live_news">Live Stream:</label>';
    echo '<select name="selected_live_news">';
    foreach ($categories as $category) {
        $selected = ($saved_live_news_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select></div>';
    
    // Form for selecting Diamond Sponsors
    echo '<div class="groupe-posts"><h5>Sponsors</h5><label for="selected_diamond_sponsors">Diamond Sponsors:</label>';
    echo '<select name="selected_diamond_sponsors">';
    foreach ($categories as $category) {
        $selected = ($saved_diamond_sponsors_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select>';
    
    // Form for selecting Gold Sponsors
    echo '<label for="selected_gold_sponsors">Gold Sponsors:</label>';
    echo '<select name="selected_gold_sponsors">';
    foreach ($categories as $category) {
        $selected = ($saved_gold_sponsors_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select>';
    
    // Form for selecting Union Sponsors
    echo '<label for="selected_union_sponsors">Union Sponsors:</label>';
    echo '<select name="selected_union_sponsors">';
    foreach ($categories as $category) {
        $selected = ($saved_union_sponsors_id == $category->term_id) ? 'selected' : '';
        echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
    }
    echo '</select></div>';
	
	
	// Form for selecting sp_role
	echo '<div class="groupe-posts"><h5>Roles</h5>';
	$sp_roles = get_terms(array(
		'taxonomy' => 'sp_role',
		'hide_empty' => false,
	));

	$role_options = array('coach', 'date_of_founding_of_the_club', 'referee', 'club_president', 'club_headquarters');

	foreach ($role_options as $role_option) {
		echo '<label for="selected_' . $role_option . '">' . ucfirst(str_replace('_', ' ', $role_option)) . ':</label>';
		echo '<select name="selected_' . $role_option . '">';
		foreach ($sp_roles as $role) {
			$selected = (get_option('event_selected_' . $role_option . '_id') == $role->term_id) ? 'selected' : '';
			echo '<option value="' . esc_attr($role->term_id) . '" ' . $selected . '>' . esc_html($role->name) . '</option>';
		}
		echo '</select>';
	}
	echo '</div>';
	
	
 
    echo '<div class="groupe-posts groupe-posts-social"><h5>Social URL:</h5>';
	
    // Form for entering Instagram URL
    echo '<label for="instagram_url">Instagram URL:</label><input type="checkbox" name="instagram_url_view" ' . ($instagram_url_option_view == 'checked' ? 'checked' : '') . '>';
    echo '<input type="text" name="instagram_url" value="' . esc_attr($instagram_url_option) . '">';
    
    // Form for entering X URL
    echo '<label for="x_url">X URL:</label><input type="checkbox" name="x_url_view" ' . ($x_url_option_view == 'checked' ? 'checked' : '') . '>';
    echo '<input type="text" name="x_url" value="' . esc_attr($x_url_option) . '">';
    
    // Form for entering YouTube URL
    echo '<label for="youtube_url">YouTube URL:</label><input type="checkbox" name="youtube_url_view" ' . ($youtube_url_option_view == 'checked' ? 'checked' : '') . '>';
    echo '<input type="text" name="youtube_url" value="' . esc_attr($youtube_url_option) . '">';

    echo '<label for="facebook_url">Facebook URL:</label><input type="checkbox" name="facebook_url_view" ' . ($facebook_url_option_view == 'checked' ? 'checked' : '') . '>';
    echo '<input type="text" name="facebook_url" value="' . esc_attr($facebook_url_option) . '">';

	echo '<label for="website_url">Website URL:</label><input type="checkbox" name="website_url_view" ' . ($website_url_option_view == 'checked' ? 'checked' : '') . '>';
	echo '<input type="text" name="website_url" value="' . esc_attr($website_url_option) . '">';

	echo '<label for="email">Email:</label><input type="checkbox" name="email_view" ' . ($email_option_view == 'checked' ? 'checked' : '') . '>';
	echo '<input type="text" name="email" value="' . esc_attr($email_option) . '">';

	echo '<label for="tiktok_url">TikTok URL:</label><input type="checkbox" name="tiktok_url_view" ' . ($tiktok_url_option_view == 'checked' ? 'checked' : '') . '>';
	echo '<input type="text" name="tiktok_url" value="' . esc_attr($tiktok_url_option) . '"></div>';

	
    echo '<input type="submit" name="submit_options" class="button button-primary" value="حفظ الخيارات">';
    echo '</form>';
    
    echo '</div>';

}


// Callback function for hide_options
function hide_options() {
    // Check if the form is submitted
    if (isset($_POST['submit_hide_options'])) {
        // Save the hide_referee option to WordPress options
        update_option('hide_refree', sanitize_text_field(isset($_POST['hide_refree']) ? 'yes' : 'no'));

        // Save the hide_players option to WordPress options
        update_option('hide_players', sanitize_text_field(isset($_POST['hide_players']) ? 'yes' : 'no'));

        // Save the new options
        $new_options = array(
            'hide_home_menu',
            'hide_local_competition_menu',
            'hide_news_menu',
            'hide_clubs_menu',
            'hide_refree_menu',
            'hide_champions_menu',
            'hide_live_menu',
            'hide_about_menu',
            'hide_sponser_menu',
            'hide_contact_menu',
            'hide_regulations_docs_menu',
            'hide_national_teams',
            'cache_interval_minutes', // Added the new option here
        );

        foreach ($new_options as $option) {
            update_option($option, sanitize_text_field(isset($_POST[$option]) ? 'yes' : 'no'));
        }

        // Save the cache interval in minutes
        update_option('cache_interval_minutes', intval($_POST['cache_interval_minutes']));

        echo '<div class="updated-notice"><p>Display options saved.</p></div>';
    }

    echo '<div class="wrap-events wrap-events-Options"><h1>Display Options</h1>';

    // Add a new form for hiding Referee, Players, and new options
    echo '<form method="post" action="">';
    echo '<div class="groupe-posts"><h5>Players In Team</h5>';

    // Form for hiding Players
    echo '<div><label for="hide_players">Show Players From Teams:</label>';
    echo '<input type="checkbox" name="hide_players" ' . checked(get_option('hide_players'), 'yes', false) . '></div></div>';

    echo '<div class="groupe-posts"><h5>Menu</h5>';

    // Form for new options
    $new_options = array(
        'hide_home_menu' => 'Show Home:',
        'hide_local_competition_menu' => 'Show Local Competition:',
        'hide_news_menu' => 'Show News:',
        'hide_clubs_menu' => 'Show Clubs:',
        'hide_refree_menu' => 'Show Referee:',
        'hide_champions_menu' => 'Show Champions:',
        'hide_live_menu' => 'Show Live Stream:',
        'hide_about_menu' => 'Show About Us:',
        'hide_sponser_menu' => 'Show Sponsors:',
        'hide_contact_menu' => 'Show Contact Us:',
        'hide_regulations_docs_menu' => 'Show Regulations & Docs:', // New option
        'hide_national_teams' => 'Show National Teams', // New option
    );
	
    // Input field for cache interval in minutes
    echo '<div><label for="cache_interval_minutes">Cache Interval in Minutes:</label>';
    echo '<input type="number" name="cache_interval_minutes" value="' . esc_attr(get_option('cache_interval_minutes')) . '" min="1"></div>';


    foreach ($new_options as $option => $label) {
        echo '<div><label for="' . $option . '">' . $label . '</label>';
        echo '<input type="checkbox" name="' . $option . '" ' . checked(get_option($option), 'yes', false) . '></div>';
    }

    echo '</div><input type="submit" name="submit_hide_options" class="button button-primary" value="حفظ الخيارات">';
    echo '</form>';

    echo '</div>';
}

function send_notifications() {
    ?>
    <div class="wrap-notifications wrap-matches">
        <h1>Send Notifications</h1>
        
        <!-- Unified Notifications Form -->
        <form method="post" action="" id="sendNotificationsForm">
            <?php
            // Get all published posts for categories 102 and 103, ordered by date (DESC)
            $all_posts = get_posts(array(
                'post_type' => 'post',
                'numberposts' => -1,
                'post_status' => 'publish',
                'category__in' => array(102, 103),
                'orderby' => 'date',
                'order' => 'DESC',
                'lang' => 'all' // Get all languages
            ));
            echo '<div class="select-group"><div class="groupe-posts">';
            echo '<h5>All Posts</h5>';
            echo '<label for="selected_post_all">Select Post:</label>';
            echo '<select name="selected_post_all" id="selected_post_all" onchange="updateNotificationFields()">';
            echo '<option value="" disabled selected>Select Post</option>'; // Default option
            foreach ($all_posts as $post) {
                echo '<option value="' . esc_attr($post->ID) . '">' . esc_html($post->post_title) . '</option>';
            }
            echo '</select>';
            
            echo '<label for="notification_title_en">Notification Title (English):</label>';
            echo '<input type="text" name="notification_title_en" id="notification_title_en" required>';
            echo '<label for="notification_content_en">Notification Content (English):</label>';
            echo '<textarea name="notification_content_en" id="notification_content_en" required></textarea>';
            
            echo '<label for="notification_title_ar">Notification Title (Arabic):</label>';
            echo '<input type="text" name="notification_title_ar" id="notification_title_ar" required>';
            echo '<label for="notification_content_ar">Notification Content (Arabic):</label>';
            echo '<textarea name="notification_content_ar" id="notification_content_ar" required></textarea>';
            
            echo '<input type="submit" name="send_notifications_all" id="send_notifications_all_button" class="button button-primary" value="Send Notifications">';
            echo '</div></div>';
            ?>
        </form>

        <!-- Custom Notification Forms -->
        <form method="post" action="" id="customNotificationForm">
            <div class="custom-notification-group groupe-posts">
                <h5>Custom Notification</h5>
                <label for="custom_notification_title_en">Notification Title (English):</label>
                <input type="text" name="custom_notification_title_en" id="custom_notification_title_en" required>
                <label for="custom_notification_content_en">Notification Content (English):</label>
                <textarea name="custom_notification_content_en" id="custom_notification_content_en" required></textarea>
                
                <label for="custom_notification_title_ar">Notification Title (Arabic):</label>
                <input type="text" name="custom_notification_title_ar" id="custom_notification_title_ar" required>
                <label for="custom_notification_content_ar">Notification Content (Arabic):</label>
                <textarea name="custom_notification_content_ar" id="custom_notification_content_ar" required></textarea>
                
               <input type="submit" name="send_custom_notification" id="send_custom_notification_button" class="button button-primary" value="Send Custom Notification">
            </div>
        </form>

        <!-- Form for Today Matches in English and Arabic -->
        <form method="post" action="" id="todayMatchesForm">
            <div class="custom-notification-group groupe-posts">
                <h5>Today Matches</h5>
                <label for="today_matches_title_en">Notification Title (English):</label>
                <input type="text" name="today_matches_title_en" id="today_matches_title_en" value="Today Matches" required>
                <label for="today_matches_content_en">Notification Content (English):</label>
                <textarea name="today_matches_content_en" id="today_matches_content_en" required><?php echo get_today_matches_content($lang = 'en'); ?></textarea>
                
                <label for="today_matches_title_ar">Notification Title (Arabic):</label>
                <input type="text" name="today_matches_title_ar" id="today_matches_title_ar" value="مباريات اليوم" required>
                <label for="today_matches_content_ar">Notification Content (Arabic):</label>
                <textarea name="today_matches_content_ar" id="today_matches_content_ar" required><?php echo get_today_matches_content($lang = 'ar'); ?></textarea>
                
                <input type="submit" name="send_today_matches_notification" id="send_today_matches_notification_button" class="button button-primary" value="Send Today Matches Notification">
            </div>
        </form>

    <script>
        function updateNotificationFields() {
            var selectedPostId = document.getElementById('selected_post_all').value;
            if (selectedPostId !== "") {
                // AJAX request to get post title and excerpt in both languages
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var responseData = JSON.parse(xhr.responseText);
                        document.getElementById('notification_title_en').value = responseData.title_en;
                        document.getElementById('notification_content_en').value = responseData.excerpt_en;
                        document.getElementById('notification_title_ar').value = responseData.title_ar;
                        document.getElementById('notification_content_ar').value = responseData.excerpt_ar;
    
                        // Disable send button if Arabic title is empty
                        document.getElementById('send_notifications_all_button').disabled = responseData.title_ar === "";
                    }
                };
                xhr.open('GET', '<?php echo admin_url("admin-ajax.php?action=get_post_data&id="); ?>' + selectedPostId, true);
                xhr.send();
            } else {
                // Clear fields if no post is selected
                document.getElementById('notification_title_en').value = "";
                document.getElementById('notification_content_en').value = "";
                document.getElementById('notification_title_ar').value = "";
                document.getElementById('notification_content_ar').value = "";
                document.getElementById('send_notifications_all_button').disabled = true;
            }
        }
    
        function checkTodayMatchesContent() {
            var contentEn = document.getElementById('today_matches_content_en').value.trim();
            var contentAr = document.getElementById('today_matches_content_ar').value.trim();
            var isDisabled = contentEn === "" || contentAr === "" || contentEn === "No matches today." || contentAr === "لا توجد مباريات اليوم.";
            document.getElementById('send_today_matches_notification_button').disabled = isDisabled;
        }
    
        document.getElementById('custom_notification_title_ar').addEventListener('input', function () {
            document.getElementById('send_custom_notification_button').disabled = this.value === "";
        });
    
        document.getElementById('today_matches_content_en').addEventListener('input', checkTodayMatchesContent);
    
        document.getElementById('today_matches_content_ar').addEventListener('input', checkTodayMatchesContent);
    
        // Initial check to disable buttons
        updateNotificationFields();
        document.getElementById('send_custom_notification_button').disabled = document.getElementById('custom_notification_title_ar').value === "";
        checkTodayMatchesContent();
    </script>
    </div>
    <?php
}

// Add AJAX handler to retrieve post data
add_action('wp_ajax_get_post_data', 'get_post_data_callback');
add_action('wp_ajax_nopriv_get_post_data', 'get_post_data_callback');

function get_post_data_callback() {
    $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $post_data = array();

    if ($post_id) {
        $post = get_post($post_id);
        
        // Get the translations of the post
        $post_en = apply_filters('wpml_object_id', $post->ID, 'post', false, 'en');
        $post_ar = apply_filters('wpml_object_id', $post->ID, 'post', false, 'ar');
        
        if ($post_en) {
            $post_en_data = get_post($post_en);
            $post_data['title_en'] = $post_en_data->post_title;
            $post_data['excerpt_en'] = wp_trim_words($post_en_data->post_content, 20, '...');
        }
        
        if ($post_ar) {
            $post_ar_data = get_post($post_ar);
            $post_data['title_ar'] = $post_ar_data->post_title;
            $post_data['excerpt_ar'] = wp_trim_words($post_ar_data->post_content, 20, '...');
        }
    }

    wp_send_json($post_data);
    wp_die();
}

if (isset($_POST['send_notifications_all'])) {
    $selected_post_all = isset($_POST['selected_post_all']) ? intval($_POST['selected_post_all']) : 0;
    $notification_title_en = isset($_POST['notification_title_en']) ? sanitize_text_field($_POST['notification_title_en']) : '';
    $notification_content_en = isset($_POST['notification_content_en']) ? sanitize_textarea_field($_POST['notification_content_en']) : '';
    $notification_title_ar = isset($_POST['notification_title_ar']) ? sanitize_text_field($_POST['notification_title_ar']) : '';
    $notification_content_ar = isset($_POST['notification_content_ar']) ? sanitize_textarea_field($_POST['notification_content_ar']) : '';
    
    // Send English notification
    $firebaseResultEn = send_notification_to_firebase($notification_title_en, $notification_content_en, $selected_post_all);
    
    // Send Arabic notification
    $firebaseResultAr = send_notification_to_firebase_ar($notification_title_ar, $notification_content_ar, $selected_post_all);
    
    echo '<div class="new-con"><div class="updated-notice"><p>Notifications sent for the selected post (All Posts) in both English and Arabic.</p></div></div>';
}

if (isset($_POST['send_custom_notification'])) {
    $custom_notification_title_en = isset($_POST['custom_notification_title_en']) ? sanitize_text_field($_POST['custom_notification_title_en']) : '';
    $custom_notification_content_en = isset($_POST['custom_notification_content_en']) ? sanitize_textarea_field($_POST['custom_notification_content_en']) : '';
    $custom_notification_title_ar = isset($_POST['custom_notification_title_ar']) ? sanitize_text_field($_POST['custom_notification_title_ar']) : '';
    $custom_notification_content_ar = isset($_POST['custom_notification_content_ar']) ? sanitize_textarea_field($_POST['custom_notification_content_ar']) : '';
    
    // Send English notification
    $firebaseResultEn = send_notification_to_firebase($custom_notification_title_en, $custom_notification_content_en, 0);
    
    // Send Arabic notification
    $firebaseResultAr = send_notification_to_firebase_ar($custom_notification_title_ar, $custom_notification_content_ar, 0);
    
    echo '<div class="new-con"><div class="updated-notice"><p>Custom notifications sent in both English and Arabic.</p></div></div>';
}

if (isset($_POST['send_today_matches_notification'])) {
    $today_matches_title_en = isset($_POST['today_matches_title_en']) ? sanitize_text_field($_POST['today_matches_title_en']) : 'Today Matches';
    $today_matches_content_en = isset($_POST['today_matches_content_en']) ? sanitize_textarea_field($_POST['today_matches_content_en']) : '';
    $today_matches_title_ar = isset($_POST['today_matches_title_ar']) ? sanitize_text_field($_POST['today_matches_title_ar']) : 'مباريات اليوم';
    $today_matches_content_ar = isset($_POST['today_matches_content_ar']) ? sanitize_textarea_field($_POST['today_matches_content_ar']) : '';
    
    // Send English notification
    $firebaseResultEn = send_notification_to_firebase($today_matches_title_en, $today_matches_content_en, 0);
    
    // Send Arabic notification
    $firebaseResultAr = send_notification_to_firebase_ar($today_matches_title_ar, $today_matches_content_ar, 0);
    
    echo '<div class="new-con"><div class="updated-notice"><p>Today Matches notifications sent in both English and Arabic.</p></div></div>';
}

function get_today_matches_content($lang) {
    // Set WPML language filter
    do_action('wpml_switch_language', $lang);

    // Get today's matches content from custom post type "sp_event"
    $today_matches_query = new WP_Query(array(
        'post_type' => 'sp_event',
        'posts_per_page' => -1,
        'date_query' => array(
            'after' => date('Y-m-d 00:00:00'),
            'before' => date('Y-m-d 23:59:59'),
        ),
        'orderby' => 'date',
        'order' => 'ASC',
        'lang' => $lang, // Ensure posts are fetched in the correct language
    ));

    $matches_info = array();

    if ($today_matches_query->have_posts()) {
        while ($today_matches_query->have_posts()) {
            $today_matches_query->the_post();
            
            // Get the post ID
            $post_id = get_the_ID();

            // Get the post title in the specified language
            $post_id_in_lang = apply_filters('wpml_object_id_filter', $post_id, 'post', true, $lang);
            $post = get_post($post_id_in_lang);

            // Get taxonomies
            $sp_league = wp_get_post_terms($post->ID, 'sp_league');
            $sp_venue = wp_get_post_terms($post->ID, 'sp_venue');

            // Get time
            $match_time = get_post_time('g:i A', false, $post->ID);
            
            // Build match information line
            $match_info_line = $post->post_title . ' - ' . ($sp_league ? $sp_league[0]->name : '') . ' - ' . $match_time . ' - ' . ($sp_venue ? $sp_venue[0]->name : '');
            
            $matches_info[] = $match_info_line;
        }
        wp_reset_postdata(); // Reset the post data to the main query
    }

    return $matches_info ? implode("\n", $matches_info) : ($lang == 'en' ? 'No matches today.' : 'لا توجد مباريات اليوم.');
}

function send_notification_to_firebase_ar($title, $message, $post_id) {
    $projectId = handball_get_settings()['project_id']; // Your Firebase project ID
    $topic = handball_get_topic();
    
    // Get the path to the service account JSON file in the child theme
    $serviceAccount = handball_get_service_json_path();
    
    // Load the service account
    $jwt = json_decode(file_get_contents($serviceAccount), true);
    if (!$jwt) {
        error_log("Failed to load service account file: " . json_last_error_msg());
        return json_encode(['status' => 'error', 'message' => 'Failed to load service account file.']);
    }

    // Create a JWT token with the required claims
    $headers = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];
    
    $claims = [
        'iss' => $jwt['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];

    // Encode header and claims as JSON
    $headerEncoded = base64_encode(json_encode($headers));
    $claimsEncoded = base64_encode(json_encode($claims));
    
    // Remove any characters not allowed in base64url encoding
    $headerEncoded = rtrim(strtr($headerEncoded, '+/', '-_'), '=');
    $claimsEncoded = rtrim(strtr($claimsEncoded, '+/', '-_'), '=');

    // Create the JWT signature
    $signatureInput = $headerEncoded . '.' . $claimsEncoded;
    openssl_sign($signatureInput, $signature, $jwt['private_key'], 'sha256');
    $signatureEncoded = base64_encode($signature);
    $signatureEncoded = rtrim(strtr($signatureEncoded, '+/', '-_'), '=');

    // Combine to form the JWT
    $jwtToken = $signatureInput . '.' . $signatureEncoded;

    // Exchange JWT for an access token
    $tokenRequest = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($tokenRequest, CURLOPT_POST, true);
    curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tokenRequest, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    curl_setopt($tokenRequest, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwtToken
    ]));

    $tokenResponse = curl_exec($tokenRequest);
    
    // Check if the token request was successful
    if (curl_errno($tokenRequest)) {
        error_log("Token request error: " . curl_error($tokenRequest));
        curl_close($tokenRequest);
        return json_encode(['status' => 'error', 'message' => 'Token request failed: ' . curl_error($tokenRequest)]);
    }
    curl_close($tokenRequest);

    // Decode the response and log it
    $tokenData = json_decode($tokenResponse, true);
    if (!$tokenData || empty($tokenData['access_token'])) {
        error_log("Failed to obtain access token: " . $tokenResponse);
        return json_encode(['status' => 'error', 'message' => 'Failed to obtain access token. Response: ' . $tokenResponse]);
    }
    
    $token = $tokenData['access_token'];

    // Prepare notification payload
    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    $data = [
        'message' => [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
            'android' => [
                'notification' => [
                    'sound' => 'default'
                ]
            ]
        ]
    ];

    // Send the notification
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    // Check if the notification was sent successfully
    if (curl_errno($ch)) {
        error_log("FCM request error: " . curl_error($ch));
        curl_close($ch);
        return json_encode(['status' => 'error', 'message' => 'FCM request failed.']);
    }

    curl_close($ch);
    $responseData = json_decode($result, true);

    if (!$responseData || isset($responseData['error'])) {
        error_log("FCM response error: " . $result);
        return json_encode(['status' => 'error', 'message' => 'Failed to send notification. FCM response error: ' . $responseData['error']['message']]);
    }

    // If successful, return a success message
    return json_encode(['status' => 'success', 'message' => 'Notification sent successfully!']);
}
function send_notification_to_firebase($title, $message, $post_id) {
    $projectId = handball_get_settings()['project_id']; // Your Firebase project ID
    $topic = handball_get_topic();

    // Get the path to the service account JSON file in the child theme
    $serviceAccount = handball_get_service_json_path();

    // Load the service account
    $jwt = json_decode(file_get_contents($serviceAccount), true);
    if (!$jwt) {
        error_log("Failed to load service account file: " . json_last_error_msg());
        return json_encode(['status' => 'error', 'message' => 'Failed to load service account file.']);
    }

    // Create a JWT token with the required claims
    $headers = [
        'alg' => 'RS256',
        'typ' => 'JWT'
    ];
    
    $claims = [
        'iss' => $jwt['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];

    // Encode header and claims as JSON
    $headerEncoded = base64_encode(json_encode($headers));
    $claimsEncoded = base64_encode(json_encode($claims));
    
    // Remove any characters not allowed in base64url encoding
    $headerEncoded = rtrim(strtr($headerEncoded, '+/', '-_'), '=');
    $claimsEncoded = rtrim(strtr($claimsEncoded, '+/', '-_'), '=');

    // Create the JWT signature
    $signatureInput = $headerEncoded . '.' . $claimsEncoded;
    openssl_sign($signatureInput, $signature, $jwt['private_key'], 'sha256');
    $signatureEncoded = base64_encode($signature);
    $signatureEncoded = rtrim(strtr($signatureEncoded, '+/', '-_'), '=');

    // Combine to form the JWT
    $jwtToken = $signatureInput . '.' . $signatureEncoded;

    // Exchange JWT for an access token
    $tokenRequest = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($tokenRequest, CURLOPT_POST, true);
    curl_setopt($tokenRequest, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($tokenRequest, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    curl_setopt($tokenRequest, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwtToken
    ]));

    $tokenResponse = curl_exec($tokenRequest);
    
    // Check if the token request was successful
    if (curl_errno($tokenRequest)) {
        error_log("Token request error: " . curl_error($tokenRequest));
        curl_close($tokenRequest);
        return json_encode(['status' => 'error', 'message' => 'Token request failed: ' . curl_error($tokenRequest)]);
    }
    curl_close($tokenRequest);

    // Decode the response and log it
    $tokenData = json_decode($tokenResponse, true);
    if (!$tokenData || empty($tokenData['access_token'])) {
        error_log("Failed to obtain access token: " . $tokenResponse);
        return json_encode(['status' => 'error', 'message' => 'Failed to obtain access token. Response: ' . $tokenResponse]);
    }
    
    $token = $tokenData['access_token'];

    // Prepare notification payload
    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    $data = [
        'message' => [
            'topic' => $topic,
            'notification' => [
                'title' => $title,
                'body' => $message,
            ],
            'android' => [
                'notification' => [
                    'sound' => 'default'
                ]
            ]
        ]
    ];

    // Check if $post_id is set before including it in the data field
//     if ($post_id) {
//         $data['message']['data'] = [
//             'post_id' => $post_id,
//         ];
//     }

    // Send the notification
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);

    // Check if the notification was sent successfully
    if (curl_errno($ch)) {
        error_log("FCM request error: " . curl_error($ch));
        curl_close($ch);
        return json_encode(['status' => 'error', 'message' => 'FCM request failed.']);
    }

    curl_close($ch);
    $responseData = json_decode($result, true);

    if (!$responseData || isset($responseData['error'])) {
        error_log("FCM response error: " . $result);
        return json_encode(['status' => 'error', 'message' => 'Failed to send notification. FCM response error: ' . $responseData['error']['message']]);
    }

    // If successful, return a success message
    return json_encode(['status' => 'success', 'message' => 'Notification sent successfully!']);
}
function events_duplication() {
    ?>
    <div class="wrap-events">
        <h1>Events Duplication</h1>
        <div class="groupe-posts">
            <h5>Events Duplication</h5>
			<button id="run-events-duplication" class="button button-primary">Run Events Duplication Now!</button>
			<form method="post">
				<button name="run_events_duplication2" style="display:none" id="run-events-duplication2" class="button button-primary">Translate Title Now</button>
			</form>
            <img id="loader-gif" src="/wp-content/uploads/loading.gif" alt="" width="60px" style="left: 100px; position: absolute; display: none" />
            <br>
           <!-- <span style="margin-top: 10px;display: inline-block;">Run twice for event title translation</span> -->
            <div id="duplication-result"></div>
        </div>
        <div class="groupe-posts">
            <h5>Update Data For Matches</h5>
            <button id="run-save-button" class="button button-primary">Update Data For Matches</button>
            <br>
            <div id="run-result"></div>
        </div>
    </div>

<script>
    jQuery(document).ready(function($) {
        $('#run-events-duplication').click(function() {
            $("#run-events-duplication").prop('disabled', true);
            $("#loader-gif").show();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'run_events_duplication'
                },
                success: function(response) {
						$("#run-events-duplication").prop('disabled', false);
						$("#loader-gif").hide();
						$('#duplication-result').html('<div class="updated-notice"><p>' + response + '</p></div>');
						$('#run-events-duplication2').click();
                    }
                }
            );
        });
        $('#run-save-button').click(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'run_save_button'
                },
                success: function(response) {
                    $('#run-result').html('<div class="updated-notice"><p>' + response + '</p></div>');
                }
            });
        });
    });
</script>
    <?php
}

add_action('wp_ajax_run_save_button', 'run_save_button_callback');
function run_save_button_callback() {
    $args = array(
        'post_type' => 'sp_event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );
    $sp_events = get_posts($args);
    foreach ($sp_events as $sp_event) {
        $post_id = $sp_event->ID;
        $existing_sp_results = get_post_meta($post_id, 'sp_results', true);
        if (!empty($existing_sp_results)) {
            $maxGoals = 0;
            $goalCounts = [];
            
            // First pass: Determine the max goals and count the goal occurrences
            foreach ($existing_sp_results as $teamId => $teamData) {
                $goals = intval($teamData['goals']);
                $goalCounts[$goals] = isset($goalCounts[$goals]) ? $goalCounts[$goals] + 1 : 1;
                if ($goals > 0) {
                    $maxGoals = max($maxGoals, $goals);
                }
            }
            
            // Check if there's a draw situation
            $isDraw = $goalCounts[$maxGoals] > 1;

            // Second pass: Assign outcomes
            foreach ($existing_sp_results as $teamId => &$teamData) {
                $goals = intval($teamData['goals']);
                if ($goals > 0) {
                    if ($isDraw && $goalCounts[$goals] > 1) {
                        $teamData['outcome'] = 'draw';
                    } elseif ($goals == $maxGoals) {
                        $teamData['outcome'] = 'win';
                    } else {
                        $teamData['outcome'] = 'lose';
                    }
                }
            }
            update_post_meta($post_id, 'sp_results', $existing_sp_results);
        }
        $post_data = array(
            'ID' => $post_id,
        );
        wp_update_post($post_data);
    }
    echo 'The data has been updated successfully.';
    wp_die();
}

// AJAX handler for events duplication
add_action('wp_ajax_run_events_duplication', 'run_events_duplication');
function run_events_duplication() {
    duplicate_arabic_posts_to_english_on_all_posts();
    echo 'Run events duplication successfully.';
    wp_die();
}

if (isset($_POST['run_events_duplication2'])) {
    update_english_posts_with_arabic_titles();
}

// Standings Update
function create_custom_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'standings_update';
    
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        team_id mediumint(9) NOT NULL,
        league_id mediumint(9) NOT NULL,
        season_id mediumint(9) NOT NULL,
        position int(11) DEFAULT 0,
        played int(11) DEFAULT 0,
        win int(11) DEFAULT 0,
        draw int(11) DEFAULT 0,
        lose int(11) DEFAULT 0,
        goals_for int(11) DEFAULT 0,
        goals_against int(11) DEFAULT 0,
        goals_diff int(11) DEFAULT 0,
        points int(11) DEFAULT 0,
        position_org int(11) DEFAULT 0,
        PRIMARY KEY (id),
        UNIQUE KEY unique_team_season_league (team_id, league_id, season_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'create_custom_table');
// register_activation_hook(__FILE__, 'create_custom_table');
function standings_update() { ?>
    <div class="wrap-events">
        <h1>Standings Update</h1>
        <form id="standings-form">
            <div class="flex">
                <div class="select">
                <label for="sp_league">Select League:</label>
                    <select id="sp_league" name="sp_league">
                        <?php
                        $leagues = get_terms(['taxonomy' => 'sp_league', 'hide_empty' => false]);
                        $leagues_with_order = [];
                        foreach ($leagues as $league) {
                            $sp_order = get_term_meta($league->term_id, 'sp_order', true);
                            $leagues_with_order[] = [
                                'term_id' => $league->term_id,
                                'name' => $league->name,
                                'sp_order' => $sp_order
                            ];
                        }
                        usort($leagues_with_order, function($a, $b) {
                            return $a['sp_order'] <=> $b['sp_order'];
                        });
                        foreach ($leagues_with_order as $league) {
                            echo "<option value='{$league['term_id']}' data-sp='{$league['sp_order']}'>{$league['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="select">
                <label for="sp_season">Select Season:</label>
                <select id="sp_season" name="sp_season">
                    <?php
                    $seasons = get_terms(['taxonomy' => 'sp_season', 'hide_empty' => false]);
                    foreach ($seasons as $season) {
                        echo "<option value='{$season->term_id}'>{$season->name}</option>";
                    }
                    ?>
                </select>
                </div>
                <div class="but">
                    <button type="button" id="fetch-teams" class="btn">Get Tables</button>
                    <div id="loader" style="display: none;">Loading...</div>
                </div>
            </div>
            <div id="teams-table">
                <!-- AJAX will load the table here -->
            </div>
            <button type="submit" id="save-standings" class="btn">Save Standings</button>
        </form>
    </div>

    <style>
        .btn {
            background-color: #0073aa;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #005177;
        }

        #loader {
            margin-top: 20px;
            font-size: 18px;
            color: #0073aa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        input[type="number"] {
            width: 60px;
        }
        .flex {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .flex .select {
            width: 30%;
        }
        .flex .select select {
            width: 100%;
        }
        .flex .but {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .flex .but .btn {
            margin-top: 0;
            height: 50px;
            display: none;
        }
        #standings-form #save-standings {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 10;
            height: 60px;
            font-size: 18px;
        }
        #teams-table th {
            height: 40px;
            text-align: center;
            font-size: 16px;
            color: #222;
        }
        #teams-table img {
            width: 45px;
            height: auto;
        }
        #teams-table input {
            width: 100% !important;
            font-size: 16px;
        }
        #teams-table table {
            width: 98%;
        }
        #teams-table tr div {
            text-align: center;
            font-size: 18px;
        }
        #teams-table tr div p {
            font-size: 18px;
            font-weight: 500;
        }
        @media(max-width: 450px) {
            
        #teams-table table {
            width: auto;
        }
        #teams-table input {
            width: 70px;
            font-size: 16px;
        }
        .flex .select {
            width: 45%;
        }
        }
    </style>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            function loadTeamsData(league, season) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'fetch_teams',
                        league: league,
                        season: season
                    },
                    success: function(response) {
                        $('#loader').hide();
                        $('#teams-table').html(response);
                    },
                    error: function(error) {
                        $('#loader').hide();
                        $('#teams-table').html('<p>There was an error processing the request.</p>');
                        console.log(error);
                    }
                });
            }
        
            // Function to fetch teams when league or season changes
            function fetchTeamsOnChange() {
                var league = $('#sp_league').val();
                var season = $('#sp_season').val();
                
                $('#loader').show();
                $('#teams-table').html('');
        
                loadTeamsData(league, season);
            }
        
            // Trigger fetchTeamsOnChange when league or season dropdown values change
            $('#sp_league, #sp_season').on('change', function() {
                fetchTeamsOnChange();
            });
        
            // Load existing data if available
            fetchTeamsOnChange();
        
            // AJAX form submission
            $('#standings-form').on('submit', function(event) {
                event.preventDefault();
        
                var formData = $(this).serialize();
        
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'save_standings',
                        data: formData
                    },
                    success: function(response) {
                        alert('Data Sent Successfully');
                        console.log(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('There was an error saving the standings.');
                    }
                });
            });
        
            // AJAX button click to fetch teams
            $('#fetch-teams').on('click', function() {
                fetchTeamsOnChange();
            });
        });
    </script>
<?php }

function fetch_teams() {
    if (!isset($_POST['league']) || !isset($_POST['season'])) {
        wp_send_json_error('Invalid parameters');
    }

    global $wpdb;
    $league_id = intval($_POST['league']);
    $season_id = intval($_POST['season']);
    $table_name = $wpdb->prefix . 'standings_update';

    // Fetch teams from sp_events table
    $teams_query = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT teams FROM sp_events WHERE season_id = %d AND league_id = %d",
            $season_id,
            $league_id
        )
    );

    if ($teams_query) {
        $teams_ids = array();
        foreach ($teams_query as $team) {
            $teams_ids[] = $team->teams;
        }

        // Convert teams IDs to comma-separated string
        $teams_ids_str = implode(',', $teams_ids);

        // Query teams
        $teams = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}posts WHERE ID IN ($teams_ids_str)"
        );

        // Fetch teams order
        $teams_order = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}standings_update WHERE season_id = $season_id AND league_id = $league_id ORDER BY position_org ASC"
        );

        // Sort teams based on position_org
        usort($teams, function($a, $b) use ($teams_order) {
            $position_a = array_search($a->ID, array_column($teams_order, 'team_id'));
            $position_b = array_search($b->ID, array_column($teams_order, 'team_id'));
            return $position_a - $position_b;
        });

        if ($teams) {
            echo '<table>';
            echo '<tr><th>Team</th><th>Position</th><th>Points</th><th>Played</th><th>Win</th><th>Draw</th><th>Lose</th><th>GF</th><th>GA</th><th>GD</th></tr>'; // Moved "Points" after "Position"
            foreach ($teams as $team) {
                // Get team ID
                $team_id = $team->ID;
            
                // Get team order
                $team_order = array_search($team_id, array_column($teams_order, 'team_id'));
            
                // Check if data exists in custom table
                $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE team_id = %d AND league_id = %d AND season_id = %d", $team_id, $league_id, $season_id), ARRAY_A);
            
                $position = $data ? $data['position'] : 0;
                $played = $data ? $data['played'] : 0;
                $win = $data ? $data['win'] : 0;
                $draw = $data ? $data['draw'] : 0;
                $lose = $data ? $data['lose'] : 0;
                $goals_for = $data ? $data['goals_for'] : 0;
                $goals_against = $data ? $data['goals_against'] : 0;
                $goals_diff = $data ? $data['goals_diff'] : 0;
                $points = $data ? $data['points'] : 0;
            
                echo '<tr class="position-' . $team_order . '">';
                echo '<td>';
                echo '<div>';
                echo '<p>' . $team->post_title . '</p>';
                echo '<div>' . get_the_post_thumbnail($team_id) . '</div>';
                echo '</div>';
                echo '</td>';
                echo '<td><input type="number" name="position[' . $team_id . ']" value="' . $position . '"></td>';
                echo '<td><input type="number" name="points[' . $team_id . ']" value="' . $points . '"></td>'; // Moved "Points" after "Position"
                echo '<td><input type="number" name="played[' . $team_id . ']" value="' . $played . '"></td>';
                echo '<td><input type="number" name="win[' . $team_id . ']" value="' . $win . '"></td>';
                echo '<td><input type="number" name="draw[' . $team_id . ']" value="' . $draw . '"></td>';
                echo '<td><input type="number" name="lose[' . $team_id . ']" value="' . $lose . '"></td>';
                echo '<td><input type="number" name="goals_for[' . $team_id . ']" value="' . $goals_for . '"></td>';
                echo '<td><input type="number" name="goals_against[' . $team_id . ']" value="' . $goals_against . '"></td>';
                echo '<td><input type="number" name="goals_diff[' . $team_id . ']" value="' . $goals_diff . '"></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'No teams found for the selected league and season.';
        }
    } else {
        echo 'No teams found for the selected league and season.';
    }

    wp_die();
}
add_action('wp_ajax_fetch_teams', 'fetch_teams');

function save_standings() {
    if (!isset($_POST['data'])) {
        wp_send_json_error('Invalid parameters');
    }

    parse_str($_POST['data'], $form_data);

    global $wpdb;
    $table_name = $wpdb->prefix . 'standings_update';

    $league = intval($form_data['sp_league']);
    $season = intval($form_data['sp_season']);

    foreach ($form_data['played'] as $team_id => $played) {
        $team_id = intval($team_id);
        $position = intval($form_data['position'][$team_id]);
        $played = intval($played);
        $win = intval($form_data['win'][$team_id]);
        $draw = intval($form_data['draw'][$team_id]);
        $lose = intval($form_data['lose'][$team_id]);
        $goals_for = intval($form_data['goals_for'][$team_id]);
        $goals_against = intval($form_data['goals_against'][$team_id]);
        $goals_diff = intval($form_data['goals_diff'][$team_id]);
        $points = intval($form_data['points'][$team_id]);
        
        // Check if the row already exists
        $existing_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE team_id = %d AND league_id = %d AND season_id = %d", $team_id, $league, $season), ARRAY_A);
        
        if ($existing_data) {
            // Update existing row
            $wpdb->update(
                $table_name,
                [
                    'position' => $position,
                    'played' => $played,
                    'win' => $win,
                    'draw' => $draw,
                    'lose' => $lose,
                    'goals_for' => $goals_for,
                    'goals_against' => $goals_against,
                    'goals_diff' => $goals_diff,
                    'points' => $points
                ],
                [
                    'team_id' => $team_id,
                    'league_id' => $league,
                    'season_id' => $season
                ]
            );
        } else {
            // Insert new row
            $wpdb->insert(
                $table_name,
                [
                    'team_id' => $team_id,
                    'league_id' => $league,
                    'season_id' => $season,
                    'position' => $position,
                    'played' => $played,
                    'win' => $win,
                    'draw' => $draw,
                    'lose' => $lose,
                    'goals_for' => $goals_for,
                    'goals_against' => $goals_against,
                    'goals_diff' => $goals_diff,
                    'points' => $points
                ]
            );
        }
    }

    if ($wpdb->last_error) {
        error_log('Database Error: ' . $wpdb->last_error);
    }

    wp_send_json_success('Data saved successfully.');
}
add_action('wp_ajax_save_standings', 'save_standings');
