<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventRegisterButton extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventRegisterButton';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventRegisterButton', $this->getScopes());
        });

        $this->name = esc_html__('Event Register Button', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
            'title' => array(
                'label'    => esc_html__('Register Button', 'mec-divi-single-builder'),
                'selector' => '.mec-booking-button',
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
            'fonts' => false,
            'button'                => array(
                'button' => array(
                    'label' => esc_html__('Register Button', 'mec-divi-single-builder'),
                    'css' => array(
                        'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-booking-button",
                        'important' => 'all',
                    ),
                    'use_icon' => false,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-booking-button",
                    'important' => 'all',
                ),
            ),
            'link_options'  => false,
            'text'          => false,
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
        $output = '';
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $data_lity_class = '';
        ob_start();
        if ($mainClass->can_show_booking_module($eventt)) {
?>
        <!-- Register Booking Button -->
        <div class="mec-reg-btn mec-frontbox">
            <?php if ($mainClass->can_show_booking_module($eventt)) : ?>
                <?php $data_lity = '';
                if (isset($set['single_booking_style']) and $set['single_booking_style'] == 'modal')  $data_lity_class = 'mec-booking-data-lity'; ?>
                <a class="mec-booking-button mec-bg-color <?php echo $data_lity_class; ?> <?php if (isset($set['single_booking_style']) and $set['single_booking_style'] != 'modal') echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo $single->uniqueid; ?>"><?php echo esc_html($mainClass->m('register_button', __('REGISTER', 'mec-divi-single-builder'))); ?></a>
                <script>
                    // Fix modal booking in some themes
                    jQuery(".mec-booking-button.mec-booking-data-lity").click(function(e) {
                        e.preventDefault();
                        var book_id = jQuery(this).attr('href');
                        lity(book_id);
                    });
                </script>
            <?php elseif (isset($eventt->data->meta['mec_more_info']) and trim($eventt->data->meta['mec_more_info']) and $eventt->data->meta['mec_more_info'] != 'http://') : ?>
                <a class="mec-booking-button mec-bg-color" target="<?php echo (isset($eventt->data->meta['mec_more_info_target']) ? $eventt->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $eventt->data->meta['mec_more_info']; ?>"><?php if (isset($eventt->data->meta['mec_more_info_title']) and trim($eventt->data->meta['mec_more_info_title'])) echo esc_html(trim($eventt->data->meta['mec_more_info_title']), 'mec-divi-single-builder');
                                                                                                                                                                                                                                                            else echo esc_html($mainClass->m('register_button', __('REGISTER', 'mec-divi-single-builder')));                                                                                                                                                                                                                                ?></a>
            <?php endif; ?>
        </div>
<?php
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if register button is set. In order for the widget in this page to be displayed correctly, please set register button for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/booking/" target="_blank">' . __('How to set register button', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output .= ob_get_clean();

        return $output;
    }
}
new MDSB_EventRegisterButton;
