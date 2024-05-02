<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventFeaturedImage extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventFeaturedImage';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventFeaturedImage', $this->getScopes());
        });

        $this->name = esc_html__('Event Featured Image', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'image' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-event-image',
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
            'thumbnail' => get_the_post_thumbnail(Admin::getLastEventID()),
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
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element} .mec-events-event-image",
                    'important' => 'all',
                ),
            ),
            'link_options'  => false,
			'text'          => false,
            'button'        => false,
            'header_level'  => false,
            'fonts'         => false,
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
            $output .= '<div class="mec-events-event-image">';
            $output .= get_the_post_thumbnail($e_id);
            $output .= '</div>';
        $output .= '</div>';
        return $output;
    }
}
new MDSB_EventFeaturedImage;
