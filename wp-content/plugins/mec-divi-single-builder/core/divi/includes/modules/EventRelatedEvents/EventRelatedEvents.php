<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventRelatedEvents extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventRelatedEvents';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventRelatedEvents', $this->getScopes());
        });

        $this->name = esc_html__('Event Related Events', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-related-events-wrap h3',
            ),
            'shape' => array(
                'label'    => esc_html__( 'shape', 'et_builder' ),
                'selector' => '.mec-related-events-wrap h3.mec-rec-events-title:before',
            ),
            'content' => array(
                'label'    => esc_html__( 'Item Content Wrapper', 'et_builder' ),
                'selector' => '.mec-related-events-wrap .mec-related-event-post .mec-related-event-content',
            ),
            'image' => array(
                'label'    => esc_html__( 'Image', 'et_builder' ),
                'selector' => '.mec-related-events-wrap .mec-related-event-post img',
            ),
            'date' => array(
                'label'    => esc_html__( 'Date', 'et_builder' ),
                'selector' => '.mec-related-events-wrap .mec-related-event-post .mec-related-event-content span',
            ),
            'link' => array(
                'label'    => esc_html__( 'Event Link', 'et_builder' ),
                'selector' => '.mec-related-events-wrap .mec-related-event-post .mec-related-event-content h5 a',
            ),
            'link_hover' => array(
                'label'    => esc_html__( 'Event Link Hover', 'et_builder' ),
                'selector' => '.mec-related-events-wrap .mec-related-event-post .mec-related-event-content h5 a:hover',
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
                        'main' => "{$this->main_css_element} .mec-related-events-wrap",
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
                        'main'      => "{$this->main_css_element} .mec-related-events-wrap h3",
                        'important' => 'all',
                    ),
                ),
                'item' => array(
                    'label'        => esc_html__('Item Wrapper', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-related-events-wrap .mec-related-event-post",
                        'important' => 'all',
                    ),
                ),
                'date' => array(
                    'label'        => esc_html__('Date', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-related-events-wrap .mec-related-event-post .mec-related-event-content span",
                        'important' => 'all',
                    ),
                ),
                'link' => array(
                    'label'        => esc_html__('Event Link', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-related-events-wrap .mec-related-event-post .mec-related-event-content h5 a",
                        'important' => 'all',
                    ),
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Event Link Hover', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-related-events-wrap .mec-related-event-post .mec-related-event-content h5 a:hover",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-related-events-wrap",
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
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $set            = $mainClass->get_settings();
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        // Event Related Events
        ob_start();
        if (!$set['related_events'] && !is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if related events is set. In order for the widget in this page to be displayed correctly, please set Related Event for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/related-events/" target="_blank">' . __('How to set related events', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        } else {
            $single->display_related_posts_widget($eventt->ID);
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventRelatedEvents;
