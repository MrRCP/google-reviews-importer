<?php
/**
 * Testimonial Post Type Registration
 *
 * This file registers the testimonial custom post type.
 * Include this file in your plugin if the post type doesn't already exist.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Testimonial post type
 */
function grp_register_testimonial_post_type() {
    $labels = array(
        'name'                  => _x('Testimonials', 'Post type general name', 'google-reviews-importer'),
        'singular_name'         => _x('Testimonial', 'Post type singular name', 'google-reviews-importer'),
        'menu_name'             => _x('Testimonials', 'Admin Menu text', 'google-reviews-importer'),
        'name_admin_bar'        => _x('Testimonial', 'Add New on Toolbar', 'google-reviews-importer'),
        'add_new'               => __('Add New', 'google-reviews-importer'),
        'add_new_item'          => __('Add New Testimonial', 'google-reviews-importer'),
        'new_item'              => __('New Testimonial', 'google-reviews-importer'),
        'edit_item'             => __('Edit Testimonial', 'google-reviews-importer'),
        'view_item'             => __('View Testimonial', 'google-reviews-importer'),
        'all_items'             => __('All Testimonials', 'google-reviews-importer'),
        'search_items'          => __('Search Testimonials', 'google-reviews-importer'),
        'parent_item_colon'     => __('Parent Testimonials:', 'google-reviews-importer'),
        'not_found'             => __('No testimonials found.', 'google-reviews-importer'),
        'not_found_in_trash'    => __('No testimonials found in Trash.', 'google-reviews-importer'),
        'featured_image'        => _x('Testimonial Image', 'Overrides the "Featured Image" phrase', 'google-reviews-importer'),
        'set_featured_image'    => _x('Set testimonial image', 'Overrides the "Set featured image" phrase', 'google-reviews-importer'),
        'remove_featured_image' => _x('Remove testimonial image', 'Overrides the "Remove featured image" phrase', 'google-reviews-importer'),
        'use_featured_image'    => _x('Use as testimonial image', 'Overrides the "Use as featured image" phrase', 'google-reviews-importer'),
        'archives'              => _x('Testimonial archives', 'The post type archive label', 'google-reviews-importer'),
        'insert_into_item'      => _x('Insert into testimonial', 'Overrides the "Insert into post" phrase', 'google-reviews-importer'),
        'uploaded_to_this_item' => _x('Uploaded to this testimonial', 'Overrides the "Uploaded to this post" phrase', 'google-reviews-importer'),
        'filter_items_list'     => _x('Filter testimonials list', 'Screen reader text for the filter links heading', 'google-reviews-importer'),
        'items_list_navigation' => _x('Testimonials list navigation', 'Screen reader text for the pagination heading', 'google-reviews-importer'),
        'items_list'            => _x('Testimonials list', 'Screen reader text for the items list heading', 'google-reviews-importer'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'testimonial'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-format-quote',
        'supports'           => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'       => true,
    );

    register_post_type('testimonial', $args);
}

/**
 * Check if Testimonial post type exists, register if it doesn't
 */
function grp_maybe_register_testimonial_post_type() {
    if (!post_type_exists('testimonial')) {
        grp_register_testimonial_post_type();
        
        // Flush rewrite rules only once
        if (!get_option('grp_flushed_rewrites')) {
            flush_rewrite_rules();
            update_option('grp_flushed_rewrites', true);
        }
    }
}

// Register the post type when the plugin runs
add_action('init', 'grp_maybe_register_testimonial_post_type');
