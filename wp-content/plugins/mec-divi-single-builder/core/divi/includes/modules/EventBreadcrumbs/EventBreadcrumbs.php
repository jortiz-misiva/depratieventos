<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventBreadcrumbs extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventBreadcrumbs';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventBreadcrumbs', $this->getScopes());
        });

        $this->name = esc_html__('Event Breadcrumbs', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'current' => array(
				'label'    => esc_html__( 'Current Page', 'mec-divi-single-builder' ),
				'selector' => '.mec-breadcrumbs .mec-current',
            ),
			'link' => array(
				'label'    => esc_html__( 'Link', 'mec-divi-single-builder' ),
				'selector' => '.mec-breadcrumbs a',
            ),
			'link_hover' => array(
				'label'    => esc_html__( 'Link Hover', 'mec-divi-single-builder' ),
				'selector' => '.mec-breadcrumbs a:hover',
            ),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-breadcrumbs i',
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
                        'main' => "{$this->main_css_element} .mec-breadcrumbs",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                ),
                'current' => array(
                    'label'        => esc_html__('Current Page', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-breadcrumbs .mec-current",
                        'important' => 'all',
                    ),
                ),
                'link' => array(
                    'label'        => esc_html__('Link', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-breadcrumbs a",
                        'important' => 'all',
                    ),
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Link Hover', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-breadcrumbs a:hover",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'        => esc_html__('Arrow Icon', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-breadcrumbs i",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-breadcrumbs",
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
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $set            = $mainClass->get_settings();
        $output = '';

        if (!$set['breadcrumbs'] && !is_singular('mec-events')) {
            $output .= '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if breadcrumbs is set. In order for the widget in this page to be displayed correctly, please set breadcrumbs for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/event-detailssingle-event-page/" target="_blank">' . __('How to set breadcrumbs', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            ob_start();
            echo '<div class="mec-breadcrumbs">';
            $single->display_breadcrumb_widget($e_id);
            echo '</div>';
            $output .= ob_get_clean();
        }

        return $output;
    }
}
new MDSB_EventBreadcrumbs;
