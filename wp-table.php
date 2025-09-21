<?php
/**
 * Plugin Name: WP Table
 * Description: Staff Table Plugin for WordPress with Updatable Times
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TABLE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_TABLE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WP_TABLE_VERSION', '1.0.0');

// Include required files
require_once WP_TABLE_PLUGIN_PATH . 'includes/class-error-handler.php';
require_once WP_TABLE_PLUGIN_PATH . 'includes/class-ajax-handler.php';
require_once WP_TABLE_PLUGIN_PATH . 'includes/endpoints/class-time-endpoint.php';
require_once WP_TABLE_PLUGIN_PATH . 'admin/class-admin.php';

/**
 * Main plugin class
 */
class WP_Table_Plugin {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Error handler instance
     */
    public $error_handler;
    
    /**
     * AJAX handler instance
     */
    public $ajax_handler;
    
    /**
     * Admin handler instance
     */
    public $admin;
    
    /**
     * Time endpoint instance
     */
    public $time_endpoint;
    
    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Initialize error handler first
        $this->error_handler = new WP_Table_Error_Handler();
        
        // Initialize other components
        $this->ajax_handler = new WP_Table_Ajax_Handler();
        $this->time_endpoint = new WP_Table_Time_Endpoint();
        
        if (is_admin()) {
            $this->admin = new WP_Table_Admin();
        }
        
        // Hook into WordPress
        add_action('init', array($this, 'setup_database'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Register shortcode
        add_shortcode('wp_staff_table', array($this, 'staff_table_shortcode'));
    }
    
    /**
     * Setup database tables
     */
    public function setup_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'staff_table';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            position varchar(100) NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script('wp-table-frontend', WP_TABLE_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WP_TABLE_VERSION, true);
        wp_enqueue_style('wp-table-frontend', WP_TABLE_PLUGIN_URL . 'assets/css/wp-table.css', array(), WP_TABLE_VERSION);
        wp_localize_script('wp-table-frontend', 'wp_table_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_table_ajax_nonce')
        ));
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'wp-table') === false) {
            return;
        }
        
        wp_enqueue_script('wp-table-admin', WP_TABLE_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WP_TABLE_VERSION, true);
        wp_enqueue_style('wp-table-admin', WP_TABLE_PLUGIN_URL . 'assets/css/wp-table.css', array(), WP_TABLE_VERSION);
        wp_localize_script('wp-table-admin', 'wp_table_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_table_admin_nonce')
        ));
    }
    
    /**
     * Staff table shortcode
     */
    public function staff_table_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_times' => 'true',
            'class' => 'wp-staff-table-container'
        ), $atts);
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <div class="wp-table-loading">Loading staff data...</div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize plugin
add_action('plugins_loaded', array('WP_Table_Plugin', 'get_instance'));

// Activation hook
register_activation_hook(__FILE__, function() {
    WP_Table_Plugin::get_instance()->setup_database();
});