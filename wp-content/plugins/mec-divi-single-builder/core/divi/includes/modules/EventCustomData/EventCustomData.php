<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventCustomData extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventCustomData';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventCustomData', $this->getScopes());
        });

        $this->name = esc_html__('Event Custom Data', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'item' => array(
				'label'    => esc_html__( 'Each Item', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item',
            ),
			'label' => array(
				'label'    => esc_html__( 'Labels', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-name',
            ),
			'value' => array(
				'label'    => esc_html__( 'Value', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value',
            ),
			'link' => array(
				'label'    => esc_html__( 'Link Value', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value a',
            ),
			'link_hover' => array(
				'label'    => esc_html__( 'Link Hover Value', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value a:hover',
            ),
			'lastitem' => array(
				'label'    => esc_html__( 'Last Item', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-data-field-items .mec-event-data-field-item:last-child',
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
                        'main' => "{$this->main_css_element} .mec-event-data-field-items",
                        'important' => 'all',
                    ),
                    'hide_font'             => true,
                    'hide_font_size'        => true,
                    'hide_letter_spacing'   => true,
                    'hide_line_height'      => true,
                    'hide_text_shadow'      => true,
                ),
                'label' => array(
                    'label'        => esc_html__('Label', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-name",
                        'important' => 'all',
                    ),
                ),
                'value' => array(
                    'label'        => esc_html__('Value', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value",
                        'important' => 'all',
                    ),
                ),
                'link' => array(
                    'label'        => esc_html__('Link Value', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value a",
                        'important' => 'all',
                    ),
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Link Hover Value', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-data-field-items .mec-event-data-field-item .mec-event-data-field-value a:hover",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-event-data-field-items",
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
        $output = '';
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        ob_start();
        if (!$set['display_event_fields'] && !is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if event data is set. In order for the widget in this page to be displayed correctly, please set event data for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/custom-fields/" target="_blank">' . __('How to set Custom Fields', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        } else {
            $single->display_data_fields($eventt);
        }
        $output .= ob_get_clean();

        return $output;
    }
}
new MDSB_EventCustomData;
