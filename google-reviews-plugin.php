<?php
/**
 * Plugin Name: Google Reviews Importer
 * Description: Automatically imports Google reviews as testimonials
 * Version: 1.0.1
 * Author: Reflect + Refine
 * Author URI: https://reflectandrefine.com
 * Text Domain: google-reviews-importer 
 * Domain Path: /languages
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include the testimonial post type registration
require_once plugin_dir_path(__FILE__) . 'testimonial-post-type.php';

class Google_Reviews_Importer {

    // Store plugin settings
    private $api_key;
    private $place_id;
    
    /**
     * Constructor - set up hooks and filters
     */
    public function __construct() {
        // Initialize plugin
        add_action('init', array($this, 'init'));
        
        // Register CRON schedule
        add_filter('cron_schedules', array($this, 'add_custom_cron_schedule'));
        
        // Setup CRON job
        add_action('wp', array($this, 'setup_schedule'));
        
        // Register the CRON action hook
        add_action('fetch_google_reviews_event', array($this, 'fetch_google_reviews'));
        
        // Add admin menu for settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add health monitoring dashboard widget
        $this->add_health_check();
        
        // Register admin actions
        $this->register_admin_actions();
        
        // Display admin notices
        add_action('admin_notices', array($this, 'display_import_notices'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Get plugin options
        $options = get_option('google_reviews_importer_settings', array(
            'api_key' => '',
            'place_id' => '',
        ));
        
        $this->api_key = $options['api_key'];
        $this->place_id = $options['place_id'];
    }
    
    /**
     * Add custom cron schedule
     */
    public function add_custom_cron_schedule($schedules) {
        $schedules['monthly'] = array(
            'interval' => 30 * DAY_IN_SECONDS,
            'display'  => __('Once a Month', 'google-reviews-importer')
        );
        return $schedules;
    }
    
    /**
     * Setup the cron schedule
     */
    public function setup_schedule() {
        if (!wp_next_scheduled('fetch_google_reviews_event')) {
            wp_schedule_event(time(), 'monthly', 'fetch_google_reviews_event');
        }
    }
    
    /**
     * Add admin menu page for settings
     */
    public function add_admin_menu() {
        add_options_page(
            __('Google Reviews Importer', 'google-reviews-importer'),
            __('Google Reviews', 'google-reviews-importer'),
            'manage_options',
            'google-reviews-importer',
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'google_reviews_importer_settings_group',
            'google_reviews_importer_settings',
            array($this, 'sanitize_settings')
        );
        
        add_settings_section(
            'google_reviews_importer_section',
            __('Google API Settings', 'google-reviews-importer'),
            array($this, 'settings_section_callback'),
            'google-reviews-importer'
        );
        
        add_settings_field(
            'api_key',
            __('Google API Key', 'google-reviews-importer'),
            array($this, 'api_key_render'),
            'google-reviews-importer',
            'google_reviews_importer_section'
        );
        
        add_settings_field(
            'place_id',
            __('Google Place ID', 'google-reviews-importer'),
            array($this, 'place_id_render'),
            'google-reviews-importer',
            'google_reviews_importer_section'
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        $sanitized['place_id'] = sanitize_text_field($input['place_id']);
        return $sanitized;
    }
    
    /**
     * Settings section description
     */
    public function settings_section_callback() {
        echo '<p>' . __('Enter your Google API credentials to import reviews.', 'google-reviews-importer') . '</p>';
    }
    
    /**
     * Render API key field
     */
    public function api_key_render() {
        $options = get_option('google_reviews_importer_settings');
        ?>
        <input type='text' class="regular-text" name='google_reviews_importer_settings[api_key]' value='<?php echo esc_attr($options['api_key'] ?? ''); ?>'>
        <?php
    }
    
    /**
     * Render Place ID field
     */
    public function place_id_render() {
        $options = get_option('google_reviews_importer_settings');
        ?>
        <input type='text' class="regular-text" name='google_reviews_importer_settings[place_id]' value='<?php echo esc_attr($options['place_id'] ?? ''); ?>'>
        <?php
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p><?php _e('This plugin automatically imports Google reviews as testimonials. Reviews are imported once a month automatically, or you can import them manually below.', 'google-reviews-importer'); ?></p>
            </div>
            
            <form action='options.php' method='post'>
                <?php
                settings_fields('google_reviews_importer_settings_group');
                do_settings_sections('google-reviews-importer');
                submit_button(__('Save Settings', 'google-reviews-importer'));
                ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Manual Import', 'google-reviews-importer'); ?></h2>
            <p><?php _e('Click the button below to manually import reviews now.', 'google-reviews-importer'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('manual_import_reviews', 'import_reviews_nonce'); ?>
                <input type="submit" name="import_reviews_now" class="button button-primary" value="<?php _e('Import Reviews Now', 'google-reviews-importer'); ?>">
            </form>
            
            <?php
            // Handle manual import
            if (isset($_POST['import_reviews_now']) && check_admin_referer('manual_import_reviews', 'import_reviews_nonce')) {
                $result = $this->fetch_google_reviews();
                if ($result === false) {
                    echo '<div class="notice notice-error"><p>' . __('Error importing reviews. Check error log for details.', 'google-reviews-importer') . '</p></div>';
                } else {
                    echo '<div class="notice notice-success"><p>' . sprintf(__('Import complete! %d new reviews imported.', 'google-reviews-importer'), $result) . '</p></div>';
                }
            }
            ?>
            
            <hr>
            
            <h2><?php _e('Plugin Health', 'google-reviews-importer'); ?></h2>
            <?php $this->display_health_widget(); ?>
            
            <hr>
            
            <h2><?php _e('Import History', 'google-reviews-importer'); ?></h2>
            <?php $this->display_import_history(); ?>
            
            <hr>
            
            <h2><?php _e('ACF Fields Status', 'google-reviews-importer'); ?></h2>
            <?php $this->display_acf_fields_status(); ?>
        </div>
        <?php
    }
    
    /**
     * Display ACF fields status
     */
    public function display_acf_fields_status() {
        if (!function_exists('get_field_object')) {
            echo '<div class="notice notice-error inline"><p>' . __('Advanced Custom Fields plugin is not active. Please install and activate ACF.', 'google-reviews-importer') . '</p></div>';
            return;
        }
        
        $required_fields = array(
            'testimonial_title' => __('Testimonial Title', 'google-reviews-importer'),
            'testimonial_summary' => __('Testimonial Summary', 'google-reviews-importer'),
            'testimonial' => __('Testimonial', 'google-reviews-importer'),
            'testimonial_date' => __('Date', 'google-reviews-importer'),
            'testimonial_rating' => __('Rating', 'google-reviews-importer'),
            'testimonial_source' => __('Source', 'google-reviews-importer')
        );
        
        $missing_fields = array();
        
        // Create a dummy post to check field existence
        $args = array(
            'post_type' => 'testimonial',
            'post_status' => 'publish',
            'posts_per_page' => 1
        );
        
        $test_posts = get_posts($args);
        
        if (empty($test_posts)) {
            // Create a temporary post to test with
            $test_post_id = wp_insert_post(array(
                'post_title' => 'ACF Test Post',
                'post_type' => 'testimonial',
                'post_status' => 'draft'
            ));
            
            foreach ($required_fields as $field_name => $field_label) {
                $field = get_field_object($field_name, $test_post_id);
                if (!$field) {
                    $missing_fields[] = $field_label;
                }
            }
            
            // Delete the test post
            wp_delete_post($test_post_id, true);
        } else {
            $test_post_id = $test_posts[0]->ID;
            
            foreach ($required_fields as $field_name => $field_label) {
                $field = get_field_object($field_name, $test_post_id);
                if (!$field) {
                    $missing_fields[] = $field_label;
                }
            }
        }
        
        if (empty($missing_fields)) {
            echo '<div class="notice notice-success inline"><p>' . __('All required ACF fields are properly configured.', 'google-reviews-importer') . '</p></div>';
        } else {
            echo '<div class="notice notice-error inline"><p>' . 
                sprintf(__('Missing required ACF fields: %s. Please configure these fields to ensure the plugin works correctly.', 'google-reviews-importer'), 
                implode(', ', $missing_fields)) . 
                '</p></div>';
            
            echo '<p>' . __('You can import the ACF field configuration from the included JSON file or set up the fields manually.', 'google-reviews-importer') . '</p>';
            
            echo '<p><a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools') . '" class="button">' . 
                __('Go to ACF Tools to Import Fields', 'google-reviews-importer') . '</a></p>';
        }
    }
    
    /**
     * Display import history
     */
    public function display_import_history() {
        $import_history = get_option('google_reviews_import_history', array());
        
        if (empty($import_history)) {
            echo '<p>' . __('No import history available yet.', 'google-reviews-importer') . '</p>';
            return;
        }
        
        echo '<table class="widefat striped">';
        echo '<thead><tr>';
        echo '<th>' . __('Date', 'google-reviews-importer') . '</th>';
        echo '<th>' . __('Reviews Imported', 'google-reviews-importer') . '</th>';
        echo '<th>' . __('Status', 'google-reviews-importer') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        // Get the last 10 imports, newest first
        $history = array_slice(array_reverse($import_history), 0, 10);
        
        foreach ($history as $import) {
            $date = isset($import['date']) ? date('F j, Y, g:i a', $import['date']) : 'Unknown';
            $count = isset($import['count']) ? intval($import['count']) : 0;
            $status = isset($import['status']) ? $import['status'] : 'unknown';
            $message = isset($import['message']) ? esc_html($import['message']) : '';
            
            $status_label = $status === 'success' 
                ? '<span style="color: #00a32a;">✅ ' . __('Success', 'google-reviews-importer') . '</span>'
                : '<span style="color: #d63638;">❌ ' . __('Failed', 'google-reviews-importer') . '</span>';
            
            echo '<tr>';
            echo '<td>' . esc_html($date) . '</td>';
            echo '<td>' . esc_html($count) . '</td>';
            echo '<td>' . $status_label . ($message ? ' - ' . $message : '') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Add health monitoring dashboard widget
     */
    public function add_health_check() {
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }

    /**
     * Register the dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'google_reviews_health',
            'Google Reviews Importer Health',
            array($this, 'display_health_widget')
        );
    }

    /**
     * Display health status in dashboard widget
     */
    public function display_health_widget() {
        // Check API connection
        $options = get_option('google_reviews_importer_settings');
        $api_key = $options['api_key'] ?? '';
        $place_id = $options['place_id'] ?? '';
        
        echo '<div style="padding: 10px; background: #f8f8f8; border-left: 4px solid #ccc;">';
        echo '<h3 style="margin-top: 0;">Google Reviews Importer Status</h3>';
        
        // Check configuration
        if (empty($api_key) || empty($place_id)) {
            echo '<p style="color: #d63638;">⚠️ <strong>Configuration Issue:</strong> API key or Place ID not configured. <a href="' . 
                 admin_url('options-general.php?page=google-reviews-importer') . '">Configure now</a></p>';
        } else {
            // Test API connection
            $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=name&key={$api_key}";
            $response = wp_remote_get($url);
            
            if (is_wp_error($response)) {
                echo '<p style="color: #d63638;">⚠️ <strong>API Connection Error:</strong> ' . esc_html($response->get_error_message()) . '</p>';
            } else {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($data['status']) && $data['status'] === 'OK') {
                    echo '<p style="color: #00a32a;">✅ <strong>API Connection:</strong> Working correctly</p>';
                } else {
                    echo '<p style="color: #d63638;">⚠️ <strong>API Error:</strong> ' . esc_html($data['error_message'] ?? $data['status'] ?? 'Unknown') . '</p>';
                }
            }
        }
        
        // Check last import time
        $last_run = get_option('google_reviews_last_import', 0);
        if ($last_run) {
            $time_diff = time() - $last_run;
            $days_diff = floor($time_diff / (60 * 60 * 24));
            
            echo '<p><strong>Last import:</strong> ' . esc_html(date('F j, Y, g:i a', $last_run)) . ' (' . esc_html($days_diff) . ' days ago)</p>';
            
            if ($days_diff > 35) {
                echo '<p style="color: #dba617;">⚠️ <strong>Import Status:</strong> Import may be overdue. Scheduled for monthly runs.</p>';
            } else {
                echo '<p style="color: #00a32a;">✅ <strong>Import Schedule:</strong> On track</p>';
            }
        } else {
            echo '<p style="color: #dba617;">⚠️ <strong>Import Status:</strong> No imports have run yet.</p>';
        }
        
        // Check for ACF dependency
        if (!function_exists('update_field')) {
            echo '<p style="color: #d63638;">⚠️ <strong>Dependency:</strong> Advanced Custom Fields plugin is not active.</p>';
        } else {
            echo '<p style="color: #00a32a;">✅ <strong>Dependencies:</strong> All required plugins active</p>';
        }
        
        // Add quick manual import option
        echo '<p style="margin-top: 15px;"><a href="' . admin_url('options-general.php?page=google-reviews-importer') . 
             '" class="button button-primary">Check Configuration</a> &nbsp; ' .
             '<a href="' . wp_nonce_url(admin_url('admin.php?action=run_google_reviews_import'), 'run_import_nonce') . 
             '" class="button">Run Manual Import</a></p>';
        
        echo '</div>';
    }

    /**
     * Register admin actions for running import
     */
    public function register_admin_actions() {
        add_action('admin_action_run_google_reviews_import', array($this, 'handle_manual_import_action'));
    }

    /**
     * Handle the admin action for manual import
     */
    public function handle_manual_import_action() {
        // Check nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'run_import_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        
        // Run the import
        $result = $this->fetch_google_reviews();
        
        // Redirect back with message
        $redirect_url = admin_url('index.php');
        $message = ($result === false) ? 'error' : 'success';
        $count = is_numeric($result) ? $result : 0;
        
        wp_redirect(add_query_arg(array(
            'google_reviews_import' => $message,
            'count' => $count
        ), $redirect_url));
        exit;
    }

    /**
     * Display admin notice after import
     */
    public function display_import_notices() {
        if (isset($_GET['google_reviews_import'])) {
            if ($_GET['google_reviews_import'] === 'success') {
                $count = intval($_GET['count'] ?? 0);
                $message = $count > 0 
                    ? sprintf(__('Success! %d new Google reviews imported.', 'google-reviews-importer'), $count)
                    : __('Import completed successfully, but no new reviews were found.', 'google-reviews-importer');
                
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . 
                    __('Error importing reviews. Please check the error log for details.', 'google-reviews-importer') . 
                    '</p></div>';
            }
        }
    }
    
    /**
     * Main function to fetch Google reviews
     * 
     * @return int|bool Number of reviews imported or false on error
     */
    public function fetch_google_reviews() {
        // Start tracking import history
        $import_history = get_option('google_reviews_import_history', array());
        $current_import = array(
            'date' => time(),
            'count' => 0,
            'status' => 'started',
        );
        
        // Check if API key and Place ID are set
        if (empty($this->api_key) || empty($this->place_id)) {
            error_log('Google Reviews Importer: API key or Place ID is not set.');
            $current_import['status'] = 'failed';
            $current_import['message'] = 'API key or Place ID not set';
            $import_history[] = $current_import;
            update_option('google_reviews_import_history', $import_history);
            return false;
        }
        
        // Check if ACF is active
        if (!function_exists('update_field')) {
            error_log('Google Reviews Importer: Advanced Custom Fields plugin is not active.');
            $current_import['status'] = 'failed';
            $current_import['message'] = 'ACF plugin not active';
            $import_history[] = $current_import;
            update_option('google_reviews_import_history', $import_history);
            return false;
        }
        
        $base_url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$this->place_id}&fields=reviews,next_page_token&key={$this->api_key}";
        $new_reviews = array();
        $next_page_token = null;
        
        try {
            do {
                // Construct the API request URL
                $url = $base_url;
                if ($next_page_token) {
                    sleep(3); // Google requires a short delay before using next_page_token
                    $url .= "&pagetoken=" . urlencode($next_page_token);
                }
                
                $response = wp_remote_get($url);
                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    error_log("Google Reviews Importer Error: " . $error_message);
                    $current_import['status'] = 'failed';
                    $current_import['message'] = 'API connection error: ' . $error_message;
                    $import_history[] = $current_import;
                    update_option('google_reviews_import_history', $import_history);
                    return false;
                }
                
                $data = json_decode(wp_remote_retrieve_body($response), true);
                
                // Check for API errors
                if (isset($data['status']) && $data['status'] !== 'OK') {
                    $error_message = $data['error_message'] ?? $data['status'];
                    error_log("Google Reviews Importer API Error: " . $error_message);
                    $current_import['status'] = 'failed';
                    $current_import['message'] = 'API error: ' . $error_message;
                    $import_history[] = $current_import;
                    update_option('google_reviews_import_history', $import_history);
                    return false;
                }
                
                if (!isset($data['result']['reviews'])) {
                    error_log("Google Reviews Importer: No reviews found for the given Place ID.");
                    $current_import['status'] = 'success';
                    $current_import['message'] = 'No reviews found';
                    $import_history[] = $current_import;
                    update_option('google_reviews_import_history', $import_history);
                    return 0;
                }
                
                // Get only reviews that do not already exist in the database
                foreach ($data['result']['reviews'] as $review) {
                    $review_text = isset($review['text']) ? sanitize_textarea_field($review['text']) : '';
                    
                    // Skip if no text
                    if (empty($review_text)) {
                        continue;
                    }
                    
                    // Check if this review already exists in the database
                    $existing_review = get_posts(array(
                        'post_type'   => 'testimonial',
                        'meta_query'  => array(
                            array(
                                'key'     => 'testimonial',
                                'value'   => $review_text,
                                'compare' => 'LIKE',
                            ),
                        ),
                        'posts_per_page' => 1,
                    ));
                    
                    if (empty($existing_review)) {
                        $new_reviews[] = $review; // Add to import list only if not found
                    }
                }
                
                // Check if there's another page of reviews
                $next_page_token = isset($data['next_page_token']) ? $data['next_page_token'] : null;
                
            } while ($next_page_token); // Continue fetching until no more pages
            
            // If no new reviews, stop processing
            if (empty($new_reviews)) {
                error_log("Google Reviews Importer: No new reviews found.");
                $current_import['status'] = 'success';
                $current_import['message'] = 'No new reviews found';
                $import_history[] = $current_import;
                update_option('google_reviews_import_history', $import_history);
                return 0;
            }
            
            // Process and store only new reviews
            $import_count = 0;
            foreach ($new_reviews as $review) {
                $reviewer_name = isset($review['author_name']) ? sanitize_text_field($review['author_name']) : __('Anonymous', 'google-reviews-importer');
                $review_text = isset($review['text']) ? sanitize_textarea_field($review['text']) : '';
                $review_rating = isset($review['rating']) ? intval($review['rating']) : 5;
                $review_date = isset($review['time']) ? date('Y-m-d', $review['time']) : current_time('Y-m-d');
                $review_summary = wp_trim_words($review_text, 20, '...');
                $review_source = 'Google';
                
                // Insert new testimonial post
                $post_id = wp_insert_post(array(
                    'post_title'   => $reviewer_name,
                    'post_content' => $review_text,
                    'post_status'  => 'publish',
                    'post_type'    => 'testimonial',
                ));
                
                if ($post_id && !is_wp_error($post_id)) {
                    update_field('testimonial_title', $reviewer_name, $post_id);
                    update_field('testimonial_summary', $review_summary, $post_id);
                    update_field('testimonial', $review_text, $post_id);
                    update_field('testimonial_date', $review_date, $post_id);
                    update_field('testimonial_rating', strval($review_rating), $post_id);
                    update_field('testimonial_source', $review_source, $post_id);
                    $import_count++;
                }
            }
            
            // Update last import time
            update_option('google_reviews_last_import', time());
            
            // Update import history
            $current_import['status'] = 'success';
            $current_import['count'] = $import_count;
            $current_import['message'] = "Successfully imported {$import_count} new reviews";
            $import_history[] = $current_import;
            update_option('google_reviews_import_history', $import_history);
            
            error_log("Google Reviews Importer: Successfully imported {$import_count} new reviews.");
            return $import_count;
            
        } catch (\Exception $e) {
            $error_message = $e->getMessage();
            error_log("Google Reviews Importer Exception: " . $error_message);
            
            // Update import history with error
            $current_import['status'] = 'failed';
            $current_import['message'] = 'Exception: ' . $error_message;
            $import_history[] = $current_import;
            update_option('google_reviews_import_history', $import_history);
            
            return false;
        }
    }
    
    /**
     * Activation hook
     */
    public static function activate() {
        // Make sure we have the testimonial post type
        if (!post_type_exists('testimonial')) {
            error_log('Google Reviews Importer: The "testimonial" post type does not exist. Reviews will still import but may not display correctly.');
        }
        
        // Check if ACF is active
        if (!function_exists('update_field')) {
            error_log('Google Reviews Importer: Advanced Custom Fields plugin is not active. Please install and activate ACF for full functionality.');
        }
        
        // Clear the scheduled hook if it exists
        wp_clear_scheduled_hook('fetch_google_reviews_event');
        
        // Schedule the hook to run
        wp_schedule_event(time(), 'monthly', 'fetch_google_reviews_event');
    }
    
    /**
     * Deactivation hook
     */
    public static function deactivate() {
        // Remove the scheduled hook
        wp_clear_scheduled_hook('fetch_google_reviews_event');
    }
}

// Initialize the plugin
$google_reviews_importer = new Google_Reviews_Importer();

// Register activation and deactivation hooks
register_activation_hook(__FILE__, array('Google_Reviews_Importer', 'activate'));
register_deactivation_hook(__FILE__, array('Google_Reviews_Importer', 'deactivate'));

// Add a shortcode to manually trigger the import (admin only)
add_shortcode('import_google_reviews', function() {
    if (current_user_can('manage_options')) {
        global $google_reviews_importer;
        $result = $google_reviews_importer->fetch_google_reviews();
        if ($result === false) {
            return '<p>Error importing reviews. Check error log for details.</p>';
        } else {
            return "<p>Import complete! {$result} new reviews imported.</p>";
        }
    }
    return '';
});
