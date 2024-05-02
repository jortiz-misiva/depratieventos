<?php

use \MEC_DIVI_Single_Builder\Core\Controller\Admin;

class MDSB_EventOrganizer extends ET_Builder_Module
{

    public $slug       = 'MDSB_EventOrganizer';
    public $vb_support = 'on';

    public function init()
    {

        add_action('mec-divi-single-builder-editor-js', function ($handle) {
            wp_localize_script($handle, 'MDSB_EventOrganizer', $this->getScopes());
        });

        $this->name = esc_html__('Event Organizer', 'mec-divi-single-builder');
        $this->main_css_element = '%%order_class%%.' . $this->slug;
        $this->advanced_fields = $this->getAdvancedFields();
        $this->custom_css_fields = array(
            'main_element' => array(
                'label'    => esc_html__('Main Element', 'mec-divi-single-builder'),
                'no_space_before_selector' => true,
            ),
			'title' => array(
				'label'    => esc_html__( 'Title', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-organizer .mec-events-single-section-title, .mec-single-event-additional-organizers .mec-events-single-section-title',
			),
			'label' => array(
				'label'    => esc_html__( 'Organizer Label', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-organizer h6, .mec-single-event-additional-organizers h6',
			),
			'detail' => array(
				'label'    => esc_html__( 'Organizer Detail', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-organizer a, .mec-single-event-additional-organizers a',
			),
			'icons' => array(
				'label'    => esc_html__( 'Icons', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-organizer i, .mec-single-event-additional-organizers i',
			),
			'image' => array(
				'label'    => esc_html__( 'Organizer Image', 'mec-divi-single-builder' ),
				'selector' => '.mec-single-event-organizer img, .mec-single-event-additional-organizers img',
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
                        'main' => "{$this->main_css_element} .mec-event-meta .mec-single-event-organizer, {$this->main_css_element} .mec-event-meta .mec-single-event-additional-organizers",
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
                        'main'      => "{$this->main_css_element} .mec-single-event-organizer .mec-events-single-section-title, {$this->main_css_element} .mec-single-event-additional-organizers .mec-events-single-section-title",
                        'important' => 'all',
                    ),
                ),
                'label' => array(
                    'label'        => esc_html__('Organizer Label', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-organizer h6, {$this->main_css_element} .mec-single-event-additional-organizers h6",
                        'important' => 'all',
                    ),
                ),
                'detail' => array(
                    'label'        => esc_html__('Organizer Detail', 'mec-divi-single-builder'),
                    'css'          => array(
                        'main'      => "{$this->main_css_element} .mec-single-event-organizer a, {$this->main_css_element} .mec-single-event-additional-organizers a",
                        'important' => 'all',
                    ),
                ),
                'icons' => array(
                    'label'      => esc_html__( 'Icons', 'mec-divi-single-builder' ),
                    'css'        => array(
                        'main' => "{$this->main_css_element} .mec-single-event-organizer i, {$this->main_css_element} .mec-single-event-organizer i:before, {$this->main_css_element} .mec-single-event-additional-organizers i, {$this->main_css_element} .mec-single-event-additional-organizers i:before",
                        'important' => "all",
                    ),
                    'hide_text_align' => true,
                ),
            ),
            'background'             => array(
                'css' => array(
                    'main' => "{$this->main_css_element}, {$this->main_css_element} .mec-single-event-organizer, {$this->main_css_element} .mec-single-event-additional-organizers",
                    'important' => 'all',
                ),
            ),
			'image'                 => array(
				'css' => array(
                    'main' => "{$this->main_css_element} .mec-single-event-organizer img, $this->main_css_element} .mec-single-event-additional-organizers img",
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
        $output = '';
        $eventt = $single->get_event_mec($e_id);
        if (!$eventt) {
            return;
        }
        $eventt = $eventt[0];
        // Event Organizer
        ob_start();
        if (isset($eventt->data->organizers[$eventt->data->meta['mec_organizer_id']]) && !empty($eventt->data->organizers[$eventt->data->meta['mec_organizer_id']])) {
            $organizer = $eventt->data->organizers[$eventt->data->meta['mec_organizer_id']];
?>
            <div class="mec-single-event-organizer">
                <?php if (isset($organizer['thumbnail']) and trim($organizer['thumbnail'])) : ?>
                    <img class="mec-img-organizer" src="<?php echo esc_url($organizer['thumbnail']); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
                <?php endif; ?>
                <h3 class="mec-events-single-section-title"><?php echo $mainClass->m('taxonomy_organizer', __('Organizer', 'mec-divi-single-builder')); ?></h3>
                <?php if (isset($organizer['thumbnail'])) : ?>
                    <dd class="mec-organizer">
                        <i class="mec-sl-home"></i>
                        <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                    </dd>
                <?php endif;
                if (isset($organizer['tel']) && !empty($organizer['tel'])) : ?>
                    <dd class="mec-organizer-tel">
                        <i class="mec-sl-phone"></i>
                        <h6><?php _e('Phone', 'mec-divi-single-builder'); ?></h6>
                        <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
                    </dd>
                <?php endif;
                if (isset($organizer['email']) && !empty($organizer['email'])) : ?>
                    <dd class="mec-organizer-email">
                        <i class="mec-sl-envelope"></i>
                        <h6><?php _e('Email', 'mec-divi-single-builder'); ?></h6>
                        <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
                    </dd>
                <?php endif;
                if (isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://') : ?>
                    <dd class="mec-organizer-url">
                        <i class="mec-sl-sitemap"></i>
                        <h6><?php _e('Website', 'mec-divi-single-builder'); ?></h6>
                        <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://' . $organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
                    </dd>
                    <?php endif;
                $organizer_description_setting = isset($set['organizer_description']) ? $set['organizer_description'] : '';
                $organizer_terms = get_the_terms($eventt->data, 'mec_organizer');
                if ($organizer_description_setting == '1') : foreach ($organizer_terms as $organizer_term) {
                        if ($organizer_term->term_id == $organizer['id']) {
                            if (isset($organizer_term->description) && !empty($organizer_term->description)) : ?>
                                <dd class="mec-organizer-description">
                                    <p><?php echo $organizer_term->description; ?></p>
                                </dd>
                <?php endif;
                        }
                    }
                endif; ?>
            </div>
<?php
            $single->show_other_organizers($eventt); // Show Additional Organizers
        } else if (!is_singular('mec-events')) {
            echo '<div class="mec-content-notification">';
            echo '<p>';
            echo '<span>';
            echo __('This widget is displayed if organizer is set. In order for the widget in this page to be displayed correctly, please set organizer for your last event.', 'mec-divi-single-builder');
            echo '</span>';
            echo '<a href="https://webnus.net/dox/modern-events-calendar/organizer-and-other-organizer/" target="_blank">' . __('How to set organizer', 'mec-divi-single-builder') . ' </a>';
            echo '</p>';
            echo '</div>';
        }
        $output .= ob_get_clean();

        return $output;
    }
}
new MDSB_EventOrganizer;
