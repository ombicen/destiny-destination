<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Destination Widget
 */
class Destiny_Destination_Widget extends \Elementor\Widget_Base
{

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
                'default' => 'Stockholm, Sverige',
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
                'default' => 'Vallentuna bil och däckservice',
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
                'default' => 'Verkstad',
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
                'default' => 'Moränvägen 13, 186 40 Vallentuna, Sweden',
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
                ],
                'condition' => [
                    'directions_icon[url]' => '', // Only show for default SVG
                ],
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

        // Get destination settings
        $destination_name = !empty($settings['destination_name']) ? $settings['destination_name'] : 'Vallentuna bil och däckservice';
        $destination_short_name = !empty($settings['destination_short_name']) ? $settings['destination_short_name'] : 'Verkstad';
        $destination_address = !empty($settings['destination_address']) ? $settings['destination_address'] : 'Moränvägen 13, 186 40 Vallentuna, Sweden';

        $icon_svg = '';
        if (!empty($settings['directions_icon']['url'])) {
            // If user uploaded an SVG, use <img>
            $icon_svg = '<img src="' . esc_url($settings['directions_icon']['url']) . '" alt="Directions" class="destiny-directions-icon"  />';
        } else {
            // Default SVG
            $icon_svg = '<svg class="destiny-directions-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 11.29l-9-9a.996.996 0 00-1.41 0l-9 9a.996.996 0 000 1.41l9 9c.39.39 1.02.39 1.41 0l9-9a.996.996 0 000-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>';
        }
