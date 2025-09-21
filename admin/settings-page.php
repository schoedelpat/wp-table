<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle settings form submission
if (isset($_POST['submit'])) {
    $nonce = $_POST['wp_table_settings_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'wp_table_settings')) {
        wp_die(__('Security check failed', 'wp-table'));
    }
    
    $table_style = sanitize_text_field($_POST['table_style']);
    $default_show_times = sanitize_text_field($_POST['default_show_times']);
    $date_format = sanitize_text_field($_POST['date_format']);
    $time_format = sanitize_text_field($_POST['time_format']);
    
    update_option('wp_table_style', $table_style);
    update_option('wp_table_default_show_times', $default_show_times);
    update_option('wp_table_date_format', $date_format);
    update_option('wp_table_time_format', $time_format);
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'wp-table') . '</p></div>';
}

// Get current settings
$table_style = get_option('wp_table_style', 'default');
$default_show_times = get_option('wp_table_default_show_times', 'true');
$date_format = get_option('wp_table_date_format', 'Y-m-d');
$time_format = get_option('wp_table_time_format', 'H:i');
?>

<div class="wrap">
    <h1><?php _e('WP Table Settings', 'wp-table'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('wp_table_settings', 'wp_table_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="table_style"><?php _e('Table Style', 'wp-table'); ?></label>
                </th>
                <td>
                    <select id="table_style" name="table_style">
                        <option value="default" <?php selected($table_style, 'default'); ?>><?php _e('Default', 'wp-table'); ?></option>
                        <option value="striped" <?php selected($table_style, 'striped'); ?>><?php _e('Striped', 'wp-table'); ?></option>
                        <option value="bordered" <?php selected($table_style, 'bordered'); ?>><?php _e('Bordered', 'wp-table'); ?></option>
                        <option value="minimal" <?php selected($table_style, 'minimal'); ?>><?php _e('Minimal', 'wp-table'); ?></option>
                    </select>
                    <p class="description"><?php _e('Choose the default style for staff tables.', 'wp-table'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="default_show_times"><?php _e('Show Times by Default', 'wp-table'); ?></label>
                </th>
                <td>
                    <select id="default_show_times" name="default_show_times">
                        <option value="true" <?php selected($default_show_times, 'true'); ?>><?php _e('Yes', 'wp-table'); ?></option>
                        <option value="false" <?php selected($default_show_times, 'false'); ?>><?php _e('No', 'wp-table'); ?></option>
                    </select>
                    <p class="description"><?php _e('Show working hours in the staff table by default.', 'wp-table'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="date_format"><?php _e('Date Format', 'wp-table'); ?></label>
                </th>
                <td>
                    <input type="text" id="date_format" name="date_format" value="<?php echo esc_attr($date_format); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Date format for displaying dates. Use PHP date format (e.g., Y-m-d, d/m/Y, F j, Y).', 'wp-table'); ?>
                        <br>
                        <?php printf(__('Current format displays as: %s', 'wp-table'), date($date_format)); ?>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="time_format"><?php _e('Time Format', 'wp-table'); ?></label>
                </th>
                <td>
                    <input type="text" id="time_format" name="time_format" value="<?php echo esc_attr($time_format); ?>" class="regular-text">
                    <p class="description">
                        <?php _e('Time format for displaying times. Use PHP time format (e.g., H:i, g:i A, H:i:s).', 'wp-table'); ?>
                        <br>
                        <?php printf(__('Current format displays as: %s', 'wp-table'), date($time_format)); ?>
                    </p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Settings', 'wp-table')); ?>
    </form>
    
    <div class="wp-table-info">
        <h2><?php _e('Plugin Information', 'wp-table'); ?></h2>
        <p><strong><?php _e('Version:', 'wp-table'); ?></strong> <?php echo WP_TABLE_VERSION; ?></p>
        <p><strong><?php _e('Database Table:', 'wp-table'); ?></strong> <?php echo $GLOBALS['wpdb']->prefix . 'wp_table_staff'; ?></p>
        
        <h3><?php _e('Support', 'wp-table'); ?></h3>
        <p><?php _e('For support and updates, visit the plugin repository:', 'wp-table'); ?></p>
        <p><a href="https://github.com/schoedelpat/wp-table" target="_blank">https://github.com/schoedelpat/wp-table</a></p>
    </div>
</div>