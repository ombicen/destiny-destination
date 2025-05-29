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
            'title',
            [
                'label' => __('Title', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Distance to Vallentuna bil och däckservice', 'destiny-destination'),
                'placeholder' => __('Enter your title', 'destiny-destination'),
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
            'show_distance',
            [
                'label' => __('Show Distance', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'destiny-destination'),
                'label_off' => __('Hide', 'destiny-destination'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_duration',
            [
                'label' => __('Show Duration', 'destiny-destination'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'destiny-destination'),
                'label_off' => __('Hide', 'destiny-destination'),
                'return_value' => 'yes',
                'default' => 'yes',
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

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'label' => __('Typography', 'destiny-destination'),
                'selector' => '{{WRAPPER}} .destiny-destination-widget',
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
?>
        <div class="destiny-destination-widget" id="<?php echo esc_attr($unique_id); ?>">
            <?php if (!empty($settings['title'])) : ?>
                <h3 class="destiny-destination-title"><?php echo esc_html($settings['title']); ?></h3>
            <?php endif; ?>

            <div class="destiny-destination-content">
                <div class="destiny-loading" style="display: none;">
                    <p><?php echo __('Getting location...', 'destiny-destination'); ?></p>
                </div>

                <div class="destiny-error" style="display: none;">
                    <p><?php echo __('Unable to get location information.', 'destiny-destination'); ?></p>
                </div>

                <div class="destiny-results" style="display: none;">
                    <?php if ($settings['show_distance'] === 'yes') : ?>
                        <div class="destiny-distance">
                            <span class="destiny-label"><?php echo __('Distance:', 'destiny-destination'); ?></span>
                            <span class="destiny-value" data-field="distance">-</span>
                        </div>
                    <?php endif; ?>

                    <?php if ($settings['show_duration'] === 'yes') : ?>
                        <div class="destiny-duration">
                            <span class="destiny-label"><?php echo __('Travel Time:', 'destiny-destination'); ?></span>
                            <span class="destiny-value" data-field="duration">-</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                var widget = $('#<?php echo esc_attr($unique_id); ?>');
                var fallbackSource = '<?php echo esc_js($settings['fallback_source']); ?>';

                function showLoading() {
                    widget.find('.destiny-loading').show();
                    widget.find('.destiny-error, .destiny-results').hide();
                }

                function showError() {
                    widget.find('.destiny-error').show();
                    widget.find('.destiny-loading, .destiny-results').hide();
                }

                function showResults(data) {
                    widget.find('.destiny-results').show();
                    widget.find('.destiny-loading, .destiny-error').hide();

                    if (data.distance) {
                        widget.find('[data-field="distance"]').text(data.distance);
                    }
                    if (data.duration) {
                        widget.find('[data-field="duration"]').text(data.duration);
                    }
                }

                function getDestinationInfo(origin) {
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

                // Try to get user's current location
                showLoading();

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            var origin = position.coords.latitude + ',' + position.coords.longitude;
                            getDestinationInfo(origin);
                        },
                        function(error) {
                            // Fallback to predefined source
                            getDestinationInfo(fallbackSource);
                        }
                    );
                } else {
                    // Geolocation not supported, use fallback
                    getDestinationInfo(fallbackSource);
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
            var unique_id='destiny-destination-' + view.model.id;
            #>
            <div class="destiny-destination-widget" id="{{ unique_id }}">
                <# if ( settings.title ) { #>
                    <h3 class="destiny-destination-title">{{ settings.title }}</h3>
                    <# } #>

                        <div class="destiny-destination-content">
                            <div class="destiny-results">
                                <# if ( settings.show_distance==='yes' ) { #>
                                    <div class="destiny-distance">
                                        <span class="destiny-label"><?php echo __('Distance:', 'destiny-destination'); ?></span>
                                        <span class="destiny-value">12.5 km</span>
                                    </div>
                                    <# } #>

                                        <# if ( settings.show_duration==='yes' ) { #>
                                            <div class="destiny-duration">
                                                <span class="destiny-label"><?php echo __('Travel Time:', 'destiny-destination'); ?></span>
                                                <span class="destiny-value">15 mins</span>
                                            </div>
                                            <# } #>
                            </div>
                        </div>
            </div>
    <?php
    }
}
