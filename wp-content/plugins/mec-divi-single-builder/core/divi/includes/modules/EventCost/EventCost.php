<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventCost extends ET_Builder_Module
{


    public $slug       = 'MDSB_EventCost';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventCost', $this->getScopes());
        });

        $this->name = esc_html__('Event Cost', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-cost .mec-cost',
			),
			'price' => array(
				'label'    => esc_html__( 'Price', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-cost dd',
			),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-event-meta i',
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

        global $eventt;
        $single         = new \MEC_skin_single();

        $eventt = $single->get_event_mec(Admin::getLastEventID());
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $mainClass      = new \MEC_main();
        $mec_cost = isset($eventt->data->meta['mec_cost']) ? $eventt->data->meta['mec_cost'] : 0;
        $scopes = [
            'Cost' => $mainClass->m('cost', __('Cost', 'mec-divi-single-builder')),
            'EventCost' => (isset($eventt->data->meta['mec_cost']) ? $mainClass->render_price($eventt->data->meta['mec_cost']) : $mec_cost)
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
                        'main' => "{$this->main_css_element} .mec-event-cost",
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
                        'main'      => "{$this->main_css_element} .mec-event-cost .mec-cost",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                ),
                'price' => array(
                    'label'        => esc_html__('Price', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-event-cost dd",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-event-meta i, {$this->main_css_element} .mec-event-meta i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-event-cost",
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
        if(get_post_type(get_the_ID() ) != 'mec-events') {
            return;
        }
        global $eventt;
        $mainClass      = new \MEC_main();
        $single         = new \MEC_skin_single();
        $eventt = $single->get_event_mec(get_the_ID( ));
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $mec_cost = isset($eventt->data->meta['mec_cost']) and $eventt->data->meta['mec_cost'] != '';
        if($mec_cost) {
        $this->process_additional_options($render_slug);
        ob_start();
        ?>
        <div class="mec-event-meta">
            <div class="mec-event-cost">
                <i class="mec-sl-wallet"></i>
                <h3 class="mec-cost"><?php echo $mainClass->m('cost', __('Cost', 'mec-divi-single-builder')); ?></h3>
                <dd class="mec-events-event-cost"><?php echo (is_numeric($eventt->data->meta['mec_cost']) ? $mainClass->render_price($eventt->data->meta['mec_cost']) : $eventt->data->meta['mec_cost']); ?></dd>
            </div>
        </div>
<?php
        } else if(!is_singular( 'mec-events' )) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if cost is set. In order for the widget in this page to be displayed correctly, please set cost for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/add-event/" target="_blank">' . __('How to set cost', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output = ob_get_clean();

        return $output;
    }
}
new MDSB_EventCost;
