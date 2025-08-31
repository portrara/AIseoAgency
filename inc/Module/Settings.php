<?php
namespace KSEO\SEO_Booster\Module;

/**
 * Settings Module for KE SEO Booster Pro
 * 
 * Handles settings page and onboarding wizard.
 * 
 * @package KSEO\SEO_Booster\Module
 */

class Settings {
    
    /**
     * Initialize the settings module
     */
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // Register settings sections
        register_setting('kseo_options', 'kseo_modules');
        register_setting('kseo_options', 'kseo_post_types');
        register_setting('kseo_options', 'kseo_openai_api_key', array(
            'sanitize_callback' => array($this, 'sanitize_encrypt_secret')
        ));
        register_setting('kseo_options', 'kseo_google_ads_credentials', array(
            'sanitize_callback' => array($this, 'sanitize_google_credentials')
        ));
        register_setting('kseo_options', 'kseo_auto_generate');
        register_setting('kseo_options', 'kseo_enable_schema');
        register_setting('kseo_options', 'kseo_enable_og_tags');
        register_setting('kseo_options', 'kseo_onboarding_completed');
        register_setting('kseo_options', 'kseo_feature_flags', array(
            'sanitize_callback' => array($this, 'sanitize_feature_flags')
        ));
        register_setting('kseo_options', 'kseo_rate_limits', array(
            'sanitize_callback' => array($this, 'sanitize_rate_limits')
        ));
        // Consolidated AI/integration + runtime options stored under single kseo_ai option
        register_setting('kseo_options', 'kseo_ai', array(
            'sanitize_callback' => array($this, 'sanitize_kseo_ai')
        ));
        
        // Add settings sections
        add_settings_section(
            'kseo_modules_section',
            __('Modules', 'kseo-seo-booster'),
            array($this, 'modules_section_callback'),
            'kseo_options'
        );
        
        add_settings_section(
            'kseo_api_section',
            __('API Configuration', 'kseo-seo-booster'),
            array($this, 'api_section_callback'),
            'kseo_options'
        );
        
        add_settings_section(
            'kseo_ai_section',
            __('AI & Integrations', 'kseo-seo-booster'),
            function () { 
                echo '<p>' . esc_html__('Manage third-party keys and runtime limits for AI modules.', 'kseo-seo-booster') . '</p>'; 
            },
            'kseo_options'
        );
        
