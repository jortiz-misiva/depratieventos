<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventGoogleMap extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventGoogleMap';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventGoogleMap', $this->getScopes());
        });

        $this->name = esc_html__('Event Google Map', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
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
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-events-meta-group-gmap",
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
        $eventt  = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        if (!$set['google_maps_status'] && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This option is disabled. In order for the widget in this page to be displayed correctly, please turn on  Google Maps Options and set the API for it there.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/google-maps-options/" target="_blank">' . __('How to set Google Map', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } elseif (!isset($eventt[0]->data->meta['mec_location_id']) && empty($eventt[0]->data->meta['mec_location_id']) && !is_singular('mec-events')) {
            $output = '<div class="mec-content-notification">';
            $output .= '<p>';
            $output .= '<span>';
            $output .= __('This widget is displayed if Google Map is set. In order for the widget in this page to be displayed correctly, please set Google Map for your last event.', 'mec-divi-single-builder');
            $output .= '</span>';
            $output .= '<a href="https://webnus.net/dox/modern-events-calendar/google-maps-options/" target="_blank">' . __('How to set Google Map', 'mec-divi-single-builder') . ' </a>';
            $output .= '</p>';
            $output .= '</div>';
        } else {
            $output  = '<div class="mec-events-meta-group mec-events-meta-group-gmap">';
            $output .= $mainClass->module('googlemap.details', array('event' => $eventt));
            $output .= '</div>';
        }

        return $output;
    }
}
new MDSB_EventGoogleMap;
