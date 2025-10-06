<?php

function global_matches() {
    // Get Selected Date To View Matches
    $selected_event_date = isset($_GET['selected_date']) ? sanitize_text_field($_GET['selected_date']) : date('Y-m-d', strtotime('+3 hours'));
    if (isset($_POST['submit_selected_date'])) {
        if (isset($_POST['selected_date'])) {
            $selected_date = sanitize_text_field($_POST['selected_date']);
            update_option('selected_event_date', $selected_date);
            wp_redirect(admin_url('admin.php?page=event-matches&selected_date=' . $selected_date));
            exit();
        }
    }
    $selected_event_date = $selected_event_date;
    // Get Season From Option To View Matches In this Season
	$season = get_option('app_season');
    // Query To Get Matches
	$args = array(
		'post_type'      => 'sp_event',
		'posts_per_page' => -1,
		'order'          => 'ASC',
		'orderby'        => 'date',
		'post_status'    => array('publish', 'future'),
	);
    // Apply Option Season If Founded
	if ($season) {
		$args['tax_query'][] = array(
			'taxonomy' => 'sp_season',
			'field'    => 'term_id',
			'terms'    => $season,
		);
	}
    // Get Matches
	$event_posts = get_posts($args);
    $unique_dates = array();
    foreach ($event_posts as $event_post) {
        $post_date = get_the_date('Y-m-d', $event_post);
        $unique_dates[$post_date] = true;
    }
    echo '<div class="wrap-matches"><h1>تحديث نتائج المباريات</h1>';
    // View Only For Administrator
    if (current_user_can('administrator')) {
        // Hide matches with results 
        echo '<label for="hide-matches-checkbox"><input type="checkbox" id="hide-matches-checkbox"> إخفاء المباريات التي تحتوي علي نتائج</label>'; // Add this line
        echo '<form id="date-form" method="post" action="">';
        echo '<div class="date-navigation">';
                echo '<button class="prev-date-button" onclick="changeDate(-1); return false;">
        <i class="fas fa-chevron-left"></i>
        </button>';
        // Input To Get Matches By Date
        echo '<input type="date" id="event-date-select" name="selected_date" value="' . esc_attr($selected_event_date) . '">';
        echo '<button class="next-date-button" onclick="changeDate(1); return false;"><i class="fas fa-chevron-right"></i></button>';
        echo '</div>';
        echo '<input type="submit" id="submit_selected_date" name="submit_selected_date" class="button button-primary" value="Filter">';
        echo '</form>';
    }
    ?>
    <!-- Script To Get Matches and Hide Matches Results And Save It -->
    <script>
        function changeDate(delta) {
            var currentDate = new Date(document.getElementById("event-date-select").value);
            currentDate.setDate(currentDate.getDate() + delta);
            var formattedDate = currentDate.toISOString().split("T")[0];
            document.getElementById("event-date-select").value = formattedDate;
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set("selected_date", formattedDate);
            window.history.replaceState({}, document.title, currentUrl);
            document.getElementById("date-form").submit();
        }
        document.addEventListener("DOMContentLoaded", function() {
            var selectDate = document.getElementById("event-date-select");
            function changeAndSubmit() {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set("selected_date", selectDate.value);
                window.history.replaceState({}, document.title, currentUrl);
                document.getElementById("date-form").submit();
            }
            selectDate.addEventListener("change", changeAndSubmit);
        });
        document.addEventListener("DOMContentLoaded", function() {
            var selectDate = document.getElementById("event-date-select");
            var hideMatchesCheckbox = document.getElementById("hide-matches-checkbox");
            function changeAndSubmit() {
                document.getElementById("date-form").submit();
            }
            selectDate.addEventListener("change", changeAndSubmit);
            hideMatchesCheckbox.addEventListener("change", function() {
                var matches = document.querySelectorAll(".event-form");
                matches.forEach(function(match) {
                    var metaValue1Input = match.querySelector("#meta_value1");
                    var metaValue2Input = match.querySelector("#meta_value2");
    				if (hideMatchesCheckbox.checked && (metaValue1Input.value !== "" && metaValue1Input.value !== "0")) {
                        match.style.display = "none";
                    } else {
                        match.style.display = "block";
                    }
                });
            });
            hideMatchesCheckbox.dispatchEvent(new Event("change"));
        });
    </script>
    <!-- Script To Send Notifications Matches -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        var sendNotificationCheckboxes = document.querySelectorAll(".send-notification-checkbox");
        function updateCookieAndSendAjax(checkbox) {
            var sendNotificationState = Array.from(sendNotificationCheckboxes).map(cb => cb.checked ? "1" : "0");
            let parentElement = checkbox.closest('form');
            let eventId = parentElement.getAttribute('data-event-id');
            var xhr = new XMLHttpRequest();
            xhr.open("POST", ajaxurl, true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    console.log(xhr.responseText);
                }
            };
console.log("eventId:", eventId);
console.log("sendNotificationState:", sendNotificationState);
xhr.send("action=save_send_notification_state&event_id=" + eventId + "&send_notification_state=" + sendNotificationState.join(","));
        }
        sendNotificationCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                updateCookieAndSendAjax(checkbox);
            });
        });
    
        });
    </script>
    <?php
    // Save Notification States For Matches
    add_action('wp_ajax_save_send_notification_state', 'save_send_notification_state');
    add_action('wp_ajax_nopriv_save_send_notification_state', 'save_send_notification_state');
    function save_send_notification_state() {
        $event_id = sanitize_text_field($_POST['event_id']);
        wp_send_json_success('Data saved successfully.');
        exit();
    }
    // Get All MAtches To Veiw For User To Update Data
	$noMatches = false;?>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Disable/Enable Save Button
		function toggleSaveButton(form, enable) {
			const ajaxButton = form.querySelector('input.ajax-button');
			if (ajaxButton) {
				if (enable) {
					ajaxButton.removeAttribute('disabled');
				} else {
					ajaxButton.removeAttribute('disabled');
				}
			}
		}

		// Automatically update input logic based on score values
		function updateScoreInputs(form) {
			let team1ScoreInput = form.querySelector("input[name='meta_value1']");
			let team2ScoreInput = form.querySelector("input[name='meta_value2']");
			if (!team1ScoreInput || !team2ScoreInput) return;

			// Limit inputs to 0, 1, 2, 3
			function restrictScoreInput(input) {
				input.addEventListener('input', function() {
					let value = parseInt(input.value, 10);
					if (isNaN(value) || value < 0 || value > 3) {
						input.value = '0';  // Clear invalid input
					} else {
						updateOtherInput(input);  // Update the opposite input
					}
				});
			}

			// Check score comparison and adjust the Save Button logic
			function handleScoreChange() {
				let currentTeam1Score = parseFloat(team1ScoreInput.value) || 0;
				let currentTeam2Score = parseFloat(team2ScoreInput.value) || 0;

				// Disable Save Button if scores are equal, any field is empty, or sum exceeds 3
				if (currentTeam1Score === currentTeam2Score || isNaN(currentTeam1Score) || isNaN(currentTeam2Score) || (currentTeam1Score + currentTeam2Score) > 3) {
					toggleSaveButton(form, false);
				} else {
					toggleSaveButton(form, true);
				}
			}

			// Attach score input restrictions and handlers
			restrictScoreInput(team1ScoreInput);
			restrictScoreInput(team2ScoreInput);
			team1ScoreInput.addEventListener('input', handleScoreChange);
			team2ScoreInput.addEventListener('input', handleScoreChange);

			handleScoreChange();
		}

		// Apply to each form
		document.querySelectorAll('.event-form .round3').forEach(form => {
			updateScoreInputs(form);
		});
	});
