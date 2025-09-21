/**
 * Admin JavaScript for WP Table Plugin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // AJAX handler for admin operations
    var WPTableAdmin = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Bind AJAX form submissions
            $('.wp-table-ajax-form').on('submit', this.handleAjaxForm);
            
            // Bind time update buttons
            $('.update-time-btn').on('click', this.handleTimeUpdate);
            
            // Bind delete confirmation
            $('.delete-staff-btn').on('click', this.confirmDelete);
        },
        
        handleAjaxForm: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submit = $form.find('input[type="submit"], button[type="submit"]');
            var originalText = $submit.val() || $submit.text();
            
            // Disable submit button
            $submit.prop('disabled', true).val('Processing...');
            
            // Prepare form data
            var formData = $form.serialize();
            
            // Make AJAX request
            $.ajax({
                url: wp_table_admin_ajax.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        WPTableAdmin.showNotice(response.message, 'success');
                        
                        // Reset form if it was an add form
                        if ($form.hasClass('add-staff-form')) {
                            $form[0].reset();
                        }
                        
                        // Reload staff list if present
                        if ($('.staff-list-table').length) {
                            WPTableAdmin.reloadStaffList();
                        }
                    } else {
                        WPTableAdmin.showNotice(response.message || 'An error occurred', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    WPTableAdmin.showNotice('Network error: ' + error, 'error');
                },
                complete: function() {
                    // Re-enable submit button
                    $submit.prop('disabled', false).val(originalText);
                }
            });
        },
        
        showNotice: function(message, type) {
            type = type || 'info';
            
            // Remove existing notices
            $('.wp-table-notice').remove();
            
            // Create new notice
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible wp-table-notice"><p>' + 
                          $('<div>').text(message).html() + '</p></div>');
            
            // Add to page
            $('.wrap h1').first().after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        }
    };
    
    // Initialize
    WPTableAdmin.init();
});