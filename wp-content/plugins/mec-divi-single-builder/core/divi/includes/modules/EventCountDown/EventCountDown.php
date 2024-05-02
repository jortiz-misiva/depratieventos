<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventCountDown extends ET_Builder_Module
{


    public $slug       = 'MDSB_EventCountDown';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventCountDown', $this->getScopes());
        });

        $this->name = esc_html__('Event Countdown', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'number' => array(
				'label'    => esc_html__( 'Number(s)', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-countdown .countdown-w span',
			),
			'label' => array(
				'label'    => esc_html__( 'Label', 'mec-divi-single-builder' ),
				'selector' => '.mec-countdown-details .countdown-w .clockdiv li p',
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
        global $eventt;
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $eventt = $single->get_event_mec(Admin::getLastEventID());
        if (!$eventt) {
            return;
        }
        $CountDown = $mainClass->module('countdown.details', array('event' => $eventt));
        $scopes = [
            'CountDown' => $CountDown
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
    public function getAdvancedFields() {
        return array(
            'fonts' => array(
                'body'   => array(
                    'label' => esc_html__('Wrapper', 'mec-divi-single-builder'),
                    'css'   => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-countdown",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                    'hide_text_align' => true,
                ),
                'number' => array(
                    'label'        => esc_html__('Number(s)', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-events-meta-group-countdown .countdown-w span",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                    'hide_text_align' => true,
                ),
                'label' => array(
                    'label'        => esc_html__('Label', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-countdown-details .countdown-w .clockdiv li p",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-events-meta-group-countdown",
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
    public function render($attrs=false, $content = null, $render_slug = false) {
        $this->process_additional_options($render_slug);
        global $eventt;
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $set            = $mainClass->get_settings();
        $eventt = $single->get_event_mec(Admin::get_the_ID());
        if (!$eventt) {
            return;
        }

        if (!$set['countdown_status'] && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if countdown is set. In order for the widget in this page to be displayed correctly, please set countdown for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/countdown-options/" target="_blank">' . __('How to set countdown', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $output = '<div class="mec-events-meta-group mec-events-meta-group-countdown">';
            $output .= $mainClass->module('countdown.details', array('event' => $eventt));
            $output .= '</div>';
        }
        return $output;
    }
}
new MDSB_EventCountDown;
