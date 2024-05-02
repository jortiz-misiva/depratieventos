<?php
use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventTime extends ET_Builder_Module
{


    public $slug       = 'MDSB_EventTime';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventTime', $this->getScopes());
        });

        $this->name = esc_html__('Event Time', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-time .mec-time',
			),
			'time' => array(
				'label'    => esc_html__( 'Time', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-time dd',
			),
			'comment' => array(
				'label'    => esc_html__( 'Time Comment', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-time .mec-time-comment',
			),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-time i',
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
        $single         = new \MEC_skin_single();
        $eventt = $single->get_event_mec(Admin::getLastEventID());
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $time_comment = isset($eventt->data->meta['mec_comment']) ? $eventt->data->meta['mec_comment'] : '-';
        $allday = isset($eventt->data->meta['mec_allday']) ? $eventt->data->meta['mec_allday'] : 0;
        if ($allday == '0' and isset($eventt->data->time) and trim($eventt->data->time['start'])){
            $EventsAbbr =  $eventt->data->time['start'] . (trim($eventt->data->time['end']) ? ' - ' . $eventt->data->time['end'] : '');
        } else {
            $EventsAbbr =  __('All day', 'mec-divi-single-builder');
        }

        $scopes = [
            'TimeComment' => $time_comment,
            'EventsAbbr' => $EventsAbbr
        ];

        $scopes['translates'] = [
            'Time' => __('Time', 'mec-divi-single-builder')
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
                        'main' => "{$this->main_css_element} .mec-single-event-time",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-time .mec-time",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                ),
                'date' => array(
                    'label'        => esc_html__('Time', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-time dd",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'comment' => array(
                    'label'        => esc_html__('Time Comment', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-time .mec-time-comment",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '14px',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-time i, {$this->main_css_element} .mec-single-event-time i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-time",
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
        $single         = new \MEC_skin_single();
        $this->process_additional_options($render_slug);
        $output = '<div class="mec-event-meta">';
        $eventt = $single->get_event_mec(Admin::get_the_ID());
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        ob_start();
        // Event Time
        if (isset($eventt->data->meta['mec_date']['start']) and !empty($eventt->data->meta['mec_date']['start'])) {
            if (isset($eventt->data->meta['mec_hide_time']) and $eventt->data->meta['mec_hide_time'] == '0') {
                $time_comment = isset($eventt->data->meta['mec_comment']) ? $eventt->data->meta['mec_comment'] : '';
                $allday = isset($eventt->data->meta['mec_allday']) ? $eventt->data->meta['mec_allday'] : 0;
?>
                <div class="mec-single-event-time">
                    <i class="mec-sl-clock " style=""></i>
                    <h3 class="mec-time"><?php _e('Time', 'mec-divi-single-builder'); ?></h3>
                    <i class="mec-time-comment"><?php echo (isset($time_comment) ? $time_comment : ''); ?></i>

                    <?php if ($allday == '0' and isset($eventt->data->time) and trim($eventt->data->time['start'])) : ?>
                        <dd><abbr class="mec-events-abbr"><?php echo $eventt->data->time['start']; ?><?php echo (trim($eventt->data->time['end']) ? ' - ' . $eventt->data->time['end'] : ''); ?></abbr></dd>
                    <?php else : ?>
                        <dd><abbr class="mec-events-abbr"><?php _e('All day', 'mec-divi-single-builder'); ?></abbr></dd>
                    <?php endif; ?>
                </div>
<?php
            }
        }
        $output .= ob_get_clean();
        $output .= '</div>';
        return $output;
    }
}
new MDSB_EventTime;