</script>
    <?php foreach ($event_posts as $event_post) {
        $event_id = $event_post->ID;
        $event_title = esc_html(get_the_title($event_id));
        $event_date = esc_html(get_the_date('Y-m-d', $event_post));
        $venue = wp_get_post_terms($event_id, 'sp_venue', array('fields' => 'names'));
        $leagues = wp_get_post_terms($event_id, 'sp_league', array('fields' => 'names'));
$league_terms = get_the_terms($event_id, 'sp_league');
$league_id = ($league_terms && !is_wp_error($league_terms) && !empty($league_terms)) ? $league_terms[0]->term_id : 0;
		$is_3_round_enabled = get_term_meta($league_id, '3_round', true) === 'enabled';
        if ($selected_event_date === 'all' || $selected_event_date === $event_date) {
			$noMatches = true;
            echo '<div class="event-form" data-event-date="' . esc_attr($event_date) . '">';
            // Match Title
            echo '<h2>' . $event_title . '</h2>';
			echo '<div class="all-meta">';
            // Match Time
			echo '<p class="time">'.get_the_date('H:i',$event_id).'</p>';
            // Match Venue
			echo '<p class="staium">'.$venue[0].'</p>';
            // Match League
			echo '<p class="leagues">'.$leagues[0].'</p>';
			echo '</div>';
            // Get Anthor Language
			$opposite_language = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en') ? 'ar' : 'en';
			$event_id_opposite_lang = apply_filters('wpml_object_id', $event_id, 'sp_event', FALSE, $opposite_language);
            // Form Has Input To Edit Match Result
			$form_class = 'ajax-form';
			if ($is_3_round_enabled) {
				$form_class .= ' round3';  // Updated class name
			}
			echo '<form class="'.$form_class.'" data-event-id="' . esc_attr($event_id) . '" data-event-id-opposite="' . esc_attr($event_id_opposite_lang) . '" data-selected-date="' . esc_attr($selected_event_date) . '">';
            $sp_results = get_post_meta($event_id, 'sp_results', true);
            // Check If Match Has Result Or Not To Show Data
            if (is_array($sp_results) && !empty($sp_results)) {
                $team_ids = get_post_meta($event_id)['sp_team'];
                $first_team_id = $team_ids[0];
                $first_team_name = get_the_title($first_team_id);
                $second_team_id = $team_ids[1];
                $second_team_name = get_the_title($second_team_id);
                echo '<div class="match-res"><label for="meta_value1">' . esc_html($first_team_name) . '</label>';
                echo '<input type="number" class="ajax-input" name="meta_value1" id="meta_value1" value="' . esc_attr($sp_results[$first_team_id]['goals']) . '">';
                echo '<input type="number" class="ajax-input" name="meta_value2" id="meta_value2" value="' . esc_attr($sp_results[$second_team_id]['goals']) . '"><label for="meta_value2">' . esc_html($second_team_name) . '</label></div>';
            } else {
                $team_ids = get_post_meta($event_id)['sp_team'];
                $first_team_id = $team_ids[0];
                $first_team_name = get_the_title($first_team_id);
                $second_team_id = $team_ids[1];
                $second_team_name = get_the_title($second_team_id);
                echo '<div class="match-res"><label for="meta_value1">' . esc_html($first_team_name) . '</label>';
                echo '<input type="number" class="ajax-input" id="meta_value1" name="meta_value1">';
                echo '<input type="number" class="ajax-input" id="meta_value2" name="meta_value2"><label for="meta_value2">' . esc_html($second_team_name) . '</label></div>';
			}
		   // 3round Match (Radio Button To Change 3round Match)
       // Status Match (Radio Button To Change Status Match)
//         if (current_user_can('administrator')) {
            echo '<div class="input-radio">';
            $status_options = array(
                'Started' => 'بدأت',
                'Live' => 'مباشر',
                '1st H' => 'ش اول',
                'Break' => 'إستراحة',
                '2nd H' => 'ش ثان',
                'Ended' => 'إنتهت',
                'Canceled' => 'الغيت',
                'Postponed' => 'تأجلت',
                'Default' => 'التوقيت',
            );
//         } else {
//             echo '<div class="input-radio">';
//             $status_options = array(
//                 'Ended' => 'إنتهت',
//             );
//         }

            // Check If This Match Has Status Or Not TO Checked
			$selected_match_status = get_post_meta($event_id, 'match_live', true); 
            foreach ($status_options as $value => $label) {
                $checked = '';
                if (empty($selected_match_status) && $value == 'Ended') {
                    $checked = 'checked';
                } else {
                    $checked = checked($value, $selected_match_status, false);
                }
                echo '<label><input type="radio" class="ajax-input" name="match_status" value="' . esc_attr($value) . '" ' . $checked . '> ' . esc_html($label) . '</label>';
            }
            echo '</div>';
            // Check If Match Todat To Update Match
			$current_date_time = date('Y-m-d', strtotime('+3 hours'));
			if($event_date == $current_date_time) {
                echo '<label class="send-notification-checkbox-label" for="send-notification-checkbox-' . esc_attr($event_id) . '"><input type="checkbox" name="send_notification_checkbox" id="send-notification-checkbox-' . esc_attr($event_id) . '" class="send-notification-checkbox">إشعار</label>';
			}
            // Save Match
			$team_1_meta = get_post_meta($event_id, 'match_3round_team_1', true);
			$team_2_meta = get_post_meta($event_id, 'match_3round_team_2', true);

			$disabled_attr = '';
			if ($is_3_round_enabled && (empty($team_1_meta) || empty($team_2_meta))) {
				$disabled_attr = 'disabled';
			}

echo '<input type="button" class="button button-primary ajax-button" value="حفظ"><span class="update-match"></span>';
            echo '</form>';
            echo '</div>';
        }
    }
    // If Not Have Any Matches Show This Image
	if ($noMatches == false) {
		echo '<div class="no-matches-message alert">
		<img src="'.get_stylesheet_directory_uri() . "/img/sad.gif".'" />
		No matches found.</div>';
	}
    ?>
    <!-- Ajax Call To Update Matches And Send Notifications -->
