<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventBooking extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventBooking';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventBooking', $this->getScopes());
        });

        $this->name = esc_html__('Event Booking', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking h4',
            ),
			'name' => array(
				'label'    => esc_html__( 'Ticket Name', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-event-ticket-name',
            ),
			'price' => array(
				'label'    => esc_html__( 'price', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-event-ticket-price',
            ),
			'description' => array(
				'label'    => esc_html__( 'Description', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-event-ticket-description',
            ),
			'submit' => array(
				'label'    => esc_html__( 'Submit Button', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-book-form-next-button, .mec-events-meta-group-booking button:not(.owl-dot):not(.gm-control-active)',
            ),
			'back' => array(
				'label'    => esc_html__( 'Back Button', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-book-form-back-button',
            ),
			'total' => array(
				'label'    => esc_html__( 'Total Price', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-book-price-total',
            ),
			'label' => array(
				'label'    => esc_html__( 'Amount Label', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-book-price-detail-description',
            ),
			'amount' => array(
				'label'    => esc_html__( 'Amount Price', 'mec-divi-single-builder' ),
				'selector' => '.mec-events-meta-group-booking .mec-book-price-detail-amount',
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
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking",
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
                        'main'      => "{$this->main_css_element} .mec-events-meta-group-booking h4",
                        'important' => 'all',
                    ),
                ),
                'name' => array(
                    'label'      => esc_html__( 'Ticket Name', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-event-ticket-name",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'price' => array(
                    'label'      => esc_html__( 'Price', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-event-ticket-price",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'description' => array(
                    'label'      => esc_html__( 'Description', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-event-ticket-description",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'total' => array(
                    'label'      => esc_html__( 'Total Price', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-book-price-total",
                        'important' => "all",
                    ),
                ),
                'label' => array(
                    'label'      => esc_html__( 'Amount Label', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-book-price-detail-description",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
                'amount' => array(
                    'label'      => esc_html__( 'Amount', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-book-price-detail-amount",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
			'button'                => array(
				'submit' => array(
					'label' => esc_html__( 'Submit Button', 'mec-divi-single-builder' ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-book-form-next-button, {$this->main_css_element} .mec-events-meta-group-booking button:not(.owl-dot):not(.gm-control-active)",
                        'important' => 'all',
                    ),
                    'use_icon' => false,
                ),
				'back' => array(
					'label' => esc_html__( 'Back Button', 'mec-divi-single-builder' ),
                    'css' => array(
                        'main' => "{$this->main_css_element} .mec-events-meta-group-booking .mec-book-form-back-button",
                        'important' => 'all',
                    ),
                    'use_icon' => false,
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
        $single_event = $eventt[0];
        $set            = $mainClass->get_settings();
        $booking_options = get_post_meta($e_id, 'mec_booking', true);
        $occurrence = (isset($single_event->date['start']['date']) ? $single_event->date['start']['date'] : (isset($_GET['occurrence']) ? sanitize_text_field($_GET['occurrence']) : ''));
        $occurrence_end_date = trim($occurrence) ? $mainClass->get_end_date_by_occurrence($single_event->data->ID, (isset($single_event->date['start']['date']) ? $single_event->date['start']['date'] : $occurrence)) : '';
        ob_start();
        if ($mainClass->is_sold($single_event, (trim($occurrence) ? $occurrence : $single_event->date['start']['date'])) and count($single_event->dates) <= 1) : ?>
            <div class="mec-sold-tickets warning-msg"><?php _e('Sold out!', 'wpl'); ?></div>
        <?php elseif ($mainClass->can_show_booking_module($single_event)) :
            $data_lity_class = '';
            if (isset($set['single_booking_style']) and $set['single_booking_style'] == 'modal') $data_lity_class = 'lity-hide '; ?>
            <div id="mec-events-meta-group-booking-<?php echo $single->uniqueid; ?>" class="<?php echo $data_lity_class; ?> mec-events-meta-group mec-events-meta-group-booking">
                <?php
                if (isset($set['booking_user_login']) and $set['booking_user_login'] == '1' and !is_user_logged_in()) {
                    echo do_shortcode('[MEC_login]');
                } elseif (isset($set['booking_user_login']) and $set['booking_user_login'] == '0' and !is_user_logged_in() and isset($booking_options['bookings_limit_for_users']) and $booking_options['bookings_limit_for_users'] == '1') {
                    echo do_shortcode('[MEC_login]');
                } else {
                    echo $mainClass->module('booking.default', array('event' => $eventt));
                }
                ?>
            </div>
<?php
        endif;
        $output .= ob_get_clean();

        if(!$output && !is_singular('mec-events')) {
            if (!$set['booking_status']) {
                $output = '<div class="mec-content-notification">';
                $output .= '<p>';
                $output .= '<span>';
                $output .= __('This widget is displayed if label is set. In order for the widget in this page to be displayed correctly, please set QR code module for your last event.', 'mec-divi-single-builder');
                $output .= '</span>';
                $output .= '<a href="https://webnus.net/dox/modern-events-calendar/qr-code-module/" target="_blank">' . __('How to set QR code module', 'mec-divi-single-builder') . ' </a>';
                $output .= '</p>';
                $output .= '</div>';
            }
        }

        return $output;
    }
}
new MDSB_EventBooking;
