<?php
// Hide Header And Footer In pages
// Add custom meta boxes to the page editor
function custom_page_meta_boxes() {
	add_meta_box('hide_header_meta_box', 'Hide Header', 'hide_header_meta_box_callback', 'page', 'side', 'default');
	add_meta_box('hide_footer_meta_box', 'Hide Footer', 'hide_footer_meta_box_callback', 'page', 'side', 'default');
}

add_action('add_meta_boxes', 'custom_page_meta_boxes');

// Callback function to display the hide header meta box
function hide_header_meta_box_callback($post) {
	$hide_header = get_post_meta($post->ID, '_hide_header', true);
?>
<label for="hide_header">
	<input type="checkbox" name="hide_header" id="hide_header" <?php checked($hide_header, 'on'); ?> />
	هل تريد إخفاء الهيدر في هذه الصفحة ؟
</label>
<?php
}

// Callback function to display the hide footer meta box
function hide_footer_meta_box_callback($post) {
	$hide_footer = get_post_meta($post->ID, '_hide_footer', true);
?>
<label for="hide_footer">
	<input type="checkbox" name="hide_footer" id="hide_footer" <?php checked($hide_footer, 'on'); ?> />
	هل تريد إخفاء الفوتر في هذه الصفحة ؟
</label>
<?php
}

// Save custom meta box values when the page is saved
function save_custom_page_meta_boxes($post_id) {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	if (!current_user_can('edit_page', $post_id)) return;

	// Save hide header value
	$hide_header = isset($_POST['hide_header']) ? 'on' : 'off';
	update_post_meta($post_id, '_hide_header', $hide_header);

	// Save hide footer value
	$hide_footer = isset($_POST['hide_footer']) ? 'on' : 'off';
	update_post_meta($post_id, '_hide_footer', $hide_footer);
}

add_action('save_post', 'save_custom_page_meta_boxes');

function enqueue_custom_styles() {
	wp_enqueue_style('custom-dashboard-styles', get_stylesheet_directory_uri() . '/custom-dashboard-styles.css', array(), rand(), 'all');
}

add_action('admin_enqueue_scripts', 'enqueue_custom_styles');


// function get_post_specific_taxonomies($post_id) {
//     global $wpdb;

//     $sql = $wpdb->prepare(
//         "SELECT t.term_id, t.name, t.slug, tt.taxonomy
//         FROM {$wpdb->terms} t
//         INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
//         INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
//         WHERE tr.object_id = %d
//         AND tt.taxonomy IN ('sp_venue', 'sp_league', 'sp_season')",
//         $post_id
//     );

//     $terms = $wpdb->get_results($sql);

//     return $terms;
// }

// Usage example
// $post_id = 18037;
// $terms = get_post_specific_taxonomies($post_id);

// if ($terms) {
//     foreach ($terms as $term) {
//         $term_id_ar = apply_filters('wpml_object_id', $term->term_id, $term->taxonomy, false, 'ar');
//         $term_id_en = apply_filters('wpml_object_id', $term->term_id, $term->taxonomy, false, 'en');

//         echo 'Term ID: ' . $term->term_id . '<br>';
//         echo 'Term ID Ar: ' . $term_id_ar . '<br>';
//         echo 'Term ID En: ' . $term_id_en . '<br>';
//         echo 'Term Name: ' . $term->name . '<br>';
//         echo 'Taxonomy: ' . $term->taxonomy . '<br>';
//         echo '<hr>';
//     }
// } else {
//     echo 'No terms found for the specified taxonomies.';
// }

// Add meta box to the custom post type
function sp_team_meta_box() {
    add_meta_box(
        'sp_team_meta_box',
        'Kuwaiti team',
        'sp_team_meta_box_callback',
        'sp_team',
        'normal',
        'default'
    );
}

add_action('add_meta_boxes', 'sp_team_meta_box');

// Meta box callback function
function sp_team_meta_box_callback($post) {
    $value = get_post_meta($post->ID, 'view_mobile', true);
    ?>
    <label for="view_mobile">
        <input type="checkbox" name="view_mobile" id="view_mobile" <?php checked($value, 'checked'); ?>>
        Activating this function allows the team to be visible within the Teams tab on the mobile application.
    </label>
    <?php
}

// Save meta box data
function sp_team_save_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $checkbox_value = isset($_POST['view_mobile']) ? 'checked' : 'not_checked';
    update_post_meta($post_id, 'view_mobile', $checkbox_value);
}

add_action('save_post', 'sp_team_save_meta_box');

