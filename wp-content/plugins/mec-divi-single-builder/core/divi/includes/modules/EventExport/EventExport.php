<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventExport extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventExport';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventExport', $this->getScopes());
        });

        $this->name = esc_html__('Event Export', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'button' => array(
                'label'    => esc_html__( 'Button', 'et_builder' ),
                'selector' => '.mec-event-exporting a',
            ),
            'button_hover' => array(
                'label'    => esc_html__( 'Button Hover', 'et_builder' ),
                'selector' => '.mec-event-exporting a:hover',
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
            'fonts' => false,
			'button'                => array(
				'buttons' => array(
					'label' => esc_html__( 'Export Buttons', 'et_builder' ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .mec-event-exporting a",
                        'important' => 'all',
                    ),
                    'use_icon' => false,
                ),
			),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element} .mec-event-exporting",
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

        if (!$set['export_module_status'] && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if export module is set. In order for the widget in this page to be displayed correctly, please set export module for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/export-module/" target="_blank">' . __('How to set export module', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $eventt = $single->get_event_mec($e_id);
            if (!$eventt) {
                return;
            }
            $eventt = $eventt[0];
            $output = $mainClass->module('export.details', array('event' => $eventt));
        }

        return $output;
    }
}
new MDSB_EventExport;
