<?php
/**
 * Admin Class
 * 
 * Handles admin interface with robust security and CRUD operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Table_Admin {
    
    /**
     * Error handler instance
     */
    private $error_handler;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->error_handler = new WP_Table_Error_Handler();
        $this->init_hooks();
    }
    
    /**
     * Initialize admin hooks
     */
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_action('admin_post_wp_table_add_staff', array($this, 'handle_add_staff'));
        add_action('admin_post_wp_table_edit_staff', array($this, 'handle_edit_staff'));
        add_action('admin_post_wp_table_delete_staff', array($this, 'handle_delete_staff'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'WP Table',
            'Staff Table',
            'manage_options',
            'wp-table',
            array($this, 'display_admin_page'),
            'dashicons-groups',
            30
        );
        
        add_submenu_page(
            'wp-table',
            'Add Staff',
            'Add Staff',
            'manage_options',
            'wp-table-add',
            array($this, 'display_add_staff_page')
        );
        
        add_submenu_page(
            'wp-table',
            'Error Logs',
            'Error Logs',
            'manage_options',
            'wp-table-logs',
            array($this, 'display_error_logs_page')
        );
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        // Handle bulk actions
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete_staff') {
            $this->handle_bulk_delete();
        }
    }
    
    /**
     * Display main admin page
     */
    public function display_admin_page() {
        try {
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
            }
            
            // Get staff data
            $staff_list = $this->get_staff_list();
            
            // Include admin template
            include_once WP_TABLE_PLUGIN_PATH . 'admin/templates/main-page.php';
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'display_admin_page');
            echo '<div class="error"><p>' . esc_html__('Error loading admin page.') . '</p></div>';
        }
    }
    
    /**
     * Display add staff page
     */
    public function display_add_staff_page() {
        try {
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
            }
            
            // Include add staff template
            include_once WP_TABLE_PLUGIN_PATH . 'admin/templates/add-staff.php';
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'display_add_staff_page');
            echo '<div class="error"><p>' . esc_html__('Error loading add staff page.') . '</p></div>';
        }
    }
    
    /**
     * Display error logs page
     */
    public function display_error_logs_page() {
        try {
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
            }
            
            // Get error logs
            $error_logs = $this->get_error_logs();
            
            // Include error logs template
            include_once WP_TABLE_PLUGIN_PATH . 'admin/templates/error-logs.php';
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'display_error_logs_page');
            echo '<div class="error"><p>' . esc_html__('Error loading error logs page.') . '</p></div>';
        }
    }
    
    /**
     * Handle add staff form submission
     */
    public function handle_add_staff() {
        try {
            // Verify nonce
            if (!$this->verify_admin_nonce('wp_table_add_staff')) {
                return;
            }
            
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                $this->redirect_with_error('Insufficient permissions.');
                return;
            }
            
            // Sanitize and validate input
            $staff_data = $this->sanitize_staff_input($_POST);
            if (!$staff_data) {
                $this->redirect_with_error('Invalid staff data provided.');
                return;
            }
            
            // Add staff member
            $staff_id = $this->add_staff_member($staff_data);
            
            if (!$staff_id) {
                $this->redirect_with_error('Failed to add staff member.');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member added via admin: %s', $staff_data['name']),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Redirect with success
            $this->redirect_with_success('Staff member added successfully.');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_add_staff');
            $this->redirect_with_error('An error occurred while adding staff member.');
        }
    }
    
    /**
     * Handle edit staff form submission
     */
    public function handle_edit_staff() {
        try {
            // Verify nonce
            if (!$this->verify_admin_nonce('wp_table_edit_staff')) {
                return;
            }
            
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                $this->redirect_with_error('Insufficient permissions.');
                return;
            }
            
            // Sanitize and validate input
            $staff_id = $this->sanitize_staff_id($_POST['staff_id'] ?? '');
            if (!$staff_id) {
                $this->redirect_with_error('Invalid staff ID provided.');
                return;
            }
            
            $staff_data = $this->sanitize_staff_input($_POST);
            if (!$staff_data) {
                $this->redirect_with_error('Invalid staff data provided.');
                return;
            }
            
            // Update staff member
            $success = $this->update_staff_member($staff_id, $staff_data);
            
            if (!$success) {
                $this->redirect_with_error('Failed to update staff member.');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member updated via admin: %s (ID: %d)', $staff_data['name'], $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Redirect with success
            $this->redirect_with_success('Staff member updated successfully.');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_edit_staff');
            $this->redirect_with_error('An error occurred while updating staff member.');
        }
    }
    
    /**
     * Handle delete staff action
     */
    public function handle_delete_staff() {
        try {
            // Verify nonce
            if (!$this->verify_admin_nonce('wp_table_delete_staff')) {
                return;
            }
            
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                $this->redirect_with_error('Insufficient permissions.');
                return;
            }
            
            // Sanitize and validate input
            $staff_id = $this->sanitize_staff_id($_POST['staff_id'] ?? $_GET['staff_id'] ?? '');
            if (!$staff_id) {
                $this->redirect_with_error('Invalid staff ID provided.');
                return;
            }
            
            // Get staff name for logging
            $staff_name = $this->get_staff_name($staff_id);
            
            // Delete staff member
            $success = $this->delete_staff_member($staff_id);
            
            if (!$success) {
                $this->redirect_with_error('Failed to delete staff member.');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member deleted via admin: %s (ID: %d)', $staff_name, $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Redirect with success
            $this->redirect_with_success('Staff member deleted successfully.');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_delete_staff');
            $this->redirect_with_error('An error occurred while deleting staff member.');
        }
    }
    
    /**
     * Handle bulk delete action
     */
    private function handle_bulk_delete() {
        try {
            // Verify nonce
            if (!$this->verify_admin_nonce('wp_table_bulk_action')) {
                return;
            }
            
            // Check capabilities
            if (!$this->error_handler->check_capability('manage_options')) {
                $this->redirect_with_error('Insufficient permissions.');
                return;
            }
            
            // Sanitize and validate staff IDs
            $staff_ids = array_map('intval', $_POST['staff_ids'] ?? array());
            $staff_ids = array_filter($staff_ids, function($id) {
                return $id > 0;
            });
            
            if (empty($staff_ids)) {
                $this->redirect_with_error('No valid staff IDs provided for deletion.');
                return;
            }
            
            // Delete staff members
            $deleted_count = $this->bulk_delete_staff_members($staff_ids);
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Bulk delete completed: %d staff members deleted', $deleted_count),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('deleted_ids' => $staff_ids)
            );
            
            // Redirect with success
            $this->redirect_with_success(sprintf('%d staff members deleted successfully.', $deleted_count));
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_bulk_delete');
            $this->redirect_with_error('An error occurred during bulk deletion.');
        }
    }
    
    /**
     * Verify admin nonce
     * 
     * @param string $action Nonce action
     * @return bool True if valid, false otherwise
     */
    private function verify_admin_nonce($action) {
        $nonce = sanitize_text_field($_POST['_wpnonce'] ?? $_GET['_wpnonce'] ?? '');
        
        if (!$this->error_handler->validate_nonce($nonce, $action)) {
            $this->redirect_with_error('Security verification failed.');
            return false;
        }
        
        return true;
    }
    
    /**
     * Sanitize staff input data
     * 
     * @param array $input Raw input data
     * @return array|false Sanitized data or false on validation failure
     */
    private function sanitize_staff_input($input) {
        $name = sanitize_text_field($input['name'] ?? '');
        $position = sanitize_text_field($input['position'] ?? '');
        $start_time = sanitize_text_field($input['start_time'] ?? '');
        $end_time = sanitize_text_field($input['end_time'] ?? '');
        
        // Validate required fields
        if (empty($name) || empty($position) || empty($start_time) || empty($end_time)) {
            return false;
        }
        
        // Validate time formats
        if (!$this->validate_time_format($start_time) || !$this->validate_time_format($end_time)) {
            return false;
        }
        
        // Additional validation: end time should be after start time
        if (strtotime($end_time) <= strtotime($start_time)) {
            return false;
        }
        
        return array(
            'name' => $name,
            'position' => $position,
            'start_time' => $start_time,
            'end_time' => $end_time
        );
    }
    
    /**
     * Sanitize staff ID
     * 
     * @param mixed $staff_id Raw staff ID
     * @return int|false Sanitized staff ID or false on validation failure
     */
    private function sanitize_staff_id($staff_id) {
        $id = intval($staff_id);
        return ($id > 0) ? $id : false;
    }
    
    /**
     * Validate time format (HH:MM)
     * 
     * @param string $time Time string
     * @return bool True if valid, false otherwise
     */
    private function validate_time_format($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }
    
    /**
     * Get staff list from database
     * 
     * @return array Staff list
     */
    private function get_staff_list() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name, position, start_time, end_time, created_at FROM {$table_name} ORDER BY name ASC"
        ), ARRAY_A);
        
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        // Sanitize output
        return array_map(function($row) {
            return array(
                'id' => intval($row['id']),
                'name' => esc_html($row['name']),
                'position' => esc_html($row['position']),
                'start_time' => esc_html($row['start_time']),
                'end_time' => esc_html($row['end_time']),
                'created_at' => esc_html($row['created_at'])
            );
        }, $results ?: array());
    }
    
    /**
     * Add staff member to database
     * 
     * @param array $staff_data Staff data
     * @return int|false Staff ID on success, false on failure
     */
    private function add_staff_member($staff_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $result = $wpdb->insert(
            $table_name,
            $staff_data,
            array('%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update staff member in database
     * 
     * @param int $staff_id Staff ID
     * @param array $staff_data Staff data
     * @return bool True on success, false on failure
     */
    private function update_staff_member($staff_id, $staff_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $result = $wpdb->update(
            $table_name,
            $staff_data,
            array('id' => $staff_id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $result !== false;
    }
    
    /**
     * Delete staff member from database
     * 
     * @param int $staff_id Staff ID
     * @return bool True on success, false on failure
     */
    private function delete_staff_member($staff_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $staff_id),
            array('%d')
        );
        
        if ($result === false) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $result !== false;
    }
    
    /**
     * Bulk delete staff members
     * 
     * @param array $staff_ids Array of staff IDs
     * @return int Number of deleted records
     */
    private function bulk_delete_staff_members($staff_ids) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        $placeholders = implode(',', array_fill(0, count($staff_ids), '%d'));
        
        $result = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE id IN ({$placeholders})",
            ...$staff_ids
        ));
        
        if ($result === false) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return intval($result);
    }
    
    /**
     * Get staff name by ID
     * 
     * @param int $staff_id Staff ID
     * @return string Staff name
     */
    private function get_staff_name($staff_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $name = $wpdb->get_var($wpdb->prepare(
            "SELECT name FROM {$table_name} WHERE id = %d",
            $staff_id
        ));
        
        return $name ?: 'Unknown';
    }
    
    /**
     * Get error logs
     * 
     * @param int $limit Number of logs to retrieve
     * @return array Error logs
     */
    private function get_error_logs($limit = 100) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_table_error_logs';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
            $limit
        ), ARRAY_A);
        
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $results ?: array();
    }
    
    /**
     * Redirect with error message
     * 
     * @param string $message Error message
     */
    private function redirect_with_error($message) {
        $this->error_handler->add_admin_notice($message, 'error');
        wp_safe_redirect(admin_url('admin.php?page=wp-table'));
        exit;
    }
    
    /**
     * Redirect with success message
     * 
     * @param string $message Success message
     */
    private function redirect_with_success($message) {
        $this->error_handler->add_admin_notice($message, 'success');
        wp_safe_redirect(admin_url('admin.php?page=wp-table'));
        exit;
    }
}