<?php
/**
 * Settings Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$default_image_size = get_option('wp_table_default_image_size', 300);
$show_images = get_option('wp_table_show_images', 'yes');
$show_email = get_option('wp_table_show_email', 'no');
$show_phone = get_option('wp_table_show_phone', 'no');
$show_times = get_option('wp_table_show_times', 'yes');

// Handle form submission
if (isset($_POST['save_settings'])) {
    $nonce = $_POST['wp_table_settings_nonce'] ?? '';
    if (wp_verify_nonce($nonce, 'wp_table_settings')) {
        update_option('wp_table_default_image_size', intval($_POST['default_image_size']));
        update_option('wp_table_show_images', sanitize_text_field($_POST['show_images']));
        update_option('wp_table_show_email', sanitize_text_field($_POST['show_email']));
        update_option('wp_table_show_phone', sanitize_text_field($_POST['show_phone']));
        update_option('wp_table_show_times', sanitize_text_field($_POST['show_times']));
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved successfully!') . '</p></div>';
        
        // Refresh values
        $default_image_size = get_option('wp_table_default_image_size', 300);
        $show_images = get_option('wp_table_show_images', 'yes');
        $show_email = get_option('wp_table_show_email', 'no');
        $show_phone = get_option('wp_table_show_phone', 'no');
        $show_times = get_option('wp_table_show_times', 'yes');
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html__('WP Table Settings'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wp_table_settings', 'wp_table_settings_nonce'); ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="default_image_size"><?php echo esc_html__('Default Image Size'); ?></label>
                    </th>
                    <td>
                        <input type="range" 
                               id="default_image_size" 
                               name="default_image_size" 
                               min="100" 
                               max="500" 
                               value="<?php echo esc_attr($default_image_size); ?>" 
                               step="10"
                               oninput="updateDefaultImageSizeDisplay(this.value)">
                        <span id="default_image_size_display"><?php echo esc_html($default_image_size); ?>px</span>
                        <p class="description">
                            <?php echo esc_html__('Default image size for new staff members (100px - 500px). Images are always square.'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="show_images"><?php echo esc_html__('Show Images by Default'); ?></label>
                    </th>
                    <td>
                        <select id="show_images" name="show_images">
                            <option value="yes" <?php selected($show_images, 'yes'); ?>>
                                <?php echo esc_html__('Yes'); ?>
                            </option>
                            <option value="no" <?php selected($show_images, 'no'); ?>>
                                <?php echo esc_html__('No'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Whether to show images by default in the frontend table.'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="show_email"><?php echo esc_html__('Show Email by Default'); ?></label>
                    </th>
                    <td>
                        <select id="show_email" name="show_email">
                            <option value="yes" <?php selected($show_email, 'yes'); ?>>
                                <?php echo esc_html__('Yes'); ?>
                            </option>
                            <option value="no" <?php selected($show_email, 'no'); ?>>
                                <?php echo esc_html__('No'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Whether to show email addresses by default in the frontend table.'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="show_phone"><?php echo esc_html__('Show Phone by Default'); ?></label>
                    </th>
                    <td>
                        <select id="show_phone" name="show_phone">
                            <option value="yes" <?php selected($show_phone, 'yes'); ?>>
                                <?php echo esc_html__('Yes'); ?>
                            </option>
                            <option value="no" <?php selected($show_phone, 'no'); ?>>
                                <?php echo esc_html__('No'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Whether to show phone numbers by default in the frontend table.'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="show_times"><?php echo esc_html__('Show Working Hours by Default'); ?></label>
                    </th>
                    <td>
                        <select id="show_times" name="show_times">
                            <option value="yes" <?php selected($show_times, 'yes'); ?>>
                                <?php echo esc_html__('Yes'); ?>
                            </option>
                            <option value="no" <?php selected($show_times, 'no'); ?>>
                                <?php echo esc_html__('No'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php echo esc_html__('Whether to show working hours by default in the frontend table.'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button(__('Save Settings'), 'primary', 'save_settings'); ?>
    </form>
    
    <hr>
    
    <h2><?php echo esc_html__('Shortcode Usage'); ?></h2>
    <p><?php echo esc_html__('Use the following shortcode to display the staff table on any page or post:'); ?></p>
    <code>[wp_staff_table]</code>
    
    <h3><?php echo esc_html__('Shortcode Parameters'); ?></h3>
    <ul>
        <li><code>show_images="true|false"</code> - <?php echo esc_html__('Show staff images (default: based on settings)'); ?></li>
        <li><code>show_times="true|false"</code> - <?php echo esc_html__('Show working hours (default: based on settings)'); ?></li>
        <li><code>show_email="true|false"</code> - <?php echo esc_html__('Show email addresses (default: based on settings)'); ?></li>
        <li><code>show_phone="true|false"</code> - <?php echo esc_html__('Show phone numbers (default: based on settings)'); ?></li>
        <li><code>status="active|inactive|all"</code> - <?php echo esc_html__('Filter by status (default: active)'); ?></li>
        <li><code>image_size="100-500"</code> - <?php echo esc_html__('Override image size in pixels (default: based on settings)'); ?></li>
    </ul>
    
    <h3><?php echo esc_html__('Examples'); ?></h3>
    <p><code>[wp_staff_table show_email="true" show_phone="true" image_size="200"]</code></p>
    <p><code>[wp_staff_table show_images="false" status="all"]</code></p>
</div>

<script>
function updateDefaultImageSizeDisplay(value) {
    document.getElementById('default_image_size_display').textContent = value + 'px';
}
</script>