# WP Table - Staff Table Plugin for WordPress

A comprehensive WordPress plugin for displaying and managing staff information with image support, time tracking capabilities, and flexible display options. Perfect for businesses that need to showcase their team with profile photos, contact information, and working hours.

## Features

- **🖼️ Image Upload & Management**: Secure file upload with automatic square cropping and resizing (100-500px)
- **👥 Staff Management**: Add, edit, and delete staff members through the WordPress admin
- **📧 Contact Information**: Email and phone fields with click-to-contact functionality
- **⏰ Time Management**: Track and display staff working hours
- **📱 Responsive Design**: Tables work perfectly on desktop, tablet, and mobile devices
- **🎯 Flexible Display**: Show/hide images, email, phone, and working hours as needed
- **⚙️ Settings Page**: Configure default behaviors and image sizes
- **🎨 Multiple Display Options**: Customizable shortcode parameters for different layouts
- **📊 Status Management**: Active/inactive staff with visual indicators
- **🔧 Admin Interface**: Comprehensive CRUD operations with bulk actions
- **🛡️ Security**: Robust input validation and secure file handling
- **🔄 Clean Uninstall**: Removes all data when plugin is deleted

## Installation

1. Upload the `wp-table` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Staff Table' in the admin menu to start adding staff members
4. Configure default settings in 'Staff Table > Settings'

## Usage

### Adding Staff Members

1. Navigate to **Staff Table > Add Staff** in your WordPress admin
2. Fill out the staff member form:
   - **Profile Image**: Upload JPEG, PNG, or GIF (max 5MB, auto-cropped to square)
   - **Image Size**: Adjust between 100-500px using the slider
   - **Name** (required): Staff member's full name
   - **Position** (required): Job title or role
   - **Email**: Contact email address
   - **Phone**: Contact phone number
   - **Start/End Time** (required): Working hours
   - **Status**: Active or Inactive
3. Click "Add Staff Member"

### Displaying Staff Table

Use the shortcode `[wp_staff_table]` to display the staff table on any page or post.

#### Shortcode Parameters

- `show_images="true|false"` - Show staff images (default: based on settings)
- `show_times="true|false"` - Show working hours (default: based on settings)
- `show_email="true|false"` - Show email addresses (default: based on settings)
- `show_phone="true|false"` - Show phone numbers (default: based on settings)
- `status="active|inactive|all"` - Filter by status (default: active)
- `image_size="100-500"` - Override image size in pixels (default: based on settings)

#### Examples

```
[wp_staff_table]
[wp_staff_table show_email="true" show_phone="true" image_size="200"]
[wp_staff_table show_images="false" status="all"]
[wp_staff_table show_times="false" show_email="true" image_size="150"]
```

### Settings

Customize your plugin through **Staff Table > Settings**:

- **Default Image Size**: Set the default size for new staff images (100-500px)
- **Show Images by Default**: Configure whether images appear by default
- **Show Email by Default**: Configure email display default
- **Show Phone by Default**: Configure phone display default  
- **Show Working Hours by Default**: Configure time display default

## Database Structure

The plugin creates a table `wp_wp_table_staff` with the following structure:

- `id` - Unique staff member ID
- `name` - Staff member name
- `position` - Job position/title
- `email` - Email address
- `phone` - Phone number
- `start_time` - Working day start time
- `end_time` - Working day end time
- `status` - Active or inactive status
- `image_url` - URL to uploaded profile image
- `image_size` - Image display size in pixels (100-500)
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

## Security Features

### Nonce Validation
- All admin and AJAX actions require valid nonce verification
- Separate nonces for admin actions and public AJAX requests
- Automatic nonce generation and validation in all forms

### Capability Checks
- Admin operations require `manage_options` capability
- User permissions are verified before any database operations
- File upload restricted to authorized users only

### Input Sanitization & Validation
- All user input is sanitized using WordPress functions
- Image file validation (type, size, dimensions)
- Time format validation using regex patterns
- Staff ID validation to ensure positive integers

### Output Escaping
- All output is escaped using `esc_html()`, `esc_attr()`, `esc_url()`, and `esc_js()`
- Admin templates properly escape all dynamic content
- Image URLs are properly validated and escaped

### Database Security
- All database operations use prepared statements with `$wpdb->prepare()`
- Proper data type specifications for prepared statements
- Safe insert, update, and delete operations with parameter binding

### File Upload Security
- File type validation (JPEG, PNG, GIF only)
- File size limits (5MB maximum)
- Secure file naming with timestamp prefixes
- Image processing to prevent malicious files
- Automatic cleanup of temporary files

## File Structure

```
wp-table/
├── wp-table.php                     # Main plugin file
├── admin/
│   ├── class-admin.php              # Admin interface with security
│   └── templates/                   # Admin page templates
│       ├── main-page.php            # Staff list page
│       ├── add-staff.php            # Add/edit staff form
│       └── settings-page.php        # Plugin settings
├── includes/
│   ├── class-error-handler.php      # Centralized error handling
│   ├── class-ajax-handler.php       # Secure AJAX operations
│   └── endpoints/
│       └── class-time-endpoint.php  # REST API endpoints
└── assets/
    ├── js/                          # JavaScript with CSRF protection
    │   ├── admin.js                 # Admin interface scripts
    │   └── frontend.js              # Frontend table scripts
    └── css/
        └── wp-table.css             # Plugin styling
```

## Styling

The plugin includes responsive CSS that works with most WordPress themes. You can customize the appearance by overriding the CSS classes in your theme:

- `.wp-staff-table-container` - Main container wrapper
- `.wp-staff-table` - The table element
- `.wp-table-image` - Image column styling
- `.wp-table-name`, `.wp-table-position`, etc. - Column-specific classes
- `.wp-table-no-image` - Placeholder for missing images

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
- **Parameters**: name, position, email, phone, start_time, end_time, status, image_size
- **Files**: staff_image (optional)

#### wp_table_update_staff
- **Method**: POST
- **Nonce**: wp_table_admin_nonce
- **Capability**: manage_options
- **Parameters**: staff_id, name, position, email, phone, start_time, end_time, status, image_size
- **Files**: staff_image (optional)

#### wp_table_delete_staff
- **Method**: POST
- **Nonce**: wp_table_admin_nonce
- **Capability**: manage_options
- **Parameters**: staff_id

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- GD or ImageMagick extension for image processing

## Changelog

### Version 1.0.0
- Initial release with comprehensive features
- Image upload and processing system
- Advanced admin interface with CRUD operations
- Flexible shortcode with multiple parameters
- Settings page for default configurations
- Enhanced security with file upload validation
- Responsive design for all devices
- Database migration support
- Comprehensive error handling and logging

## Support

For support, feature requests, or bug reports, please visit:
[https://github.com/schoedelpat/wp-table](https://github.com/schoedelpat/wp-table)

## License

This plugin is licensed under the GPL v3 or later.