?>
        <div class="destiny-destination-widget" id="<?php echo esc_attr($unique_id); ?>">
            <div class="destiny-loading" style="display: block;">
                <span><?php echo __('Loading...', 'destiny-destination'); ?></span>
            </div>
            <div class="destiny-error" style="display: none;">
                <span><?php echo __('Unable to get location', 'destiny-destination'); ?></span>
            </div>
            <div class="destiny-results" style="display: none;">
                <div class="destiny-route-subtitle">
                    <span class="destiny-source-label"></span>
                    <svg class="destiny-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M10 17l5-5-5-5v10z" />
                    </svg>
                    <span class="destiny-destination-label"
                        data-tippy-content="<strong><?php echo esc_attr($destination_name); ?></strong><br><?php echo esc_attr($destination_address); ?>"
                        title="<?php echo esc_attr($destination_address); ?>"><?php echo esc_html($destination_short_name); ?></span>
                </div>
                <div class="destiny-data-info">
                    <span class="destiny-info">
                        <span class="destiny-time-value">15 min</span>
                        <span class="destiny-distance-value"> (12.5 km)</span>
                    </span>
                    <a href="#" class="destiny-directions-link" target="_blank">
                        <?php echo $icon_svg; ?>
                    </a>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Global state to prevent multiple requests across all widget instances
                window.destinyDestinationGlobal = window.destinyDestinationGlobal || {
                    initialized: false,
                    locationPromise: null,
                    dataPromise: null,
                    cachedResponse: null,
                    pendingWidgets: [],
                    finalOrigin: null,
                    usedFallback: false
                };

                var global = window.destinyDestinationGlobal;
                var widget = $('#<?php echo esc_attr($unique_id); ?>');
                var fallbackSource = '<?php echo esc_js($settings['fallback_source']); ?>';
                var destination = '<?php echo esc_js($destination_name); ?>';
                var destinationAddress = '<?php echo esc_js($destination_address); ?>';
                var enableCache = '<?php echo esc_js($settings['enable_cache'] === 'yes' ? 'yes' : 'no'); ?>';
                var cacheTime = <?php echo intval($settings['cache_time'] ?? 60); ?>;

                console.log('Widget initializing:', '<?php echo esc_attr($unique_id); ?>');
                console.log('Cache settings - enableCache:', enableCache, 'cacheTime:', cacheTime);
                console.log('Fallback source:', fallbackSource);
                console.log('Global state check - initialized:', global.initialized, 'locationPromise exists:', !!global
                    .locationPromise, 'dataPromise exists:', !!global.dataPromise);

                function showLoading() {
                    console.log('DISPLAY: Showing loading state for widget:', '<?php echo esc_attr($unique_id); ?>');
                    widget.find('.destiny-loading').css('display', 'block');
                    widget.find('.destiny-error, .destiny-results').css('display', 'none');
                }

                function showError() {
                    console.log('DISPLAY: Showing error state for widget:', '<?php echo esc_attr($unique_id); ?>');
                    console.log('ERROR TRACE: Called at', new Date().toISOString());
                    console.trace('ERROR CALL STACK');
                    widget.find('.destiny-error').css('display', 'block');
                    widget.find('.destiny-loading, .destiny-results').css('display', 'none');
                }

                function colorForTraffic(normal, traffic) {
                    // Convert "12 min" to minutes
                    function toMinutes(str) {
                        if (!str) return 0;
                        var m = str.match(/(\d+)(?:\s*min)?/);
                        return m ? parseInt(m[1], 10) : 0;
                    }
                    var n = toMinutes(normal);
                    var t = toMinutes(traffic);
                    if (n === 0 || t === 0) return '';
                    var ratio = t / n;
                    if (ratio <= 1.15) return 'green'; // up to 15% longer
                    if (ratio <= 1.4) return 'orange'; // up to 40% longer
                    return 'red'; // much longer
                }

                function showResults(data) {
                    console.log('DISPLAY: Showing results for widget:', '<?php echo esc_attr($unique_id); ?>', 'Data:',
                        data);

                    // More aggressive DOM manipulation to prevent override
                    widget.find('.destiny-results').show().css('display', 'block !important');
                    widget.find('.destiny-loading, .destiny-error').hide().css('display', 'none !important');

                    // Check what's actually visible after DOM manipulation
                    setTimeout(function() {
                        console.log('DOM CHECK: Results visible:', widget.find('.destiny-results').is(':visible'));
                        console.log('DOM CHECK: Error visible:', widget.find('.destiny-error').is(':visible'));
                        console.log('DOM CHECK: Loading visible:', widget.find('.destiny-loading').is(':visible'));

                        // If results are still not visible, force it again
                        if (!widget.find('.destiny-results').is(':visible')) {
                            console.log('FORCE FIX: Results not visible, forcing display');
                            widget.find('.destiny-results').attr('style', 'display: block !important');
                            widget.find('.destiny-error, .destiny-loading').attr('style',
                                'display: none !important');
                        }
                    }, 100);

                    let timeText = data.duration_in_traffic || data.duration;
                    let distanceText = data.distance ? ' (' + data.distance + ')' : '';
                    let color = colorForTraffic(data.duration, data.duration_in_traffic);
                    let timeElem = widget.find('.destiny-time-value');
                    let distanceElem = widget.find('.destiny-distance-value');
                    timeElem.css('color', color);
                    timeElem.text(timeText);
                    distanceElem.text(distanceText);
                }

                function updateDirectionsLink(origin) {
                    let url = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(destination) +
                        '&origin=' + encodeURIComponent(origin);
                    widget.find('.destiny-directions-link').attr('href', url);
                }

                function setSourceLabel(usedFallback) {
                    let label = usedFallback ? fallbackSource : 'Din position';
                    widget.find('.destiny-source-label').text(label);
                }

                // Convert geolocation to promise - only called once globally
                function getLocationPromiseGlobal() {
                    if (global.locationPromise) {
                        console.log('Reusing existing location promise');
                        return global.locationPromise;
                    }

                    console.log('Creating new location promise');
                    global.locationPromise = new Promise((resolve, reject) => {
                        if (!navigator.geolocation) {
                            // Fallback immediately if geolocation is not supported
                            resolve({
                                origin: fallbackSource,
                                usedFallback: true
                            });
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            position => {
                                const origin = position.coords.latitude + ',' + position.coords.longitude;
                                console.log('GPS location success:', origin);
                                resolve({
                                    origin,
                                    usedFallback: false
                                });
                            },
                            error => {
                                console.log('GPS location failed:', error.message);
                                // Fallback to fallbackSource if geolocation fails
                                resolve({
                                    origin: fallbackSource,
                                    usedFallback: true
                                });
                            }, {
                                timeout: 10000,
                                maximumAge: 300000,
                                enableHighAccuracy: false
                            }
                        );
                    });

                    return global.locationPromise;
                }

                // Global function to get destination info - only called once
                function getDestinationDataGlobal(origin, usedFallback) {
                    if (global.dataPromise) {
                        console.log('AJAX REUSE: Reusing existing data promise for origin:', origin);
                        return global.dataPromise;
                    }

                    console.log('AJAX NEW: Making NEW AJAX request to:', destiny_destination_ajax.ajax_url, 'with origin:',
                        origin);
                    console.log('AJAX NEW: Used fallback:', usedFallback);

                    global.dataPromise = $.ajax({
                        url: destiny_destination_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_destination_info',
                            origin: origin,
                            widget_id: '<?php echo esc_attr($unique_id); ?>',
                            nonce: destiny_destination_ajax.nonce
                        }
                    }).done(function(response) {
                        console.log('AJAX response received:', response);
                        if (response.status === 'success') {
                            global.cachedResponse = response;
                            global.finalOrigin = origin;
                            global.usedFallback = usedFallback;

                            // Update all pending widgets
                            global.pendingWidgets.forEach(function(widgetData) {
                                widgetData.updateWidget(response);
                            });
                            global.pendingWidgets = [];
                        } else {
                            console.log('API returned error status');
                            global.pendingWidgets.forEach(function(widgetData) {
                                widgetData.showError();
                            });
                            global.pendingWidgets = [];
                        }
                    }).fail(function(xhr, status, error) {
                        console.log('AJAX request failed:', error);
                        global.pendingWidgets.forEach(function(widgetData) {
                            widgetData.showError();
                        });
                        global.pendingWidgets = [];
                    });

                    return global.dataPromise;
                }

                // Initialize the global process only once
                function initializeGlobalProcess() {
                    if (global.initialized) {
                        console.log('Global process already initialized, skipping');
                        return Promise.resolve();
                    }

                    console.log('Starting global initialization process');
                    global.initialized = true;

                    return getLocationPromiseGlobal()
                        .then(({
                            origin,
                            usedFallback
                        }) => {
                            if (usedFallback) {
                                console.log('FALLBACK PATH: Using fallback address:', origin);
                            } else {
                                console.log('GPS SUCCESS PATH: Got GPS coordinates:', origin);
                            }
                            console.log('Used fallback:', usedFallback);
                            return getDestinationDataGlobal(origin, usedFallback);
                        });
                }

                // Widget-specific initialization
                function initializeWidget() {
                    console.log('WIDGET INIT: Initializing widget:', '<?php echo esc_attr($unique_id); ?>');

                    // If we already have cached data, use it immediately without showing loading
                    if (global.cachedResponse && global.finalOrigin !== null) {
                        console.log('WIDGET INIT: Using cached global data immediately for widget:',
                            '<?php echo esc_attr($unique_id); ?>');
                        updateDirectionsLink(global.finalOrigin);
                        setSourceLabel(global.usedFallback);
                        showResults(global.cachedResponse);
                        return;
                    }

                    // Only show loading if we don't have cached data
                    showLoading();

                    console.log('WIDGET INIT: No cached data, adding to pending widgets list');
                    // Add this widget to the pending list
                    global.pendingWidgets.push({
                        widgetId: '<?php echo esc_attr($unique_id); ?>',
                        updateWidget: function(response) {
                            console.log('PENDING UPDATE: Updating widget from pending list:',
                                '<?php echo esc_attr($unique_id); ?>');
                            updateDirectionsLink(global.finalOrigin);
                            setSourceLabel(global.usedFallback);
                            showResults(response);
                        },
                        showError: function() {
                            console.log('PENDING ERROR: Showing error from pending list for widget:',
                                '<?php echo esc_attr($unique_id); ?>');
                            showError();
                        }
                    });

                    // Start the global process
                    initializeGlobalProcess();
                }

                // Initialize this widget instance
                initializeWidget();

                // Initialize Tippy.js tooltips
                if (typeof tippy !== 'undefined') {
                    tippy('.destiny-destination-label', {
                        allowHTML: true,
                        theme: 'light-border',
                        placement: 'top',
                        animation: 'fade',
                        duration: [200, 150],
                        maxWidth: 250,
                        delay: [0, 20000],
                        interactive: true,
                        hideOnClick: true
                    });
                }
            });
        </script>
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
