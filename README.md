# WP Table - Staff Table Plugin for WordPress

A WordPress plugin for displaying and managing staff information with time tracking capabilities. Perfect for businesses that need to showcase their team with working hours and contact information.

## Features

- **Staff Management**: Add, edit, and delete staff members through the WordPress admin
- **Flexible Display**: Show/hide email, phone, and working hours as needed
- **Multiple Table Styles**: Choose from default, striped, bordered, and minimal table designs
- **Responsive Design**: Tables work perfectly on desktop, tablet, and mobile devices
- **Time Management**: Track and display staff working hours
- **Shortcode Support**: Easy integration into any page or post
- **Clean Uninstall**: Removes all data when plugin is deleted

## Installation

1. Upload the `wp-table` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'WP Table' in the admin menu to start adding staff members

## Usage

### Adding Staff Members

1. Navigate to **WP Table > Staff Members** in your WordPress admin
2. Fill out the staff member form with:
   - Name (required)
   - Position
   - Email
   - Phone
   - Start Time
   - End Time
   - Status (Active/Inactive)
3. Click "Add Staff Member"

### Displaying Staff Table

Use the shortcode `[wp_staff_table]` in any page, post, or widget to display your staff table.

#### Shortcode Parameters

- `show_times="true|false"` - Display working hours (default: true)
- `show_email="true|false"` - Display email addresses (default: false)
- `show_phone="true|false"` - Display phone numbers (default: false)
- `status="active|inactive|all"` - Filter by staff status (default: active)

#### Examples

```
[wp_staff_table]
[wp_staff_table show_email="true" show_phone="true"]
[wp_staff_table show_times="false" status="all"]
[wp_staff_table show_email="true" show_phone="true" show_times="true" status="active"]
```

### Settings

Customize your plugin through **WP Table > Settings**:

- **Table Style**: Choose from default, striped, bordered, or minimal styles
- **Show Times by Default**: Set the default behavior for displaying working hours
- **Date Format**: Customize how dates are displayed
- **Time Format**: Customize how times are displayed

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
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

## Styling

The plugin includes responsive CSS that works with most WordPress themes. You can customize the appearance by overriding the CSS classes in your theme:

- `.wp-table-container` - Main container wrapper
- `.wp-staff-table` - The table element
- `.wp-table-style-{style}` - Style-specific classes
- `.wp-table-name`, `.wp-table-position`, etc. - Column-specific classes

## Development

### File Structure

```
wp-table/
├── wp-table.php              # Main plugin file
├── uninstall.php             # Uninstall cleanup script
├── admin/
│   ├── admin-page.php        # Staff management page
│   └── settings-page.php     # Plugin settings page
├── assets/
│   ├── css/
│   │   ├── admin.css         # Admin panel styles
│   │   └── frontend.css      # Frontend table styles
│   └── js/
│       ├── admin.js          # Admin panel JavaScript
│       └── frontend.js       # Frontend JavaScript
├── templates/
│   └── staff-table.php       # Frontend table template
└── languages/                # Translation files (future)
```

### Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

This plugin is licensed under the GPL v3 or later.

## Support

For support, feature requests, or bug reports, please visit:
[https://github.com/schoedelpat/wp-table](https://github.com/schoedelpat/wp-table)

## Changelog

### Version 1.0.0
- Initial release
- Staff member management
- Frontend table display
- Shortcode support
- Multiple table styles
- Responsive design
- Admin interface
