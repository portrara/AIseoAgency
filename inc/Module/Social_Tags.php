<?php
namespace KSEO\SEO_Booster\Module;

/**
 * Social Tags Module for KE SEO Booster Pro
 * 
 * @package KSEO\SEO_Booster\Module
 */

/**
 * Social Tags Class
 * 
 * @since 2.0.0
 */
class Social_Tags {
    
    /**
     * Initialize the social tags module
     */
    public function __construct() {
        add_action('wp_head', array($this, 'output'), 1);
    }
    
    /**
     * Output social tags
     */
    public function output() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        if (!$post) {
            return;
        }
        
        // Check if social tags are enabled
        if (!get_option('kseo_enable_og_tags', true)) {
            return;
        }
        
        $this->output_open_graph_tags($post);
        $this->output_twitter_cards($post);
    }
    
    /**
     * Output Open Graph tags
     */
    private function output_open_graph_tags($post) {
        $title = get_post_meta($post->ID, '_kseo_title', true);
        if (empty($title)) {
            $title = get_the_title($post);
        }
        
        $description = get_post_meta($post->ID, '_kseo_description', true);
        if (empty($description)) {
            $description = wp_trim_words(get_the_excerpt($post), 25, '...');
        }
        
        $image = get_post_meta($post->ID, '_kseo_og_image', true);
        if (empty($image)) {
            $image = get_the_post_thumbnail_url($post->ID, 'large');
        }
        
        $url = get_permalink($post);
        
        echo '<meta property="og:type" content="article" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
        
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="630" />' . "\n";
        }
        
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '" />' . "\n";
    }
    
    /**
     * Output Twitter Card tags
     */
    private function output_twitter_cards($post) {
        $title = get_post_meta($post->ID, '_kseo_title', true);
        if (empty($title)) {
            $title = get_the_title($post);
        }
        
        $description = get_post_meta($post->ID, '_kseo_description', true);
        if (empty($description)) {
            $description = wp_trim_words(get_the_excerpt($post), 25, '...');
        }
        
        $image = get_post_meta($post->ID, '_kseo_twitter_image', true);
        if (empty($image)) {
            $image = get_the_post_thumbnail_url($post->ID, 'large');
        }
        
        echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
        
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
        }
        
        // Add Twitter site and creator if configured
        $twitter_site = get_option('kseo_twitter_site', '');
        if (!empty($twitter_site)) {
            echo '<meta name="twitter:site" content="' . esc_attr($twitter_site) . '" />' . "\n";
        }
        
        $twitter_creator = get_option('kseo_twitter_creator', '');
        if (!empty($twitter_creator)) {
            echo '<meta name="twitter:creator" content="' . esc_attr($twitter_creator) . '" />' . "\n";
        }
    }
} 