        add_settings_section(
            'kseo_general_section',
            __('General Settings', 'kseo-seo-booster'),
            array($this, 'general_section_callback'),
            'kseo_options'
        );
    }
    
    /**
     * Render the settings page
     */
    public function render() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        
        ?>
        <div class="wrap">
            <h1><?php _e('KE SEO Booster Pro Settings', 'kseo-seo-booster'); ?></h1>
            
            <nav class="nav-tab-wrapper">
                <a href="?page=kseo-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'kseo-seo-booster'); ?>
                </a>
                <a href="?page=kseo-settings&tab=modules" class="nav-tab <?php echo $active_tab === 'modules' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Modules', 'kseo-seo-booster'); ?>
                </a>
                <a href="?page=kseo-settings&tab=api" class="nav-tab <?php echo $active_tab === 'api' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('API Settings', 'kseo-seo-booster'); ?>
                </a>
                <a href="?page=kseo-settings&tab=onboarding" class="nav-tab <?php echo $active_tab === 'onboarding' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Setup Wizard', 'kseo-seo-booster'); ?>
                </a>
            </nav>
            
            <div class="kseo-settings-content">
                <?php
                switch ($active_tab) {
                    case 'general':
                        $this->render_general_tab();
                        break;
                    case 'modules':
                        $this->render_modules_tab();
                        break;
                    case 'api':
                        $this->render_api_tab();
                        break;
                    case 'onboarding':
                        $this->render_onboarding_tab();
                        break;
                    default:
                        $this->render_general_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render general settings tab
     */
    private function render_general_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('kseo_options');
            do_settings_sections('kseo_options');
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="kseo_post_types"><?php _e('Post Types', 'kseo-seo-booster'); ?></label>
                    </th>
                    <td>
                        <?php
                        $post_types = get_post_types(array('public' => true), 'objects');
                        $selected_types = get_option('kseo_post_types', array('post', 'page'));
                        
                        // Ensure selected_types is always an array
                        if (!is_array($selected_types)) {
                            $selected_types = array('post', 'page');
                        }
                        
                        foreach ($post_types as $post_type) {
                            $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="kseo_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' /> ' . esc_html($post_type->label) . '</label><br>';
                        }
                        ?>
                        <p class="description"><?php _e('Select post types to enable SEO optimization.', 'kseo-seo-booster'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="kseo_auto_generate"><?php _e('Auto Generate', 'kseo-seo-booster'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="kseo_auto_generate" name="kseo_auto_generate" value="1" <?php checked(get_option('kseo_auto_generate', true), '1'); ?> />
                            <?php _e('Automatically generate SEO meta when saving posts', 'kseo-seo-booster'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="kseo_enable_schema"><?php _e('Schema Markup', 'kseo-seo-booster'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="kseo_enable_schema" name="kseo_enable_schema" value="1" <?php checked(get_option('kseo_enable_schema', true), '1'); ?> />
                            <?php _e('Enable JSON-LD schema markup', 'kseo-seo-booster'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="kseo_enable_og_tags"><?php _e('Open Graph Tags', 'kseo-seo-booster'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="kseo_enable_og_tags" name="kseo_enable_og_tags" value="1" <?php checked(get_option('kseo_enable_og_tags', true), '1'); ?> />
                            <?php _e('Enable Open Graph tags for social media', 'kseo-seo-booster'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Render modules settings tab
     */
    private function render_modules_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('kseo_options');
            ?>
            
            <h3><?php _e('Module Settings', 'kseo-seo-booster'); ?></h3>
            <p><?php _e('Configure which SEO modules are enabled.', 'kseo-seo-booster'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Available Modules', 'kseo-seo-booster'); ?></th>
                    <td>
                        <?php
                        $modules = get_option('kseo_modules', array());
                        if (!is_array($modules)) {
                            $modules = array();
                        }
                        
                        $available_modules = array(
                            'meta_box' => __('SEO Meta Box', 'kseo-seo-booster'),
                            'meta_output' => __('Meta Output', 'kseo-seo-booster'),
                            'social_tags' => __('Social Tags', 'kseo-seo-booster'),
                            'schema' => __('Schema Markup', 'kseo-seo-booster'),
                            'sitemap' => __('XML Sitemap', 'kseo-seo-booster'),
                            'keyword_suggest' => __('Keyword Suggestions', 'kseo-seo-booster'),
                            'ai_generator' => __('AI Content Generator', 'kseo-seo-booster'),
                            'bulk_audit' => __('Bulk Audit', 'kseo-seo-booster'),
                            'internal_link' => __('Internal Linking', 'kseo-seo-booster'),
                            'api' => __('API Integration', 'kseo-seo-booster')
                        );
                        
                        foreach ($available_modules as $module_key => $module_name) {
                            $checked = isset($modules[$module_key]) && $modules[$module_key] ? 'checked' : '';
                            echo '<label><input type="checkbox" name="kseo_modules[' . esc_attr($module_key) . ']" value="1" ' . $checked . ' /> ' . esc_html($module_name) . '</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Render API settings tab
     */
    private function render_api_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('kseo_options');
            ?>
            
            <h3><?php _e('API Configuration', 'kseo-seo-booster'); ?></h3>
            <p><?php _e('Configure external API integrations.', 'kseo-seo-booster'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="kseo_openai_api_key"><?php _e('OpenAI API Key', 'kseo-seo-booster'); ?></label>
                    </th>
                    <td>
                        <input type="password" id="kseo_openai_api_key" name="kseo_openai_api_key" 
                               value="<?php echo esc_attr(get_option('kseo_openai_api_key')); ?>" class="regular-text" />
                        <p class="description"><?php _e('Enter your OpenAI API key for AI content generation.', 'kseo-seo-booster'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php $this->render_google_ads_credentials(); ?>
            
            <?php submit_button(); ?>
        </form>
        <?php
    }

    /**
     * Render Google Ads API credentials section
     */
    private function render_google_ads_credentials() {
        ?>
        <h3><?php _e('Google Ads API Credentials', 'kseo-seo-booster'); ?></h3>
        <p><?php _e('Configure Google Ads API for keyword suggestions.', 'kseo-seo-booster'); ?></p>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="kseo_google_customer_id"><?php _e('Customer ID', 'kseo-seo-booster'); ?></label>
                </th>
                <td>
                    <input type="text" id="kseo_google_customer_id" name="kseo_google_ads_credentials[customer_id]" 
                           value="<?php echo esc_attr($this->get_google_credential('customer_id')); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="kseo_google_developer_token"><?php _e('Developer Token', 'kseo-seo-booster'); ?></label>
                </th>
                <td>
                    <input type="password" id="kseo_google_developer_token" name="kseo_google_ads_credentials[developer_token]" 
                           value="<?php echo esc_attr($this->get_google_credential('developer_token')); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="kseo_google_client_id"><?php _e('Client ID', 'kseo-seo-booster'); ?></label>
                </th>
                <td>
                    <input type="text" id="kseo_google_client_id" name="kseo_google_ads_credentials[client_id]" 
                           value="<?php echo esc_attr($this->get_google_credential('client_id')); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="kseo_google_client_secret"><?php _e('Client Secret', 'kseo-seo-booster'); ?></label>
                </th>
                <td>
                    <input type="password" id="kseo_google_client_secret" name="kseo_google_ads_credentials[client_secret]" 
                           value="<?php echo esc_attr($this->get_google_credential('client_secret')); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render onboarding tab
     */
    private function render_onboarding_tab() {
        ?>
        <div class="kseo-onboarding">
            <h3><?php _e('Welcome to KE SEO Booster Pro!', 'kseo-seo-booster'); ?></h3>
            <p><?php _e('Let\'s get you started with a quick setup wizard.', 'kseo-seo-booster'); ?></p>
            
            <form id="kseo-onboarding-form" method="post" action="options.php">
                <?php settings_fields('kseo_options'); ?>
                
                <div class="kseo-onboarding-steps">
                    <div class="kseo-step active" data-step="1">
                        <h4><?php _e('Step 1: Post Types', 'kseo-seo-booster'); ?></h4>
                        <p><?php _e('Select which post types should have SEO optimization enabled.', 'kseo-seo-booster'); ?></p>
                        
                        <div class="kseo-post-types-selection">
                            <?php
                            $post_types = get_post_types(array('public' => true), 'objects');
                            $selected_types = get_option('kseo_post_types', array('post', 'page'));
                            
                            // Ensure selected_types is always an array
                            if (!is_array($selected_types)) {
                                $selected_types = array('post', 'page');
                            }
                            
                            foreach ($post_types as $post_type) {
                                $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
                                echo '<label><input type="checkbox" name="kseo_post_types[]" value="' . esc_attr($post_type->name) . '" ' . $checked . ' /> ' . esc_html($post_type->label) . '</label><br>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="kseo-step" data-step="2">
                        <h4><?php _e('Step 2: API Keys', 'kseo-seo-booster'); ?></h4>
                        <p><?php _e('Enter your API keys for enhanced functionality.', 'kseo-seo-booster'); ?></p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="kseo_openai_api_key"><?php _e('OpenAI API Key', 'kseo-seo-booster'); ?></label>
                                </th>
                                <td>
                                    <input type="password" id="kseo_openai_api_key" name="kseo_openai_api_key" 
                                           value="<?php echo esc_attr(get_option('kseo_openai_api_key')); ?>" class="regular-text" />
                                    <p class="description"><?php _e('Optional: For AI content generation features.', 'kseo-seo-booster'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="kseo-step" data-step="3">
                        <h4><?php _e('Step 3: Enable Modules', 'kseo-seo-booster'); ?></h4>
                        <p><?php _e('Choose which SEO modules to enable.', 'kseo-seo-booster'); ?></p>
                        
                        <div class="kseo-modules-selection">
                            <?php
                            $modules = get_option('kseo_modules', array());
                            if (!is_array($modules)) {
                                $modules = array();
                            }
                            
                            $available_modules = array(
                                'meta_box' => __('SEO Meta Box', 'kseo-seo-booster'),
                                'meta_output' => __('Meta Output', 'kseo-seo-booster'),
                                'social_tags' => __('Social Tags', 'kseo-seo-booster'),
                                'schema' => __('Schema Markup', 'kseo-seo-booster'),
                                'sitemap' => __('XML Sitemap', 'kseo-seo-booster')
                            );
                            
                            foreach ($available_modules as $module_key => $module_name) {
                                $checked = isset($modules[$module_key]) && $modules[$module_key] ? 'checked' : '';
                                echo '<label><input type="checkbox" name="kseo_modules[' . esc_attr($module_key) . ']" value="1" ' . $checked . ' /> ' . esc_html($module_name) . '</label><br>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="kseo-onboarding-actions">
                    <button type="button" class="button button-secondary" id="kseo-prev-step" style="display: none;"><?php _e('Previous', 'kseo-seo-booster'); ?></button>
                    <button type="button" class="button button-primary" id="kseo-next-step"><?php _e('Next', 'kseo-seo-booster'); ?></button>
                    <button type="button" class="button button-primary" id="kseo-complete-setup" style="display: none;"><?php _e('Complete Setup', 'kseo-seo-booster'); ?></button>
                </div>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var currentStep = 1;
            var totalSteps = 3;
            
            function showStep(step) {
                $('.kseo-step').removeClass('active');
                $('.kseo-step[data-step="' + step + '"]').addClass('active');
                
                if (step === 1) {
                    $('#kseo-prev-step').hide();
                } else {
                    $('#kseo-prev-step').show();
                }
                
                if (step === totalSteps) {
                    $('#kseo-next-step').hide();
                    $('#kseo-complete-setup').show();
                } else {
                    $('#kseo-next-step').show();
                    $('#kseo-complete-setup').hide();
                }
            }
            
            $('#kseo-next-step').click(function() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            });
            
            $('#kseo-prev-step').click(function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });
            
            $('#kseo-complete-setup').click(function() {
                var formData = $('#kseo-onboarding-form').serialize();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'kseo_complete_onboarding',
                        nonce: '<?php echo wp_create_nonce('kseo_nonce'); ?>',
                        formData: formData
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Setup completed successfully!');
                            location.reload();
                        } else {
                            alert('Setup failed: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Setup failed. Please try again.');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Get Google credential value
     */
    private function get_google_credential($key) {
        $credentials = get_option('kseo_google_ads_credentials', array());
        if (!is_array($credentials)) {
            $credentials = array();
        }
        return isset($credentials[$key]) ? $credentials[$key] : '';
    }
    
    /**
     * Section callbacks
     */
    public function modules_section_callback() {
        echo '<p>' . __('Configure which modules are enabled. Some modules require API configuration.', 'kseo-seo-booster') . '</p>';
    }
    
    public function api_section_callback() {
        echo '<p>' . __('Configure API keys for advanced features like AI content generation and keyword suggestions.', 'kseo-seo-booster') . '</p>';
    }
    
    public function general_section_callback() {
        echo '<p>' . __('Configure general settings for the SEO plugin.', 'kseo-seo-booster') . '</p>';
    }

    /**
     * Sanitization methods
     */
    public function sanitize_feature_flags($value) {
        $out = array();
        $in = is_array($value) ? $value : array();
        $out['rate_limit_enabled'] = !empty($in['rate_limit_enabled']) ? 1 : 0;
        $out['strict_json_validation'] = !empty($in['strict_json_validation']) ? 1 : 0;
        $out['bearer_only'] = !empty($in['bearer_only']) ? 1 : 0;
        return $out;
    }

    public function sanitize_rate_limits($value) {
        $out = array();
        $in = is_array($value) ? $value : array();
        foreach ($in as $route => $limit) {
            $route = sanitize_text_field($route);
            $limit = max(1, min(10000, (int) $limit));
            $out[$route] = $limit;
        }
        return $out;
    }

    /**
     * Sanitize and encrypt single secret option
     */
    public function sanitize_encrypt_secret($value) {
        $current = get_option('kseo_openai_api_key');
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return $current; // keep existing
        }
        return 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt($value);
    }

    /**
     * Sanitize and encrypt Google credentials array
     */
    public function sanitize_google_credentials($value) {
        $current = get_option('kseo_google_ads_credentials', array());
        $value = is_array($value) ? $value : array();
        $out = $current;
        // Non-secret fields can be stored as-is
        if (isset($value['customer_id'])) {
            $out['customer_id'] = sanitize_text_field($value['customer_id']);
        }
        if (isset($value['client_id'])) {
            $out['client_id'] = sanitize_text_field($value['client_id']);
        }
        // Secrets: only overwrite when provided
        if (!empty($value['developer_token'])) {
            $out['developer_token'] = 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt(sanitize_text_field($value['developer_token']));
        }
        if (!empty($value['client_secret'])) {
            $out['client_secret'] = 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt(sanitize_text_field($value['client_secret']));
        }
        return $out;
    }

    /**
     * Inline process: test alert button
     */
    private function maybe_send_test_alert() {
        if (isset($_POST['kseo_send_test_alert']) && check_admin_referer('kseo_security_nonce', 'kseo_security_nonce')) {
            \KSEO\SEO_Booster\Core\Alerts::send('test', array('message' => 'This is a test alert from KSEO'));
            echo '<div class="notice notice-success"><p>' . esc_html__('Test alert dispatched (check email/Slack).', 'kseo-seo-booster') . '</p></div>';
        }
    }

    /**
     * Sanitize consolidated kseo_ai option
     */
    public function sanitize_kseo_ai($value) {
        $current = get_option('kseo_ai', array());
        $in = is_array($value) ? $value : array();
        $out = $current;

        // Plain fields
        if (isset($in['gsc_client_id'])) { $out['gsc_client_id'] = sanitize_text_field($in['gsc_client_id']); }
        if (isset($in['serp_provider'])) { $out['serp_provider'] = sanitize_text_field($in['serp_provider']); }
        if (isset($in['cache_ttl'])) { $out['cache_ttl'] = max(1, min(168, intval($in['cache_ttl']))); }
        if (isset($in['index_ttl'])) { $out['index_ttl'] = max(1, min(168, intval($in['index_ttl']))); }
        if (isset($in['max_candidates'])) { $out['max_candidates'] = max(1, min(10000, intval($in['max_candidates']))); }
        if (isset($in['max_urls_per_run'])) { $out['max_urls_per_run'] = max(1, min(500, intval($in['max_urls_per_run']))); }
        $out['weekly_cron_enabled'] = !empty($in['weekly_cron_enabled']) ? 1 : 0;
        $out['alert_email_enabled'] = !empty($in['alert_email_enabled']) ? 1 : 0;
        if (isset($in['alert_email_to'])) { $out['alert_email_to'] = sanitize_email($in['alert_email_to']); }
        if (isset($in['slack_webhook_url'])) { $out['slack_webhook_url'] = esc_url_raw($in['slack_webhook_url']); }
        $out['alert_decay_enabled'] = !empty($in['alert_decay_enabled']) ? 1 : 0;
        $out['alert_cannibal_enabled'] = !empty($in['alert_cannibal_enabled']) ? 1 : 0;

        // Secrets: overwrite only if provided
        if (!empty($in['gsc_client_secret'])) {
            $out['gsc_client_secret'] = 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt(sanitize_text_field($in['gsc_client_secret']));
        }
        if (!empty($in['ga4_json_key'])) {
            $ga = is_string($in['ga4_json_key']) ? trim($in['ga4_json_key']) : '';
            $out['ga4_json_key'] = 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt($ga);
        }
        if (!empty($in['rank_api_key'])) {
            $out['rank_api_key'] = 'enc:' . \KSEO\SEO_Booster\Security\Crypto::encrypt(sanitize_text_field($in['rank_api_key']));
        }

        return $out;
    }
} 