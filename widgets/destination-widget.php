<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Destination Widget
 */
class Destiny_Destination_Widget extends \Elementor\Widget_Base
{

    const DEFAULT_DESTINATION_NAME = 'Vallentuna bil och däckservice';
    const DEFAULT_DESTINATION_SHORT_NAME = 'Verkstad';
    const DEFAULT_DESTINATION_ADDRESS = 'Moränvägen 13, 186 40 Vallentuna, Sweden';
    const DEFAULT_FALLBACK_SOURCE = 'Stockholm, Sverige';
    /**
     * Get widget name.
     */
    public function get_name()
    {
        return 'destiny_destination';
    }

    /**
     * Get widget title.
     */
    public function get_title()
    {
        return __('Destination Info', 'destiny-destination');
    }

    /**
     * Get widget icon.
     */
    public function get_icon()
    {
        return 'eicon-google-maps';
    }

    /**
     * Get widget categories.
     */
    public function get_categories()
    {
        return ['general'];
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords()
    {
        return ['destination', 'distance', 'time', 'maps', 'directions'];
    }

    /**
     * Utility: Get a setting with fallback to default
     */
    private function get_setting_with_default($settings, $key, $default)
    {
        return !empty($settings[$key]) ? $settings[$key] : $default;
    }

    /**
     * Register widget controls.
     */
    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'content_section_source',
            [
                'label' => __('Source Address', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'fallback_source',
            [
                'label' => __('Fallback Source Address', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => self::DEFAULT_FALLBACK_SOURCE,
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
                'placeholder' => __('Enter fallback address if GPS not available', 'destiny-destination'),
                'description' => __('This address will be used if user location cannot be detected', 'destiny-destination'),
            ]
        );
        $this->end_controls_section();

        // Icon Section
        $this->start_controls_section(
            'content_section_destination',
            [
                'label' => __('Destination Info', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'destination_name',
            [
                'label' => __('Destination Name', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => self::DEFAULT_DESTINATION_NAME,
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => true,
                'placeholder' => __('Enter destination business name', 'destiny-destination'),
                'description' => __('The name of the destination business', 'destiny-destination'),
            ]
        );

        $this->add_control(
            'destination_short_name',
            [
                'label' => __('Destination Short Name', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => self::DEFAULT_DESTINATION_SHORT_NAME,
                'label_block' => true,
                'placeholder' => __('Enter short display name', 'destiny-destination'),
                'description' => __('Short name displayed in the widget (e.g., "Verkstad")', 'destiny-destination'),
            ]
        );

        $this->add_control(
            'destination_address',
            [
                'label' => __('Destination Address', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => self::DEFAULT_DESTINATION_ADDRESS,
                'placeholder' => __('Enter complete destination address', 'destiny-destination'),
                'description' => __('Full address for Google Maps API and tooltip display', 'destiny-destination'),
            ]
        );
        $this->end_controls_section();
        // Icon Section
        $this->start_controls_section(
            'icon_section',
            [
                'label' => __('Directions Icon', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'directions_icon',
            [
                'label' => __('Directions Icon', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => '', // Will use inline SVG as fallback
                ],
                'media_types' => ['svg'],
                'description' => __('Upload a custom SVG icon for the directions link, or leave empty for default.', 'destiny-destination'),
            ]
        );

        $this->end_controls_section();

        // Icon Section
        $this->start_controls_section(
            'content_section_cache',
            [
                'label' => __('Caching settings', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'enable_cache',
            [
                'label' => __('Enable Caching', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'destiny-destination'),
                'label_off' => __('No', 'destiny-destination'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => __('Cache API results to improve performance and reduce API calls', 'destiny-destination'),
            ]
        );

        $this->add_control(
            'cache_time',
            [
                'label' => __('Cache Duration (minutes)', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 1440, // 24 hours
                'step' => 1,
                'default' => 60,
                'description' => __('How long to cache results (1-1440 minutes)', 'destiny-destination'),
                'condition' => [
                    'enable_cache' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();


        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Text Style', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .destiny-destination-widget' => 'color: {{VALUE}}',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'label' => __('Typography', 'destiny-destination'),
                'selector' => '{{WRAPPER}} .destiny-destination-widget',
            ]
        );
        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section_subtitle',
            [
                'label' => __('Subheader Style', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'route_subtitle_color',
            [
                'label' => __('Route Subtitle Color', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#888',
                'selectors' => [
                    '{{WRAPPER}} .destiny-route-subtitle' => 'color: {{VALUE}};',
                ],
            ]
        );



        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typography',
                'label' => __('Route Subtitle Typography', 'destiny-destination'),
                'selector' => '{{WRAPPER}} .destiny-route-subtitle',
            ]
        );

        $this->end_controls_section();
        $this->start_controls_section(
            'style_section_icon',
            [
                'label' => __('Icon Style', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'icon_color',
            [
                'label' => __('Directions Icon Color', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#007cba',
                'selectors' => [
                    '{{WRAPPER}} .destiny-directions-icon' => 'color: {{VALUE}}; fill: {{VALUE}};',
                    '{{WRAPPER}} .destiny-directions-icon svg' => 'color: {{VALUE}}; fill: {{VALUE}};',
                    '{{WRAPPER}} .destiny-directions-icon path' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .destiny-directions-icon circle' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .destiny-directions-icon polygon' => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .destiny-directions-icon rect' => 'fill: {{VALUE}};',
                ],
                'description' => __('Controls the color of both default and uploaded SVG icons', 'destiny-destination'),
            ]
        );
        $this->add_control(
            'icon_size',
            [
                'label' => __('Directions Icon Size', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', '%'],
                'range' => [
                    'px' => ['min' => 10, 'max' => 64],
                    'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
                    'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
                    '%' => ['min' => 10, 'max' => 100],
                ],

                'selectors' => [
                    '{{WRAPPER}} .destiny-directions-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .destiny-directions-icon img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $unique_id = 'destiny-destination-' . $this->get_id();

        // Use utility for settings
        $destination_name = $this->get_setting_with_default($settings, 'destination_name', self::DEFAULT_DESTINATION_NAME);
        $destination_short_name = $this->get_setting_with_default($settings, 'destination_short_name', self::DEFAULT_DESTINATION_SHORT_NAME);
        $destination_address = $this->get_setting_with_default($settings, 'destination_address', self::DEFAULT_DESTINATION_ADDRESS);

        // Check if we're in Elementor editor mode
        $is_editor_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $widget_class = $is_editor_mode ? 'destiny-destination-widget is-results' : 'destiny-destination-widget is-loading';



        $icon_svg = '';
        if (!empty($settings['directions_icon']['url'])) {
            // Only allow SVGs for security
            $icon_url = esc_url($settings['directions_icon']['url']);
            if (strtolower(pathinfo($icon_url, PATHINFO_EXTENSION)) === 'svg') {
                // Get the file path from URL
                $upload_dir = wp_upload_dir();
                $file_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $icon_url);

                // Read SVG content if file exists and is readable
                if (file_exists($file_path) && is_readable($file_path)) {
                    $svg_content = file_get_contents($file_path);

                    // Basic security: ensure it's actually SVG content
                    if ($svg_content && strpos($svg_content, '<svg') !== false) {
                        // Add our class to the SVG
                        $svg_content = str_replace('<svg', '<svg class="destiny-directions-icon"', $svg_content);

                        // Remove hardcoded fill colors to allow CSS control
                        $svg_content = preg_replace('/fill\s*=\s*["\'][^"\']*["\']/', '', $svg_content);
                        $svg_content = preg_replace('/style\s*=\s*["\'][^"\']*fill\s*:[^;"\']*[;]?[^"\']*["\']/', '', $svg_content);

                        $icon_svg = $svg_content;
                    } else {
                        // Fallback to img tag if SVG content is invalid
                        $icon_svg = '<img src="' . $icon_url . '" alt="Directions" class="destiny-directions-icon" loading="lazy" />';
                    }
                } else {
                    // Fallback to img tag if file is not accessible
                    $icon_svg = '<img src="' . $icon_url . '" alt="Directions" class="destiny-directions-icon" loading="lazy" />';
                }
            } else {
                $icon_svg = '';
            }
        } else {
            // Default SVG
            $icon_svg = '<svg class="destiny-directions-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M21.71 11.29l-9-9a.996.996 0 00-1.41 0l-9 9a.996.996 0 000 1.41l9 9c.39.39 1.02.39 1.41 0l9-9a.996.996 0 000-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>';
        }
?>
<div class="<?php echo esc_attr($widget_class); ?>" id="<?php echo esc_attr($unique_id); ?>"
    data-fallback-source="<?php echo esc_attr($settings['fallback_source']); ?>"
    data-destination="<?php echo esc_attr($destination_name); ?>"
    data-destination-address="<?php echo esc_attr($destination_address); ?>"
    data-enable-cache="<?php echo esc_attr($settings['enable_cache'] === 'yes' ? 'yes' : 'no'); ?>"
    data-cache-time="<?php echo intval($settings['cache_time'] ?? 60); ?>">

    <div class="destiny-loading">
        <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
            style="width: 24px; height: 24px; display: block; margin: 10px auto;">
            <circle cx="4" cy="12" r="3" opacity="1">
                <animate id="spinner_qYjJ" begin="0;spinner_t4KZ.end-0.25s" attributeName="opacity" dur="0.75s"
                    values="1;.2" fill="freeze" />
            </circle>
            <circle cx="12" cy="12" r="3" opacity=".4">
                <animate begin="spinner_qYjJ.begin+0.15s" attributeName="opacity" dur="0.75s" values="1;.2"
                    fill="freeze" />
            </circle>
            <circle cx="20" cy="12" r="3" opacity=".3">
                <animate id="spinner_t4KZ" begin="spinner_qYjJ.begin+0.3s" attributeName="opacity" dur="0.75s"
                    values="1;.2" fill="freeze" />
            </circle>
        </svg>
    </div>

    <div class="destiny-error">
        <span>Unable to get location</span>
    </div>

    <div class="destiny-results">
        <div class="destiny-route-subtitle">
            <span class="destiny-source-label"><?php echo $is_editor_mode ? 'Din position' : ''; ?></span>
            <svg class="destiny-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24">
                <path fill="currentColor" d="M10 17l5-5-5-5v10z" />
            </svg>
            <span class="destiny-destination-label"
                data-tippy-content="<strong><?php echo esc_attr($destination_name); ?></strong><br><?php echo esc_attr($destination_address); ?>"
                title="<?php echo esc_attr($destination_address); ?>"><?php echo esc_html($destination_short_name); ?></span>
        </div>
        <div class="destiny-data-info">
            <span class="destiny-info">
                <span class="destiny-time-value" <?php echo $is_editor_mode ? ' style="color: green;"' : ''; ?>>15
                    min</span>
                <span class="destiny-distance-value"> (12.5 km)</span>
            </span>
            <a href="#" class="destiny-directions-link" target="_blank">
                <?php echo $icon_svg; ?>
            </a>
        </div>
    </div>
</div>
<?php
    }

    /**
     * Render widget output in the editor.
     */
    protected function content_template()
    {
    ?>
<# var unique_id='destiny-destination-' + view.model.id; var destination_name=settings.destination_name
    || 'Vallentuna bil och däckservice' ; var destination_short_name=settings.destination_short_name || 'Verkstad' ; var
    destination_address=settings.destination_address || 'Moränvägen 13, 186 40 Vallentuna, Sweden' ; var
    enable_cache=settings.enable_cache || 'yes' ; var cache_time=settings.cache_time || 60; var icon='' ; if
    (settings.directions_icon && settings.directions_icon.url) { icon='<img src="' + settings.directions_icon.url
    + '" alt="Directions" class="destiny-directions-icon" />' ; } else { icon=`<svg class=\"destiny-directions-icon\"
    xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\">
    <path fill=\"currentColor\" d=\"M21.71 11.29l-9-9a.996.996 0 00-1.41 0l-9 9a.996.996 0 000 1.41l9 9c.39.39 1.02.39
        1.41 0l9-9a.996.996 0 000-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z\" /></svg>`;
    }
    #>
    <div class="destiny-destination-widget" id="{{ unique_id }}">
        <div class="destiny-loading" style="display: none;">
            <span>Loading...</span>
        </div>
        <div class="destiny-error" style="display: none;">
            <span>Unable to get location</span>
        </div>
        <div class="destiny-results" style="display: block;">
            <div class="destiny-route-subtitle">
                <span class="destiny-source-label">Din position</span>
                <svg class="destiny-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                    viewBox="0 0 24 24">
                    <path fill="currentColor" d="M10 17l5-5-5-5v10z" />
                </svg>
                <span class="destiny-destination-label"
                    data-tippy-content="<strong>{{ destination_name }}</strong><br>{{ destination_address }}"
                    title="{{ destination_address }}">{{ destination_short_name }}</span>
            </div>
            <div class="destiny-data-info">
                <span class="destiny-info">
                    <span class="destiny-time-value" style="color:green;">15 min</span>
                    <span class="destiny-distance-value"> (12.5 km)</span>
                </span>
                <a href="#" class="destiny-directions-link" target="_blank">
                    {{{ icon }}}
                </a>
            </div>
        </div>
    </div>
    <?php
    }
}