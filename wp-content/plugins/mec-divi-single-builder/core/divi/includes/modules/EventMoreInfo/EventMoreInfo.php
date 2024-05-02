<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventMoreInfo extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventMoreInfo';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventMoreInfo', $this->getScopes());
        });

        $this->name = esc_html__('Event More Info', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-event-more-info .mec-cost',
            ),
            'link' => array(
                'label'    => esc_html__( 'Link', 'et_builder' ),
                'selector' => '.mec-event-more-info dd a',
            ),
            'link_hover' => array(
                'label'    => esc_html__( 'Link Hover', 'et_builder' ),
                'selector' => '.mec-event-more-info dd a:hover',
            ),
            'icon' => array(
                'label'    => esc_html__( 'Icon', 'et_builder' ),
                'selector' => '.mec-event-more-info i',
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
                        'main' => "{$this->main_css_element} .mec-event-more-info",
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
                        'main'      => "{$this->main_css_element} .mec-event-more-info .mec-cost",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                ),
                'link' => array(
                    'label'        => esc_html__('Link', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-more-info dd",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Link Hover', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-more-info dd:hover",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'et_builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-event-more-info i, {$this->main_css_element} .mec-event-more-info i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-event-more-info",
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
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        ob_start();
		// More Info
		if (isset($eventt->data->meta['mec_more_info']) and trim($eventt->data->meta['mec_more_info']) and $eventt->data->meta['mec_more_info'] != 'http://') {
			?>
			<div class="mec-event-more-info">
				<i class="mec-sl-info"></i>
				<h3 class="mec-cost"><?php echo $mainClass->m('more_info_link', __('More Info', 'mec-divi-single-builder')); ?></h3>
				<dd class="mec-events-event-more-info"><a class="mec-more-info-button a mec-color-hover" target="<?php echo (isset($eventt->data->meta['mec_more_info_target']) ? $eventt->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $eventt->data->meta['mec_more_info']; ?>"><?php echo ((isset($eventt->data->meta['mec_more_info_title']) and trim($eventt->data->meta['mec_more_info_title'])) ? $eventt->data->meta['mec_more_info_title'] : __('Read More', 'mec-divi-single-builder')); ?></a></dd>
			</div>
			<?php
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if read more is set. In order for the widget in this page to be displayed correctly, please set read more for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/add-event/" target="_blank">' . __('How to set read more', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }

        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventMoreInfo;
