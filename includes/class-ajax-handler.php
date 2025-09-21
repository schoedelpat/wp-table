<?php
/**
 * AJAX Handler Class
 * 
 * Handles all AJAX requests with robust security and error handling
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Table_Ajax_Handler {
    
    /**
     * Error handler instance
     */
    private $error_handler;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->error_handler = new WP_Table_Error_Handler();
        $this->init_ajax_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    private function init_ajax_hooks() {
        // Public AJAX actions
        add_action('wp_ajax_wp_table_get_staff', array($this, 'handle_get_staff'));
        add_action('wp_ajax_nopriv_wp_table_get_staff', array($this, 'handle_get_staff'));
        
        // Admin AJAX actions
        add_action('wp_ajax_wp_table_add_staff', array($this, 'handle_add_staff'));
        add_action('wp_ajax_wp_table_update_staff', array($this, 'handle_update_staff'));
        add_action('wp_ajax_wp_table_delete_staff', array($this, 'handle_delete_staff'));
        add_action('wp_ajax_wp_table_update_time', array($this, 'handle_update_time'));
    }
    
    /**
     * Handle get staff AJAX request
     */
    public function handle_get_staff() {
        try {
            // Verify nonce
            if (!$this->verify_ajax_nonce('wp_table_ajax_nonce')) {
                return;
            }
            
            // Get staff data
            $staff_data = $this->get_staff_data();
            
            // Send success response
            $this->send_ajax_response(true, 'Staff data retrieved successfully', array(
                'staff' => $staff_data
            ));
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_get_staff');
            $this->send_ajax_error('Failed to retrieve staff data');
        }
    }
    
    /**
     * Handle add staff AJAX request
     */
    public function handle_add_staff() {
        try {
            // Verify nonce and capabilities
            if (!$this->verify_admin_ajax_request()) {
                return;
            }
            
            // Sanitize and validate input
            $staff_data = $this->sanitize_staff_input($_POST);
            if (!$staff_data) {
                $this->send_ajax_error('Invalid staff data provided');
                return;
            }
            
            // Add staff member
            $staff_id = $this->add_staff_member($staff_data);
            
            if (!$staff_id) {
                $this->send_ajax_error('Failed to add staff member');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member added: %s', $staff_data['name']),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Send success response
            $this->send_ajax_response(true, 'Staff member added successfully', array(
                'staff_id' => $staff_id
            ));
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_add_staff');
            $this->send_ajax_error('Failed to add staff member');
        }
    }
    
    /**
     * Handle update staff AJAX request
     */
    public function handle_update_staff() {
        try {
            // Verify nonce and capabilities
            if (!$this->verify_admin_ajax_request()) {
                return;
            }
            
            // Sanitize and validate input
            $staff_id = $this->sanitize_staff_id($_POST['staff_id'] ?? '');
            if (!$staff_id) {
                $this->send_ajax_error('Invalid staff ID provided');
                return;
            }
            
            $staff_data = $this->sanitize_staff_input($_POST);
            if (!$staff_data) {
                $this->send_ajax_error('Invalid staff data provided');
                return;
            }
            
            // Update staff member
            $success = $this->update_staff_member($staff_id, $staff_data);
            
            if (!$success) {
                $this->send_ajax_error('Failed to update staff member');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member updated: %s (ID: %d)', $staff_data['name'], $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Send success response
            $this->send_ajax_response(true, 'Staff member updated successfully');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_update_staff');
            $this->send_ajax_error('Failed to update staff member');
        }
    }
    
    /**
     * Handle delete staff AJAX request
     */
    public function handle_delete_staff() {
        try {
            // Verify nonce and capabilities
            if (!$this->verify_admin_ajax_request()) {
                return;
            }
            
            // Sanitize and validate input
            $staff_id = $this->sanitize_staff_id($_POST['staff_id'] ?? '');
            if (!$staff_id) {
                $this->send_ajax_error('Invalid staff ID provided');
                return;
            }
            
            // Get staff name for logging
            $staff_name = $this->get_staff_name($staff_id);
            
            // Delete staff member
            $success = $this->delete_staff_member($staff_id);
            
            if (!$success) {
                $this->send_ajax_error('Failed to delete staff member');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff member deleted: %s (ID: %d)', $staff_name, $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            // Send success response
            $this->send_ajax_response(true, 'Staff member deleted successfully');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_delete_staff');
            $this->send_ajax_error('Failed to delete staff member');
        }
    }
    
    /**
     * Handle update time AJAX request
     */
    public function handle_update_time() {
        try {
            // Verify nonce
            if (!$this->verify_ajax_nonce('wp_table_ajax_nonce')) {
                return;
            }
            
            // Check if user can edit posts (basic capability for time updates)
            if (!$this->error_handler->check_capability('edit_posts')) {
                $this->send_ajax_error('Insufficient permissions');
                return;
            }
            
            // Sanitize and validate input
            $staff_id = $this->sanitize_staff_id($_POST['staff_id'] ?? '');
            $start_time = sanitize_text_field($_POST['start_time'] ?? '');
            $end_time = sanitize_text_field($_POST['end_time'] ?? '');
            
            if (!$staff_id || !$this->validate_time_format($start_time) || !$this->validate_time_format($end_time)) {
                $this->send_ajax_error('Invalid time data provided');
                return;
            }
            
            // Update staff times
            $success = $this->update_staff_times($staff_id, $start_time, $end_time);
            
            if (!$success) {
                $this->send_ajax_error('Failed to update staff times');
                return;
            }
            
            // Log success
            $this->error_handler->log_error(
                sprintf('Staff times updated for ID: %d', $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id, 'start_time' => $start_time, 'end_time' => $end_time)
            );
            
            // Send success response
            $this->send_ajax_response(true, 'Staff times updated successfully');
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'handle_update_time');
            $this->send_ajax_error('Failed to update staff times');
        }
    }
    
    /**
     * Verify AJAX nonce
     * 
     * @param string $nonce_action Nonce action
     * @return bool True if valid, false otherwise
     */
    private function verify_ajax_nonce($nonce_action) {
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        
        if (!$this->error_handler->validate_nonce($nonce, $nonce_action)) {
            $this->send_ajax_error('Security verification failed');
            return false;
        }
        
        return true;
    }
    
    /**
     * Verify admin AJAX request (nonce + capability)
     * 
     * @return bool True if valid, false otherwise
     */
    private function verify_admin_ajax_request() {
        // Verify nonce
        if (!$this->verify_ajax_nonce('wp_table_admin_nonce')) {
            return false;
        }
        
        // Check admin capability
        if (!$this->error_handler->check_capability('manage_options')) {
            $this->send_ajax_error('Insufficient permissions');
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
     * Get staff data from database
     * 
     * @return array Staff data
     */
    private function get_staff_data() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name, position, start_time, end_time FROM {$table_name} ORDER BY name ASC"
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
                'end_time' => esc_html($row['end_time'])
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
     * Update staff times in database
     * 
     * @param int $staff_id Staff ID
     * @param string $start_time Start time
     * @param string $end_time End time
     * @return bool True on success, false on failure
     */
    private function update_staff_times($staff_id, $start_time, $end_time) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'start_time' => $start_time,
                'end_time' => $end_time
            ),
            array('id' => $staff_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $result !== false;
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
     * Send AJAX response
     * 
     * @param bool $success Success status
     * @param string $message Response message
     * @param array $data Additional data
     */
    private function send_ajax_response($success, $message, $data = array()) {
        wp_send_json(array(
            'success' => $success,
            'message' => sanitize_text_field($message),
            'data' => $data
        ));
    }
    
    /**
     * Send AJAX error response
     * 
     * @param string $message Error message
     */
    private function send_ajax_error($message) {
        $this->send_ajax_response(false, $message);
    }
}