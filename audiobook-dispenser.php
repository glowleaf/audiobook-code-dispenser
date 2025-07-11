<?php
/**
 * Plugin Name: Audiobook Code Dispenser
 * Plugin URI: https://georgesaoulidis.com
 * Description: Dispense audiobook promotional codes from CSV uploads with MailerLite integration
 * Version: 1.0.0
 * Author: George Saoulidis
 * Author URI: https://georgesaoulidis.com
 * Text Domain: audiobook-code-dispenser
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 *
 * @package AudiobookCodeDispenser
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ACD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACD_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ACD_VERSION', '1.0.0');

class AudiobookDispenser {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_ajax_acd_upload_csv', array($this, 'handle_csv_upload'));
        add_action('wp_ajax_acd_delete_book', array($this, 'handle_delete_book'));
        add_action('wp_ajax_acd_get_stats', array($this, 'get_stats'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_acd_dispense_code', array($this, 'dispense_code'));
        add_action('wp_ajax_nopriv_acd_dispense_code', array($this, 'dispense_code'));
        
        // Shortcode
        add_shortcode('audiobook_dispenser', array($this, 'render_shortcode'));
    }
    
    public function activate() {
        $this->create_tables();
        $this->insert_default_books();
    }
    
    public function deactivate() {
        // Clean up if needed
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Books table
        $books_table = $wpdb->prefix . 'acd_books';
        $sql_books = "CREATE TABLE $books_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            status enum('active','inactive') DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY title (title)
        ) $charset_collate;";
        
        // Codes table
        $codes_table = $wpdb->prefix . 'acd_codes';
        $sql_codes = "CREATE TABLE $codes_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            book_id mediumint(9) NOT NULL,
            code varchar(50) NOT NULL,
            marketplace enum('US','GB') NOT NULL,
            status enum('AVAILABLE','REDEEMED','DISPENSED') DEFAULT 'AVAILABLE',
            generated_on date,
            redemption_date date,
            dispensed_at datetime,
            dispensed_to varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code_marketplace (code, marketplace),
            KEY book_id (book_id),
            KEY status_marketplace (status, marketplace)
        ) $charset_collate;";
        
        // Settings table
        $settings_table = $wpdb->prefix . 'acd_settings';
        $sql_settings = "CREATE TABLE $settings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value text,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_books);
        dbDelta($sql_codes);
        dbDelta($sql_settings);
        
        // Insert default settings
        $this->insert_default_settings();
    }
    
    private function insert_default_settings() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'acd_settings';
        
        $default_settings = array(
            'mailerlite_api_key' => '',
            'mailerlite_group_id' => '',
            'widget_title' => 'Free Audiobook Codes',
            'widget_subtitle' => 'Get your free audiobook from George Saoulidis'
        );
        
        foreach ($default_settings as $key => $value) {
            $wpdb->replace($settings_table, array(
                'setting_key' => $key,
                'setting_value' => $value
            ));
        }
    }
    
    private function insert_default_books() {
        // No longer insert hardcoded books
        // Books will be created when CSV files are uploaded
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Audiobook Dispenser',
            'Audiobook Dispenser',
            'manage_options',
            'audiobook-dispenser',
            array($this, 'admin_page'),
            'dashicons-media-audio',
            30
        );
        
        add_submenu_page(
            'audiobook-dispenser',
            'Settings',
            'Settings',
            'manage_options',
            'audiobook-dispenser-settings',
            array($this, 'settings_page')
        );
    }
    
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'audiobook-dispenser') === false) {
            return;
        }
        
        wp_enqueue_style('acd-admin', ACD_PLUGIN_URL . 'assets/admin.css', array(), ACD_VERSION);
        
        wp_enqueue_script('acd-admin', ACD_PLUGIN_URL . 'assets/admin.js', array('jquery'), ACD_VERSION, true);
        wp_localize_script('acd-admin', 'acd_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('acd_nonce')
        ));
    }
    
    public function frontend_enqueue_scripts() {
        wp_enqueue_style('acd-frontend', ACD_PLUGIN_URL . 'assets/frontend.css', array(), ACD_VERSION);
        
        wp_enqueue_script('acd-frontend', ACD_PLUGIN_URL . 'assets/frontend.js', array('jquery'), ACD_VERSION, true);
        wp_localize_script('acd-frontend', 'acd_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('acd_nonce')
        ));
    }
    
    public function admin_page() {
        global $wpdb;
        
        $books_table = $wpdb->prefix . 'acd_books';
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        // Get books with code counts
        $books = $wpdb->get_results("
            SELECT b.*, 
                   COUNT(CASE WHEN c.marketplace = 'US' AND c.status = 'AVAILABLE' THEN 1 END) as us_available,
                   COUNT(CASE WHEN c.marketplace = 'GB' AND c.status = 'AVAILABLE' THEN 1 END) as uk_available,
                   COUNT(CASE WHEN c.marketplace = 'US' AND c.status = 'DISPENSED' THEN 1 END) as us_dispensed,
                   COUNT(CASE WHEN c.marketplace = 'GB' AND c.status = 'DISPENSED' THEN 1 END) as uk_dispensed
            FROM $books_table b 
            LEFT JOIN $codes_table c ON b.id = c.book_id 
            GROUP BY b.id 
            ORDER BY b.title
        ");
        
        include ACD_PLUGIN_PATH . 'templates/admin-page.php';
    }
    
    public function settings_page() {
        if (isset($_POST['submit'])) {
            $this->save_settings();
        }
        
        $settings = $this->get_settings();
        include ACD_PLUGIN_PATH . 'templates/settings-page.php';
    }
    
    private function save_settings() {
        if (!wp_verify_nonce($_POST['acd_settings_nonce'], 'acd_save_settings')) {
            return;
        }
        
        global $wpdb;
        $settings_table = $wpdb->prefix . 'acd_settings';
        
        $settings = array(
            'mailerlite_api_key' => sanitize_text_field($_POST['mailerlite_api_key']),
            'mailerlite_group_id' => sanitize_text_field($_POST['mailerlite_group_id']),
            'widget_title' => sanitize_text_field($_POST['widget_title']),
            'widget_subtitle' => sanitize_text_field($_POST['widget_subtitle'])
        );
        
        foreach ($settings as $key => $value) {
            $wpdb->replace($settings_table, array(
                'setting_key' => $key,
                'setting_value' => $value
            ));
        }
        
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }
    
    private function get_settings() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'acd_settings';
        
        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM $settings_table");
        $settings = array();
        
        foreach ($results as $result) {
            $settings[$result->setting_key] = $result->setting_value;
        }
        
        return $settings;
    }
    
    public function handle_csv_upload() {
        if (!wp_verify_nonce($_POST['nonce'], 'acd_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $marketplace = sanitize_text_field($_POST['marketplace']);
        
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload failed');
        }
        
        $filename = $_FILES['csv_file']['name'];
        $file = $_FILES['csv_file']['tmp_name'];
        
        // Extract book title from filename
        $book_title = $this->extract_book_title_from_filename($filename);
        if (!$book_title) {
            wp_send_json_error('Could not extract book title from filename. Please use format: promocodes-BookTitle-YYYY-MM-DD.csv');
        }
        
        // Create or get book ID (this will create if not exists, or return existing ID)
        $book_id = $this->create_or_get_book($book_title);
        if (!$book_id) {
            wp_send_json_error('Failed to create/find book entry');
        }
        
        $result = $this->process_csv_file($file, $book_id, $marketplace);
        
        if ($result['success']) {
            $result['book_title'] = $book_title;
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    private function process_csv_file($file, $book_id, $marketplace) {
        global $wpdb;
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        $handle = fopen($file, 'r');
        if (!$handle) {
            return array('success' => false, 'message' => 'Could not open CSV file');
        }
        
        $headers = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        
        // Check if there are existing codes for this book and marketplace
        $existing_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $codes_table 
            WHERE book_id = %d AND marketplace = %s
        ", $book_id, $marketplace));
        
        // Clear existing codes for this book and marketplace to replace with new data
        if ($existing_count > 0) {
            $wpdb->delete($codes_table, array(
                'book_id' => $book_id,
                'marketplace' => $marketplace
            ));
        }
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            if (count($data) >= 3) {
                $code = sanitize_text_field($data[0]);
                $status = sanitize_text_field($data[1]);
                $csv_marketplace = sanitize_text_field($data[2]);
                $generated_on = isset($data[3]) ? $data[3] : null;
                $redemption_date = isset($data[4]) ? $data[4] : null;
                
                // Convert CSV marketplace format
                if ($csv_marketplace === 'GB') {
                    $csv_marketplace = 'GB';
                } else {
                    $csv_marketplace = 'US';
                }
                
                // Only import codes for the selected marketplace
                if ($csv_marketplace !== $marketplace) {
                    continue;
                }
                
                // Convert dates
                $generated_on = $this->parse_date($generated_on);
                $redemption_date = $this->parse_date($redemption_date);
                
                $insert_data = array(
                    'book_id' => $book_id,
                    'code' => $code,
                    'marketplace' => $marketplace,
                    'status' => $status,
                    'generated_on' => $generated_on,
                    'redemption_date' => $redemption_date
                );
                
                $result = $wpdb->insert($codes_table, $insert_data);
                
                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }
        }
        
        fclose($handle);
        
        $action = $existing_count > 0 ? 'updated' : 'imported';
        return array(
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'existing_count' => $existing_count,
            'message' => "Successfully $action $imported codes for $marketplace marketplace" . ($skipped > 0 ? ", skipped $skipped invalid entries" : "")
        );
    }
    
    private function parse_date($date_string) {
        if (empty($date_string)) {
            return null;
        }
        
        $date = DateTime::createFromFormat('m/d/y', $date_string);
        if ($date) {
            return $date->format('Y-m-d');
        }
        
        return null;
    }
    
    private function extract_book_title_from_filename($filename) {
        // Expected format: promocodes-BookTitle-YYYY-MM-DD.csv
        if (!preg_match('/^promocodes-(.+)-\d{4}-\d{2}-\d{2}\.csv$/i', $filename, $matches)) {
            return false;
        }
        
        $title = $matches[1];
        // Replace hyphens with spaces and clean up
        $title = str_replace('-', ' ', $title);
        $title = trim($title);
        
        return $title;
    }
    
    private function create_or_get_book($title) {
        global $wpdb;
        $books_table = $wpdb->prefix . 'acd_books';
        
        // Check if book already exists
        $existing_book = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $books_table WHERE title = %s",
            $title
        ));
        
        if ($existing_book) {
            return $existing_book->id;
        }
        
        // Create new book
        $result = $wpdb->insert($books_table, array(
            'title' => $title,
            'status' => 'active'
        ));
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    

    public function handle_delete_book() {
        if (!wp_verify_nonce($_POST['nonce'], 'acd_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $book_id = intval($_POST['book_id']);
        
        // Delete codes first
        $codes_table = $wpdb->prefix . 'acd_codes';
        $wpdb->delete($codes_table, array('book_id' => $book_id));
        
        // Delete book
        $books_table = $wpdb->prefix . 'acd_books';
        $result = $wpdb->delete($books_table, array('id' => $book_id));
        
        if ($result) {
            wp_send_json_success('Book deleted successfully');
        } else {
            wp_send_json_error('Failed to delete book');
        }
    }
    
    public function get_stats() {
        if (!wp_verify_nonce($_POST['nonce'], 'acd_nonce')) {
            wp_die('Security check failed');
        }
        
        global $wpdb;
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        $stats = $wpdb->get_row("
            SELECT 
                COUNT(*) as total_codes,
                COUNT(CASE WHEN status = 'AVAILABLE' THEN 1 END) as available_codes,
                COUNT(CASE WHEN status = 'DISPENSED' THEN 1 END) as dispensed_codes,
                COUNT(CASE WHEN marketplace = 'US' AND status = 'DISPENSED' THEN 1 END) as us_dispensed,
                COUNT(CASE WHEN marketplace = 'GB' AND status = 'DISPENSED' THEN 1 END) as uk_dispensed
            FROM $codes_table
        ");
        
        wp_send_json_success($stats);
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => '',
            'subtitle' => ''
        ), $atts);
        
        $settings = $this->get_settings();
        $title = !empty($atts['title']) ? $atts['title'] : $settings['widget_title'];
        $subtitle = !empty($atts['subtitle']) ? $atts['subtitle'] : $settings['widget_subtitle'];
        
        // Get available books
        global $wpdb;
        $books_table = $wpdb->prefix . 'acd_books';
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        $books = $wpdb->get_results("
            SELECT b.*, 
                   COUNT(CASE WHEN c.marketplace = 'US' AND c.status = 'AVAILABLE' THEN 1 END) as us_available,
                   COUNT(CASE WHEN c.marketplace = 'GB' AND c.status = 'AVAILABLE' THEN 1 END) as uk_available
            FROM $books_table b 
            LEFT JOIN $codes_table c ON b.id = c.book_id 
            WHERE b.status = 'active'
            GROUP BY b.id 
            HAVING us_available > 0 OR uk_available > 0
            ORDER BY b.title
        ");
        
        ob_start();
        include ACD_PLUGIN_PATH . 'templates/frontend-widget.php';
        return ob_get_clean();
    }
    
    public function dispense_code() {
        if (!wp_verify_nonce($_POST['nonce'], 'acd_nonce')) {
            wp_send_json_error('Security check failed');
        }
        
        $email = sanitize_email($_POST['email']);
        $book_id = intval($_POST['book_id']);
        $marketplace = sanitize_text_field($_POST['marketplace']);
        
        if (!$email || !$book_id || !in_array($marketplace, array('US', 'GB'))) {
            wp_send_json_error('Invalid data provided');
        }
        
        // Get next available code
        $code = $this->get_next_available_code($book_id, $marketplace);
        
        if (!$code) {
            wp_send_json_error('No codes available for this book in the selected region');
        }
        
        // Mark code as dispensed
        $this->mark_code_dispensed($code['id'], $email);
        
        // Add to MailerLite
        $mailerlite_result = $this->add_to_mailerlite($email, $code, $marketplace);
        
        // Get book title
        global $wpdb;
        $books_table = $wpdb->prefix . 'acd_books';
        $book = $wpdb->get_row($wpdb->prepare("SELECT title FROM $books_table WHERE id = %d", $book_id));
        
        wp_send_json_success(array(
            'code' => $code['code'],
            'book_title' => $book->title,
            'marketplace' => $marketplace,
            'mailerlite_success' => $mailerlite_result
        ));
    }
    
    private function get_next_available_code($book_id, $marketplace) {
        global $wpdb;
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $codes_table 
            WHERE book_id = %d AND marketplace = %s AND status = 'AVAILABLE' 
            ORDER BY id ASC 
            LIMIT 1
        ", $book_id, $marketplace), ARRAY_A);
    }
    
    private function mark_code_dispensed($code_id, $email) {
        global $wpdb;
        $codes_table = $wpdb->prefix . 'acd_codes';
        
        $wpdb->update(
            $codes_table,
            array(
                'status' => 'DISPENSED',
                'dispensed_at' => current_time('mysql'),
                'dispensed_to' => $email
            ),
            array('id' => $code_id)
        );
    }
    
    private function add_to_mailerlite($email, $code, $marketplace) {
        $settings = $this->get_settings();
        $api_key = $settings['mailerlite_api_key'];
        $group_id = $settings['mailerlite_group_id'];
        
        if (empty($api_key) || empty($group_id)) {
            return false;
        }
        
        global $wpdb;
        $books_table = $wpdb->prefix . 'acd_books';
        $book = $wpdb->get_row($wpdb->prepare("SELECT title FROM $books_table WHERE id = %d", $code['book_id']));
        
        $data = array(
            'email' => $email,
            'fields' => array(
                'book_title' => $book->title,
                'region' => $marketplace,
                'audiobook_code' => $code['code'],
                'dispensed_date' => current_time('Y-m-d H:i:s')
            )
        );
        
        $response = wp_remote_post("https://api.mailerlite.com/api/v2/groups/$group_id/subscribers", array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-MailerLite-ApiKey' => $api_key
            ),
            'body' => json_encode($data),
            'timeout' => 30
        ));
        
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }
}

// Initialize the plugin
if (class_exists('AudiobookDispenser')) {
    new AudiobookDispenser();
}
