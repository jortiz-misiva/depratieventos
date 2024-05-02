<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventLabel extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventLabel';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventLabel', $this->getScopes());
        });

        $this->name = esc_html__('Event Label', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-single-event-label h3',
            ),
            'icon' => array(
                'label'    => esc_html__( 'Icon', 'et_builder' ),
                'selector' => '.mec-single-event-label i',
            ),
            'lables' => array(
                'label'    => esc_html__( 'Labels', 'et_builder' ),
                'selector' => '.mec-single-event-label dd',
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
                        'main' => "{$this->main_css_element} .mec-single-event-label",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-label h3",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'        => esc_html__('Icon', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-label i, {$this->main_css_element} .mec-single-event-label i:before",
                        'important' => 'all',
                    ),
                ),
                'labels' => array(
                    'label'        => esc_html__('Labels', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-label dd",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-label",
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
        $output = '';
        ob_start();
        if (isset($eventt->data->labels) and !empty($eventt->data->labels)) {
            $mec_items = count($eventt->data->labels);
            $mec_i = 0; ?>
            <div class="mec-single-event-label">
                <i class="mec-fa-bookmark-o"></i>
                <h3 class="mec-cost"><?php echo $mainClass->m('taxonomy_labels', __('Labels', 'mec-divi-single-builder')); ?></h3>
                <?php foreach ($eventt->data->labels as $labels => $label) :
                    $seperator = (++$mec_i === $mec_items) ? '' : ',';
                    echo '<dd style="color:' . $label['color'] . '">' . $label["name"] . $seperator . '</dd>';
                endforeach; ?>
            </div>
<?php
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if label is set. In order for the widget in this page to be displayed correctly, please set label for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/label/" target="_blank">' . __('How to set label', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventLabel;
