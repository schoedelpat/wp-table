/* WP Table - Frontend JavaScript */

jQuery(document).ready(function($) {
    
    // Make tables responsive
    $('.wp-staff-table').each(function() {
        if (!$(this).closest('.wp-table-container').length) {
            $(this).wrap('<div class="wp-table-container"></div>');
        }
    });
    
    // Add click-to-call functionality for phone numbers
    $('.wp-table-phone a[href^="tel:"]').on('click', function(e) {
        // Check if device supports tel: links
        if (!('ontouchstart' in document.documentElement)) {
            // On desktop, show the phone number instead of trying to call
            e.preventDefault();
            var phone = $(this).text();
            alert('Phone: ' + phone);
        }
    });
    
    // Enhanced table interactions
    $('.wp-staff-table tbody tr').hover(
        function() {
            $(this).addClass('wp-table-row-hover');
        },
        function() {
            $(this).removeClass('wp-table-row-hover');
        }
    );
    
    // Add sorting functionality (basic)
    $('.wp-staff-table th').on('click', function() {
        var table = $(this).closest('table');
        var index = $(this).index();
        var rows = table.find('tbody tr').get();
        var isAsc = !$(this).hasClass('wp-table-sort-asc');
        
        // Remove existing sort classes
        table.find('th').removeClass('wp-table-sort-asc wp-table-sort-desc');
        
        // Add sort class to current column
        $(this).addClass(isAsc ? 'wp-table-sort-asc' : 'wp-table-sort-desc');
        
        rows.sort(function(a, b) {
            var aText = $(a).find('td').eq(index).text().trim();
            var bText = $(b).find('td').eq(index).text().trim();
            
            // Try to parse as numbers for time columns
            if ($(table.find('th').eq(index)).hasClass('wp-table-hours')) {
                var aTime = parseTime(aText);
                var bTime = parseTime(bText);
                if (aTime !== null && bTime !== null) {
                    return isAsc ? aTime - bTime : bTime - aTime;
                }
            }
            
            // String comparison
            if (isAsc) {
                return aText.localeCompare(bText);
            } else {
                return bText.localeCompare(aText);
            }
        });
        
        // Reorder rows
        $.each(rows, function(index, row) {
            table.find('tbody').append(row);
        });
    });
    
    function parseTime(timeString) {
        // Parse time strings like "09:00 - 17:00" for sorting
        var match = timeString.match(/(\d{1,2}):(\d{2})/);
        if (match) {
            return parseInt(match[1]) * 60 + parseInt(match[2]);
        }
        return null;
    }
    
    // Accessibility improvements
    $('.wp-staff-table').attr('role', 'table');
    $('.wp-staff-table th').attr('scope', 'col');
    $('.wp-staff-table tbody th').attr('scope', 'row');
    
    // Print styles support
    if (window.matchMedia) {
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (mql.matches) {
                // Print mode - could add special handling
                console.log('Entering print mode');
            } else {
                // Screen mode
                console.log('Exiting print mode');
            }
        });
    }
    
});