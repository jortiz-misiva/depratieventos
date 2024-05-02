<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventLocalTime extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventLocalTime';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventLocalTime', $this->getScopes());
        });

        $this->name = esc_html__('Event Local Time', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-local-time-details h3',
            ),
            'icon' => array(
                'label'    => esc_html__( 'icon', 'et_builder' ),
                'selector' => '.mec-local-time-details i',
            ),
            'details' => array(
                'label'    => esc_html__( 'Details Label', 'et_builder' ),
                'selector' => '.mec-local-time-details li',
            ),
            'value' => array(
                'label'    => esc_html__( 'Details Value', 'et_builder' ),
                'selector' => '.mec-local-time-details li span',
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
                        'main' => "{$this->main_css_element} .mec-local-time-details",
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
                        'main'      => "{$this->main_css_element} .mec-local-time-details h3",
                        'important' => 'all',
                    ),
                ),
                'details' => array(
                    'label'        => esc_html__('Details Label', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-local-time-details li",
                        'important' => 'all',
                    ),
                ),
                'value' => array(
                    'label'        => esc_html__('Details Value', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-local-time-details li span",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'        => esc_html__('Icon', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-local-time-details i:before",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-local-time-details",
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
        $single         = new \MEC_skin_single();
        $mainClass      = new \MEC_main();
        $set            = $mainClass->get_settings();
        $set['local_time_module_status'] = isset($set['local_time_module_status']) ? $set['local_time_module_status'] : false;
        if (!$set['local_time_module_status'] && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if label is set. In order for the widget in this page to be displayed correctly, please set QR code module for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/qr-code-module/" target="_blank">' . __('How to set QR code module', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $eventt = $single->get_event_mec($e_id);
            if (!$eventt) {
                return;
            }
            $eventt = $eventt[0];
            $output = '<div class="mec-event-meta mec-local-time-details mec-frontbox">';
            $output .= $mainClass->module('local-time.details', array('event' => $eventt));
            $output .= '</div>';
        }
        return $output;
    }
}
new MDSB_EventLocalTime;
