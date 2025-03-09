<?php
// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('google_reviews_importer_settings');

// Remove scheduled event
wp_clear_scheduled_hook('fetch_google_reviews_event');