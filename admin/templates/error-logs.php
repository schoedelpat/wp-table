<?php
/**
 * Error Logs Page Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html__('Error Logs'); ?></h1>
    
    <div class="tablenav top">
        <div class="alignright actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-table')); ?>" class="button">
                <?php echo esc_html__('← Back to Staff List'); ?>
            </a>
        </div>
    </div>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-timestamp">
                    <?php echo esc_html__('Timestamp'); ?>
                </th>
                <th scope="col" class="manage-column column-level">
                    <?php echo esc_html__('Level'); ?>
                </th>
                <th scope="col" class="manage-column column-message column-primary">
                    <?php echo esc_html__('Message'); ?>
                </th>
                <th scope="col" class="manage-column column-user">
                    <?php echo esc_html__('User'); ?>
                </th>
                <th scope="col" class="manage-column column-ip">
                    <?php echo esc_html__('IP Address'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($error_logs)) : ?>
                <?php foreach ($error_logs as $log) : ?>
                    <tr>
                        <td class="column-timestamp">
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['timestamp']))); ?>
                        </td>
                        <td class="column-level">
                            <span class="log-level log-level-<?php echo esc_attr($log['level']); ?>">
                                <?php echo esc_html(strtoupper($log['level'])); ?>
                            </span>
                        </td>
                        <td class="column-message column-primary">
                            <?php echo esc_html($log['message']); ?>
                            <?php if (!empty($log['context'])) : ?>
                                <div class="row-actions">
                                    <span class="context">
                                        <a href="#" class="toggle-context" data-context="<?php echo esc_attr($log['id']); ?>">
                                            <?php echo esc_html__('Show Context'); ?>
                                        </a>
                                    </span>
                                </div>
                                <div class="context-data" id="context-<?php echo esc_attr($log['id']); ?>" style="display: none;">
                                    <pre><?php echo esc_html($log['context']); ?></pre>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="column-user">
                            <?php if ($log['user_id']) : ?>
                                <?php 
                                $user = get_user_by('id', $log['user_id']);
                                echo $user ? esc_html($user->display_name) : esc_html__('Unknown User');
                                ?>
                            <?php else : ?>
                                <em><?php echo esc_html__('Guest'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td class="column-ip">
                            <?php echo esc_html($log['ip_address'] ?: 'N/A'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" class="no-items">
                        <?php echo esc_html__('No error logs found.'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.log-level {
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.log-level-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.log-level-warning {
    background-color: #fff3cd;
    color: #856404;
}

.log-level-error {
    background-color: #f8d7da;
    color: #721c24;
}

.log-level-critical {
    background-color: #dc3545;
    color: #ffffff;
}

.context-data {
    margin-top: 5px;
    background: #f1f1f1;
    padding: 10px;
    border-radius: 3px;
}

.context-data pre {
    margin: 0;
    font-size: 12px;
    line-height: 1.4;
    max-height: 200px;
    overflow-y: auto;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.toggle-context').on('click', function(e) {
        e.preventDefault();
        var contextId = $(this).data('context');
        var contextDiv = $('#context-' + contextId);
        
        if (contextDiv.is(':visible')) {
            contextDiv.hide();
            $(this).text('<?php echo esc_js(__('Show Context')); ?>');
        } else {
            contextDiv.show();
            $(this).text('<?php echo esc_js(__('Hide Context')); ?>');
        }
    });
});
</script>