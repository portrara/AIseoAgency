<?php
namespace KSEO\SEO_Booster\Module;

/**
 * Sitemap Module for KE SEO Booster Pro
 * 
 * @package KSEO\SEO_Booster\Module
 */

/**
 * Sitemap Class
 * 
 * @since 2.0.0
 */
class Sitemap {
    
    /**
     * Initialize the sitemap module
     */
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_sitemap_request'));
        add_action('save_post', array($this, 'invalidate_sitemap_cache'));
        add_action('delete_post', array($this, 'invalidate_sitemap_cache'));
    }
    
    /**
     * Add rewrite rules for sitemap
     */
    public function add_rewrite_rules() {
        add_rewrite_rule('sitemap\.xml$', 'index.php?kseo_sitemap=1', 'top');
        add_rewrite_rule('sitemap-([^/]+)\.xml$', 'index.php?kseo_sitemap=1&kseo_sitemap_type=$matches[1]', 'top');
    }
    
    /**
     * Handle sitemap requests
     */
    public function handle_sitemap_request() {
        if (get_query_var('kseo_sitemap')) {
            $this->output_sitemap();
            exit;
        }
    }
    
    /**
     * Generate sitemap
     */
    public function generate() {
        $sitemap = $this->build_sitemap();
        return $sitemap;
    }
    
    /**
     * Output sitemap XML
     */
    private function output_sitemap() {
        header('Content-Type: application/xml; charset=utf-8');
        header('X-Robots-Tag: noindex');
        
        $sitemap = $this->build_sitemap();
        echo $sitemap;
    }
    
    /**
     * Build sitemap XML
     */
    private function build_sitemap() {
        $post_types = get_option('kseo_post_types', array('post', 'page'));
        if (!is_array($post_types)) {
            $post_types = array('post', 'page');
        }
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Add homepage
        $xml .= $this->build_url_entry(home_url(), '1.0', 'daily');
        
        // Add posts
        foreach ($post_types as $post_type) {
            $posts = get_posts(array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => 1000,
                'orderby' => 'modified'
            ));
            
            foreach ($posts as $post) {
                $priority = ($post_type === 'page') ? '0.8' : '0.6';
                $changefreq = 'weekly';
                
                $xml .= $this->build_url_entry(
                    get_permalink($post->ID),
                    $priority,
                    $changefreq,
                    get_the_modified_date('c', $post->ID)
                );
            }
        }
        
        // Add categories
        $categories = get_categories(array('hide_empty' => true));
        foreach ($categories as $category) {
            $xml .= $this->build_url_entry(
                get_category_link($category->term_id),
                '0.5',
                'weekly'
            );
        }
        
        // Add tags
        $tags = get_tags(array('hide_empty' => true));
        foreach ($tags as $tag) {
            $xml .= $this->build_url_entry(
                get_tag_link($tag->term_id),
                '0.4',
                'monthly'
            );
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Build URL entry for sitemap
     */
    private function build_url_entry($url, $priority, $changefreq, $lastmod = '') {
        $entry = "\t<url>\n";
        $entry .= "\t\t<loc>" . esc_url($url) . "</loc>\n";
        
        if (!empty($lastmod)) {
            $entry .= "\t\t<lastmod>" . esc_html($lastmod) . "</lastmod>\n";
        }
        
        $entry .= "\t\t<changefreq>" . esc_html($changefreq) . "</changefreq>\n";
        $entry .= "\t\t<priority>" . esc_html($priority) . "</priority>\n";
        $entry .= "\t</url>\n";
        
        return $entry;
    }
    
    /**
     * Invalidate sitemap cache
     */
    public function invalidate_sitemap_cache() {
        delete_transient('kseo_sitemap_cache');
    }
    
    /**
     * Get cached sitemap
     */
    private function get_cached_sitemap() {
        return get_transient('kseo_sitemap_cache');
    }
    
    /**
     * Cache sitemap
     */
    private function cache_sitemap($sitemap) {
        set_transient('kseo_sitemap_cache', $sitemap, HOUR_IN_SECONDS);
    }
} 