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
        
        $table_name = $wpdb->prefix . 'wp_table_staff';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            position varchar(100) NOT NULL,
            email varchar(100) DEFAULT '' NOT NULL,
            phone varchar(20) DEFAULT '' NOT NULL,
            start_time time NOT NULL,
            end_time time NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            image_url varchar(255) DEFAULT '' NOT NULL,
            image_size int(3) DEFAULT 300 NOT NULL,
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
            'show_images' => get_option('wp_table_show_images', 'yes'),
            'show_times' => get_option('wp_table_show_times', 'yes'),
            'show_email' => get_option('wp_table_show_email', 'no'),
            'show_phone' => get_option('wp_table_show_phone', 'no'),
            'status' => 'active',
            'image_size' => get_option('wp_table_default_image_size', 300),
            'class' => 'wp-staff-table-container'
        ), $atts);
        
        // Get staff data
        global $wpdb;
        $table_name = $wpdb->prefix . 'wp_table_staff';
        
        $where_clause = '';
        if ($atts['status'] !== 'all') {
            $where_clause = $wpdb->prepare(' WHERE status = %s', $atts['status']);
        }
        
        $staff_list = $wpdb->get_results(
            "SELECT * FROM {$table_name}{$where_clause} ORDER BY name ASC",
            ARRAY_A
        );
        
        if (!$staff_list) {
            return '<div class="wp-table-no-staff"><p>' . esc_html__('No staff members found.') . '</p></div>';
        }
        
        // Sanitize image size
        $image_size = intval($atts['image_size']);
        if ($image_size < 100 || $image_size > 500) {
            $image_size = 300;
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($atts['class']); ?>">
            <table class="wp-staff-table">
                <thead>
                    <tr>
                        <?php if ($atts['show_images'] === 'true' || $atts['show_images'] === 'yes') : ?>
                            <th class="wp-table-image"><?php echo esc_html__('Photo'); ?></th>
                        <?php endif; ?>
                        <th class="wp-table-name"><?php echo esc_html__('Name'); ?></th>
                        <th class="wp-table-position"><?php echo esc_html__('Position'); ?></th>
                        <?php if ($atts['show_email'] === 'true' || $atts['show_email'] === 'yes') : ?>
                            <th class="wp-table-email"><?php echo esc_html__('Email'); ?></th>
                        <?php endif; ?>
                        <?php if ($atts['show_phone'] === 'true' || $atts['show_phone'] === 'yes') : ?>
                            <th class="wp-table-phone"><?php echo esc_html__('Phone'); ?></th>
                        <?php endif; ?>
                        <?php if ($atts['show_times'] === 'true' || $atts['show_times'] === 'yes') : ?>
                            <th class="wp-table-hours"><?php echo esc_html__('Working Hours'); ?></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($staff_list as $staff) : ?>
                        <tr>
                            <?php if ($atts['show_images'] === 'true' || $atts['show_images'] === 'yes') : ?>
                                <td class="wp-table-image">
                                    <?php if (!empty($staff['image_url'])) : ?>
                                        <img src="<?php echo esc_url($staff['image_url']); ?>" 
                                             alt="<?php echo esc_attr($staff['name']); ?>" 
                                             style="width: <?php echo esc_attr($image_size); ?>px; height: <?php echo esc_attr($image_size); ?>px; object-fit: cover; border-radius: 4px;">
                                    <?php else : ?>
                                        <div class="wp-table-no-image" 
                                             style="width: <?php echo esc_attr($image_size); ?>px; height: <?php echo esc_attr($image_size); ?>px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666;">
                                            <?php echo esc_html__('No Photo'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="wp-table-name">
                                <strong><?php echo esc_html($staff['name']); ?></strong>
                            </td>
                            <td class="wp-table-position">
                                <?php echo esc_html($staff['position']); ?>
                            </td>
                            <?php if ($atts['show_email'] === 'true' || $atts['show_email'] === 'yes') : ?>
                                <td class="wp-table-email">
                                    <?php if (!empty($staff['email'])) : ?>
                                        <a href="mailto:<?php echo esc_attr($staff['email']); ?>">
                                            <?php echo esc_html($staff['email']); ?>
                                        </a>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($atts['show_phone'] === 'true' || $atts['show_phone'] === 'yes') : ?>
                                <td class="wp-table-phone">
                                    <?php if (!empty($staff['phone'])) : ?>
                                        <a href="tel:<?php echo esc_attr($staff['phone']); ?>">
                                            <?php echo esc_html($staff['phone']); ?>
                                        </a>
                                    <?php else : ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($atts['show_times'] === 'true' || $atts['show_times'] === 'yes') : ?>
                                <td class="wp-table-hours">
                                    <?php echo esc_html($staff['start_time'] . ' - ' . $staff['end_time']); ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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