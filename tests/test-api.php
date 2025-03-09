<?php
/**
 * API Test Script for Google Reviews Importer
 * 
 * This script tests connectivity to the Google Places API
 * using the configured credentials.
 * 
 * Usage: php tests/test-api.php
 */

// Include WordPress environment if running in WP context
$wp_load_path = dirname(__FILE__) . '/../../../wp-load.php';
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
    $wp_loaded = true;
    
    // Get credentials from WordPress options
    $options = get_option('google_reviews_importer_settings', array());
    $api_key = $options['api_key'] ?? '';
    $place_id = $options['place_id'] ?? '';
    
    echo "Using credentials from WordPress settings.\n";
} else {
    $wp_loaded = false;
    
    // Get credentials from environment variables or command line
    $api_key = getenv('GOOGLE_API_KEY') ?: '';
    $place_id = getenv('GOOGLE_PLACE_ID') ?: '';
    
    echo "WordPress environment not detected. Using environment variables.\n";
}

// Validate credentials
if (empty($api_key)) {
    echo "Error: API key not configured.\n";
    exit(1);
}

if (empty($place_id)) {
    echo "Error: Place ID not configured.\n";
    exit(1);
}

echo "Testing connection to Google Places API...\n";

// Construct API URL
$url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$place_id}&fields=name,rating&key={$api_key}";

// Make API request
if ($wp_loaded && function_exists('wp_remote_get')) {
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        echo "API Connection Error: " . $response->get_error_message() . "\n";
        exit(1);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
} else {
    // Fallback to file_get_contents if wp_remote_get isn't available
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'user_agent' => 'Google Reviews Importer Test Script',
        ]
    ]);
    
    $body = @file_get_contents($url, false, $context);
    
    if ($body === false) {
        echo "API Connection Error: Unable to connect to Google API.\n";
        exit(1);
    }
    
    $data = json_decode($body, true);
}

// Analyze response
if (isset($data['status']) && $data['status'] === 'OK') {
    $place_name = $data['result']['name'] ?? 'Unknown';
    $rating = $data['result']['rating'] ?? 'Not available';
    
    echo "✅ API connection successful!\n";
    echo "Place Name: {$place_name}\n";
    echo "Rating: {$rating}\n";
    exit(0);
} else {
    $error = $data['error_message'] ?? $data['status'] ?? 'Unknown error';
    echo "❌ API Error: {$error}\n";
    
    if (isset($data['status']) && $data['status'] === 'REQUEST_DENIED') {
        echo "This usually indicates an issue with your API key. Please check:\n";
        echo "1. The API key is correct\n";
        echo "2. The Places API is enabled in your Google Cloud Console\n";
        echo "3. Any API restrictions (HTTP referrers, IP addresses) are correctly configured\n";
    }
    
    exit(1);
}
