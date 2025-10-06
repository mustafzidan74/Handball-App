<?php 
function register_sp_team_api_endpoint() {
	register_rest_route('handball/v1', '/team/', array(
		'methods' => 'GET',
		'callback' => 'sp_team_api_callback',
		'args' => array(
			'id' => array(
				'validate_callback' => function($param, $request, $key) {
					return is_numeric($param);
				}
			),
			'lang' => array(
				'validate_callback' => function ($param, $request, $key) {
					// Validate the language parameter
					// You may need to adjust this validation based on your specific requirements
					return in_array($param, array('ar', 'en'));
				},
			),
		),
	));
}
add_action('rest_api_init', 'register_sp_team_api_endpoint');

function sp_team_api_callback($data) {
	$team_id = $data->get_param('id');
    $season = get_option('app_season');
	$lang = $data->get_param('lang');

	// Set the WPML language for the queries
	if ($lang) {
		do_action('wpml_switch_language', $lang);
	}

	$team_position = get_team_position($team_id, get_option('app_order'));
	// Get team information
	$team_data = array(
		'id' => $team_id,
		'title' => get_the_title($team_id),
		'image' => get_the_post_thumbnail_url($team_id) ? get_the_post_thumbnail_url($team_id) : null,
		'banner' => get_post_meta($team_id)['sp_url'],
		'order'	=> $team_position,
// 		'content' => get_post_field('post_content', $team_id),
	);

					
	// Get associated players based on the team ID
	$players_query = new WP_Query(array(
		'post_type' => 'sp_player',
		'meta_query' => array(
			array(
				'key' => 'sp_current_team', // Assuming 'team_id' is the custom field linking players to teams
				'value' => $team_id,
				'compare' => '=',
			),
		),
	));

	$players_data = array();
	if ($players_query->have_posts()) {
		while ($players_query->have_posts()) {
			$players_query->the_post();
			$player_id = get_the_ID();
			$pos = wp_get_post_terms($player_id, 'sp_position', array('fields' => 'names'))[0];
			$player_data = array(
				'id' => $player_id,
				'title' => get_the_title($player_id),
				'image' => get_the_post_thumbnail_url($player_id) ? get_the_post_thumbnail_url($player_id) : null,
				'position' => $pos,
				'birthday' => get_post_time('Y-m-d', false, $player_id),
// 				'team'	=> get_the_title($team_id),
				'nationality' => get_full_nationality_name(get_post_meta($player_id)['sp_nationality'][0], $lang),
			);

			$players_data[] = $player_data;
		}
		wp_reset_postdata(); // Reset the post data to the main query
	}

	// Get matches for the team based on the team ID
	if ($season) {
		$matches_query = new WP_Query(array(
			'post_type' => 'sp_event',
			'post_status'    => array('publish', 'future'),
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'sp_team',
					'value' => $team_id,
					'compare' => '=',
				),
			),
			// Add taxonomy query for 'sp_season' if season parameter is provided
			'tax_query' => array(
				array(
					'taxonomy' => 'sp_season',
					'field'    => 'term_id',
					'terms'    => $season,
				),
			),
		));
	} else {
		$matches_query = new WP_Query(array(
			'post_type' => 'sp_event',
			'post_status'    => array('publish', 'future'),
			'meta_query' => array(
				array(
					'key' => 'sp_team',
					'value' => $team_id,
					'compare' => '=',
				),
			),
		));
	}

	$matches_data = array();
	$past_matches = array();
	$future_matches = array();

	if ($matches_query->have_posts()) {
		while ($matches_query->have_posts()) {
			$matches_query->the_post();
			$match_id = get_the_ID();

			// Compare event date with current date
			$event_date = get_post_time('Y-m-d', false, $match_id); 
			$current_date = date('Y-m-d');
			$is_future = $event_date >= $current_date;
        // Reuse logic from get_matches function to retrieve match data
        $resultMatch = get_post_meta($match_id, 'sp_results', true);
        $resultMatch = !empty($resultMatch[$team_id]) ? $resultMatch[$team_id] : null;


			// Reuse logic from get_matches function to retrieve match data
			$match_data = array(
				'id'     => $match_id,
				'title'  => get_the_title($match_id),
				'date'   => get_post_time('Y-m-d', false, $match_id),// Assuming 'sp_day' is the meta key for date
				'time'   => get_post_time('H:i:s', false, $match_id),
				'status' => get_post_meta($event->ID,'match_live', true), // Update with the appropriate status logic
				'week'   => get_post_meta($match_id, 'sp_day', true), // Update with the appropriate week information
				'venue'  => wp_get_post_terms($match_id, 'sp_venue', array('fields' => 'names')),
				'teams'  => get_teams_data2($match_id), // Custom function to get teams data
				'leagues' => wp_get_post_terms($match_id, 'sp_league', array('fields' => 'names')),
				'seasons' => wp_get_post_terms($match_id, 'sp_season', array('fields' => 'names')),
			);

			// Check conditions for including the event in past_matches or future_matches
			if ($event_date >= $current_date) {
				$future_matches[] = $match_data;
			} else {
				$past_matches[] = $match_data;
			}
			

			// ... (add more fields based on your requirements)

			$matches_data[] = $match_data;
		}
		wp_reset_postdata(); // Reset the post data to the main query
	}
	// Combine team, player, and match data
	$result_data = array(
// 		'staff_data'=> $staff_data,
		'team' => $team_data,
		'players' => $players_data,
		'past_matches' => $past_matches,
		'future_matches' => $future_matches,
	);

	return rest_ensure_response($result_data);
}

