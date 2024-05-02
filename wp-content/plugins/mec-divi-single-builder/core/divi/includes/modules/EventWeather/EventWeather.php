<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventWeather extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventWeather';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventWeather', $this->getScopes());
        });

        $this->name = esc_html__('Event Weather', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-weather .mec-events-single-section-title',
			),
			'weather' => array(
				'label'    => esc_html__('Weather Name', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-weather dd',
			),
			'address' => array(
				'label'    => esc_html__( 'Address', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-weather .mec-events-address .mec-address',
			),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-weather i',
			),
			'image' => array(
				'label'    => esc_html__( 'Location Image', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-weather img',
			),
		);

    }

    public function get_fields()
    {
        return [
            'content' => is_front_page() ? 'Frontend' : 'Backend'
        ];
    }

    /**
     * Get Scopes
     *
     * @since     1.0.0
     */
    public function getScopes($scope = false)
    {
        if (!Admin::getLastEventID()) {
            return;
        }
        $content = $this->get_content(Admin::getLastEventID());
        $scopes = [
            'content' => $content
        ];

        if ($scope) {
            if (isset($scopes[$scope])) {
                return $scopes[$scope];
            }
        }

        return $scopes;
    }

    /**
     * get Advanced Fields
     *
     * @since     1.0.0
     */
    public function getAdvancedFields()
    {
        return array(
            'fonts' => array(
                'body'   => array(
                    'label' => esc_html__('Wrapper', 'mec-divi-single-builder'),
                    'css'   => array(
                        'main' => "{$this->main_css_element} .mec-event-meta .mec-single-event-weather",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                ),
                'title' => array(
                    'label'        => esc_html__('Title', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-weather .mec-events-single-section-title",
                        'important' => 'all',
                    ),
                ),
                'weather' => array(
                    'label'        => esc_html__('Location Name', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-weather dd",
                        'important' => 'all',
                    ),
                ),
                'address' => array(
                    'label'        => esc_html__('Address', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-weather .mec-events-address .mec-address",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-weather i, {$this->main_css_element} .mec-single-event-weather i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-weather",
                    'important' => 'all',
                ),
            ),
            'link_options'  => false,
			'text'          => false,
            'button'        => false,
            'header_level'  => false,
        );
    }

    /**
     * Render Alert widget output on the frontend.
     *
     *
     * @since 1.0.0
     * @access protected
     */
    public function render($attrs = false, $content = null, $render_slug = false)
    {
        $e_id = Admin::get_the_ID();
        $this->process_additional_options($render_slug);


        $output = '<div class="mec-event-meta">';
        // Event Location
        $output .= $this->get_content($e_id);
        $output .= '</div>';
        return $output;
    }

    /**
    * Get Content
    *
    * @since     1.0.0
    */
    public function get_content($e_id) {
        global $eventt;
        $single         = new \MEC_skin_single();
        $mainClass      = new \MEC_main();
        $set            = $mainClass->get_settings();
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $set['weather_module_status'] = isset($set['weather_module_status']) ? $set['weather_module_status'] : false;
        if (((!$set['weather_module_status']) or (!isset($eventt->data->locations[$eventt->data->meta['mec_location_id']])) && !is_singular('mec-events')) ) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if weather is set. In order for the widget in this page to be displayed correctly, please set location for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/weather-module/" target="_blank">' . __('How to set weather', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $output = $mainClass->module('weather.details', array('event' => $eventt));
        }
        // Event Weather

        return $output;
    }

}
new MDSB_EventWeather;
