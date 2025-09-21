<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wp_table_staff';

// Build query based on shortcode attributes
$where_clause = '';
if ($atts['status'] !== 'all') {
    $where_clause = $wpdb->prepare(" WHERE status = %s", $atts['status']);
}

$staff_members = $wpdb->get_results("SELECT * FROM $table_name" . $where_clause . " ORDER BY name ASC");

if (empty($staff_members)) {
    echo '<p class="wp-table-no-staff">' . __('No staff members found.', 'wp-table') . '</p>';
    return;
}

$table_style = get_option('wp_table_style', 'default');
$time_format = get_option('wp_table_time_format', 'H:i');
?>

<div class="wp-table-container">
    <table class="wp-staff-table wp-table-style-<?php echo esc_attr($table_style); ?>">
        <thead>
            <tr>
                <th class="wp-table-name"><?php _e('Name', 'wp-table'); ?></th>
                <th class="wp-table-position"><?php _e('Position', 'wp-table'); ?></th>
                
                <?php if ($atts['show_email'] === 'true'): ?>
                    <th class="wp-table-email"><?php _e('Email', 'wp-table'); ?></th>
                <?php endif; ?>
                
                <?php if ($atts['show_phone'] === 'true'): ?>
                    <th class="wp-table-phone"><?php _e('Phone', 'wp-table'); ?></th>
                <?php endif; ?>
                
                <?php if ($atts['show_times'] === 'true'): ?>
                    <th class="wp-table-hours"><?php _e('Working Hours', 'wp-table'); ?></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($staff_members as $staff): ?>
                <tr class="wp-table-staff-row wp-table-status-<?php echo esc_attr($staff->status); ?>">
                    <td class="wp-table-name">
                        <strong><?php echo esc_html($staff->name); ?></strong>
                    </td>
                    <td class="wp-table-position">
                        <?php echo esc_html($staff->position); ?>
                    </td>
                    
                    <?php if ($atts['show_email'] === 'true'): ?>
                        <td class="wp-table-email">
                            <?php if (!empty($staff->email)): ?>
                                <a href="mailto:<?php echo esc_attr($staff->email); ?>">
                                    <?php echo esc_html($staff->email); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_phone'] === 'true'): ?>
                        <td class="wp-table-phone">
                            <?php if (!empty($staff->phone)): ?>
                                <a href="tel:<?php echo esc_attr($staff->phone); ?>">
                                    <?php echo esc_html($staff->phone); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_times'] === 'true'): ?>
                        <td class="wp-table-hours">
                            <?php 
                            $start_time = date($time_format, strtotime($staff->start_time));
                            $end_time = date($time_format, strtotime($staff->end_time));
                            echo esc_html($start_time . ' - ' . $end_time);
                            ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>