function get_full_nationality_name($short_name, $lang) {
    // Define arrays for nationalities in different languages
    $nationalities_en = array(
                'alg' => 'Algeria',
        'ang' => 'Angola',
        'ben' => 'Benin',
        'bot' => 'Botswana',
        'bfa' => 'Burkina Faso',
        'bdi' => 'Burundi',
        'cmr' => 'Cameroon',
        'cpv' => 'Cape Verde',
        'cta' => 'Central African Republic',
        'cha' => 'Chad',
        'com' => 'Comoros',
        'cod' => 'Democratic Republic of the Congo',
        'dji' => 'Djibouti',
        'egy' => 'Egypt',
        'eqg' => 'Equatorial Guinea',
        'eri' => 'Eritrea',
        'eth' => 'Ethiopia',
        'gab' => 'Gabon',
        'gam' => 'Gambia',
        'gha' => 'Ghana',
        'gui' => 'Guinea',
        'gnb' => 'Guinea-Bissau',
        'civ' => 'Ivory Coast',
        'ken' => 'Kenya',
        'les' => 'Lesotho',
        'lbr' => 'Liberia',
        'lby' => 'Libya',
        'mad' => 'Madagascar',
        'mli' => 'Mali',
        'mtn' => 'Mauritania',
        'mri' => 'Mauritius',
        'mar' => 'Morocco',
        'moz' => 'Mozambique',
        'nam' => 'Namibia',
        'nig' => 'Niger',
        'nga' => 'Nigeria',
        'cgo' => 'Republic of the Congo',
        'rwa' => 'Rwanda',
        'stp' => 'Sao Tome and Principe',
        'sen' => 'Senegal',
        'sey' => 'Seychelles',
        'sle' => 'Sierra Leone',
        'som' => 'Somalia',
        'rsa' => 'South Africa',
        'ssd' => 'South Sudan',
        'sdn' => 'Sudan',
        'swz' => 'Swaziland',
        'tan' => 'Tanzania',
        'tog' => 'Togo',
        'tun' => 'Tunisia',
        'uga' => 'Uganda',
        'esh' => 'Western Sahara',
        'zam' => 'Zambia',
        'zim' => 'Zimbabwe',
        'afg' => 'Afghanistan',
        'arm' => 'Armenia',
        'aze' => 'Azerbaijan',
        'bhr' => 'Bahrain',
        'ban' => 'Bangladesh',
        'bhu' => 'Bhutan',
        'bru' => 'Brunei',
        'mya' => 'Burma',
        'cam' => 'Cambodia',
        'chn' => 'China',
        'cyp' => 'Cyprus',
        'geo' => 'Georgia',
        'hkg' => 'Hong Kong',
        'ind' => 'India',
        'irn' => 'Iran',
        'irq' => 'Iraq',
        'isr' => 'Israel',
        'jpn' => 'Japan',
        'jor' => 'Jordan',
        'kaz' => 'Kazakhstan',
        'kuw' => 'Kuwait',
        'kgz' => 'Kyrgyzstan',
        'lao' => 'Laos',
        'lib' => 'Lebanon',
        'mac' => 'Macau',
        'mas' => 'Malaysia',
        'mdv' => 'Maldives',
        'mng' => 'Mongolia',
        'nep' => 'Nepal',
        'prk' => 'North Korea',
        'oma' => 'Oman',
        'pak' => 'Pakistan',
        'ple' => 'Palestine',
        'phi' => 'Philippines',
        'qat' => 'Qatar',
        'ksa' => 'Saudi Arabia',
        'sin' => 'Singapore',
        'kor' => 'South Korea',
        'sri' => 'Sri Lanka',
        'syr' => 'Syria',
        'tpe' => 'Taiwan',
        'tjk' => 'Tajikistan',
        'tha' => 'Thailand',
        'tkm' => 'Turkmenistan',
        'uae' => 'United Arab Emirates',
        'uzb' => 'Uzbekistan',
        'vie' => 'Vietnam',
        'yem' => 'Yemen',
        'alb' => 'Albania',
        'and' => 'Andorra',
        'aut' => 'Austria',
        'blr' => 'Belarus',
        'bel' => 'Belgium',
        'bih' => 'Bosnia and Herzegovina',
        'bul' => 'Bulgaria',
        'cro' => 'Croatia',
        'cze' => 'Czech Republic',
        'den' => 'Denmark',
        'eng' => 'England',
        'est' => 'Estonia',
        'fro' => 'Faroe Islands',
        'fin' => 'Finland',
        'fra' => 'France',
        'ger' => 'Germany',
        'gre' => 'Greece',
        'hun' => 'Hungary',
        'isl' => 'Iceland',
        'irl' => 'Ireland',
        'ita' => 'Italy',
        'kos' => 'Kosovo',
        'lva' => 'Latvia',
        'lie' => 'Liechtenstein',
        'ltu' => 'Lithuania',
        'lux' => 'Luxembourg',
        'mkd' => 'Macedonia',
        'mwi' => 'Malawi',
        'mlt' => 'Malta',
        'mda' => 'Moldova',
        'mco' => 'Monaco',
        'mne' => 'Montenegro',
        'ned' => 'Netherlands',
        'nir' => 'Northern Ireland',
        'nor' => 'Norway',
        'pol' => 'Poland',
        'por' => 'Portugal',
        'rou' => 'Romania',
        'rus' => 'Russia',
        'smr' => 'San Marino',
        'sco' => 'Scotland',
        'srb' => 'Serbia',
        'svk' => 'Slovakia',
        'svn' => 'Slovenia',
        'esp' => 'Spain',
        'swz' => 'Swaziland',
        'swe' => 'Sweden',
        'sui' => 'Switzerland',
        'tur' => 'Turkey',
        'ukr' => 'Ukraine',
        'gbr' => 'United Kingdom',
        'vat' => 'Vatican City',
        'wal' => 'Wales',
        'aia' => 'Anguilla',
        'atg' => 'Antigua and Barbuda',
        'aru' => 'Aruba',
        'bah' => 'Bahamas',
        'brb' => 'Barbados',
        'blz' => 'Belize',
        'ber' => 'Bermuda',
        'vgb' => 'British Virgin Islands',
        'can' => 'Canada',
        'cay' => 'Cayman Islands',
        'crc' => 'Costa Rica',
        'cub' => 'Cuba',
        'cuw' => 'Curacao',
        'dma' => 'Dominica',
        'dom' => 'Dominican Republic',
        'slv' => 'El Salvador',
        'grn' => 'Grenada',
        'gua' => 'Guatemala',
        'hai' => 'Haiti',
        'hon' => 'Honduras',
        'jam' => 'Jamaica',
        'mex' => 'Mexico',
        'msr' => 'Montserrat',
        'nca' => 'Nicaragua',
        'pan' => 'Panama',
        'pur' => 'Puerto Rico',
        'skn' => 'Saint Kitts and Nevis',
        'lca' => 'Saint Lucia',
        'vin' => 'Saint Vincent and the Grenadines',
        'tca' => 'Turks and Caicos Islands',
        'vir' => 'US Virgin Islands',
        'usa' => 'United States',
        'wif' => 'West Indies',
        'asa' => 'American Samoa',
        'aus' => 'Australia',
        'cok' => 'Cook Islands',
        'tls' => 'East Timor',
        'fij' => 'Fiji',
        'gum' => 'Guam',
        'idn' => 'Indonesia',
        'kir' => 'Kiribati',
        'mhl' => 'Marshall Islands',
        'fsm' => 'Micronesia',
        'nru' => 'Nauru',
        'ncl' => 'New Caledonia',
        'nzl' => 'New Zealand',
        'plw' => 'Palau',
        'png' => 'Papua New Guinea',
        'sam' => 'Samoa',
        'sol' => 'Solomon Islands',
        'tah' => 'Tahiti',
        'tga' => 'Tonga',
        'tuv' => 'Tuvalu',
        'van' => 'Vanuatu',
        'arg' => 'Argentina',
        'bol' => 'Bolivia',
        'bra' => 'Brazil',
        'chi' => 'Chile',
        'col' => 'Colombia',
        'ecu' => 'Ecuador',
        'guy' => 'Guyana',
        'par' => 'Paraguay',
        'per' => 'Peru',
        'sur' => 'Suriname',
        'tri' => 'Trinidad and Tobago',
        'uru' => 'Uruguay',
        'ven' => 'Venezuela',
    );

    $nationalities_ar = array(
		'alg' => 'الجزائر',
		'ang' => 'أنغولا',
		'ben' => 'بنين',
		'bot' => 'بتسوانا',
		'bfa' => 'بوركينا فاسو',
		'bdi' => 'بوروندي',
		'cmr' => 'الكاميرون',
		'cpv' => 'الرأس الأخضر',
		'cta' => 'جمهورية أفريقيا الوسطى',
		'cha' => 'تشاد',
		'com' => 'جزر القمر',
		'cod' => 'جمهورية الكونغو الديمقراطية',
		'dji' => 'جيبوتي',
		'egy' => 'مصر',
		'eqg' => 'غينيا الاستوائية',
		'eri' => 'إريتريا',
		'eth' => 'إثيوبيا',
		'gab' => 'الغابون',
		'gam' => 'غامبيا',
		'gha' => 'غانا',
		'gui' => 'غينيا',
		'gnb' => 'غينيا بيساو',
		'civ' => 'ساحل العاج',
		'ken' => 'كينيا',
		'les' => 'ليسوتو',
		'lbr' => 'ليبيريا',
		'lby' => 'ليبيا',
		'mad' => 'مدغشقر',
		'mli' => 'مالي',
		'mtn' => 'موريتانيا',
		'mri' => 'موريشيوس',
		'mar' => 'المغرب',
		'moz' => 'موزمبيق',
		'nam' => 'ناميبيا',
		'nig' => 'نيجيريا',
		'nga' => 'نيجيريا',
		'cgo' => 'جمهورية الكونغو',
		'rwa' => 'رواندا',
		'stp' => 'ساو تومي وبرينسيبي',
		'sen' => 'السنغال',
		'sey' => 'سيشل',
		'sle' => 'سيراليون',
		'som' => 'الصومال',
		'rsa' => 'جنوب أفريقيا',
		'ssd' => 'جنوب السودان',
		'sdn' => 'السودان',
		'swz' => 'إسواتيني',
		'tan' => 'تنزانيا',
		'tog' => 'توجو',
		'tun' => 'تونس',
		'uga' => 'أوغندا',
		'esh' => 'الصحراء الغربية',
		'zam' => 'زامبيا',
		'zim' => 'زيمبابوي',
		'afg' => 'أفغانستان',
		'arm' => 'أرمينيا',
		'aze' => 'أذربيجان',
		'bhr' => 'البحرين',
		'ban' => 'بنغلاديش',
		'bhu' => 'بوتان',
		'bru' => 'بروناي',
		'mya' => 'بورما',
		'cam' => 'كمبوديا',
		'chn' => 'الصين',
		'cyp' => 'قبرص',
		'geo' => 'جورجيا',
		'hkg' => 'هونغ كونغ',
		'ind' => 'الهند',
		'irn' => 'إيران',
		'irq' => 'العراق',
		'isr' => 'إسرائيل',
		'jpn' => 'اليابان',
		'jor' => 'الأردن',
		'kaz' => 'كازاخستان',
		'kuw' => 'الكويت',
		'kgz' => 'قرغيزستان',
		'lao' => 'لاوس',
		'lib' => 'لبنان',
		'mac' => 'ماكاو',
		'mas' => 'ماليزيا',
		'mdv' => 'جزر المالديف',
		'mng' => 'منغوليا',
		'nep' => 'نيبال',
		'prk' => 'كوريا الشمالية',
		'oma' => 'عمان',
		'pak' => 'باكستان',
		'ple' => 'فلسطين',
		'phi' => 'الفيلبين',
		'qat' => 'قطر',
		'ksa' => 'المملكة العربية السعودية',
		'sin' => 'سنغافورة',
		'kor' => 'كوريا الجنوبية',
		'sri' => 'سريلانكا',
		'syr' => 'سوريا',
		'tpe' => 'تايوان',
		'tjk' => 'طاجيكستان',
		'tha' => 'تايلاند',
		'tkm' => 'تركمانستان',
		'uae' => 'الإمارات العربية المتحدة',
		'uzb' => 'أوزبكستان',
		'vie' => 'فيتنام',
		'yem' => 'اليمن',
		'alb' => 'ألبانيا',
		'and' => 'أندورا',
		'aut' => 'النمسا',
		'blr' => 'بيلاروس',
		'bel' => 'بلجيكا',
		'bih' => 'البوسنة والهرسك',
		'bul' => 'بلغاريا',
		'cro' => 'كرواتيا',
		'cze' => 'التشيك',
		'den' => 'الدانمارك',
		'eng' => 'إنجلترا',
		'est' => 'إستونيا',
		'fro' => 'جزر فارو',
		'fin' => 'فنلندا',
		'fra' => 'فرنسا',
		'ger' => 'ألمانيا',
		'gre' => 'اليونان',
		'hun' => 'هنغاريا',
		'isl' => 'أيسلندا',
		'irl' => 'أيرلندا',
		'ita' => 'إيطاليا',
		'kos' => 'كوسوفو',
		'lva' => 'لاتفيا',
		'lie' => 'ليختنشتاين',
		'ltu' => 'ليتوانيا',
		'lux' => 'لوكسمبورغ',
		'mkd' => 'مقدونيا',
		'mwi' => 'ملاوي',
		'mlt' => 'مالطا',
		'mda' => 'مولدوفا',
		'mco' => 'موناكو',
		'mne' => 'الجبل الأسود',
		'ned' => 'هولندا',
		'nir' => 'إيرلندا الشمالية',
		'nor' => 'النرويج',
		'pol' => 'بولندا',
		'por' => 'البرتغال',
		'rou' => 'رومانيا',
		'rus' => 'روسيا',
		'smr' => 'سان مارينو',
		'sco' => 'اسكتلندا',
		'srb' => 'صربيا',
		'svk' => 'سلوفاكيا',
		'svn' => 'سلوفينيا',
		'esp' => 'إسبانيا',
		'swe' => 'السويد',
		'sui' => 'سويسرا',
		'tur' => 'تركيا',
		'ukr' => 'أوكرانيا',
		'gbr' => 'المملكة المتحدة',
		'vat' => 'الفاتيكان',
		'wal' => 'ويلز',
		'aia' => 'أنغويلا',
		'atg' => 'أنتيغوا وبربودا',
		'aru' => 'أروبا',
		'bah' => 'البهاما',
		'brb' => 'بربادوس',
		'blz' => 'بليز',
		'ber' => 'برمودا',
		'vgb' => 'جزر العذراء البريطانية',
		'can' => 'كندا',
		'cay' => 'جزر كايمان',
		'crc' => 'كوستا ريكا',
		'cub' => 'كوبا',
		'cuw' => 'كوراساو',
		'dma' => 'دومينيكا',
		'dom' => 'جمهورية الدومينيكان',
		'slv' => 'السلفادور',
		'grn' => 'غرينادا',
		'gua' => 'غواتيمالا',
		'hai' => 'هايتي',
		'hon' => 'هندوراس',
		'jam' => 'جامايكا',
		'mex' => 'المكسيك',
		'msr' => 'مونتسرات',
		'nca' => 'نيكاراغوا',
		'pan' => 'بنما',
		'pur' => 'بورتوريكو',
		'skn' => 'سانت كيتس ونيفيس',
		'lca' => 'سانت لوسيا',
		'vin' => 'سانت فنسنت وجزر الغرينادين',
		'tca' => 'جزر توركس وكايكوس',
		'vir' => 'جزر فيرجن الأمريكية',
		'usa' => 'الولايات المتحدة',
		'wif' => 'الهند الغربية',
		'asa' => 'ساموا الأمريكية',
		'aus' => 'أستراليا',
		'cok' => 'جزر كوك',
		'tls' => 'تيمور الشرقية',
		'fij' => 'فيجي',
		'gum' => 'غوام',
		'idn' => 'إندونيسيا',
		'kir' => 'كيريباتي',
		'mhl' => 'جزر مارشال',
		'fsm' => 'ميكرونيزيا',
		'nru' => 'ناورو',
		'ncl' => 'كاليدونيا الجديدة',
		'nzl' => 'نيوزيلندا',
		'plw' => 'بالاو',
		'png' => 'بابوا غينيا الجديدة',
		'sam' => 'ساموا',
		'sol' => 'جزر سليمان',
		'tah' => 'تاهيتي',
		'tga' => 'تونغا',
		'tuv' => 'توفالو',
		'van' => 'فانواتو',
		'arg' => 'الأرجنتين',
		'bol' => 'بوليفيا',
		'bra' => 'البرازيل',
		'chi' => 'تشيلي',
		'col' => 'كولومبيا',
		'ecu' => 'الإكوادور',
		'guy' => 'غيانا',
		'par' => 'باراغواي',
		'per' => 'بيرو',
		'sur' => 'سورينام',
		'tri' => 'ترينيداد وتوباغو',
		'uru' => 'أوروغواي',
		'ven' => 'فنزويلا',

    );

    // Choose the appropriate array based on the language parameter
    $nationalities = ($lang == 'ar') ? $nationalities_ar : $nationalities_en;

    // Check if the short name exists in the mapping, return full name if found, otherwise return the short name
    return isset($nationalities[$short_name]) ? $nationalities[$short_name] : $short_name;
}



// Custom function to get teams data
function get_teams_data2($match_id) {
	$team_ids = get_post_meta($match_id, 'sp_team');

	$teams_data = array();

	foreach ($team_ids as $index => $team_id) {
		$team_id = !empty($team_id) ? (int)$team_id : null;
		$team_post = apply_filters('wpml_object_id', $team_id, 'sp_team', true);

		$resultMatch = get_post_meta($match_id, 'sp_results', true);

		$resultMatch = !empty($resultMatch[$team_id]) ? $resultMatch[$team_id] : null;

		if ($team_post) {
			$team_title = get_the_title($team_post);
			$team_image = get_the_post_thumbnail_url($team_post, 'full'); // Change 'full' to the desired image size
			$team_image = $team_image ? $team_image : '';

			$team_type = ($index == 0) ? 'first_team' : 'second_team';

			$teams_data[$team_type] = array(
				'id' => $team_id,
				'title' => $team_title,
				'image' => $team_image,
				'results' => $resultMatch,
			);
		}
	}

	return $teams_data;
}

function get_team_position٢($team_id, $table_id) {
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
