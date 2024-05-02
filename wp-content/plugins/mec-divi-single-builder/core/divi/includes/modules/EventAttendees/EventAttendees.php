<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventAttendees extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventAttendees';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventAttendees', $this->getScopes());
        });

        add_action('mec-divi-single-builder-editor-css', function ($styling) {
            echo '<style>.mec-wrap .mec-attendees-list-details p {
                font-weight: 300;
                margin: 20px 0 0 0;
                color: #8d8d8d;
            }
            
            .mec-wrap .mec-attendees-list-details li {
                list-style: none;
                display: block;
                margin-top: 15px;
            }
            
            .mec-wrap .mec-attendees-list-details li .mec-attendee-avatar {
                display: inline-block;
            }
            
            .mec-wrap .mec-attendees-list-details li .mec-attendee-profile-link {
                display: inline-block;
                vertical-align: top;
                margin-left: 10px;
            }
            
            .mec-attendees-list-details ul {
                margin-bottom: 0;
            }
            
            .mec-attendees-list-details .mec-attendee-profile-link a {
                color: #8d8d8d;
                display: block;
            }
            
            .mec-attendees-list-details .mec-attendee-profile-link span {
                display: inline-block;
                color: #000;
                vertical-align: middle;
                cursor: pointer;
            }
            
            .mec-attendees-list-details span.mec-attendee-profile-ticket-number {
                border-radius: 50px;
                width: 20px;
                height: 20px;
                font-size: 12px;
                text-align: center;
                color: #fff;
                margin-right: 4px;
                line-height: 20px;
            }
            
            #wrap .mec-attendees-list-details span.mec-attendee-profile-ticket-number {
                line-height: 19px;
            }
            
            .mec-attendees-list-details .mec-attendee-profile-link span i {
                vertical-align: middle;
                font-size: 9px;
                font-weight: bold;
                margin-left: 5px;
            }
            
            .mec-attendees-list-details .mec-attendees-toggle { border: 1px solid #e6e6e6;background: #fafafa;padding: 15px 15px 0;border-radius: 3px;margin: 12px 0 20px 52px;position: relative;font-size: 13px;box-shadow: 0 3px 1px 0 rgba(0,0,0,.02);}
            .mec-attendees-list-details .mec-attendees-toggle:before, .mec-attendees-list-details .mec-attendees-toggle:after { content: \'\'; display: block; position: absolute; left: 50px; width: 0; height: 0; border-style: solid; border-width: 10px; } 
            .mec-attendees-list-details .mec-attendees-toggle:after { top: -20px; border-color: transparent transparent #fafafa transparent; } 
            .mec-attendees-list-details .mec-attendees-toggle:before { top: -21px; border-color: transparent transparent #e1e1e1 transparent; }
            .mec-attendees-list-details .mec-attendees-toggle .mec-attendees-item { padding-bottom: 15px;}
            .mec-attendees-list-details .mec-attendee-avatar img { border-radius: 3px}
            .mec-attendee-avatar-sec { float: left; width: 50px; margin-right: 12px; }
            .mec-attendee-profile-name-sec, .mec-attendee-profile-ticket-sec { float: left; width: calc(100% - 62px); margin-top: 3px; }
            </style>';
        });

        $this->name = esc_html__('Event Attendees', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'image' => array(
				'label'    => esc_html__( 'Images', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details img',
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-frontbox-title',
            ),
			'number' => array(
				'label'    => esc_html__( 'Ticket Number', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-attendee-profile-ticket-number',
            ),
			'tickets' => array(
				'label'    => esc_html__( 'Ticket Typo', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-color-hover',
            ),
			'link' => array(
				'label'    => esc_html__( 'Link', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-attendee-profile-link a',
            ),
			'link_hover' => array(
				'label'    => esc_html__( 'Link Hover', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-attendee-profile-link a:hover',
            ),
			'name' => array(
				'label'    => esc_html__( 'Profile Name', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-attendee-profile-name-sec',
            ),
			'ticket' => array(
				'label'    => esc_html__( 'Profile Tickets', 'mec-divi-single-builder' ),
				'selector' => '.mec-attendees-list-details .mec-attendee-profile-ticket-sec',
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
                        'main' => "{$this->main_css_element} .mec-attendees-list-details",
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
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-frontbox-title",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'number' => array(
                    'label'        => esc_html__('Tickets Number', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-attendee-profile-ticket-number",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'tickets' => array(
                    'label'        => esc_html__('Tickets', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-color-hover",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'link' => array(
                    'label'        => esc_html__('Link', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-attendee-profile-link a",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'link_hover' => array(
                    'label'        => esc_html__('Link Hover', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-attendee-profile-link a:hover",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'name' => array(
                    'label'        => esc_html__('Profile Name', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-attendee-profile-name-sec",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
                'ticket' => array(
                    'label'        => esc_html__('Profile Tickets', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-attendees-list-details .mec-attendee-profile-ticket-sec",
                        'important' => 'all',
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background' => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-attendees-list-details",
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
        if(!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $output = $mainClass->module('attendees-list.details', array('event' => $eventt));
        if(!$output && !is_singular('mec-events')) {
            if (!isset($set['bp_status'])) {
                $output = '<div class="mec-content-notification">';
                $output .= '<p>';
                $output .= '<span>';
                $output .= __('This widget is displayed if buddypress is set. In order for the widget in this page to be displayed correctly, please set buddypress for your last event.', 'mec-divi-single-builder');
                $output .= '</span>';
                $output .= '<a href="https://webnus.net/dox/modern-events-calendar/buddypress/" target="_blank">' . __('How to set buddypress', 'mec-divi-single-builder') . ' </a>';
                $output .= '</p>';
                $output .= '</div>';
            }
        }
        return $output;
    }
}
new MDSB_EventAttendees;
