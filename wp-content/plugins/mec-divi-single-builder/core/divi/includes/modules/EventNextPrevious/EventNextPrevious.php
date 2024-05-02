<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventNextPrevious extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventNextPrevious';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventNextPrevious', $this->getScopes());
        });

        $this->name = esc_html__('Event Next Previous', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-event-meta h3',
            ),
            'icon' => array(
                'label'    => esc_html__( 'Details Icon', 'et_builder' ),
                'selector' => '.mec-next-event-details i:before',
            ),
            'details' => array(
                'label'    => esc_html__( 'Details Title', 'et_builder' ),
                'selector' => '.mec-event-meta h6',
            ),
            'content' => array(
                'label'    => esc_html__( 'Details Title', 'et_builder' ),
                'selector' => '.mec-next-event-details .mec-events-abbr, .mec-next-event-details .mec-events-abbr span',
            ),
            'button' => array(
                'label'    => esc_html__( 'Button', 'et_builder' ),
                'selector' => '.mec-next-event-details a',
            ),
            'button_hover' => array(
                'label'    => esc_html__( 'Button Hover', 'et_builder' ),
                'selector' => '.mec-next-event-details a:hover',
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
                    'label' => esc_html__('Wrapper', 'et_builder'),
                    'css'   => array(
                        'main' => "{$this->main_css_element} .mec-next-event-details",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                ),
                'title' => array(
                    'label'        => esc_html__('Title', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-next-event-details h3",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'        => esc_html__('Details Icon', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-next-event-details i:before",
                        'important' => 'all',
                    ),
                ),
                'detail' => array(
                    'label'        => esc_html__('Detail Title', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-next-event-details h6",
                        'important' => 'all',
                    ),
                ),
                'content' => array(
                    'label'        => esc_html__('Detail Content', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-next-event-details .mec-events-abbr, {$this->main_css_element} .mec-next-event-details .mec-events-abbr span",
                        'important' => 'all',
                    ),
                ),
            ),
			'button'                => array(
				'button' => array(
					'label' => esc_html__( 'Register Button', 'et_builder' ),
                    'css' => array(
                        'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-next-event-details a",
                        'important' => 'all',
                    ),
                    'use_icon' => false,
                ),
			),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-next-event-details",
                    'important' => 'all',
                ),
            ),
            'link_options'  => false,
            'text'          => false,
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
        $output .= $this->get_content($e_id);
        $output .= '</div>';
        return $output;
    }

    /**
     * Get Content
     *
     * @since     1.0.0
     */
    public function get_content($e_id)
    {
        global $eventt;
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $set            = $mainClass->get_settings();
        $set['next_event_module_status'] = isset($set['next_event_module_status']) ? $set['next_event_module_status'] : false;
        if (!$set['next_event_module_status'] && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if next events module is set. In order for the widget in this page to be displayed correctly, please set next events module for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/next-event-module/" target="_blank">' . __('How to set next events module', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $eventt = $single->get_event_mec($e_id);
            if (!$eventt) {
                return;
            }
            $eventt = $eventt[0];
            $output = $mainClass->module('next-event.details', array('event' => $eventt));
        }

        return $output;
    }
}
new MDSB_EventNextPrevious;
