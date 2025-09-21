<?php
/**
 * Add/Edit Staff Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if editing existing staff
$is_edit = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['staff_id']);
$staff_id = $is_edit ? intval($_GET['staff_id']) : 0;
$staff_data = array();

if ($is_edit && $staff_id > 0) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'staff_table';
    $staff_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE id = %d",
        $staff_id
    ), ARRAY_A);
    
    if (!$staff_data) {
        wp_die(esc_html__('Staff member not found.'));
    }
}

$page_title = $is_edit ? __('Edit Staff Member') : __('Add New Staff Member');
$form_action = $is_edit ? 'wp_table_edit_staff' : 'wp_table_add_staff';
$nonce_action = $is_edit ? 'wp_table_edit_staff' : 'wp_table_add_staff';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field($nonce_action); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php if ($is_edit) : ?>
            <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff_id); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="staff_name"><?php echo esc_html__('Name'); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="staff_name" 
                               name="name" 
                               value="<?php echo esc_attr($staff_data['name'] ?? ''); ?>" 
                               class="regular-text" 
                               required 
                               maxlength="255">
                        <p class="description"><?php echo esc_html__('Enter the staff member\'s full name.'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="staff_position"><?php echo esc_html__('Position'); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="staff_position" 
                               name="position" 
                               value="<?php echo esc_attr($staff_data['position'] ?? ''); ?>" 
                               class="regular-text" 
                               required 
                               maxlength="100">
                        <p class="description"><?php echo esc_html__('Enter the staff member\'s job position or title.'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="staff_start_time"><?php echo esc_html__('Start Time'); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="time" 
                               id="staff_start_time" 
                               name="start_time" 
                               value="<?php echo esc_attr($staff_data['start_time'] ?? ''); ?>" 
                               required>
                        <p class="description"><?php echo esc_html__('Select the staff member\'s work start time.'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="staff_end_time"><?php echo esc_html__('End Time'); ?> <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="time" 
                               id="staff_end_time" 
                               name="end_time" 
                               value="<?php echo esc_attr($staff_data['end_time'] ?? ''); ?>" 
                               required>
                        <p class="description"><?php echo esc_html__('Select the staff member\'s work end time.'); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <?php submit_button($is_edit ? __('Update Staff Member') : __('Add Staff Member')); ?>
    </form>
    
    <div style="margin-top: 20px;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-table')); ?>" class="button">
            <?php echo esc_html__('← Back to Staff List'); ?>
        </a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Validate time inputs
    $('#staff_start_time, #staff_end_time').on('change', function() {
        var startTime = $('#staff_start_time').val();
        var endTime = $('#staff_end_time').val();
        
        if (startTime && endTime) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date('2000-01-01 ' + endTime);
            
            if (end <= start) {
                alert('<?php echo esc_js(__('End time must be after start time.')); ?>');
                $(this).focus();
            }
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var name = $('#staff_name').val().trim();
        var position = $('#staff_position').val().trim();
        var startTime = $('#staff_start_time').val();
        var endTime = $('#staff_end_time').val();
        
        if (!name || !position || !startTime || !endTime) {
            e.preventDefault();
            alert('<?php echo esc_js(__('Please fill in all required fields.')); ?>');
            return false;
        }
        
        var start = new Date('2000-01-01 ' + startTime);
        var end = new Date('2000-01-01 ' + endTime);
        
        if (end <= start) {
            e.preventDefault();
            alert('<?php echo esc_js(__('End time must be after start time.')); ?>');
            return false;
        }
    });
});
</script>