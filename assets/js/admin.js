/* WP Table - Admin JavaScript */

jQuery(document).ready(function($) {
    
    // Confirm delete actions
    $('.wp-table-delete-staff').on('click', function(e) {
        if (!confirm(wp_table_admin.delete_confirm)) {
            e.preventDefault();
            return false;
        }
    });
    
    // Form validation
    $('#wp-table-add-staff-form').on('submit', function(e) {
        var name = $('#name').val().trim();
        if (name === '') {
            alert(wp_table_admin.name_required);
            $('#name').focus();
            e.preventDefault();
            return false;
        }
        
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime && endTime && startTime >= endTime) {
            alert(wp_table_admin.time_validation);
            $('#end_time').focus();
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-hide notices after 5 seconds
    $('.notice.is-dismissible').delay(5000).fadeOut();
    
    // Time picker improvements
    $('#start_time, #end_time').on('change', function() {
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();
        
        if (startTime && endTime && startTime >= endTime) {
            $(this).addClass('error');
            $('#time-validation-message').remove();
            $(this).after('<span id="time-validation-message" style="color: red; font-size: 12px; display: block; margin-top: 4px;">End time must be after start time</span>');
        } else {
            $('#start_time, #end_time').removeClass('error');
            $('#time-validation-message').remove();
        }
    });
    
    // Settings page preview
    $('#table_style').on('change', function() {
        var style = $(this).val();
        updateTablePreview(style);
    });
    
    function updateTablePreview(style) {
        // This could be enhanced to show a live preview of table styles
        console.log('Table style changed to:', style);
    }
    
});

// Object to hold localized strings (will be populated by wp_localize_script)
var wp_table_admin = wp_table_admin || {
    delete_confirm: 'Are you sure you want to delete this staff member?',
    name_required: 'Name is required.',
    time_validation: 'End time must be after start time.'
};