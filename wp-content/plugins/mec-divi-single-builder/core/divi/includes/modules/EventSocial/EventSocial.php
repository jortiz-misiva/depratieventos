<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventSocial extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventSocial';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventSocial', $this->getScopes());
        });

        $this->name = esc_html__('Event Social', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-event-social h3',
            ),
            'icon' => array(
                'label'    => esc_html__( 'Icon', 'et_builder' ),
                'selector' => '.mec-event-social i',
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
                        'main' => "{$this->main_css_element} .mec-event-social",
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
                        'main'      => "{$this->main_css_element} .mec-event-social h3",
                        'important' => 'all',
                    ),
                    'font_size' => array(
                        'default' => '16px',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'et_builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-event-social i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-event-social",
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
        ob_start();
        if (!isset($set['social_network_status']) or (isset($set['social_network_status']) and !$set['social_network_status'])) return;
        $url = isset($eventt->data->permalink) ? $eventt->data->permalink : '';
        if (trim($url) == '') return;
        $socials = $mainClass->get_social_networks();
        if($set['social_network_status']) {
?>
        <div class="mec-event-social mec-frontbox">
            <h3 class="mec-social-single mec-frontbox-title"><?php _e('Share this event', 'mec-divi-single-builder'); ?></h3>
            <div class="mec-event-sharing">
                <div class="mec-links-details">
                    <ul>
                        <?php
                        foreach ($socials as $social) {
                            if (!isset($set['sn'][$social['id']]) or (isset($set['sn'][$social['id']]) and !$set['sn'][$social['id']])) continue;
                            if (is_callable($social['function'])) echo call_user_func($social['function'], $url, $eventt);
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
<?php
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if social networks is set. In order for the widget in this page to be displayed correctly, please set label for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/social-networks/" target="_blank">' . __('How to set social networks', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventSocial;