<script>
   jQuery(document).ready(function ($) {
        // Check If Notification Input Checked Or Not 
        $(".send-notification-checkbox").each(function() {
            $(this).change(function() {
                if($(this).hasClass("active")) {
                    $(this).removeClass("active");
                    $(this).attr("value", "no");    
                } else {
                    $(this).addClass("active");
                    $(this).attr("value", "yes");
                }
            });
        });
        // Save Match Ajax To Send Data and Notifications
        $(".ajax-button").on("click", function () {
            var button = $(this);
            // Disable the button after click
//             button.prop("disabled", true);  // Disable the button

            
            var eventForm = button.closest(".ajax-form");
            var selectedRadio = eventForm.find("input[name='match_status']:checked");
            var selected3round = eventForm.find("input[name='match_3round']:checked");
            var match_3round_team_1 = eventForm.find("input[name='match_3round_team_1']").prop("checked") ? 'true' : 'false';
            var match_3round_team_2 = eventForm.find("input[name='match_3round_team_2']").prop("checked") ? 'true' : 'false';
            var sendNotificationCheckbox = eventForm.find(".send-notification-checkbox");
            var eventData = {
                action: "save_event_data",
                event_id: eventForm.data("event-id"),
                selected_date: eventForm.data("selected-date"),
                meta_value1: eventForm.find("input[name='meta_value1']").val(),
                meta_value2: eventForm.find("input[name='meta_value2']").val(),
                send_notification_checkbox: eventForm.find("input[name='send_notification_checkbox']").val(),
                match_status: selectedRadio.val(),
                match_3round: selected3round.val(), 
                match_3round_team_1: match_3round_team_1,
                match_3round_team_2: match_3round_team_2
            };
            // Check If Notification Box Check Or Not To Show Right Saved Message
            if (sendNotificationCheckbox.prop("checked")) {
                button.next(".update-match").text("تم تحديث المباراة والإشعار").fadeIn().delay(2000).fadeOut();
				 $("input[type='checkbox']").each(function () {
        $(this).prop("checked", false)      // Uncheck the checkbox
               .removeClass("active")       // Remove 'active' class
               .attr("value", "no");        // Reset value attribute
    });
            } else {
                button.next(".update-match").text("تم تحديث المباراة").fadeIn().delay(2000).fadeOut();
            }
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: eventData,
                success: function (response) {
                    // Optionally, re-enable the button if needed after success
                    // button.prop("disabled", false);

                },
                error: function (error) {
                    console.log(error.responseJSON.data);
                    // Optionally, re-enable the button if needed after error
                    // button.prop("disabled", false);
                },
            });
        });
    });    
