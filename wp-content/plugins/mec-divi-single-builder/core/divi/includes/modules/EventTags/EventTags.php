<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventTags extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventTags';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventTags', $this->getScopes());
        });

        $this->name = esc_html__('Event Tags', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
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
                    'css'   => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-tags",
                        'important' => 'all',
                    ),
                    'label' => esc_html__('Wrapper', 'mec-divi-single-builder'),
                ),
                'header' => array(
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-events-meta-group-tags, {$this->main_css_element} .mec-events-meta-group-tags .mec-events-single-section-title",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '18px',
                    ),
                    'label'        => esc_html__('MEC Location', 'mec-divi-single-builder'),
                ),
                'header_level' => false,
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-events-meta-group-tags",
                    'important' => 'all',
                ),
            ),
            'link_options' => false,
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
        ob_start();
        $posttags = get_the_tags($e_id);
        if(!$posttags && !is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if tags is set. In order for the widget in this page to be displayed correctly, please set tags for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/tags/" target="_blank">' . __('How to set tags', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        } else {
            echo '<div class="mec-events-meta-group mec-events-meta-group-tags">';
            the_tags(__('Tags: ', 'mec-divi-single-builder'), ', ', '<br />');
            echo '</div>';
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventTags;
