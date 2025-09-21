<?php
/**
 * Time Endpoint Class
 * 
 * Handles time-related operations with security measures
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Table_Time_Endpoint {
    
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
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('wp-table/v1', '/time/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_staff_time'),
            'permission_callback' => array($this, 'check_read_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'validate_callback' => array($this, 'validate_staff_id')
                )
            )
        ));
        
        register_rest_route('wp-table/v1', '/time/(?P<id>\d+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_staff_time'),
            'permission_callback' => array($this, 'check_edit_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'validate_callback' => array($this, 'validate_staff_id')
                ),
                'start_time' => array(
                    'required' => true,
                    'validate_callback' => array($this, 'validate_time_format'),
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'end_time' => array(
                    'required' => true,
                    'validate_callback' => array($this, 'validate_time_format'),
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        register_rest_route('wp-table/v1', '/times', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_all_staff_times'),
            'permission_callback' => array($this, 'check_read_permission')
        ));
    }
    
    /**
     * Get staff time by ID
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_staff_time($request) {
        try {
            $staff_id = intval($request->get_param('id'));
            
            // Get staff time data
            $time_data = $this->get_staff_time_data($staff_id);
            
            if (!$time_data) {
                return new WP_Error(
                    'staff_not_found',
                    'Staff member not found',
                    array('status' => 404)
                );
            }
            
            // Log access
            $this->error_handler->log_error(
                sprintf('Staff time accessed for ID: %d', $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array('staff_id' => $staff_id)
            );
            
            return new WP_REST_Response($time_data, 200);
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'get_staff_time');
            return new WP_Error(
                'internal_error',
                'Internal server error',
                array('status' => 500)
            );
        }
    }
    
    /**
     * Update staff time
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_staff_time($request) {
        try {
            $staff_id = intval($request->get_param('id'));
            $start_time = sanitize_text_field($request->get_param('start_time'));
            $end_time = sanitize_text_field($request->get_param('end_time'));
            
            // Validate time logic
            if (!$this->validate_time_logic($start_time, $end_time)) {
                return new WP_Error(
                    'invalid_time_range',
                    'End time must be after start time',
                    array('status' => 400)
                );
            }
            
            // Check if staff exists
            if (!$this->staff_exists($staff_id)) {
                return new WP_Error(
                    'staff_not_found',
                    'Staff member not found',
                    array('status' => 404)
                );
            }
            
            // Update staff time
            $success = $this->update_staff_time_data($staff_id, $start_time, $end_time);
            
            if (!$success) {
                return new WP_Error(
                    'update_failed',
                    'Failed to update staff time',
                    array('status' => 500)
                );
            }
            
            // Log update
            $this->error_handler->log_error(
                sprintf('Staff time updated for ID: %d', $staff_id),
                WP_Table_Error_Handler::ERROR_LEVEL_INFO,
                array(
                    'staff_id' => $staff_id,
                    'start_time' => $start_time,
                    'end_time' => $end_time
                )
            );
            
            return new WP_REST_Response(array(
                'message' => 'Staff time updated successfully',
                'staff_id' => $staff_id,
                'start_time' => $start_time,
                'end_time' => $end_time
            ), 200);
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'update_staff_time');
            return new WP_Error(
                'internal_error',
                'Internal server error',
                array('status' => 500)
            );
        }
    }
    
    /**
     * Get all staff times
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_all_staff_times($request) {
        try {
            // Get all staff time data
            $times_data = $this->get_all_staff_times_data();
            
            // Log access
            $this->error_handler->log_error(
                'All staff times accessed',
                WP_Table_Error_Handler::ERROR_LEVEL_INFO
            );
            
            return new WP_REST_Response(array(
                'times' => $times_data
            ), 200);
            
        } catch (Exception $e) {
            $this->error_handler->handle_exception($e, 'get_all_staff_times');
            return new WP_Error(
                'internal_error',
                'Internal server error',
                array('status' => 500)
            );
        }
    }
    
    /**
     * Check read permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool True if user can read, false otherwise
     */
    public function check_read_permission($request) {
        // Allow public read access for frontend display
        return true;
    }
    
    /**
     * Check edit permission
     * 
     * @param WP_REST_Request $request Request object
     * @return bool True if user can edit, false otherwise
     */
    public function check_edit_permission($request) {
        if (!$this->error_handler->check_capability('edit_posts')) {
            $this->error_handler->log_error(
                'Unauthorized time update attempt',
                WP_Table_Error_Handler::ERROR_LEVEL_WARNING,
                array('user_id' => get_current_user_id())
            );
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate staff ID
     * 
     * @param mixed $param Parameter value
     * @param WP_REST_Request $request Request object
     * @param string $key Parameter key
     * @return bool True if valid, false otherwise
     */
    public function validate_staff_id($param, $request, $key) {
        $id = intval($param);
        return $id > 0;
    }
    
    /**
     * Validate time format
     * 
     * @param mixed $param Parameter value
     * @param WP_REST_Request $request Request object
     * @param string $key Parameter key
     * @return bool True if valid, false otherwise
     */
    public function validate_time_format($param, $request, $key) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $param);
    }
    
    /**
     * Validate time logic (end time after start time)
     * 
     * @param string $start_time Start time
     * @param string $end_time End time
     * @return bool True if valid, false otherwise
     */
    private function validate_time_logic($start_time, $end_time) {
        $start = strtotime($start_time);
        $end = strtotime($end_time);
        
        return $end > $start;
    }
    
    /**
     * Check if staff exists
     * 
     * @param int $staff_id Staff ID
     * @return bool True if exists, false otherwise
     */
    private function staff_exists($staff_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE id = %d",
            $staff_id
        ));
        
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return intval($count) > 0;
    }
    
    /**
     * Get staff time data by ID
     * 
     * @param int $staff_id Staff ID
     * @return array|null Staff time data or null if not found
     */
    private function get_staff_time_data($staff_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT id, name, position, start_time, end_time FROM {$table_name} WHERE id = %d",
            $staff_id
        ), ARRAY_A);
        
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        if (!$result) {
            return null;
        }
        
        // Sanitize output
        return array(
            'id' => intval($result['id']),
            'name' => esc_html($result['name']),
            'position' => esc_html($result['position']),
            'start_time' => esc_html($result['start_time']),
            'end_time' => esc_html($result['end_time'])
        );
    }
    
    /**
     * Update staff time data
     * 
     * @param int $staff_id Staff ID
     * @param string $start_time Start time
     * @param string $end_time End time
     * @return bool True on success, false on failure
     */
    private function update_staff_time_data($staff_id, $start_time, $end_time) {
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
        
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }
        
        return $result !== false;
    }
    
    /**
     * Get all staff times data
     * 
     * @return array All staff times data
     */
    private function get_all_staff_times_data() {
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
}