</script>

    <?php
    echo '</div>';
}
// Create Function To Save Data Which Send In Ajax Call
add_action('wp_ajax_save_event_data', 'save_event_data');
add_action('wp_ajax_nopriv_save_event_data', 'save_event_data');
function save_event_data() {
    // Match ID
    $event_id = sanitize_text_field($_POST['event_id']);
    // Match Date
    $selected_date = sanitize_text_field($_POST['selected_date']);
    // Goals For First Team
    $meta_value1 = intval($_POST['meta_value1']);
    // Goals For Second Team
    $meta_value2 = intval($_POST['meta_value2']);
    // Check If User Administrator || Time Is Right
    if ($selected_date === date('Y-m-d', strtotime('+3 hours')) || current_user_can('administrator')) {
        // Get Result Match And If Empty Convert To Array
        $sp_results = get_post_meta($event_id, 'sp_results', true);
        if (!$sp_results) {
            $sp_results = array();
        }
        // Get Match Status
        $match_status = sanitize_text_field($_POST['match_status']);
        // Get Match Notification Checkbox
        $send_notification_checkbox = $_POST['send_notification_checkbox'];
        // Update Match Status 
        update_post_meta($event_id, 'match_live', $match_status);
        // Opposite Language
        $opposite_language = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en') ? 'ar' : 'en';
        $event_id_opposite_lang = apply_filters('wpml_object_id', $event_id, 'sp_event', FALSE, $opposite_language);
        update_post_meta($event_id, 'match_team_1_3d_point', $meta_value1);
        update_post_meta($event_id, 'match_team_2_3d_point', $meta_value2);
        update_post_meta($event_id_opposite_lang, 'match_team_1_3d_point', $meta_value1);
        update_post_meta($event_id_opposite_lang, 'match_team_2_3d_point', $meta_value2);
        // Check If Match Results Array Or Not And Save Data
        if (!is_array($sp_results) || empty($sp_results)) {
            $team_ids = get_post_meta($event_id)['sp_team'];
            foreach ($team_ids as $team_id) {
                $sp_results[$team_id] = array(
                    'firsthalf'=> '',
                    'secondhalf'=> '',
                    'goals' => '',
                    'outcome' => '',
                );
            }
        }
        // Get Team IDs From Result Meta
        $team_ids = array_keys($sp_results);
        $first_team_id = $team_ids[0];
        $second_team_id = $team_ids[1];
        // Update Goals For Team In Match 
        $sp_results[$first_team_id]['goals'] = $meta_value1;
        $sp_results[$second_team_id]['goals'] = $meta_value2;
    $outcome_team1 = '';
    $outcome_team2 = '';
    if ($sp_results[$first_team_id]['goals'] > $sp_results[$second_team_id]['goals']) {
        $outcome_team1 = 'win';
        $outcome_team2 = 'lose';
    } elseif ($sp_results[$first_team_id]['goals'] < $sp_results[$second_team_id]['goals']) {
        $outcome_team1 = 'lose';
        $outcome_team2 = 'win';
    } else {
        $outcome_team1 = 'draw';
        $outcome_team2 = 'draw';
    }
    $sp_results[$first_team_id]['outcome'] = $outcome_team1;
    $sp_results[$second_team_id]['outcome'] = $outcome_team2;
    update_post_meta($event_id, 'sp_results', $sp_results);
    $opposite_language = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en') ? 'ar' : 'en';
    $event_id_opposite_lang = apply_filters('wpml_object_id', $event_id, 'sp_event', FALSE, $opposite_language);
    $team_ids_opposite_lang = get_post_meta($event_id_opposite_lang)['sp_team'];
    $first_team_id_opposite_lang = $team_ids_opposite_lang[0];
    $second_team_id_opposite_lang = $team_ids_opposite_lang[1];
    update_post_meta($event_id_opposite_lang, 'sp_results', array(
        $first_team_id_opposite_lang => array(
            'goals' => $meta_value1,
            'outcome' => ($meta_value1 > $meta_value2) ? 'win' : (($meta_value1 < $meta_value2) ? 'lose' : 'draw'),
        ),
        $second_team_id_opposite_lang => array(
            'goals' => $meta_value2,
            'outcome' => ($meta_value2 > $meta_value1) ? 'win' : (($meta_value2 < $meta_value1) ? 'lose' : 'draw'),
        ),
    ));
    update_post_meta($event_id_opposite_lang, 'match_live', $match_status);
	$opposite_language = (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'en') ? 'ar' : 'en';
	$event_id_opposite_lang = apply_filters('wpml_object_id', $event_id, 'sp_event', FALSE, $opposite_language);
	$sp_results_opposite_lang = get_post_meta($event_id_opposite_lang, 'sp_results', true);
	$allTeam = get_post_meta($event_id)['sp_team'];
	$allTeamopposite = get_post_meta($event_id_opposite_lang)['sp_team'];
        $current_date_gmt3 = date('Y-m-d', strtotime('+3 hours'));
        // Check If Match Today
        if ($selected_date === $current_date_gmt3) {
            // Send En Notification
            send_notification_to_firebase_silent_en($event_id_opposite_lang, $match_status, $allTeamopposite, $sp_results_opposite_lang, ICL_LANGUAGE_CODE, $event_id_opposite_lang, $allTeamopposite, $sp_results_opposite_lang, $opposite_language, $send_notification_checkbox);
            // Send Ar Notification
            send_notification_to_firebase_silent_ar($event_id, $match_status, $allTeam, $sp_results, ICL_LANGUAGE_CODE, $event_id_opposite_lang, $allTeamopposite, $sp_results_opposite_lang, $opposite_language, $send_notification_checkbox);
        }

        // Call Cache Clear URL
        $cache_clear_url = 'https://bit.ly/3A2MPcQ';
        $response = wp_remote_get($cache_clear_url);

        // Check if request was successful
        if (is_wp_error($response)) {
            wp_send_json_error('Data saved but failed to clear cache.');
        } else {
            $response_body = wp_remote_retrieve_body($response);
            wp_send_json_success(array('message' => 'Data saved successfully.', 'cache_clear_response' => $response_body));
        }
        exit();
    }
}
// Function To Send Match Notification (En)
function send_notification_to_firebase_silent_en($post_id, $status, $team_ids, $teams, $language, $event_id_opposite_lang, $team_ids_opposite, $sp_results_opposite_lang, $opposite_language, $notify) {
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
        'exp' => time() + 3600, // 1 hour expiration
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
    
    // Get Team Data
    $team1_id = $team_ids[0];
    $team2_id = $team_ids[1];
    $team1_data = $teams[$team1_id];
    $team2_data = $teams[$team2_id];
		// Fetch the league associated with the current event ($post_id)
$league_terms = wp_get_post_terms($post_id, 'sp_league', array('fields' => 'names'));

// Ensure we get the correct league name for this event
$league_name = (!empty($league_terms) && is_array($league_terms)) ? implode(', ', $league_terms) : '';
    
    // Get Event and Team Titles
    global $wpdb;
    $event_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $post_id));
    $event_title_opp = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $event_id_opposite_lang));
    $team1_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $team1_id));
    $team2_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $team2_id));

    // Prepare the notification body
    $messageBody = $team1_title . ' ' . $team1_data['goals'] . ' - ' . $team2_title . ' ' . $team2_data['goals'];
    $translated_status = $status;

    // Build the notification payload based on whether to send a silent notification or not
    if ($notify === 'yes') {

        // Build the notification payload for a standard notification
        $data = [
            'message' => [
                'topic' => $topic,
                'notification' => [
				'title' => $league_name . ' - ' . $event_title . ' (' . $translated_status . ')',
                    'body' => $messageBody,
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default'
                    ]
                ],
                'data' => [
                    'match_id' => (int)$post_id . ';' . (int)$event_id_opposite_lang,
                    'team1' => $team1_id . ';' . $team_ids_opposite[0] . ';' . $team1_data['goals'],
                    'team2' => $team2_id . ';' . $team_ids_opposite[1] . ';' . $team2_data['goals'],
                    'status' => $status
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

    // If $notify is not 'yes', return a message indicating no notification was sent
    return json_encode(['status' => 'info', 'message' => 'No notification sent.']);
}

// Function To Send Match Notification (AR)
function send_notification_to_firebase_silent_ar($post_id, $status, $team_ids, $teams, $language, $event_id_opposite_lang, $team_ids_opposite, $sp_results_opposite_lang, $opposite_language, $notify) {
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
        'exp' => time() + 3600, // 1 hour expiration
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
    
    // Get Team Data
    $team1_id = $team_ids[0];
    $team2_id = $team_ids[1];
    $team1_data = $teams[$team1_id];
    $team2_data = $teams[$team2_id];
    
    // Get Event and Team Titles
    global $wpdb;
	// Fetch the league associated with the current event ($post_id)
$league_terms = wp_get_post_terms($post_id, 'sp_league', array('fields' => 'names'));

// Ensure we get the correct league name for this event
$league_name = (!empty($league_terms) && is_array($league_terms)) ? implode(', ', $league_terms) : '';

    $event_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $post_id));
    $team1_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $team1_id));
    $team2_title = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $team2_id));

    // Prepare the notification body
    $messageBody = $team1_title . ' ' . $team1_data['goals'] . ' - ' . $team2_title . ' ' . $team2_data['goals'];
    $translated_status = translate_status_to_arabic($status);

    // Build the notification payload based on whether to send a silent notification or not
    if ($notify === 'yes') {
		
        // Build the notification payload for a standard notification
        $data = [
            'message' => [
                'topic' => $topic,
                'notification' => [
					'title' => $league_name . ' - ' . $event_title . ' (' . $translated_status . ')',
                    'body' => $messageBody,
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default'
                    ]
                ],
                'data' => [
                    'match_id' => (int)$post_id . ';' . (int)$event_id_opposite_lang,
                    'team1' => $team1_id . ';' . $team_ids_opposite[0] . ';' . $team1_data['goals'],
                    'team2' => $team2_id . ';' . $team_ids_opposite[1] . ';' . $team2_data['goals'],
                    'status' => $status
                ]
            ]
        ];
    } else {
        // Build the notification payload for a silent notification
        $data = [
            'message' => [
                'topic' => $topic,
                'data' => [
                    'match_id' => (int)$post_id . ';' . (int)$event_id_opposite_lang,
                    'team1' => $team1_id . ';' . $team_ids_opposite[0] . ';' . $team1_data['goals'],
                    'team2' => $team2_id . ';' . $team_ids_opposite[1] . ';' . $team2_data['goals'],
                    'status' => $status
                ]
            ]
        ];
    }

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
// Function To Translate Status In AR Notifications
function translate_status_to_arabic($english_status) {
    $translation_map = array(
        'Started' => 'بدأت',
        'Live' => 'مباشر',
        '1st H' => 'الشوط الأول',
        'Break' => 'استراحة',
        '2nd H' => 'الشوط الثاني',
        'Ended' => 'إنتهت',
        'Canceled' => 'ألغيت',
        'Postponed' => 'تأجلت',
        'Default' => 'إفتراضي',
    );
    return isset($translation_map[$english_status]) ? $translation_map[$english_status] : $english_status;
}