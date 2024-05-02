<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventLocation extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventLocation';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventLocation', $this->getScopes());
        });

        $this->name = esc_html__('Event Location', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-location .mec-events-single-section-title',
			),
			'location' => array(
				'label'    => esc_html__( 'Location Name', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-location dd',
			),
			'address' => array(
				'label'    => esc_html__( 'Address', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-location .mec-events-address .mec-address',
			),
			'icon' => array(
				'label'    => esc_html__( 'Icon', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-location i',
			),
			'image' => array(
				'label'    => esc_html__( 'Location Image', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-location img',
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
                        'main' => "{$this->main_css_element} .mec-event-meta .mec-single-event-location",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-location .mec-events-single-section-title",
                        'important' => 'all',
                    ),
                ),
                'location' => array(
                    'label'        => esc_html__('Location Name', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-location dd",
                        'important' => 'all',
                    ),
                ),
                'address' => array(
                    'label'        => esc_html__('Address', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-location .mec-events-address .mec-address",
                        'important' => 'all',
                    ),
                ),
                'icon' => array(
                    'label'      => esc_html__( 'Icon', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-location i, {$this->main_css_element} .mec-single-event-location i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-location",
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
    public function get_content($e_id) {
        global $eventt;
        $single         = new \MEC_skin_single();
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        $mainClass      = new \MEC_main();
        $output = '';
        ob_start();
		if (isset($eventt->data->locations[$eventt->data->meta['mec_location_id']]) and !empty($eventt->data->locations[$eventt->data->meta['mec_location_id']])) {
			$location = $eventt->data->locations[$eventt->data->meta['mec_location_id']];
			?>
			<div class="mec-single-event-location">
				<?php if ($location['thumbnail']) : ?>
					<img class="mec-img-location" src="<?php echo esc_url($location['thumbnail']); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
				<?php endif; ?>
				<i class="mec-sl-location-pin"></i>
				<h3 class="mec-events-single-section-title mec-location"><?php echo $mainClass->m('taxonomy_location', __('Location', 'mec-divi-single-builder')); ?></h3>
				<dd class="author fn org"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
				<dd class="location">
					<address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
				</dd>
			</div>
			<?php
			$single->show_other_locations($eventt); // Show Additional Locations
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if location is set. In order for the widget in this page to be displayed correctly, please set location for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/location/" target="_blank">' . __('How to set location', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }

        $output .= ob_get_clean();

        return $output;
    }

}
new MDSB_EventLocation;
