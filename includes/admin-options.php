<?php
// Handball Options - single page settings and helpers
if (!defined('ABSPATH')) exit;

if (!defined('HANDBALL_PLUGIN_PATH')) {
    define('HANDBALL_PLUGIN_PATH', plugin_dir_path(__FILE__) . '../');
}
if (!defined('HANDBALL_PLUGIN_URL')) {
    define('HANDBALL_PLUGIN_URL', plugin_dir_url(__FILE__) . '../');
}

if (!function_exists('handball_get_settings')) {
function handball_get_settings() {
    $defaults = [
        'project_id'   => 'handball-notifications',
        'topic_ar'     => 'new_matches_ar_demo',
        'topic_en'     => 'new_matches_en_demo',
        'apis_enabled' => [],
        'app_options'  => [
            'App Options'        => ['visible' => 1, 'label' => 'App Options'],
            'Display Options'    => ['visible' => 1, 'label' => 'Display Options'],
            'Results Update'     => ['visible' => 1, 'label' => 'Results Update'],
            'Send Notifications' => ['visible' => 1, 'label' => 'Send Notifications'],
            'Events Duplication' => ['visible' => 1, 'label' => 'Events Duplication'],
            'Standings Update'   => ['visible' => 1, 'label' => 'Standings Update'],
            'Match Translations' => ['visible' => 1, 'label' => 'Match Translations'],
        ]
    ];
    $opt = get_option('handball_options', []);
    if (!is_array($opt)) $opt = [];
    $opt = array_replace_recursive($defaults, $opt);
    if (!isset($opt['apis_enabled']) || !is_array($opt['apis_enabled'])) $opt['apis_enabled'] = [];
    return $opt;
}}

if (!function_exists('handball_get_topic')) {
function handball_get_topic() {
    $s = handball_get_settings();
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    if (strpos($locale, 'ar') === 0) {
        return $s['topic_ar'];
    }
    return $s['topic_en'];
}}

if (!function_exists('handball_get_service_json_path')) {
function handball_get_service_json_path() {
    $filename = 'handball-notifications-firebase-adminsdk-djle4-ebd601aedf.json';
    $path = HANDBALL_PLUGIN_PATH . 'notifications/' . $filename;
    return file_exists($path) ? $path : '';
}}

// Load APIs conditionally
add_action('init', function() {
    $s = handball_get_settings();
    $enabled = isset($s['apis_enabled']) ? (array)$s['apis_enabled'] : [];
    $api_dir = trailingslashit(HANDBALL_PLUGIN_PATH) . 'templates-parts/api';
    if (is_dir($api_dir)) {
        foreach (glob($api_dir . '/*.php') as $file) {
            $slug = basename($file, '.php');
            if (empty($enabled[$slug])) {
                continue;
            }
            require_once $file;
        }
    }
}, 5);

// Admin menu label rename + visibility
add_action('admin_menu', function() {
    $s = handball_get_settings();
    global $submenu;
    if (!is_array($submenu)) return;

    foreach ($submenu as $parent_slug => &$items) {
        foreach ($items as $idx => &$item) {
            $raw_title = wp_strip_all_tags($item[0]);
            if (isset($s['app_options'][$raw_title])) {
                $cfg = $s['app_options'][$raw_title];
                if (!empty($cfg['label']) && $cfg['label'] !== $raw_title) {
                    $item[0] = esc_html($cfg['label']);
                }
                if (isset($cfg['visible']) && intval($cfg['visible']) === 0) {
                    remove_submenu_page($parent_slug, $item[2]);
                }
            }
        }
    }
}, 1000);

// Admin page
add_action('admin_menu', function() {
    add_options_page(
        __('Handball Options','handball'),
        __('Handball Options','handball'),
        'manage_options',
        'handball-options',
        'handball_render_options_page'
    );
});

add_action('admin_init', function() {
    register_setting('handball_options_group', 'handball_options');
});

if (!function_exists('handball_handle_json_upload')) { function handball_handle_json_upload() {
    if (!current_user_can('manage_options')) return;
    if (!isset($_FILES['handball_service_json']) || empty($_FILES['handball_service_json']['name'])) return;
    $required = 'handball-notifications-firebase-adminsdk-djle4-ebd601aedf.json';
    $file = $_FILES['handball_service_json'];
    if ($file['name'] !== $required) {
        add_settings_error('handball_options', 'handball_json_name', __('The JSON file must be named exactly: ', 'handball') . $required, 'error');
        return;
    }
    $dest_dir = HANDBALL_PLUGIN_PATH . 'notifications/';
    if (!file_exists($dest_dir)) wp_mkdir_p($dest_dir);
    $dest = $dest_dir . $required;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        add_settings_error('handball_options', 'handball_json_move', __('Failed to move JSON file to plugin notifications folder.', 'handball'), 'error');
        return;
    }
    add_settings_error('handball_options', 'handball_json_ok', __('JSON uploaded successfully to plugin notifications folder.', 'handball'), 'updated');
}
}

