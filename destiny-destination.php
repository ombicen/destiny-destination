<?php

/**
 * Plugin Name: Destiny Destination Widget
 * Description: Elementor widget to display time and distance to Vallentuna bil och däckservice
 * Version: 1.1.0
 * Author: Your Name
 * Text Domain: destiny-destination
 * Domain Path: /languages
 * Elementor tested up to: 3.18.0
 * Elementor Pro tested up to: 3.18.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('DESTINY_DESTINATION_VERSION', '1.1.0');
define('DESTINY_DESTINATION_MINIMUM_ELEMENTOR_VERSION', '2.0.0');
define('DESTINY_DESTINATION_MINIMUM_PHP_VERSION', '7.0');

/**
 * Main Plugin Class
 */
final class Destiny_Destination
{

    /**
     * Instance
     */
    private static $_instance = null;

    /**
     * Instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'i18n'));
        add_action('plugins_loaded', array($this, 'init'));
    }

    /**
     * Load Textdomain
     */
    public function i18n()
    {
        load_plugin_textdomain('destiny-destination');
    }

    /**
     * Log messages to WordPress debug log
     */
    public static function log($message, $level = 'INFO')
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_entry = sprintf(
                '[%s] [DESTINY-DESTINATION] [%s] %s',
                date('Y-m-d H:i:s'),
                $level,
                $message
            );
            error_log($log_entry);
        }
    }

    /**
     * Initialize the plugin
     */
    public function init()
    {
        // Check if Elementor installed and activated
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', array($this, 'admin_notice_missing_main_plugin'));
            return;
        }

        // Check for required Elementor version
        if (!version_compare(ELEMENTOR_VERSION, DESTINY_DESTINATION_MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_elementor_version'));
            return;
        }

        // Check for required PHP version
        if (version_compare(PHP_VERSION, DESTINY_DESTINATION_MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', array($this, 'admin_notice_minimum_php_version'));
            return;
        }

        // Include required files
        require_once(__DIR__ . '/includes/class-google-maps-api.php');

        // Add Plugin actions
        add_action('elementor/widgets/widgets_registered', array($this, 'init_widgets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Initialize Google Maps API
        new Destiny_Google_Maps_API();
    }

    /**
     * Admin notice
     */
    public function admin_notice_missing_main_plugin()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" to be installed and activated.', 'destiny-destination'),
            '<strong>' . esc_html__('Destiny Destination Widget', 'destiny-destination') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'destiny-destination') . '</strong>'
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum Elementor version
     */
    public function admin_notice_minimum_elementor_version()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'destiny-destination'),
            '<strong>' . esc_html__('Destiny Destination Widget', 'destiny-destination') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'destiny-destination') . '</strong>',
            DESTINY_DESTINATION_MINIMUM_ELEMENTOR_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Admin notice for minimum PHP version
     */
    public function admin_notice_minimum_php_version()
    {
        if (isset($_GET['activate'])) unset($_GET['activate']);
        $message = sprintf(
            esc_html__('"%1$s" requires "%2$s" version %3$s or greater.', 'destiny-destination'),
            '<strong>' . esc_html__('Destiny Destination Widget', 'destiny-destination') . '</strong>',
            '<strong>' . esc_html__('PHP', 'destiny-destination') . '</strong>',
            DESTINY_DESTINATION_MINIMUM_PHP_VERSION
        );
        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    /**
     * Init Widgets
     */
    public function init_widgets()
    {
        // Include Widget files
        require_once(__DIR__ . '/widgets/destination-widget.php');

        // Register widget
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Destiny_Destination_Widget());
    }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts()
    {
        // Always enqueue jQuery
        wp_enqueue_script('jquery');

        // Create a minimal script handle for our AJAX localization
        wp_register_script(
            'destiny-destination-ajax',
            false, // No file, just for localization
            ['jquery'],
            DESTINY_DESTINATION_VERSION,
            true
        );
        wp_enqueue_script('destiny-destination-ajax');

        wp_localize_script('destiny-destination-ajax', 'destiny_destination_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('destiny_destination_nonce')
        ));

        // Main widget JavaScript
        wp_enqueue_script(
            'destiny-destination-widget',
            plugins_url('/assets/js/destiny-destination-widget.js', __FILE__),
            ['jquery', 'destiny-destination-ajax'],
            DESTINY_DESTINATION_VERSION,
            true
        );

        // Tippy.js for tooltips (optional)
        if (!wp_script_is('tippy-js-main', 'enqueued')) {
            wp_enqueue_script(
                'popper-js',
                'https://unpkg.com/@popperjs/core@2',
                [],
                '2.11.8',
                true
            );

            wp_enqueue_script(
                'tippy-js-main',
                'https://unpkg.com/tippy.js@6',
                ['popper-js'],
                '6.3.7',
                true
            );
        }

        wp_enqueue_style(
            'destiny-destination-css',
            plugins_url('/assets/css/destiny-destination.css', __FILE__),
            [],
            DESTINY_DESTINATION_VERSION
        );
    }
}

