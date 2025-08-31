<?php
/**
 * Uninstall KE SEO Booster Pro
 * 
 * This file is executed when the plugin is uninstalled.
 * 
 * @package KSEO\SEO_Booster
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress database functions
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

/**
 * Clean up all plugin data
 */
function kseo_cleanup_data() {
    global $wpdb;
    
    // Remove all plugin options
    $options_to_delete = array(
        'kseo_modules',
        'kseo_post_types',
        'kseo_openai_api_key',
        'kseo_google_ads_credentials',
        'kseo_auto_generate',
        'kseo_enable_schema',
        'kseo_enable_og_tags',
        'kseo_onboarding_completed',
        'kseo_feature_flags',
        'kseo_rate_limits',
        'kseo_ai',
        'kseo_version',
        'kseo_twitter_site',
        'kseo_twitter_creator',
        'kseo_crawl_delay'
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
    }
    
    // Remove all post meta
    $meta_keys_to_delete = array(
        '_kseo_title',
        '_kseo_description',
        '_kseo_keywords',
        '_kseo_og_image',
        '_kseo_twitter_image',
        '_kseo_enable_schema',
        '_kseo_enable_og_tags',
        '_kseo_schema_ld',
        '_kseo_meta_description',
        '_kseo_meta_title',
        '_kseo_meta_keywords',
        '_kseo_meta_robots',
        '_kseo_canonical_url',
        '_kseo_redirect_url'
    );
    
    foreach ($meta_keys_to_delete as $meta_key) {
        delete_metadata('post', 0, $meta_key, '', true);
    }
    
    // Remove all transients
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_kseo_%'));
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", '_transient_timeout_kseo_%'));
    
    // Drop custom tables
    $tables_to_drop = array(
        $wpdb->prefix . 'kseo_project',
        $wpdb->prefix . 'kseo_keyword',
        $wpdb->prefix . 'kseo_content',
        $wpdb->prefix . 'kseo_issue',
        $wpdb->prefix . 'kseo_experiment',
        $wpdb->prefix . 'kseo_experiment_variant',
        $wpdb->prefix . 'kseo_job',
        $wpdb->prefix . 'kseo_webhook_outbox',
        $wpdb->prefix . 'kseo_api_key',
        $wpdb->prefix . 'kseo_integration',
        $wpdb->prefix . 'kseo_ai_keywords',
        $wpdb->prefix . 'kseo_ai_events'
    );
    
    foreach ($tables_to_drop as $table) {
        $wpdb->query("DROP TABLE IF EXISTS {$table}");
    }
    
    // Remove custom capabilities from administrator role
    $admin_role = get_role('administrator');
    if ($admin_role) {
        $capabilities_to_remove = array(
            'kseo_optimize_content',
            'kseo_manage_settings',
            'kseo_run_audits',
            'kseo_view_reports',
            'kseo_export_data'
        );
        
        foreach ($capabilities_to_remove as $cap) {
            $admin_role->remove_cap($cap);
        }
    }
    
    // Clear scheduled hooks
    wp_clear_scheduled_hook('kseo_daily_analysis');
    wp_clear_scheduled_hook('kseo_sitemap_regeneration');
    wp_clear_scheduled_hook('kseo_robots_regeneration');
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Clear any cached data
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
}

// Run the cleanup
kseo_cleanup_data();

// Log the uninstallation
error_log('KE SEO Booster Pro: Plugin uninstalled and all data cleaned up');
