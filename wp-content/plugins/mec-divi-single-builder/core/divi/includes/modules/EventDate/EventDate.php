<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventDate extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventDate';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventDate', $this->getScopes());
        });

        $this->name = esc_html__('Event Date', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__('Title', 'mec-divi-single-builder'),
                'selector' => '.mec-single-event-date .mec-date',
            ),
            'date' => array(
                'label'    => esc_html__('Date', 'mec-divi-single-builder'),
                'selector' => '.mec-single-event-date dd',
            ),
            'icon' => array(
                'label'    => esc_html__('Icon', 'mec-divi-single-builder'),
                'selector' => '.mec-single-event-date i',
            ),
        );
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
                        'main' => "{$this->main_css_element} .mec-single-event-date",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-date .mec-date",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                ),
                'date' => array(
                    'label'        => esc_html__('Date', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-date dd",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__('Icon', 'mec-divi-single-builder'),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-date i, {$this->main_css_element} .mec-single-event-date i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-date",
                    'important' => 'all',
                ),
            ),
            'link_options'  => false,
            'text'          => false,
            'button'        => false,
            'header_level'  => false,
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
        // Event Location
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
        ob_start();
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $occurrence             = (isset($eventt->date['start']['date']) ? $eventt->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
        $occurrence_end_date     = trim($occurrence) ? $mainClass->get_end_date_by_occurrence($eventt->data->ID, (isset($eventt->date['start']['date']) ? $eventt->date['start']['date'] : $occurrence)) : '';
        // Event Date
        if (isset($eventt->data->meta['mec_date']['start']) and !empty($eventt->data->meta['mec_date']['start'])) {
?>
            <div class="mec-single-event-date">
                <i class="mec-sl-calendar"></i>
                <h3 class="mec-date"><?php _e('Date', 'mec-divi-single-builder'); ?></h3>
                <dd><abbr class="mec-events-abbr"><?php echo $mainClass->date_label((trim($occurrence) ? array('date' => $occurrence) : $eventt->date['start']), (trim($occurrence_end_date) ? array('date' => $occurrence_end_date) : (isset($eventt->date['end']) ? $eventt->date['end'] : NULL)), $set['single_date_format1']); ?></abbr></dd>
            </div>
<?php
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventDate;
