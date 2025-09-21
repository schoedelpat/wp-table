<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wp_table_staff';

// Handle form submissions
if (isset($_POST['action'])) {
    $nonce = $_POST['wp_table_nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'wp_table_action')) {
        wp_die(__('Security check failed', 'wp-table'));
    }
    
    switch ($_POST['action']) {
        case 'add_staff':
            $name = sanitize_text_field($_POST['name']);
            $position = sanitize_text_field($_POST['position']);
            $email = sanitize_email($_POST['email']);
            $phone = sanitize_text_field($_POST['phone']);
            $start_time = sanitize_text_field($_POST['start_time']);
            $end_time = sanitize_text_field($_POST['end_time']);
            $status = sanitize_text_field($_POST['status']);
            
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'position' => $position,
                    'email' => $email,
                    'phone' => $phone,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'status' => $status
                )
            );
            
            echo '<div class="notice notice-success"><p>' . __('Staff member added successfully!', 'wp-table') . '</p></div>';
            break;
            
        case 'delete_staff':
            $id = intval($_POST['staff_id']);
            $wpdb->delete($table_name, array('id' => $id));
            echo '<div class="notice notice-success"><p>' . __('Staff member deleted successfully!', 'wp-table') . '</p></div>';
            break;
    }
}

// Get all staff members
$staff_members = $wpdb->get_results("SELECT * FROM $table_name ORDER BY name ASC");
?>

<div class="wrap">
    <h1><?php _e('WP Table - Staff Members', 'wp-table'); ?></h1>
    
    <!-- Add New Staff Form -->
    <div class="wp-table-form-container">
        <h2><?php _e('Add New Staff Member', 'wp-table'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('wp_table_action', 'wp_table_nonce'); ?>
            <input type="hidden" name="action" value="add_staff">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Name', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="name" name="name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="position"><?php _e('Position', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="position" name="position" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email"><?php _e('Email', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="email" name="email" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="phone"><?php _e('Phone', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="phone" name="phone" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="start_time"><?php _e('Start Time', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="start_time" name="start_time" value="09:00">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="end_time"><?php _e('End Time', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <input type="time" id="end_time" name="end_time" value="17:00">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="status"><?php _e('Status', 'wp-table'); ?></label>
                    </th>
                    <td>
                        <select id="status" name="status">
                            <option value="active"><?php _e('Active', 'wp-table'); ?></option>
                            <option value="inactive"><?php _e('Inactive', 'wp-table'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Add Staff Member', 'wp-table')); ?>
        </form>
    </div>
    
    <!-- Staff Members Table -->
    <div class="wp-table-list-container">
        <h2><?php _e('Current Staff Members', 'wp-table'); ?></h2>
        
        <?php if (empty($staff_members)): ?>
            <p><?php _e('No staff members found. Add your first staff member above.', 'wp-table'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'wp-table'); ?></th>
                        <th><?php _e('Position', 'wp-table'); ?></th>
                        <th><?php _e('Email', 'wp-table'); ?></th>
                        <th><?php _e('Phone', 'wp-table'); ?></th>
                        <th><?php _e('Working Hours', 'wp-table'); ?></th>
                        <th><?php _e('Status', 'wp-table'); ?></th>
                        <th><?php _e('Actions', 'wp-table'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_members as $staff): ?>
                        <tr>
                            <td><strong><?php echo esc_html($staff->name); ?></strong></td>
                            <td><?php echo esc_html($staff->position); ?></td>
                            <td><?php echo esc_html($staff->email); ?></td>
                            <td><?php echo esc_html($staff->phone); ?></td>
                            <td><?php echo esc_html($staff->start_time . ' - ' . $staff->end_time); ?></td>
                            <td>
                                <span class="status-<?php echo esc_attr($staff->status); ?>">
                                    <?php echo esc_html(ucfirst($staff->status)); ?>
                                </span>
                            </td>
                            <td>
                                <form method="post" style="display: inline;" onsubmit="return confirm('<?php _e('Are you sure you want to delete this staff member?', 'wp-table'); ?>');">
                                    <?php wp_nonce_field('wp_table_action', 'wp_table_nonce'); ?>
                                    <input type="hidden" name="action" value="delete_staff">
                                    <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff->id); ?>">
                                    <button type="submit" class="button button-small button-link-delete">
                                        <?php _e('Delete', 'wp-table'); ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Shortcode Usage -->
    <div class="wp-table-shortcode-info">
        <h2><?php _e('Usage', 'wp-table'); ?></h2>
        <p><?php _e('Use the following shortcode to display the staff table on any page or post:', 'wp-table'); ?></p>
        <code>[wp_staff_table]</code>
        
        <h3><?php _e('Shortcode Parameters', 'wp-table'); ?></h3>
        <ul>
            <li><code>show_times="true|false"</code> - <?php _e('Show working hours (default: true)', 'wp-table'); ?></li>
            <li><code>show_email="true|false"</code> - <?php _e('Show email addresses (default: false)', 'wp-table'); ?></li>
            <li><code>show_phone="true|false"</code> - <?php _e('Show phone numbers (default: false)', 'wp-table'); ?></li>
            <li><code>status="active|inactive|all"</code> - <?php _e('Filter by status (default: active)', 'wp-table'); ?></li>
        </ul>
        
        <h3><?php _e('Examples', 'wp-table'); ?></h3>
        <p><code>[wp_staff_table show_email="true" show_phone="true"]</code></p>
        <p><code>[wp_staff_table show_times="false" status="all"]</code></p>
    </div>
</div>