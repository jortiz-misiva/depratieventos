<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventCancellation extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventCancellation';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventCancellation', $this->getScopes());
        });

        $this->name = esc_html__('Event Cancellation', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Cancellation Reason', 'mec-divi-single-builder' ),
				'selector' => '.mec-cancellation-reason span',
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
                        'main' => "{$this->main_css_element} .mec-cancellation-reason",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                ),
                'title' => array(
                    'label'        => esc_html__('Cancellation Reason', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-cancellation-reason span",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background' => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-cancellation-reason",
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
        $display_reason = get_post_meta($e_id, 'mec_display_cancellation_reason_in_single_page', true);
        if (!$display_reason && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if cancellation reason is set. In order for the widget in this page to be displayed correctly, please set cancellation reason for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            // $output .= '<a href="#" target="_blank">' . __('Cancellation Reason', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $output = '<div class="mec-event-meta">';
            $output .= $this->get_content($e_id);
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Get Content
     *
     * @since     1.0.0
     */
    public function get_content($e_id)
    {
        $mainClass      = new \MEC_main();
        $display_reason = get_post_meta($e_id, 'mec_display_cancellation_reason_in_single_page', true);
        if(!$display_reason && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if cancellation reason is set. In order for the widget in this page to be displayed correctly, please set cancellation reason for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            // $output .= '<a href="#" target="_blank">' . __('Cancellation Reason', 'mec-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $output = $mainClass->display_cancellation_reason($e_id, $display_reason);
            if(!$output && !is_singular('mec-events')) {
                $output = '<div class="mec-content-notification">';
                $output .= '<p>';
                $output .= '<span>';
                $output .= __('This widget is displayed if cancellation reason is set. In order for the widget in this page to be displayed correctly, please set cancellation reason for your last event.', 'mec-divi-single-builder');
                $output .= '</span>';
                // $output .= '<a href="#" target="_blank">' . __('Cancellation Reason', 'mec-single-builder') . ' </a>';
                $output .= '</p>';
                $output .= '</div>';
            }
        }
        return $output;
    }
}
new MDSB_EventCancellation;
