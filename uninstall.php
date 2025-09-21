<?php
/**
 * WP Table Uninstall Script
 * 
 * This file is called when the plugin is uninstalled (deleted) from WordPress.
 * It will clean up all plugin data from the database.
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Delete the custom table
$table_name = $wpdb->prefix . 'wp_table_staff';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete plugin options
delete_option('wp_table_style');
delete_option('wp_table_default_show_times');
delete_option('wp_table_date_format');
delete_option('wp_table_time_format');

// Clean up any transients (if we had any)
delete_transient('wp_table_version_check');

// Clear any cached data
wp_cache_flush();