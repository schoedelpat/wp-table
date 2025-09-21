<?php
/**
 * Plugin Name: WP Table - Staff Table Plugin
 * Plugin URI: https://github.com/schoedelpat/wp-table
 * Description: Staff Table Plugin for WordPress with Updatable Times. Display and manage staff information in a table format with time tracking capabilities.
 * Version: 1.0.0
 * Author: schoedelpat
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: wp-table
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_TABLE_VERSION', '1.0.0');
define('WP_TABLE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_TABLE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_TABLE_PLUGIN_FILE', __FILE__);

/**
 * Main WP Table Plugin Class
 */
class WP_Table_Plugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize the plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('wp-table', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize admin functionality
        if (is_admin()) {
            $this->init_admin();
        }
        
        // Initialize frontend functionality
        $this->init_frontend();
    }
    
    /**
     * Initialize admin functionality
     */
    private function init_admin() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Initialize frontend functionality
     */
    private function init_frontend() {
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_shortcode('wp_staff_table', array($this, 'staff_table_shortcode'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_tables();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_table_staff';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            position varchar(100) DEFAULT '' NOT NULL,
            email varchar(100) DEFAULT '' NOT NULL,
            phone varchar(20) DEFAULT '' NOT NULL,
            start_time time DEFAULT '09:00:00' NOT NULL,
            end_time time DEFAULT '17:00:00' NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('WP Table', 'wp-table'),
            __('WP Table', 'wp-table'),
            'manage_options',
            'wp-table',
            array($this, 'admin_page'),
            'dashicons-grid-view',
            30
        );
        
        add_submenu_page(
            'wp-table',
            __('Staff Members', 'wp-table'),
            __('Staff Members', 'wp-table'),
            'manage_options',
            'wp-table',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'wp-table',
            __('Settings', 'wp-table'),
            __('Settings', 'wp-table'),
            'manage_options',
            'wp-table-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        include_once WP_TABLE_PLUGIN_DIR . 'admin/admin-page.php';
    }
    
    /**
     * Settings page content
     */
    public function settings_page() {
        include_once WP_TABLE_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'wp-table') !== false) {
            wp_enqueue_style('wp-table-admin', WP_TABLE_PLUGIN_URL . 'assets/css/admin.css', array(), WP_TABLE_VERSION);
            wp_enqueue_script('wp-table-admin', WP_TABLE_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), WP_TABLE_VERSION, true);
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('wp-table-frontend', WP_TABLE_PLUGIN_URL . 'assets/css/frontend.css', array(), WP_TABLE_VERSION);
        wp_enqueue_script('wp-table-frontend', WP_TABLE_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), WP_TABLE_VERSION, true);
    }
    
    /**
     * Staff table shortcode
     */
    public function staff_table_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_times' => 'true',
            'show_email' => 'false',
            'show_phone' => 'false',
            'status' => 'active'
        ), $atts, 'wp_staff_table');
        
        ob_start();
        include WP_TABLE_PLUGIN_DIR . 'templates/staff-table.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
new WP_Table_Plugin();