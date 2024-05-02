<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;
use Elementor\Post_CSS_File;
use Elementor\Core\Files\CSS\Post;

class MDSB_EventContent extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventContent';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventContent', $this->getScopes());
        });

        if (class_exists('\Elementor\Core\Files\CSS\Post')) {
            $css_file = new Post(Admin::getLastEventID());
            $css_file->enqueue();
        } elseif (class_exists('\Elementor\Post_CSS_File')) {
            $css_file = new Post_CSS_File(Admin::getLastEventID());
            $css_file->enqueue();
        }

        $this->name = esc_html__('Event Content', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'content' => array(
				'label'    => esc_html__( 'Content', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-description',
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
        $content = get_post(Admin::getLastEventID())->post_content;
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
                        'main' => "{$this->main_css_element} .mec-event-meta",
                        'important' => 'all',
                    ),
                    'label' => esc_html__('Wrapper', 'mec-divi-single-builder'),
                ),
                'content' => array(
                    'label'        => esc_html__('Content', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-meta .mec-single-event-description",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-description",
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
        if (class_exists('\Elementor\Core\Files\CSS\Post')) {
            $css_file = new Post(Admin::get_the_ID());
            $css_file->enqueue();
        } elseif (class_exists('\Elementor\Post_CSS_File')) {
            $css_file = new Post_CSS_File(Admin::get_the_ID());
            $css_file->enqueue();
        }


        $output = '<div class="mec-event-meta">';
            $output .= '<div class="mec-single-event-description mec-events-content">';
            $output .= apply_filters('the_content', get_the_content($e_id)) . '<br />';
            $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
}
new MDSB_EventContent;
