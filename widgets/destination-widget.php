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
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'destiny-destination'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'fallback_source',
            [
                'label' => __('Fallback Source Address', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Stockholm, Sweden',
                'placeholder' => __('Enter fallback address if GPS not available', 'destiny-destination'),
                'description' => __('This address will be used if user location cannot be detected', 'destiny-destination'),
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

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'destiny-destination'),
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
                'name' => 'typography',
                'label' => __('Typography', 'destiny-destination'),
                'selector' => '{{WRAPPER}} .destiny-destination-widget',
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

        $this->add_control(
            'icon_size',
            [
                'label' => __('Directions Icon Size', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => ['min' => 10, 'max' => 64],
                    'em' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
                    'rem' => ['min' => 0.5, 'max' => 4, 'step' => 0.1],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 16,
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
        $icon_svg = '';
        if (!empty($settings['directions_icon']['url'])) {
            // If user uploaded an SVG, use <img>
            $icon_svg = '<img src="' . esc_url($settings['directions_icon']['url']) . '" alt="Directions" class="destiny-directions-icon" style="width:16px;height:16px;vertical-align:middle;" />';
        } else {
            // Default SVG
            $icon_svg = '<svg class="destiny-directions-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"><path fill="currentColor" d="M21.71 11.29l-9-9a.996.996 0 00-1.41 0l-9 9a.996.996 0 000 1.41l9 9c.39.39 1.02.39 1.41 0l9-9a.996.996 0 000-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z"/></svg>';
        }
?>
        <div class="destiny-destination-widget" id="<?php echo esc_attr($unique_id); ?>">
            <div class="destiny-loading">
                <span><?php echo __('Loading...', 'destiny-destination'); ?></span>
            </div>
            <div class="destiny-error">
                <span><?php echo __('Unable to get location', 'destiny-destination'); ?></span>
            </div>
            <div class="destiny-results">
                <div class="destiny-route-subtitle">
                    <span class="destiny-source-label"></span>
                    <svg class="destiny-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M10 17l5-5-5-5v10z"/></svg>
                    <span class="destiny-destination-label" title="Moränvägen 13, 186 40 Vallentuna, Sweden">Verkstad</span>
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
                var widget = $('#<?php echo esc_attr($unique_id); ?>');
                var fallbackSource = '<?php echo esc_js($settings['fallback_source']); ?>';
                var destination = 'Vallentuna bil och däckservice';
                var destinationAddress = 'Moränvägen 13, 186 40 Vallentuna, Sweden';

                function showLoading() {
                    widget.find('.destiny-loading').show();
                    widget.find('.destiny-error, .destiny-results').hide();
                }

                function showError() {
                    widget.find('.destiny-error').show();
                    widget.find('.destiny-loading, .destiny-results').hide();
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
                    widget.find('.destiny-results').show();
                    widget.find('.destiny-loading, .destiny-error').hide();

                    let timeText = data.duration_in_traffic || data.duration;
                    let distanceText = data.distance ? ' (' + data.distance + ')' : '';
                    let color = colorForTraffic(data.duration, data.duration_in_traffic);
                    let infoElem = widget.find('.destiny-info');
                    let timeElem = widget.find('.destiny-time-value');
                    let distanceElem = widget.find('.destiny-distance-value');
                    timeElem.css('color', color);
                    timeElem.text(timeText);
                    distanceElem.text(distanceText);

        
                }

                function updateDirectionsLink(origin) {
                    let url = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(destination) + '&origin=' + encodeURIComponent(origin);
                    widget.find('.destiny-directions-link').attr('href', url);
                }

                function setSourceLabel(source, usedFallback) {
                    let label = usedFallback ? source : 'Din position';
                    widget.find('.destiny-source-label').text(label);
                }

                function getDestinationInfo(origin, usedFallback) {
                    updateDirectionsLink(origin);
                    setSourceLabel(usedFallback ? fallbackSource : 'Din position', usedFallback);
                    $.ajax({
                        url: destiny_destination_ajax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'get_destination_info',
                            origin: origin,
                            nonce: destiny_destination_ajax.nonce
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                showResults(response);
                            } else {
                                showError();
                            }
                        },
                        error: function() {
                            showError();
                        }
                    });
                }

                showLoading();

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            var origin = position.coords.latitude + ',' + position.coords.longitude;
                            getDestinationInfo(origin, false);
                        },
                        function(error) {
                            getDestinationInfo(fallbackSource, true);
                        }
                    );
                } else {
                    getDestinationInfo(fallbackSource, true);
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
        <#
        var unique_id = 'destiny-destination-' + view.model.id;
        var icon = '';
        if (settings.directions_icon && settings.directions_icon.url) {
            icon = '<img src="' + settings.directions_icon.url + '" alt="Directions" class="destiny-directions-icon" style="width:16px;height:16px;vertical-align:middle;" />';
        } else {
            icon = `<svg class=\"destiny-directions-icon\" xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\"><path fill=\"currentColor\" d=\"M21.71 11.29l-9-9a.996.996 0 00-1.41 0l-9 9a.996.996 0 000 1.41l9 9c.39.39 1.02.39 1.41 0l9-9a.996.996 0 000-1.41zM14 14.5V12h-4v3H8v-4c0-.55.45-1 1-1h5V7.5l3.5 3.5-3.5 3.5z\"/></svg>`;
        }
        #>
        <div class="destiny-destination-widget" id="{{ unique_id }}">
            <div class="destiny-results">
                <div class="destiny-route-subtitle">
                    <span class="destiny-source-label">Din position</span>
                    <svg class="destiny-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M10 17l5-5-5-5v10z"/></svg>
                    <span class="destiny-destination-label" title="Moränvägen 13, 186 40 Vallentuna, Sweden">Verkstad</span>
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
