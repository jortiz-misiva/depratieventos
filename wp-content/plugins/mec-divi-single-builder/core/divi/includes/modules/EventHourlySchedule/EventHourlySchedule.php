<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventHourlySchedule extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventHourlySchedule';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventHourlySchedule', $this->getScopes());
        });

        $this->name = esc_html__('Event Hourly Schedule', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-frontbox-title',
			),
			'days' => array(
				'label'    => esc_html__( 'Days', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-part',
			),
			'schedule_time' => array(
				'label'    => esc_html__( 'Schedule Time', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-time span',
			),
			'schedule_title' => array(
				'label'    => esc_html__( 'Schedule Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-title',
			),
            'schedule_description' => array(
				'label'    => esc_html__( 'Schedule Description', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-description',
			),
            'schedule_speaker_title' => array(
				'label'    => esc_html__( 'Schedule Speaker Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-speakers h6',
			),
            'schedule_speaker_name' => array(
				'label'    => esc_html__( 'Schedule Speaker Name', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-schedule .mec-schedule-speakers .mec-hourly-schedule-speaker-lightbox',
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
        if(!Admin::getLastEventID()) {
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
                        'main' => "{$this->main_css_element} .mec-event-schedule",
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
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-frontbox-title",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '22px',
                    ),
                ),
                'days' => array(
                    'label'        => esc_html__('Days', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-part",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '18px',
                    ),
                ),
                'schedule_time' => array(
                    'label'        => esc_html__('Schedule Time', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-time span",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '12px',
                    ),
                    'hide_text_align' => true,
                ),
                'schedule_title' => array(
                    'label'        => esc_html__('Schedule Title', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-title",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '13px',
                    ),
                    'hide_text_align' => true,
                ),
                'schedule_description' => array(
                    'label'        => esc_html__('Schedule Description', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-description",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '12px',
                    ),
                    'hide_text_align' => true,
                ),
                'schedule_speaker_title' => array(
                    'label'        => esc_html__('Schedule Speaker Title', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-speakers h6",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                    'hide_text_align' => true,
                ),
                'schedule_speaker_name' => array(
                    'label'        => esc_html__('Schedule Speaker Name', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-schedule .mec-schedule-speakers .mec-hourly-schedule-speaker-lightbox",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '12px',
                    ),
                    'hide_text_align' => true,
                ),

            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-event-schedule",
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
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $output = '';

        ob_start();
        // Event Hourly Schedule
        $single->display_hourly_schedules_widget($eventt);
        $output .= ob_get_clean();
        if(!$output && !is_singular('mec-events')) {
            $output .= '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if Hourly Schedule is set. In order for the widget in this page to be displayed correctly, please set Hourly Schedule for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/hourly-schedule/" target="_blank">' . __('How to set Hourly Schedule', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        }
        return $output;
    }

}
new MDSB_EventHourlySchedule;
