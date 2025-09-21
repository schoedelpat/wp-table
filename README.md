# wp-table
Staff Table Plugin for WordPress with Updatable Times

## Overview
This WordPress plugin provides a comprehensive staff management system with robust security measures and error handling. It allows administrators to manage staff members, their positions, and work times through a secure admin interface.

## Security Features

### Nonce Validation
- All admin and AJAX actions require valid nonce verification
- Separate nonces for admin actions (`wp_table_admin_nonce`) and public AJAX (`wp_table_ajax_nonce`)
- Automatic nonce generation and validation in all forms and AJAX requests

### Capability Checks
- Admin operations require `manage_options` capability
- Time updates require `edit_posts` capability
- User permissions are verified before any database operations

### Input Sanitization
- All user input is sanitized using WordPress functions (`sanitize_text_field`, etc.)
- Time format validation using regex patterns
- Staff ID validation to ensure positive integers
- Position and name length limits enforced

### Output Escaping
- All output is escaped using `esc_html()`, `esc_attr()`, `esc_url()`, and `esc_js()`
- Admin templates properly escape all dynamic content
- AJAX responses sanitize messages before sending

### Database Security
- All database operations use prepared statements with `$wpdb->prepare()`
- Proper data type specifications (`%s`, `%d`) for prepared statements
- Safe insert, update, and delete operations with parameter binding

### Error Handling
- Comprehensive error logging system with different severity levels
- Centralized error handler (`WP_Table_Error_Handler`) for consistent logging
- Try/catch blocks around all critical operations
- No sensitive data exposed in error responses
- Admin notices for user feedback without revealing system details

## Features

### Admin Interface
- **Staff Management**: Add, edit, delete staff members
- **Bulk Operations**: Delete multiple staff members at once
- **Error Logs**: View detailed error logs with context
- **Time Management**: Update staff working hours
- **Form Validation**: Client-side and server-side validation

### AJAX Endpoints
- **Get Staff**: Retrieve staff data for frontend display
- **Add Staff**: Add new staff members via AJAX
- **Update Staff**: Edit existing staff information
- **Delete Staff**: Remove staff members
- **Update Times**: Modify staff working hours

### REST API
- **GET /wp-table/v1/time/{id}**: Get staff time information
- **POST /wp-table/v1/time/{id}**: Update staff working times
- **GET /wp-table/v1/times**: Get all staff times

### Frontend Display
- **Shortcode**: `[wp_staff_table]` to display staff table
- **Responsive Design**: Mobile-friendly table layout
- **AJAX Loading**: Dynamic content loading without page refresh

## Installation

1. Upload the plugin files to `/wp-content/plugins/wp-table/`
2. Activate the plugin through the WordPress admin interface
3. Navigate to "Staff Table" in the admin menu to start managing staff

## Usage

### Admin Usage
1. Go to **Staff Table** in WordPress admin
2. Click **Add New Staff** to add staff members
3. Fill in required information: Name, Position, Start Time, End Time
4. Manage existing staff from the main Staff Table page
5. View error logs from the **Error Logs** submenu

### Frontend Usage
Add the shortcode `[wp_staff_table]` to any post, page, or widget to display the staff table.

## Security Implementation Details

### File Structure
```
wp-table/
├── wp-table.php                           # Main plugin file
├── admin/
│   ├── class-admin.php                    # Admin interface with security
│   └── templates/                         # Escaped admin templates
├── includes/
│   ├── class-error-handler.php            # Centralized error handling
│   ├── class-ajax-handler.php             # Secure AJAX operations
│   └── endpoints/
│       └── class-time-endpoint.php        # REST API endpoints
└── assets/
    ├── js/                                # JavaScript with CSRF protection
    └── css/                               # Styling
```

### Security Checklist
- [x] Nonce validation for all admin and AJAX actions
- [x] Capability checks throughout the application
- [x] Input sanitization for all user inputs
- [x] Output escaping for all dynamic content
- [x] Prepared statements for all database operations
- [x] Try/catch blocks for defensive programming
- [x] Centralized error logging without data exposure
- [x] No sensitive information in AJAX responses
- [x] CSRF protection in forms and AJAX requests
- [x] SQL injection prevention via prepared statements
- [x] XSS prevention via output escaping

## API Documentation

### AJAX Actions
All AJAX actions require proper nonce validation and capability checks.

#### wp_table_get_staff
- **Method**: POST
- **Nonce**: wp_table_ajax_nonce
- **Capability**: None (public)
- **Returns**: Sanitized staff data

#### wp_table_add_staff
- **Method**: POST
- **Nonce**: wp_table_admin_nonce
- **Capability**: manage_options
- **Parameters**: name, position, start_time, end_time

#### wp_table_update_staff
- **Method**: POST
- **Nonce**: wp_table_admin_nonce
- **Capability**: manage_options
- **Parameters**: staff_id, name, position, start_time, end_time

#### wp_table_delete_staff
- **Method**: POST
- **Nonce**: wp_table_admin_nonce
- **Capability**: manage_options
- **Parameters**: staff_id

#### wp_table_update_time
- **Method**: POST
- **Nonce**: wp_table_ajax_nonce
- **Capability**: edit_posts
- **Parameters**: staff_id, start_time, end_time

## Changelog

### 1.0.0
- Initial release with comprehensive security implementation
- Nonce validation and capability checks
- Input sanitization and output escaping
- Prepared statements for database operations
- Centralized error handling and logging
- AJAX endpoints with security measures
- REST API with permission callbacks
- Admin interface with form validation
- Frontend shortcode with responsive design

## License
GPL-2.0+
