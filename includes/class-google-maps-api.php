<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Google Maps API Integration Class
 */
class Destiny_Google_Maps_API
{

    private $api_key;
    private $destination = 'Moränvägen 13, 186 40 Vallentuna, Sweden';

    public function __construct()
    {
        $this->api_key = get_option('destiny_destination_google_api_key', '');

        // Add admin hooks for settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Format and validate origin coordinates or address
     */
    private function format_origin($origin)
    {
        // Trim whitespace
        $origin = trim($origin);

        // Check if it looks like coordinates (lat,lng)
        if (preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $origin)) {
            // It's coordinates - ensure proper formatting
            $coords = explode(',', $origin);
            $lat = floatval(trim($coords[0]));
            $lng = floatval(trim($coords[1]));

            // Validate coordinate ranges
            if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                $formatted = $lat . ',' . $lng;
                if (class_exists('Destiny_Destination')) {
                    Destiny_Destination::log('Origin recognized as coordinates: ' . $formatted);
                }
                return $formatted;
            } else {
                if (class_exists('Destiny_Destination')) {
                    Destiny_Destination::log('Invalid coordinate ranges - lat: ' . $lat . ', lng: ' . $lng, 'WARNING');
                }
            }
        }

        // It's an address or invalid coordinates, return as-is
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Origin treated as address: ' . $origin);
        }
        return $origin;
    }

    /**
     * Get distance and duration from origin to destination
     */
    public function get_distance_matrix($origin)
    {
        // Log the input parameters
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('get_distance_matrix called with origin: ' . $origin);
            Destiny_Destination::log('Destination: ' . $this->destination);
            Destiny_Destination::log('API Key configured: ' . (!empty($this->api_key) ? 'Yes' : 'No'));
        }

        if (empty($this->api_key)) {
            if (class_exists('Destiny_Destination')) {
                Destiny_Destination::log('Google Maps API key not configured - returning error', 'WARNING');
            }
            return array(
                'status' => 'error',
                'message' => 'Google Maps API key not configured',
                'distance' => 'N/A',
                'duration' => 'N/A'
            );
        }

        // Validate and format origin
        $formatted_origin = $this->format_origin($origin);
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Formatted origin: ' . $formatted_origin);
        }

        $api_url = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        $params = array(
            'origins' => $formatted_origin,
            'destinations' => $this->destination,
            'units' => 'metric',
            'mode' => 'driving',
            'language' => 'sv',
            'departure_time' => 'now', // Enable traffic-based duration
            'key' => $this->api_key
        );

        // Log the API request parameters (without exposing the full API key)
        $log_params = $params;
        $log_params['key'] = substr($this->api_key, 0, 8) . '...' . substr($this->api_key, -4);
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Google Maps API request params: ' . json_encode($log_params));
            Destiny_Destination::log('Origin format check - should be lat,lng: ' . $origin);
        }

        $url = $api_url . '?' . http_build_query($params);

        // Log the full URL (without API key for security)
        $log_url = str_replace($this->api_key, '[API_KEY]', $url);
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Google Maps API URL: ' . $log_url);
        }

        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'sslverify' => true
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            if (class_exists('Destiny_Destination')) {
                Destiny_Destination::log('Google Maps API HTTP error: ' . $error_message, 'ERROR');
            }
            return array(
                'status' => 'error',
                'message' => 'Failed to connect to Google Maps API',
                'distance' => 'N/A',
                'duration' => 'N/A'
            );
        }

        $body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        // Log the raw response
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Google Maps API HTTP response code: ' . $response_code);
            Destiny_Destination::log('Google Maps API raw response: ' . $body);
        }

        $data = json_decode($body, true);

        if (!$data || $data['status'] !== 'OK') {
            $api_status = $data['status'] ?? 'Unknown error';
            $error_message = isset($data['error_message']) ? $data['error_message'] : 'No error message provided';

            if (class_exists('Destiny_Destination')) {
                Destiny_Destination::log('Google Maps API error status: ' . $api_status, 'ERROR');
                Destiny_Destination::log('Google Maps API error message: ' . $error_message, 'ERROR');
            }

            return array(
                'status' => 'error',
                'message' => 'Google Maps API error: ' . $api_status,
                'distance' => 'N/A',
                'duration' => 'N/A'
            );
        }

        // Log successful API response structure
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Google Maps API response rows count: ' . count($data['rows']));
            if (isset($data['rows'][0]['elements'])) {
                Destiny_Destination::log('Google Maps API response elements count: ' . count($data['rows'][0]['elements']));
            }
        }

        $element = $data['rows'][0]['elements'][0];

        if ($element['status'] !== 'OK') {
            if (class_exists('Destiny_Destination')) {
                Destiny_Destination::log('Google Maps API element status error: ' . $element['status'], 'ERROR');
            }
            return array(
                'status' => 'error',
                'message' => 'Route not found',
                'distance' => 'N/A',
                'duration' => 'N/A'
            );
        }

        // Get both normal and traffic durations
        $duration = isset($element['duration']['text']) ? $element['duration']['text'] : null;
        $duration_value = isset($element['duration']['value']) ? $element['duration']['value'] : null;
        $duration_in_traffic = isset($element['duration_in_traffic']['text']) ? $element['duration_in_traffic']['text'] : $duration;
        $duration_in_traffic_value = isset($element['duration_in_traffic']['value']) ? $element['duration_in_traffic']['value'] : $duration_value;

        $result = array(
            'status' => 'success',
            'distance' => $element['distance']['text'],
            'duration' => $duration, // normal
            'duration_value' => $duration_value,
            'duration_in_traffic' => $duration_in_traffic, // with traffic
            'duration_in_traffic_value' => $duration_in_traffic_value,
            'distance_value' => $element['distance']['value']
        );

        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Google Maps API successful result: ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Get mock data for testing when API key is not available
     */
    public function get_mock_data($origin)
    {
        // Log mock data usage
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('get_mock_data called with origin: ' . $origin);
            Destiny_Destination::log('Using mock data because no API key is configured');
        }

        // Generate somewhat realistic mock data based on common Swedish distances
        $mock_distances = array('8.2 km', '12.5 km', '15.3 km', '6.7 km', '21.1 km');
        $mock_durations = array('12 min', '18 min', '23 min', '9 min', '28 min');
        $mock_durations_traffic = array('14 min', '22 min', '30 min', '11 min', '40 min'); // Simulate traffic
        $index = abs(crc32($origin)) % count($mock_distances);
        $result = array(
            'status' => 'success',
            'distance' => $mock_distances[$index],
            'duration' => $mock_durations[$index],
            'duration_value' => $mock_durations[$index], // Not used, but for compatibility
            'duration_in_traffic' => $mock_durations_traffic[$index],
            'duration_in_traffic_value' => $mock_durations_traffic[$index],
            'mock' => true
        );

        // Log the generated mock data
        if (class_exists('Destiny_Destination')) {
            Destiny_Destination::log('Mock data generated (index: ' . $index . '): ' . json_encode($result));
        }

        return $result;
    }

    /**
     * Add admin menu for settings
     */
    public function add_admin_menu()
    {
        add_options_page(
            'Destiny Destination Settings',
            'Destiny Destination',
            'manage_options',
            'destiny-destination-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('destiny_destination_settings', 'destiny_destination_google_api_key');

        add_settings_section(
            'destiny_destination_main',
            'Google Maps API Configuration',
            array($this, 'settings_section_callback'),
            'destiny-destination-settings'
        );

        add_settings_field(
            'destiny_destination_google_api_key',
            'Google Maps API Key',
            array($this, 'api_key_field_callback'),
            'destiny-destination-settings',
            'destiny_destination_main'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback()
    {
        echo '<p>Configure your Google Maps API key to get real-time distance and duration data.</p>';
        echo '<p><strong>Note:</strong> Without an API key, the widget will display mock data for testing purposes.</p>';
        echo '<p><a href="https://developers.google.com/maps/documentation/distance-matrix/get-api-key" target="_blank">Get your Google Maps API Key</a></p>';
    }

    /**
     * API key field callback
     */
    public function api_key_field_callback()
    {
        $api_key = get_option('destiny_destination_google_api_key', '');
        echo '<input type="text" id="destiny_destination_google_api_key" name="destiny_destination_google_api_key" value="' . esc_attr($api_key) . '" size="50" />';
        echo '<p class="description">Enter your Google Maps Distance Matrix API key.</p>';
    }

    /**
     * Settings page
     */
    public function settings_page()
    {
?>
<div class="wrap">
    <h1>Destiny Destination Settings</h1>

    <div class="notice notice-info">
        <p><strong>Destination:</strong> Vallentuna bil och däckservice, Moränvägen 13, 186 40 Vallentuna</p>
        <p>This widget calculates distance and travel time from the user's location (or fallback address) to the above
            destination.</p>
    </div>

    <form method="post" action="options.php">
        <?php
                settings_fields('destiny_destination_settings');
                do_settings_sections('destiny-destination-settings');
                submit_button();
                ?>
    </form>

    <div class="card">
        <h2>Widget Usage</h2>
        <p>To use this widget:</p>
        <ol>
            <li>Edit any page with Elementor</li>
            <li>Search for "Destination Info" in the widget panel</li>
            <li>Drag the widget to your desired location</li>
            <li>Configure the settings in the widget panel</li>
            <li>Save and view your page</li>
        </ol>
    </div>
</div>
<?php
    }
}