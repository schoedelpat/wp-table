<?php
/**
 * Main Admin Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html__('Staff Table Management'); ?></h1>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-table-add')); ?>" class="button button-primary">
                <?php echo esc_html__('Add New Staff'); ?>
            </a>
        </div>
    </div>
    
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('wp_table_bulk_action'); ?>
        <input type="hidden" name="action" value="bulk_delete_staff">
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td id="cb" class="manage-column column-cb check-column">
                        <input id="cb-select-all-1" type="checkbox">
                    </td>
                    <th scope="col" class="manage-column column-name column-primary">
                        <?php echo esc_html__('Name'); ?>
                    </th>
                    <th scope="col" class="manage-column column-position">
                        <?php echo esc_html__('Position'); ?>
                    </th>
                    <th scope="col" class="manage-column column-start-time">
                        <?php echo esc_html__('Start Time'); ?>
                    </th>
                    <th scope="col" class="manage-column column-end-time">
                        <?php echo esc_html__('End Time'); ?>
                    </th>
                    <th scope="col" class="manage-column column-created">
                        <?php echo esc_html__('Created'); ?>
                    </th>
                    <th scope="col" class="manage-column column-actions">
                        <?php echo esc_html__('Actions'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($staff_list)) : ?>
                    <?php foreach ($staff_list as $staff) : ?>
                        <tr>
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="staff_ids[]" value="<?php echo esc_attr($staff['id']); ?>">
                            </th>
                            <td class="column-name column-primary">
                                <strong><?php echo esc_html($staff['name']); ?></strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-table-add&action=edit&staff_id=' . $staff['id'])); ?>">
                                            <?php echo esc_html__('Edit'); ?>
                                        </a> |
                                    </span>
                                    <span class="delete">
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=wp_table_delete_staff&staff_id=' . $staff['id']), 'wp_table_delete_staff')); ?>" 
                                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this staff member?')); ?>')">
                                            <?php echo esc_html__('Delete'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td class="column-position">
                                <?php echo esc_html($staff['position']); ?>
                            </td>
                            <td class="column-start-time">
                                <?php echo esc_html($staff['start_time']); ?>
                            </td>
                            <td class="column-end-time">
                                <?php echo esc_html($staff['end_time']); ?>
                            </td>
                            <td class="column-created">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($staff['created_at']))); ?>
                            </td>
                            <td class="column-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-table-add&action=edit&staff_id=' . $staff['id'])); ?>" 
                                   class="button button-small">
                                    <?php echo esc_html__('Edit'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="no-items">
                            <?php echo esc_html__('No staff members found.'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($staff_list)) : ?>
            <div class="tablenav bottom">
                <div class="alignleft actions">
                    <button type="submit" class="button action" 
                            onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete the selected staff members?')); ?>')">
                        <?php echo esc_html__('Delete Selected'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle select all checkbox
    $('#cb-select-all-1').on('change', function() {
        $('input[name="staff_ids[]"]').prop('checked', $(this).prop('checked'));
    });
    
    // Update select all checkbox when individual checkboxes change
    $('input[name="staff_ids[]"]').on('change', function() {
        var total = $('input[name="staff_ids[]"]').length;
        var checked = $('input[name="staff_ids[]"]:checked').length;
        $('#cb-select-all-1').prop('checked', total === checked);
    });
});
</script>