// Add custom fields to taxonomy edit page
function sp_league_taxonomy_add_fields() {
    $taxonomies = array('sp_league'); // Replace with your taxonomy name

    foreach ($taxonomies as $taxonomy) {
        add_action($taxonomy . '_add_form_fields', 'sp_league_taxonomy_add_fields_callback', 10, 2);
        add_action($taxonomy . '_edit_form_fields', 'sp_league_taxonomy_edit_fields_callback', 10, 2);
    }
}
add_action('init', 'sp_league_taxonomy_add_fields');

// Callback function to display the custom fields in the add form
function sp_league_taxonomy_add_fields_callback($taxonomy) {
    ?>
    <div class="form-field">
        <label for="hide_league_in_app">
            <input type="checkbox" name="hide_league_in_app" id="hide_league_in_app" value="true">
            Hide League in App
        </label>
    </div>
    <div class="form-field">
        <label for="hide_standings_in_app">
            <input type="checkbox" name="hide_standings_in_app" id="hide_standings_in_app" value="true">
            Hide Standings in App
        </label>
    </div>
    <?php
}

// Callback function to display the custom fields in the edit form
function sp_league_taxonomy_edit_fields_callback($term, $taxonomy) {
    $hide_league_in_app = get_term_meta($term->term_id, 'hide_league_in_app', true);
    $hide_standings_in_app = get_term_meta($term->term_id, 'hide_standings_in_app', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="hide_league_in_app">Hide League in App</label></th>
        <td>
            <label for="hide_league_in_app">
                <input type="checkbox" name="hide_league_in_app" id="hide_league_in_app" value="true" <?php checked($hide_league_in_app, 'true'); ?>>
            </label>
        </td>
    </tr>
    <tr class="form-field">
        <th scope="row"><label for="hide_standings_in_app">Hide Standings in App</label></th>
        <td>
            <label for="hide_standings_in_app">
                <input type="checkbox" name="hide_standings_in_app" id="hide_standings_in_app" value="true" <?php checked($hide_standings_in_app, 'true'); ?>>
            </label>
        </td>
    </tr>
    <?php
}

// Save custom fields when the taxonomy is saved
function sp_league_taxonomy_save_fields($term_id) {
    if (isset($_POST['hide_league_in_app'])) {
        update_term_meta($term_id, 'hide_league_in_app', 'true');
    } else {
        update_term_meta($term_id, 'hide_league_in_app', 'false');
    }

    if (isset($_POST['hide_standings_in_app'])) {
        update_term_meta($term_id, 'hide_standings_in_app', 'true');
    } else {
        update_term_meta($term_id, 'hide_standings_in_app', 'false');
    }
}
add_action('edited_sp_league', 'sp_league_taxonomy_save_fields');
add_action('create_sp_league', 'sp_league_taxonomy_save_fields');


// *********************** 3 Round In Leagues *********************** //
// Hook into the 'sp_league_add_form_fields' and 'sp_league_edit_form_fields' to add the meta field.
add_action('sp_league_add_form_fields', 'add_sp_league_meta_field', 10, 2);
add_action('sp_league_edit_form_fields', 'edit_sp_league_meta_field', 10, 2);

function add_sp_league_meta_field($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="3_round">
			<input type="checkbox" name="3_round" id="3_round" value="true">
			<?php _e('Enable 3 Round', 'textdomain'); ?>
		</label>
    </div>
    <?php
}

function edit_sp_league_meta_field($term, $taxonomy) {
    $value = get_term_meta($term->term_id, '3_round', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="3_round"><?php _e('Enable 3 Round', 'textdomain'); ?></label></th>
        <td>
            <input type="checkbox" name="3_round" id="3_round" value="true" <?php checked($value, 'true'); ?>>
        </td>
    </tr>
    <?php
}
// Hook into 'created_sp_league' and 'edited_sp_league' to save the custom meta.
add_action('created_sp_league', 'save_sp_league_meta', 10, 2);
add_action('edited_sp_league', 'save_sp_league_meta', 10, 2);

function save_sp_league_meta($term_id) {
    if (isset($_POST['3_round'])) {
        update_term_meta($term_id, '3_round', 'true');
    } else {
        update_term_meta($term_id, '3_round', 'false');
    }
}
function register_sp_league_meta_for_wpml() {
    if (function_exists('icl_register_string')) {
        icl_register_string('sp_league', '3_round', '3 Round');
    }
}
add_action('init', 'register_sp_league_meta_for_wpml');
// Hook into 'created_sp_league' and 'edited_sp_league' to save the custom meta across all languages.
add_action('created_sp_league', 'save_sp_league_meta_across_languages', 10, 2);
add_action('edited_sp_league', 'save_sp_league_meta_across_languages', 10, 2);

function save_sp_league_meta_across_languages($term_id) {
    // Get the value of the meta field from the submitted form
    $meta_value = isset($_POST['3_round']) ? 'true' : 'false';

    // Update the meta field for the current language
    update_term_meta($term_id, '3_round', $meta_value);

    // Check if WPML is active
    if (function_exists('icl_object_id')) {
        // Get all translations of the current term
        $translations = apply_filters('wpml_get_element_translations', null, apply_filters('wpml_element_trid', null, $term_id, 'tax_sp_league'), 'tax_sp_league');

        // Loop through each translation and update the meta field
        if (!empty($translations)) {
            foreach ($translations as $lang => $term_translation) {
                if ($term_translation->element_id != $term_id) {
                    update_term_meta($term_translation->element_id, '3_round', $meta_value);
                }
            }
        }
    }
}
function register_sp_league_meta_for_wpml_sync() {
    if (function_exists('icl_register_string')) {
        icl_register_string('sp_league', '3_round', '3 Round');
    }
}
add_action('init', 'register_sp_league_meta_for_wpml_sync');




add_action('rest_api_init', function () {
    register_rest_route('handball/v1', '/teams/', array(
        'methods'  => 'GET',
        'callback' => 'custom_teams_endpoint_callback',
    ));
});

function custom_teams_endpoint_callback(WP_REST_Request $request) {
    $page = $request->get_param('page');
    $lang = $request->get_param('lang') ? $request->get_param('lang') : 'ar'; // Default to 'ar' if no 'lang' is provided
    
    // Prepare the URL for the existing API call with dynamic language parameter
    $existing_api_url = "https://handball.org.kw/wp-json/json2/v1/teams.php?page={$page}&lang={$lang}&posts_per_page=50";
    
    // Make an API request to the existing API
    $response = wp_remote_get($existing_api_url);
    
    // Check for errors in the API response
    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Unable to retrieve teams data', array('status' => 500));
    }

    // Get the body of the response
    $body = wp_remote_retrieve_body($response);
    
    // Decode the JSON response
    $data = json_decode($body, true);
    
    // Return the API response
    return rest_ensure_response($data);
}
function sp_add_match_status_meta_box() {
    add_meta_box(
        'sp_match_status',
        'Match Status',
        'sp_match_status_callback',
        'sp_event',
        'side'
    );
}
add_action('add_meta_boxes', 'sp_add_match_status_meta_box');

function sp_match_status_callback($post) {
    $value = get_post_meta($post->ID, '_sp_match_status', true);
$statuses = [
    'not_started'   => icl_t('Match Status', 'Not Started', 'Not Started'),
    'live'          => icl_t('Match Status', 'Live', 'Live'),
    'first_half'    => icl_t('Match Status', '1st Half', '1st Half'),
    'second_half'   => icl_t('Match Status', '2nd Half', '2nd Half'),
    'finished'      => icl_t('Match Status', 'Finished', 'Finished'),
    'suspended'     => icl_t('Match Status', 'Suspended', 'Suspended'),
    'postponed'     => icl_t('Match Status', 'Postponed', 'Postponed'),
    'cancelled'     => icl_t('Match Status', 'Cancelled', 'Cancelled'),
];

    echo '<select name="sp_match_status" style="width:100%;">';
    foreach ($statuses as $key => $label) {
        echo '<option value="'. esc_attr($key) .'" '. selected($value, $key, false) .'>'. esc_html($label) .'</option>';
    }
    echo '</select>';
    echo '<input type="hidden" name="sp_match_status_nonce" value="' . wp_create_nonce('save_sp_match_status') . '">';
}

function sp_save_match_status($post_id) {
    if (!isset($_POST['sp_match_status_nonce']) || !wp_verify_nonce($_POST['sp_match_status_nonce'], 'save_sp_match_status')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['sp_match_status'])) {
        update_post_meta($post_id, '_sp_match_status', sanitize_text_field($_POST['sp_match_status']));
    }
}
add_action('save_post', 'sp_save_match_status');

