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
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field($nonce_action); ?>
        <input type="hidden" name="action" value="<?php echo esc_attr($form_action); ?>">
        <?php if ($is_edit) : ?>
            <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff_id); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="staff_image"><?php echo esc_html__('Profile Image'); ?></label>
                    </th>
                    <td>
                        <?php if ($is_edit && !empty($staff_data['image_url'])) : ?>
                            <div class="current-image" style="margin-bottom: 10px;">
                                <img src="<?php echo esc_url($staff_data['image_url']); ?>" 
                                     alt="Current profile image" 
                                     style="width: <?php echo esc_attr($staff_data['image_size'] ?? 300); ?>px; height: <?php echo esc_attr($staff_data['image_size'] ?? 300); ?>px; object-fit: cover; border: 1px solid #ddd;">
                                <p><em><?php echo esc_html__('Current image'); ?></em></p>
                            </div>
                        <?php endif; ?>
                        <input type="file" 
                               id="staff_image" 
                               name="staff_image" 
                               accept="image/jpeg,image/png,image/gif">
                        <p class="description">
                            <?php echo esc_html__('Upload a profile image (JPEG, PNG, or GIF). Image will be automatically resized to a square format. Maximum file size: 5MB.'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="image_size"><?php echo esc_html__('Image Size'); ?></label>
                    </th>
                    <td>
                        <input type="range" 
                               id="image_size" 
                               name="image_size" 
                               min="100" 
                               max="500" 
                               value="<?php echo esc_attr($staff_data['image_size'] ?? 300); ?>" 
                               step="10"
                               oninput="updateImageSizeDisplay(this.value)">
                        <span id="image_size_display"><?php echo esc_html($staff_data['image_size'] ?? 300); ?>px</span>
                        <p class="description"><?php echo esc_html__('Choose image size between 100px and 500px. Images are always square.'); ?></p>
                    </td>
                </tr>
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
                        <label for="staff_email"><?php echo esc_html__('Email'); ?></label>
                    </th>
                    <td>
                        <input type="email" 
                               id="staff_email" 
                               name="email" 
                               value="<?php echo esc_attr($staff_data['email'] ?? ''); ?>" 
                               class="regular-text" 
                               maxlength="100">
                        <p class="description"><?php echo esc_html__('Enter the staff member\'s email address.'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="staff_phone"><?php echo esc_html__('Phone'); ?></label>
                    </th>
                    <td>
                        <input type="tel" 
                               id="staff_phone" 
                               name="phone" 
                               value="<?php echo esc_attr($staff_data['phone'] ?? ''); ?>" 
                               class="regular-text" 
                               maxlength="20">
                        <p class="description"><?php echo esc_html__('Enter the staff member\'s phone number.'); ?></p>
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
                <tr>
                    <th scope="row">
                        <label for="staff_status"><?php echo esc_html__('Status'); ?></label>
                    </th>
                    <td>
                        <select id="staff_status" name="status">
                            <option value="active" <?php selected($staff_data['status'] ?? 'active', 'active'); ?>>
                                <?php echo esc_html__('Active'); ?>
                            </option>
                            <option value="inactive" <?php selected($staff_data['status'] ?? 'active', 'inactive'); ?>>
                                <?php echo esc_html__('Inactive'); ?>
                            </option>
                        </select>
                        <p class="description"><?php echo esc_html__('Set the staff member\'s current status.'); ?></p>
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
function updateImageSizeDisplay(value) {
    document.getElementById('image_size_display').textContent = value + 'px';
}

jQuery(document).ready(function($) {
    // Image preview functionality
    $('#staff_image').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // Remove existing preview
                $('.image-preview').remove();
                
                // Create preview
                var preview = $('<div class="image-preview" style="margin-top: 10px;">' +
                    '<img src="' + e.target.result + '" style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ddd;">' +
                    '<p><em><?php echo esc_js(__('Preview (image will be resized and cropped to square)')); ?></em></p>' +
                    '</div>');
                
                $('#staff_image').after(preview);
            };
            reader.readAsDataURL(file);
        }
    });
    
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
        
        // Validate image file if provided
        var imageFile = $('#staff_image')[0].files[0];
        if (imageFile) {
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(imageFile.type)) {
                e.preventDefault();
                alert('<?php echo esc_js(__('Please upload a valid image file (JPEG, PNG, or GIF).')); ?>');
                return false;
            }
            
            if (imageFile.size > 5 * 1024 * 1024) { // 5MB
                e.preventDefault();
                alert('<?php echo esc_js(__('Image file size must be less than 5MB.')); ?>');
                return false;
            }
        }
    });
});
</script>