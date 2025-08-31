<?php
namespace KSEO\SEO_Booster\Module;

/**
 * Schema Module for KE SEO Booster Pro
 * 
 * @package KSEO\SEO_Booster\Module
 */

/**
 * Schema Class
 * 
 * @since 2.0.0
 */
class Schema {
    
    /**
     * Initialize the schema module
     */
    public function __construct() {
        add_action('wp_head', array($this, 'output'), 2);
    }
    
    /**
     * Output schema markup
     */
    public function output() {
        if (!is_singular()) {
            return;
        }
        
        global $post;
        if (!$post) {
            return;
        }
        
        // Check if schema is enabled
        if (!get_option('kseo_enable_schema', true)) {
            return;
        }
        
        $schema = $this->generate_schema($post);
        if (!empty($schema)) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
        }
    }
    
    /**
     * Generate schema markup for a post
     */
    private function generate_schema($post) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title($post),
            'datePublished' => get_the_date('c', $post),
            'dateModified' => get_the_modified_date('c', $post),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author)
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'url' => home_url()
            ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink($post)
            )
        );
        
        // Add description if available
        $description = get_post_meta($post->ID, '_kseo_description', true);
        if (!empty($description)) {
            $schema['description'] = $description;
        }
        
        // Add featured image if available
        $image_url = get_the_post_thumbnail_url($post->ID, 'large');
        if ($image_url) {
            $schema['image'] = array(
                '@type' => 'ImageObject',
                'url' => $image_url
            );
        }
        
        // Add article body
        $content = wp_strip_all_tags($post->post_content);
        if (!empty($content)) {
            $schema['articleBody'] = wp_trim_words($content, 100, '...');
        }
        
        // Add keywords if available
        $keywords = get_post_meta($post->ID, '_kseo_keywords', true);
        if (!empty($keywords)) {
            $schema['keywords'] = $keywords;
        }
        
        // Add breadcrumb schema
        $breadcrumb_schema = $this->generate_breadcrumb_schema($post);
        if (!empty($breadcrumb_schema)) {
            $schema['breadcrumb'] = $breadcrumb_schema;
        }
        
        return $schema;
    }
    
    /**
     * Generate breadcrumb schema
     */
    private function generate_breadcrumb_schema($post) {
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => 1,
            'name' => get_bloginfo('name'),
            'item' => home_url()
        );
        
        // Add category breadcrumbs
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $category = $categories[0]; // Use first category
            $breadcrumbs[] = array(
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $category->name,
                'item' => get_category_link($category->term_id)
            );
        }
        
        $breadcrumbs[] = array(
            '@type' => 'ListItem',
            'position' => count($breadcrumbs) + 1,
            'name' => get_the_title($post),
            'item' => get_permalink($post)
        );
        
        return array(
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbs
        );
    }
} 