<?php
/**
 * Error Handler Class
 * 
 * Centralized error handling for logging and admin notices
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Table_Error_Handler {
    
    /**
     * Error levels
     */
    const ERROR_LEVEL_INFO = 'info';
    const ERROR_LEVEL_WARNING = 'warning';
    const ERROR_LEVEL_ERROR = 'error';
    const ERROR_LEVEL_CRITICAL = 'critical';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }
    
    /**
     * Log error message
     * 
     * @param string $message Error message
     * @param string $level Error level
     * @param array $context Additional context data
     */
    public function log_error($message, $level = self::ERROR_LEVEL_ERROR, $context = array()) {
        // Sanitize input
        $message = sanitize_text_field($message);
        $level = sanitize_text_field($level);
        
        // Prepare log entry
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'level' => $level,
            'message' => $message,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'context' => wp_json_encode($context)
        );
        
        // Log to WordPress error log
        if (WP_DEBUG_LOG) {
            error_log(sprintf(
                '[WP_TABLE] %s - %s: %s (User: %d, IP: %s)',
                $log_entry['timestamp'],
                strtoupper($level),
                $message,
                $log_entry['user_id'],
                $log_entry['ip_address']
            ));
        }
        
        // Store in database for admin review
        $this->store_error_log($log_entry);
        
        // Show admin notice for critical errors
        if ($level === self::ERROR_LEVEL_CRITICAL || $level === self::ERROR_LEVEL_ERROR) {
            $this->add_admin_notice($message, 'error');
        } elseif ($level === self::ERROR_LEVEL_WARNING) {
            $this->add_admin_notice($message, 'warning');
        }
    }
    
    /**
     * Store error log in database
     * 
     * @param array $log_entry Log entry data
     */
    private function store_error_log($log_entry) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_table_error_logs';
        
        // Create table if it doesn't exist
        $this->create_error_log_table();
        
        // Insert log entry using prepared statement
        $result = $wpdb->insert(
            $table_name,
            $log_entry,
            array('%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('[WP_TABLE] Failed to store error log: ' . $wpdb->last_error);
        }
    }
    
    /**
     * Create error log table
     */
    private function create_error_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_table_error_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            context longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY timestamp (timestamp),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin notice
     * 
     * @param string $message Notice message
     * @param string $type Notice type (error, warning, success, info)
     */
    public function add_admin_notice($message, $type = 'error') {
        if (!is_admin()) {
            return;
        }
        
        // Sanitize inputs
        $message = sanitize_text_field($message);
        $type = sanitize_text_field($type);
        
        // Store notice in transient for display
        $notices = get_transient('wp_table_admin_notices') ?: array();
        $notices[] = array(
            'message' => $message,
            'type' => $type,
            'timestamp' => time()
        );
        
        set_transient('wp_table_admin_notices', $notices, 300); // 5 minutes
    }
    
    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        $notices = get_transient('wp_table_admin_notices');
        
        if (!$notices || !is_array($notices)) {
            return;
        }
        
        foreach ($notices as $notice) {
            $class = 'notice notice-' . esc_attr($notice['type']);
            printf(
                '<div class="%s"><p>%s</p></div>',
                esc_attr($class),
                esc_html($notice['message'])
            );
        }
        
        // Clear notices after displaying
        delete_transient('wp_table_admin_notices');
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = sanitize_text_field($_SERVER[$key]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }
    
    /**
     * Handle exception
     * 
     * @param Exception $exception Exception object
     * @param string $context Context where exception occurred
     */
    public function handle_exception($exception, $context = '') {
        $message = sprintf(
            'Exception in %s: %s (File: %s, Line: %d)',
            $context,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        $this->log_error($message, self::ERROR_LEVEL_CRITICAL, array(
            'context' => $context,
            'exception_class' => get_class($exception),
            'stack_trace' => $exception->getTraceAsString()
        ));
    }
    
    /**
     * Validate nonce with error handling
     * 
     * @param string $nonce Nonce to validate
     * @param string $action Nonce action
     * @return bool True if valid, false otherwise
     */
    public function validate_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            $this->log_error(
                sprintf('Invalid nonce for action: %s', $action),
                self::ERROR_LEVEL_WARNING,
                array(
                    'action' => $action,
                    'provided_nonce' => $nonce
                )
            );
            return false;
        }
        
        return true;
    }
    
    /**
     * Check user capabilities with error handling
     * 
     * @param string $capability Required capability
     * @return bool True if user has capability, false otherwise
     */
    public function check_capability($capability) {
        if (!current_user_can($capability)) {
            $this->log_error(
                sprintf('User lacks required capability: %s', $capability),
                self::ERROR_LEVEL_WARNING,
                array(
                    'capability' => $capability,
                    'user_id' => get_current_user_id()
                )
            );
            return false;
        }
        
        return true;
    }
}