function render_match_translations_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'match_meta_translations';

    // قائمة الميتا المتاحة
    $meta_keys = ['sp_day', 'match_status'];

    // معالجة POST (إضافة، تعديل، حذف)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        check_admin_referer('match_translations_nonce'); // حماية nonce

        $meta_key = sanitize_text_field($_POST['meta_key']);
        $value_en = sanitize_text_field($_POST['value_en']);
        $value_ar = sanitize_text_field($_POST['value_ar']);

        if ($_POST['action'] === 'add') {
            $wpdb->insert($table, compact('meta_key', 'value_en', 'value_ar'));
        } elseif ($_POST['action'] === 'update') {
            $id = intval($_POST['id']);
            $wpdb->update($table, compact('meta_key', 'value_en', 'value_ar'), ['id' => $id]);
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            $wpdb->delete($table, ['id' => $id]);
        }
        // الصفحة ستعاد تحميلها تلقائياً بعد العملية
    }

    // جلب كل البيانات بدون فلتر (لأننا نفلتر جافاسكريبتياً)
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY meta_key ASC, id ASC");

    ?>
    <div class="wrap" style="max-width: 1000px;">
        <h2>Match Meta Translations</h2>

        <!-- نموذج إضافة جديد فوق كل شيء -->
        <div style="margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 6px;">
            <h3 style="margin-top:0;">Add New Translation</h3>
            <form method="post" style="max-width:600px;">
                <?php wp_nonce_field('match_translations_nonce'); ?>
                <input type="hidden" name="action" value="add" />
                <table class="form-table">
                    <tr>
                        <th scope="row">Meta Key</th>
                        <td>
                            <select name="meta_key" required>
                                <?php foreach ($meta_keys as $key): ?>
                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">English Value</th>
                        <td><input type="text" name="value_en" required style="width: 100%;" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Arabic Value</th>
                        <td><input type="text" name="value_ar" required style="width: 100%;" /></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" class="button-primary button" value="Add Translation" /></p>
            </form>
        </div>

        <!-- فلتر مباشر -->
        <label for="filter_meta_key"><strong>Filter by Meta Key:</strong> </label>
        <select id="filter_meta_key" style="margin-bottom: 15px;">
            <option value="all">-- Show All --</option>
            <?php foreach ($meta_keys as $key): ?>
                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($key); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- جدول الترجمات -->
        <table class="widefat fixed" cellspacing="0" style="width:100%; max-width:1000px;">
            <thead>
                <tr>
                    <th style="width:40px;">ID</th>
                    <th>Meta Key</th>
                    <th>English</th>
                    <th>Arabic</th>
                    <th style="width:170px;">Actions</th>
                </tr>
            </thead>
            <tbody id="translations_table_body">
                <?php if ($rows): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr data-meta-key="<?php echo esc_attr($row->meta_key); ?>" data-row-id="<?php echo intval($row->id); ?>">
                            <td><?php echo intval($row->id); ?></td>
                            <td class="meta_key_display"><?php echo esc_html($row->meta_key); ?></td>
                            <td class="value_en_display"><?php echo esc_html($row->value_en); ?></td>
                            <td class="value_ar_display"><?php echo esc_html($row->value_ar); ?></td>
                            <td style="white-space:nowrap;">
                                <button class="button button-small edit-btn" data-id="<?php echo intval($row->id); ?>">Edit</button>
                                <form method="post" style="display:inline-block; margin-left:5px;">
                                    <?php wp_nonce_field('match_translations_nonce'); ?>
                                    <input type="hidden" name="id" value="<?php echo intval($row->id); ?>" />
                                    <input type="hidden" name="action" value="delete" />
                                    <input type="submit" class="button button-danger button-small" value="Delete" onclick="return confirm('Are you sure?');" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; font-style:italic;">No translations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <style>
        /* تنسيق الحقول عند التعديل */
        .editing input, .editing select {
            width: 95%;
            box-sizing: border-box;
        }
        .editing .edit-btn { display: none; }
        .editing .save-btn, .editing .cancel-btn {
            display: inline-block;
            margin-left: 5px;
        }
        .save-btn, .cancel-btn {
            display: none;
        }
    </style>

    <script>
    (function(){
        const filterSelect = document.getElementById('filter_meta_key');
        const tableBody = document.getElementById('translations_table_body');

        // فلتر الصفوف حسب اختيار المستخدم
        filterSelect.addEventListener('change', function(){
            const filter = this.value;
            const rows = tableBody.querySelectorAll('tr');

            rows.forEach(row => {
                const metaKey = row.getAttribute('data-meta-key');
                if(filter === 'all' || metaKey === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // إضافة وظيفة زر التعديل
        tableBody.addEventListener('click', function(e){
            if(!e.target.classList.contains('edit-btn')) return;
            const btn = e.target;
            const tr = btn.closest('tr');
            const rowId = tr.getAttribute('data-row-id');

            if(tr.classList.contains('editing')) return; // اذا مفتوح للتعديل مسبقاً

            tr.classList.add('editing');

            // استبدال الخلايا بنماذج إدخال قابلة للتعديل
            const metaKeyText = tr.querySelector('.meta_key_display').innerText.trim();
            const valueEnText = tr.querySelector('.value_en_display').innerText.trim();
            const valueArText = tr.querySelector('.value_ar_display').innerText.trim();

            // اختر القائمة المنسدلة مع القيم المتاحة للميتا
            const metaKeys = <?php echo json_encode($meta_keys); ?>;

            // بناء select للميتا
            let selectHtml = '<select name="meta_key">';
            metaKeys.forEach(function(key) {
                selectHtml += `<option value="${key}" ${key === metaKeyText ? 'selected' : ''}>${key}</option>`;
            });
            selectHtml += '</select>';

            tr.querySelector('.meta_key_display').innerHTML = selectHtml;
            tr.querySelector('.value_en_display').innerHTML = `<input type="text" name="value_en" value="${valueEnText}" />`;
            tr.querySelector('.value_ar_display').innerHTML = `<input type="text" name="value_ar" value="${valueArText}" />`;

            // استبدال زر التعديل بزر حفظ وإلغاء
            btn.style.display = 'none';

            let saveBtn = document.createElement('button');
            saveBtn.textContent = 'Save';
            saveBtn.className = 'button button-primary button-small save-btn';

            let cancelBtn = document.createElement('button');
            cancelBtn.textContent = 'Cancel';
            cancelBtn.className = 'button button-secondary button-small cancel-btn';

            btn.parentNode.appendChild(saveBtn);
            btn.parentNode.appendChild(cancelBtn);

            // عند الضغط على حفظ
            saveBtn.addEventListener('click', function(){
                // إنشاء نموذج POST ديناميكي لإرسال التحديث
                const form = document.createElement('form');
                form.method = 'post';

                // إضافة الحقول المطلوبة
                form.innerHTML = `
                    <?php echo wp_nonce_field('match_translations_nonce', '_wpnonce', true, false); ?>
                    <input type="hidden" name="action" value="update" />
                    <input type="hidden" name="id" value="${rowId}" />
                `;

                const selectedMetaKey = tr.querySelector('select[name="meta_key"]').value;
                const valEn = tr.querySelector('input[name="value_en"]').value.trim();
                const valAr = tr.querySelector('input[name="value_ar"]').value.trim();

                if(valEn === '' || valAr === '') {
                    alert('Please fill all fields before saving.');
                    return;
                }

                // إنشاء عناصر input لإرسال القيم
                const metaInput = document.createElement('input');
                metaInput.type = 'hidden';
                metaInput.name = 'meta_key';
                metaInput.value = selectedMetaKey;
                form.appendChild(metaInput);

                const valEnInput = document.createElement('input');
                valEnInput.type = 'hidden';
                valEnInput.name = 'value_en';
                valEnInput.value = valEn;
                form.appendChild(valEnInput);

                const valArInput = document.createElement('input');
                valArInput.type = 'hidden';
                valArInput.name = 'value_ar';
                valArInput.value = valAr;
                form.appendChild(valArInput);

                document.body.appendChild(form);
                form.submit();
            });

            // عند الضغط على إلغاء
            cancelBtn.addEventListener('click', function(){
                tr.classList.remove('editing');

                // إعادة القيم الأصلية للعرض فقط
                tr.querySelector('.meta_key_display').textContent = metaKeyText;
                tr.querySelector('.value_en_display').textContent = valueEnText;
                tr.querySelector('.value_ar_display').textContent = valueArText;

                saveBtn.remove();
                cancelBtn.remove();
                btn.style.display = 'inline-block';
            });
        });

    })();
    </script>

    <?php
}

function hide_header_footer_styles() {
	if (is_singular()) {
		$hide_header = get_post_meta(get_the_ID(), '_hide_header', true);
		$hide_footer = get_post_meta(get_the_ID(), '_hide_footer', true);

		if ($hide_header == 'on' || $hide_footer == 'on') {
			echo '<style>';
			if ($hide_header == 'on') {
				echo '#Header { display: none !important; }';
			}
			if ($hide_footer == 'on') {
				echo '#Footer { display: none !important; }';
			}
			echo '</style>';
		}
	}
}
add_action('wp_head', 'hide_header_footer_styles');