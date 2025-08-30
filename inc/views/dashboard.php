<?php
/**
 * Dashboard View for KE SEO Booster Pro
 * 
 * @package KSEO\SEO_Booster\Views
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('KE SEO Booster Pro Dashboard', 'kseo-seo-booster'); ?></h1>
    
    <div class="kseo-dashboard">
        <div class="kseo-stats">
            <h3><?php _e('Quick Stats', 'kseo-seo-booster'); ?></h3>
            <div class="kseo-stats-grid">
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number kseo-stats-posts">
                        <?php 
                        try {
                            if (method_exists($this, 'get_optimized_posts_count')) {
                                echo esc_html((int) $this->get_optimized_posts_count());
                            } else {
                                echo '0';
                                error_log('KE SEO Booster: get_optimized_posts_count method not found');
                            }
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting optimized posts count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Posts Optimized', 'kseo-seo-booster'); ?></span>
                </div>
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number kseo-stats-keywords">
                        <?php 
                        try {
                            if (method_exists($this, 'get_total_keywords_count')) {
                                echo esc_html((int) $this->get_total_keywords_count());
                            } else {
                                echo '0';
                                error_log('KE SEO Booster: get_total_keywords_count method not found');
                            }
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting total keywords count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Total Keywords', 'kseo-seo-booster'); ?></span>
                </div>
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number">
                        <?php 
                        try {
                            $post_types = get_option('kseo_post_types', array('post', 'page'));
                            if (!is_array($post_types)) {
                                $post_types = array('post', 'page');
                            }
                            echo esc_html((int) count($post_types));
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting post types count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Post Types', 'kseo-seo-booster'); ?></span>
                </div>
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number">
                        <?php 
                        try {
                            if (method_exists($this, 'get_total_posts_count')) {
                                echo esc_html((int) $this->get_total_posts_count());
                            } else {
                                echo '0';
                                error_log('KE SEO Booster: get_total_posts_count method not found');
                            }
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting total posts count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Total Posts', 'kseo-seo-booster'); ?></span>
                </div>
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number">
                        <?php 
                        try {
                            if (method_exists($this, 'get_schema_enabled_posts_count')) {
                                echo esc_html((int) $this->get_schema_enabled_posts_count());
                            } else {
                                echo '0';
                                error_log('KE SEO Booster: get_schema_enabled_posts_count method not found');
                            }
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting schema enabled posts count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Schema Enabled', 'kseo-seo-booster'); ?></span>
                </div>
                <div class="kseo-stat-item">
                    <span class="kseo-stat-number">
                        <?php 
                        try {
                            if (method_exists($this, 'get_social_tags_enabled_posts_count')) {
                                echo esc_html((int) $this->get_social_tags_enabled_posts_count());
                            } else {
                                echo '0';
                                error_log('KE SEO Booster: get_social_tags_enabled_posts_count method not found');
                            }
                        } catch (Exception $e) {
                            echo '0';
                            error_log('KE SEO Booster: Error getting social tags enabled posts count: ' . $e->getMessage());
                        }
                        ?>
                    </span>
                    <span class="kseo-stat-label"><?php _e('Social Tags', 'kseo-seo-booster'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="kseo-actions">
            <h3><?php _e('Quick Actions', 'kseo-seo-booster'); ?></h3>
            <div class="kseo-actions-grid">
                <a href="<?php echo admin_url('admin.php?page=kseo-settings'); ?>" class="button button-primary">
                    <?php _e('Configure Settings', 'kseo-seo-booster'); ?>
                </a>
                <a href="<?php echo admin_url('edit.php'); ?>" class="button">
                    <?php _e('Manage Posts', 'kseo-seo-booster'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=kseo-bulk-audit'); ?>" class="button">
                    <?php _e('Bulk Audit', 'kseo-seo-booster'); ?>
                </a>
            </div>
        </div>
        
        <div class="kseo-recent-activity">
            <h3><?php _e('Recent Activity', 'kseo-seo-booster'); ?></h3>
            <div class="kseo-activity-list">
                <?php
                try {
                    $post_types = get_option('kseo_post_types', array('post', 'page'));
                    if (!is_array($post_types)) {
                        $post_types = array('post', 'page');
                    }
                    
                    $recent_posts = get_posts(array(
                        'numberposts' => 5,
                        'post_type' => $post_types,
                        'meta_query' => array(
                            array(
                                'key' => '_kseo_title',
                                'compare' => 'EXISTS'
                            )
                        )
                    ));
                    
                    if ($recent_posts) {
                        foreach ($recent_posts as $post) {
                            try {
                                $seo_title = get_post_meta($post->ID, '_kseo_title', true);
                                $seo_description = get_post_meta($post->ID, '_kseo_description', true);
                                ?>
                                <div class="kseo-activity-item">
                                    <h4><a href="<?php echo get_edit_post_link($post->ID); ?>"><?php echo esc_html($post->post_title); ?></a></h4>
                                    <?php if ($seo_title) : ?>
                                        <p><strong><?php _e('SEO Title:', 'kseo-seo-booster'); ?></strong> <?php echo esc_html($seo_title); ?></p>
                                    <?php endif; ?>
                                    <?php if ($seo_description) : ?>
                                        <p><strong><?php _e('Meta Description:', 'kseo-seo-booster'); ?></strong> <?php echo esc_html(wp_trim_words($seo_description, 10)); ?></p>
                                    <?php endif; ?>
                                    <small><?php echo get_the_date('', $post->ID); ?></small>
                                </div>
                                <?php
                            } catch (Exception $e) {
                                error_log('KE SEO Booster: Error processing post ' . $post->ID . ': ' . $e->getMessage());
                                continue;
                            }
                        }
                    } else {
                        echo '<p>' . __('No optimized posts found. Start by editing a post and adding SEO meta.', 'kseo-seo-booster') . '</p>';
                    }
                } catch (Exception $e) {
                    error_log('KE SEO Booster: Error loading recent activity: ' . $e->getMessage());
                    echo '<p>' . __('Unable to load recent activity. Please check the error logs.', 'kseo-seo-booster') . '</p>';
                }
                ?>
            </div>
        </div>
        
        <div class="kseo-help">
            <h3><?php _e('Getting Started', 'kseo-seo-booster'); ?></h3>
            <div class="kseo-help-content">
                <ol>
                    <li><?php _e('Configure your settings in the Settings tab', 'kseo-seo-booster'); ?></li>
                    <li><?php _e('Edit any post or page to add SEO meta', 'kseo-seo-booster'); ?></li>
                    <li><?php _e('Use the AI generation features to create optimized content', 'kseo-seo-booster'); ?></li>
                    <li><?php _e('Monitor your SEO performance with the dashboard', 'kseo-seo-booster'); ?></li>
                </ol>
            </div>
        </div>
        
        <div class="kseo-health-status">
            <h3><?php _e('Plugin Health Status', 'kseo-seo-booster'); ?></h3>
            <div class="kseo-health-content">
                <?php
                try {
                    if (method_exists($this, 'get_plugin_health_status')) {
                        $health_status = $this->get_plugin_health_status();
                        ?>
                        <div class="kseo-health-grid">
                            <div class="kseo-health-item">
                                <strong><?php _e('Modules:', 'kseo-seo-booster'); ?></strong>
                                <?php echo esc_html($health_status['modules_loaded']); ?> / <?php echo esc_html($health_status['modules_total']); ?>
                            </div>
                            <div class="kseo-health-item">
                                <strong><?php _e('Database Tables:', 'kseo-seo-booster'); ?></strong>
                                <?php 
                                $table_status = array_filter($health_status['database_tables']);
                                echo esc_html(count($table_status)) . ' / ' . esc_html(count($health_status['database_tables']));
                                ?>
                            </div>
                            <div class="kseo-health-item">
                                <strong><?php _e('API Connections:', 'kseo-seo-booster'); ?></strong>
                                <?php 
                                $api_status = array_filter($health_status['api_connections']);
                                echo esc_html(count($api_status)) . ' / ' . esc_html(count($health_status['api_connections']));
                                ?>
                            </div>
                        </div>
                        <?php
                    } else {
                        echo '<p>' . __('Plugin health status not available.', 'kseo-seo-booster') . '</p>';
                    }
                } catch (Exception $e) {
                    error_log('KE SEO Booster: Error getting plugin health status: ' . $e->getMessage());
                    echo '<p>' . __('Unable to load plugin health status. Please check the error logs.', 'kseo-seo-booster') . '</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<style>
.kseo-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.kseo-stat-item {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    text-align: center;
}

.kseo-stat-number {
    display: block;
    font-size: 2em;
    font-weight: bold;
    color: #0073aa;
}

.kseo-stat-label {
    display: block;
    margin-top: 5px;
    color: #666;
}

.kseo-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin-top: 20px;
}

.kseo-activity-list {
    margin-top: 20px;
}

.kseo-activity-item {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
}

.kseo-activity-item h4 {
    margin: 0 0 10px 0;
}

.kseo-activity-item p {
    margin: 5px 0;
}

.kseo-activity-item small {
    color: #666;
}

.kseo-help-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 20px;
}

.kseo-help-content ol {
    margin: 0;
    padding-left: 20px;
}

.kseo-help-content li {
    margin-bottom: 10px;
}

.kseo-health-status {
    margin-top: 30px;
}

.kseo-health-content {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 20px;
}

.kseo-health-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.kseo-health-item {
    padding: 15px;
    background: #f9f9f9;
    border-radius: 3px;
    text-align: center;
}
</style> 