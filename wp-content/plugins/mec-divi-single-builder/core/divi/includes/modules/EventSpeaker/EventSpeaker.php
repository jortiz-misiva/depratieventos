<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventSpeaker extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventSpeaker';
    public $vb_support = 'on';

    public function init()
    {
        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventSpeaker', $this->getScopes());
        });

        $this->name = esc_html__('Event Speaker', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__( 'Title', 'et_builder' ),
                'selector' => '.mec-speakers-details h3',
            ),
            'name' => array(
                'label'    => esc_html__( 'Name', 'et_builder' ),
                'selector' => '.mec-speakers-details .mec-speaker-avatar .mec-speaker-name',
            ),
            'job' => array(
                'label'    => esc_html__( 'Job', 'et_builder' ),
                'selector' => '.mec-speakers-details .mec-speaker-avatar .mec-speaker-job-title',
            ),
            'avatar' => array(
                'label'    => esc_html__( 'Avatar', 'et_builder' ),
                'selector' => '.mec-speakers-details .mec-speaker-avatar img',
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
                        'main' => "{$this->main_css_element} .mec-speakers-details",
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
                        'main'      => "{$this->main_css_element} .mec-speakers-details h3",
                        'important' => 'all',
                    ),
                ),
                'name' => array(
                    'label'        => esc_html__('Speaker Name', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-speakers-details .mec-speaker-avatar .mec-speaker-name",
                        'important' => 'all',
                    ),
                ),
                'job' => array(
                    'label'        => esc_html__('Speaker Job', 'et_builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-speakers-details .mec-speaker-avatar .mec-speaker-job-title",
                        'important' => 'all',
                    ),
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-speakers-details",
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
        ob_start();
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $set['speakers_status'] = isset($set['speakers_status']) ? $set['speakers_status'] : false;
        if (!$set['speakers_status'] && !is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if speaker is set. In order for the widget in this page to be displayed correctly, please set speaker for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/speaker/" target="_blank">' . __('How to set speaker', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        } else {
        // Event Speaker
        $output = $mainClass->module('speakers.details', array('event' => $eventt));
        if (!$output && !is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if speaker is set. In order for the widget in this page to be displayed correctly, please set speaker for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/speaker/" target="_blank">' . __('How to set speaker', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        } else {
            echo $output;
        }
?>

        <script>
            // Fix modal speaker in some themes
            jQuery(".mec-speaker-avatar a").click(function(e) {
                e.preventDefault();
                var id = jQuery(this).attr('href');
                lity(id);
            });

            // Fix modal booking in some themes
            function openBookingModal() {
                jQuery(".mec-booking-button.mec-booking-data-lity").on('click', function(e) {
                    e.preventDefault();
                    var book_id = jQuery(this).attr('href');
                    Lity.close();
                    lity(book_id);
                });
            }
        </script>
<?php
        }
        $output = ob_get_clean();
        return $output;
    }
}
new MDSB_EventSpeaker;