Destiny_Destination::instance();

// AJAX handlers
add_action('wp_ajax_get_destination_info', 'destiny_get_destination_info');
add_action('wp_ajax_nopriv_get_destination_info', 'destiny_get_destination_info');

function destiny_get_destination_info()
{
    check_ajax_referer('destiny_destination_nonce', 'nonce');

    $origin = sanitize_text_field($_POST['origin'] ?? '');
    $widget_id = sanitize_text_field($_POST['widget_id'] ?? '');

    // Debug log the received parameters
    if (class_exists('Destiny_Destination')) {
        Destiny_Destination::log('AJAX Handler - Widget ID: ' . $widget_id);
        Destiny_Destination::log('AJAX Handler - Origin: ' . $origin);
    }

    // Only process requests from our widgets
    if (!isset($_POST['widget_id']) || substr($_POST['widget_id'], 0, 20) !== 'destiny-destination-') {
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('AJAX Handler - Rejecting request: Invalid or missing widget_id', 'WARNING');
        }
        wp_send_json_error('Invalid request source');
        return;
    }

    // Backend-controlled settings
    $destination = 'Moränvägen 13, 186 40 Vallentuna, Sweden'; // Default destination
    $fallback_source = 'Stockholm, Sverige'; // Default fallback

    // Determine if this is a fallback request (not GPS coordinates)
    $is_fallback_request = !preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $origin);

    // Enable cache only for fallback requests
    $enable_cache = $is_fallback_request;
    $cache_time = 60; // Default cache time in minutes

    if (class_exists('Destiny_Destination')) {
        Destiny_Destination::log('AJAX Handler - Destination: ' . $destination);
        Destiny_Destination::log('AJAX Handler - Fallback source: ' . $fallback_source);
        Destiny_Destination::log('AJAX Handler - Is fallback request: ' . ($is_fallback_request ? 'yes' : 'no'));
        Destiny_Destination::log('AJAX Handler - Enable cache: ' . ($enable_cache ? 'yes' : 'no'));
        Destiny_Destination::log('AJAX Handler - Cache time: ' . $cache_time);
    }

    // Initialize Google Maps API
    $google_maps = new Destiny_Google_Maps_API();

    // Try to get real data first, fall back to mock data
    $api_key = get_option('destiny_destination_google_api_key', '');

    if (!empty($api_key)) {
        $response = $google_maps->get_distance_matrix($origin, $destination, $fallback_source, $enable_cache, $cache_time);
    } else {
        $response = $google_maps->get_mock_data($origin);
    }

    // Convert to WordPress AJAX format
    if ($response['status'] === 'success') {
        // Add additional fields for frontend
        $response['source_display'] = $fallback_source;
        $response['directions_url'] = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($destination) . '&origin=' . urlencode($origin);

        wp_send_json_success($response);
    } else {
        wp_send_json_error($response['message'] ?? 'Unknown error occurred');
    }
}
