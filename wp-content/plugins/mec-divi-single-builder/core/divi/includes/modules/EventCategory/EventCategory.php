<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventCategory extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventCategory';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventCategory', $this->getScopes());
        });

        $this->name = esc_html__('Event Category', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-category dt',
            ),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-category i',
            ),
			'cat_icon' => array(
				'label'    => esc_html__( 'Category Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-category dd i',
            ),
			'link' => array(
				'label'    => esc_html__( 'Link Value', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-category a',
            ),
			'link_hover' => array(
				'label'    => esc_html__( 'Link Hover Value', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-category a:hover',
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
                        'main' => "{$this->main_css_element} .mec-single-event-category",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-category dt",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-category i, {$this->main_css_element} .mec-single-event-category i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'cat_icon' => array(
                    'label'      => esc_html__( 'Category Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-category dd i, {$this->main_css_element} .mec-single-event-category dd i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'link' => array(
                    'label'        => esc_html__('Link', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-category a",
                        'important' => 'all',
                    ),
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Link Hover', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-category a:hover",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-category",
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
        $output = '';
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        ob_start();
        // Event Categories
        if (isset($eventt->data->categories)) {
            echo '<div class="mec-single-event-category mec-event-meta">';
?>
            <i class="mec-sl-folder"></i>
            <dt><?php echo $mainClass->m('taxonomy_categories', __('Category', 'mec-divi-single-builder')); ?></dt>
<?php
            foreach ($eventt->data->categories as $category) {
                $icon = get_metadata('term', $category['id'], 'mec_cat_icon', true);
                $icon = isset($icon) && $icon != '' ? '<i class="' . $icon . ' mec-color"></i>' : '<i class="mec-fa-angle-right"></i>';
                echo '<dd class="mec-events-event-categories">
					<a href="' . get_term_link($category['id'], 'mec_category') . '" class="mec-color-hover" rel="tag">' . $icon . $category['name'] . '</a></dd>';
            }
            echo '</div>';
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if category is set. In order for the widget in this page to be displayed correctly, please set category for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/categories/" target="_blank">' . __('How to set category', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output .= ob_get_clean();

        return $output;
    }
}
new MDSB_EventCategory;
