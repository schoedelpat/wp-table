/**
 * Frontend JavaScript for WP Table Plugin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Frontend handler for staff table
    var WPTableFrontend = {
        
        init: function() {
            this.loadStaffData();
        },
        
        loadStaffData: function() {
            var $container = $('.wp-staff-table-container');
            
            $.ajax({
                url: wp_table_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wp_table_get_staff',
                    nonce: wp_table_ajax.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.staff) {
                        WPTableFrontend.renderStaffTable(response.data.staff);
                    }
                }
            });
        },
        
        renderStaffTable: function(staffData) {
            var $container = $('.wp-staff-table-container');
            var html = '<table class="wp-staff-table"><thead><tr>' +
                      '<th>Name</th><th>Position</th><th>Start Time</th><th>End Time</th>' +
                      '</tr></thead><tbody>';
            
            if (staffData.length > 0) {
                $.each(staffData, function(index, staff) {
                    html += '<tr>' +
                           '<td>' + $('<div>').text(staff.name).html() + '</td>' +
                           '<td>' + $('<div>').text(staff.position).html() + '</td>' +
                           '<td>' + $('<div>').text(staff.start_time).html() + '</td>' +
                           '<td>' + $('<div>').text(staff.end_time).html() + '</td>' +
                           '</tr>';
                });
            } else {
                html += '<tr><td colspan="4">No staff members found</td></tr>';
            }
            
            html += '</tbody></table>';
            $container.html(html);
        }
    };
    
    // Initialize if container exists
    if ($('.wp-staff-table-container').length > 0) {
        WPTableFrontend.init();
    }
});