function handball_render_options_page() {
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
        check_admin_referer('handball_options_save');
        
        // Handle file upload first
        if (isset($_FILES['handball_service_json'])) {
            handball_handle_json_upload();
        }
        
        // Handle settings save
        if (isset($_POST['handball_options'])) {
            update_option('handball_options', $_POST['handball_options']);
            add_settings_error('handball_options', 'handball_saved', __('Settings saved.', 'handball'), 'updated');
        }
    }
    
    $s = handball_get_settings();
    ?>
    <div class="wrap">
      <h1><?php _e('Handball Options','handball'); ?></h1>
      <?php settings_errors('handball_options'); ?>
      <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('handball_options_save'); ?>

        <h2><?php _e('Notifications','handball'); ?></h2>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="project_id">Firebase Project ID</label></th>
            <td>
              <input type="text" id="project_id" name="handball_options[project_id]" value="<?php echo esc_attr($s['project_id']); ?>" class="regular-text">
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="topic_ar">Topic (Arabic)</label></th>
            <td><input type="text" id="topic_ar" name="handball_options[topic_ar]" value="<?php echo esc_attr($s['topic_ar']); ?>" class="regular-text"></td>
          </tr>
          <tr>
            <th scope="row"><label for="topic_en">Topic (English)</label></th>
            <td><input type="text" id="topic_en" name="handball_options[topic_en]" value="<?php echo esc_attr($s['topic_en']); ?>" class="regular-text"></td>
          </tr>
          <tr>
            <th scope="row">Service Account JSON</th>
            <td>
              <input type="file" name="handball_service_json" accept=".json">
              <p class="description">
                <?php _e('Upload into plugin notifications folder. The file name must be exactly', 'handball'); ?>:
                <code>handball-notifications-firebase-adminsdk-djle4-ebd601aedf.json</code>
              </p>
              <p><?php echo handball_get_service_json_path() ? '<span style="color:green">File present.</span>' : '<span style="color:#a00">File missing.</span>'; ?></p>
            </td>
          </tr>
        </table>

        <h2><?php _e('APIs','handball'); ?></h2>
        <p class="description"><?php _e('Enable or disable each API under templates-parts/api. All are disabled by default.','handball'); ?></p>
        <table class="form-table" role="presentation">
          <?php
          $api_dir = trailingslashit(HANDBALL_PLUGIN_PATH) . 'templates-parts/api';
          if (is_dir($api_dir)) {
              $files = glob($api_dir . '/*.php');
              sort($files);
              foreach ($files as $file) {
                  $slug = basename($file, '.php');
                  $checked = !empty($s['apis_enabled'][$slug]) ? 'checked' : '';
                  echo '<tr><th scope="row"><label>'.esc_html($slug).'</label></th><td>';
                  echo '<label><input type="checkbox" name="handball_options[apis_enabled]['.esc_attr($slug).']" value="1" '.$checked.'> '.__('Enabled','handball').'</label>';
                  echo '<br><code>'.esc_html(str_replace(trailingslashit(HANDBALL_PLUGIN_PATH), '', $file)).'</code>';
                  echo '</td></tr>';
              }
          } else {
              echo '<tr><td colspan="2"><em>No API files found.</em></td></tr>';
          }
          ?>
        </table>

        <h2><?php _e('App Options','handball'); ?></h2>
        <p class="description"><?php _e('Show/Hide and rename items under App Options submenu.','handball'); ?></p>
        <table class="form-table" role="presentation">
          <?php
          foreach ($s['app_options'] as $key => $cfg) {
              $visible = isset($cfg['visible']) ? intval($cfg['visible']) : 1;
              $label   = isset($cfg['label']) ? $cfg['label'] : $key;
              echo '<tr>';
              echo '<th scope="row">'.esc_html($key).'</th>';
              echo '<td>';
              echo '<label style="margin-right:12px"><input type="checkbox" name="handball_options[app_options]['.esc_attr($key).'][visible]" value="1" '.checked($visible,1,false).'> '.__('Visible','handball').'</label>';
              echo '<br><input type="text" name="handball_options[app_options]['.esc_attr($key).'][label]" value="'.esc_attr($label).'" class="regular-text">';
              echo '</td>';
              echo '</tr>';
          }
          ?>
        </table>

        <?php submit_button(); ?>
      </form>
    </div>
    <?php
}
