<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventTitle extends ET_Builder_Module
{


    public $slug       = 'MDSB_EventTitle';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventTitle', $this->getScopes());
        });

        $this->name = esc_html__('Event Title', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-title .mec-single-title',
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
        $scopes = [
            'title' => get_the_title(Admin::getLastEventID())
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
                        'main' => "{$this->main_css_element} .mec-single-event-title",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-title .mec-single-title",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '33px',
                    ),
                ),
            ),
            'background' => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-title",
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
        $output = '<div class="mec-event-meta">';
        $header_level = isset($attrs['header_level']) ? $attrs['header_level'] : 'h1';
        $tag = et_pb_process_header_level($header_level, 'h1');
        // Event Date
        if ($this->getScopes('is_date')) {
            $output .= '<div class="mec-single-event-title">
                <'. $tag . ' class="mec-single-title">' . get_the_title() . '</'. $tag .'>
            </div>';
        }
        $output .= '</div>';
        return $output;
    }
}
new MDSB_EventTitle;
