<div class="wrap">
    <h1>Audiobook Dispenser Settings</h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('acd_save_settings', 'acd_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="mailerlite_api_key">MailerLite API Key</label>
                </th>
                <td>
                    <input type="text" id="mailerlite_api_key" name="mailerlite_api_key" 
                           value="<?php echo esc_attr($settings['mailerlite_api_key'] ?? ''); ?>" 
                           class="regular-text" />
                    <p class="description">Enter your MailerLite API key for email integration.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="mailerlite_group_id">MailerLite Group ID</label>
                </th>
                <td>
                    <input type="text" id="mailerlite_group_id" name="mailerlite_group_id" 
                           value="<?php echo esc_attr($settings['mailerlite_group_id'] ?? ''); ?>" 
                           class="regular-text" />
                    <p class="description">Enter your MailerLite group ID where subscribers will be added.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="widget_title">Widget Title</label>
                </th>
                <td>
                    <input type="text" id="widget_title" name="widget_title" 
                           value="<?php echo esc_attr($settings['widget_title'] ?? 'Free Audiobook Codes'); ?>" 
                           class="regular-text" />
                    <p class="description">Title displayed on the frontend widget.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="widget_subtitle">Widget Subtitle</label>
                </th>
                <td>
                    <input type="text" id="widget_subtitle" name="widget_subtitle" 
                           value="<?php echo esc_attr($settings['widget_subtitle'] ?? 'Get your free audiobook from George Saoulidis'); ?>" 
                           class="regular-text" />
                    <p class="description">Subtitle displayed on the frontend widget.</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
    
    <div class="acd-shortcode-info">
        <h2>Usage</h2>
        <p>Use the following shortcode to display the audiobook dispenser widget:</p>
        <code>[audiobook_dispenser]</code>
        
        <p>You can also customize the title and subtitle:</p>
        <code>[audiobook_dispenser title="Custom Title" subtitle="Custom Subtitle"]</code>
    </div>
</div> 