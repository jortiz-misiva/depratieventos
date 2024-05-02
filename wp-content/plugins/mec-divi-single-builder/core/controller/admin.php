<?php

namespace MEC_DIVI_Single_Builder\Core\Controller;

// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 *   Admin.
 *
 *   @author     Webnus <info@webnus.biz>
 *   @package     Modern Events Calendar
 *   @since     1.0.0
 */
class Admin
{

    /**
     *  Instance of this class.
     *
     *  @since     1.0.0
     *  @access     private
     *  @var     Admin
     */
    private static $instance;

    /**
     *  Provides access to a single instance of a module using the Singleton pattern.
     *
     *  @since   1.0.0
     *  @return  object
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->setHooks();
        $this->loadParts();
    }

    /**
     *  Load Parts.
     *
     *  @since   1.0.0
     */
    public function loadParts()
    {
        include_once MECDSBDIR . DS . 'core' . DS . 'divi' . DS . 'mec-divi-single-builder.php';
    }
    /**
     *  Set Hooks.
     *
     *  @since   1.0.0
     */
    public function setHooks()
    {
        // Render Applied Styles
        add_action('et_update_post', [$this, 'setup_styles'], 10);

        // Apply ESDB post type for Divi Builder
        add_filter('et_builder_post_types', [$this, 'apply_post_type_to_divi'], 1, 1);

        // Enqueue Editor Styles
        add_action('wp_head', [$this, 'editor_styles'] , 9999);

        // Enqueue Admin Styles
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // Add Sub Menu
        add_action('after_mec_submenu_action', [$this, 'esdb_submenu']);

        // Apply Single File
        add_filter('single_template',  [$this, 'esdb_single']);

        // Load The Builder for Modal View
        add_action('mec-ajax-load-single-page-before',  [$this, 'load_the_builder_modal'], 10);

        // Load The Styles
        add_action('wp_enqueue_scripts',  [$this, 'load_the_styles'], 10, 1);

        // Load The Styles
        add_action('mec_single_style',  [$this, 'add_single_style'], 10, 1);

        // Add MEC Divi Builder Settings in MEC Single Event Settings
        add_action('mec_single_style_setting_after',  [$this, 'add_settings'], 10, 1);

        // Add MetaBox
        add_action('add_meta_boxes',  [$this, 'sb_metabox'], 10, 1);

        // Add MetaBox
        add_action('add_meta_boxes',  [$this, 'content_width_metabox'], 10, 1);

        // Save Event
        add_action('save_post_mec-events',  [$this, 'save_event'], 10, 1);

        add_action('save_post_mec_esdb',  [$this, 'update_mec_divi_content_width'], 10, 1);
    }

    /**
     * Get The ID
     *
     * @since     1.0.0
     */
    public static function get_the_ID()
    {
        $id = get_the_ID();
        if (!$id || get_post_type($id) !== 'mec-events') {
            if (isset($_REQUEST['id'])) {
                global $post;
                $posts = get_posts([
                    'post__in' => [esc_attr($_REQUEST['id'])],
                    'numberposts' => 1,
                    'order'            => 'DESC',
                    'post_type' => 'mec-events'
                ]);
                global $post, $eventt;

                $eventt = get_post($event_id);
                foreach ($posts as $post) {
                    setup_postdata($post);
                }
                return get_the_ID();
            }
        }

        return $id;
    }


    /**
     * Add Single Style
     *
     * @since     1.0.0
     */
    public function add_single_style($set)
    {
        if (
            !function_exists('\et_theme_builder_frontend_render_layout')
        ) return;

        $selected = isset($set['single_single_style']) && $set['single_single_style'] == 'divi-builder' ? 'selected="selected"' : '';
        echo '<option value="divi-builder"' . $selected . '>' . __('Divi Single Builder', 'mec-divi-single-builder') . '</option>';
    }

    /**
     * Render Applied Styles
     *
     * @since     1.0.0
     */
    public function setup_styles($post_id)
    {
        if ('mec_esdb' == get_post_type($post_id)) {
            $permalink = get_post_permalink($post_id);
            file_get_contents($permalink);
        }
    }

    /**
     * Save event data
     *
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return void
     */
    public static function save_event($post_id)
    {

        // Check if our nonce is set.
        if (!isset($_POST['mec_event_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['mec_event_nonce'], 'mec_event_data')) {
            return;
        }
        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) {
            return;
        }

        $_mec = isset($_POST['mec']) ? $_POST['mec'] : array();
        if (!$_mec) {
            return;
        }


        if (isset($_mec['single_divi_single_default_builder'])) {
            update_post_meta($post_id, 'single_divi_single_default_builder', $_mec['single_divi_single_default_builder']);
        }

        if (isset($_mec['single_modal_default_divi_builder'])) {
            update_post_meta($post_id, 'single_modal_default_divi_builder', $_mec['single_modal_default_divi_builder']);
        }
    }

    /**
     * Save Content Width
     *
     * @author Webnus <info@webnus.biz>
     * @param int $post_id
     * @return void
     */
    public static function update_mec_divi_content_width($post_id)
    {

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') and DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['mec_divi_content_width'])) {
            return;
        }


        update_post_meta($post_id, 'mec_divi_content_width', esc_attr($_POST['mec_divi_content_width']));
    }

    public function esdb_single($single)
    {
        global $post;
        if ($post->post_type == 'mec_esdb') {
            if (file_exists(MECDSBDIR . 'templates/single-mec_esdb.php')) {
                return MECDSBDIR . 'templates/single-mec_esdb.php';
            }
        }

        return $single;
    }

    /**
     * Apply ESDB post type for Divi Builder
     *
     * @since     1.0.0
     */
    public function apply_post_type_to_divi($postTypes)
    {
        $postTypes[] = 'mec_esdb';
        return $postTypes;
    }

    public function create_esdb_post()
    {

        global $wpdb;
        $post_title = 'Divi Single Builder';

        $query = $wpdb->prepare(
            'SELECT * FROM ' . $wpdb->posts . ' WHERE post_title = %s AND post_type = \'mec_esdb\' order by \'publish_date\' desc',
            $post_title
        );
        $wpdb->query($query);

        if (
            $wpdb->num_rows
        ) :

            $post = $wpdb->get_row($query);
            return $post->ID;

        else :

            $new_post   = [
                'post_title'    => $post_title,
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_date'     => date('Y-m-d H:i:s'),
                'post_author'   => '1',
                'post_type'     => 'mec_esdb',
                'post_category' => [0],
            ];

            $post_id = wp_insert_post($new_post);
            return $post_id;

        endif;
    }

    public function load_the_builder_modal()
    {
        $event_id = $_REQUEST['id'];

        $mainClass      = new \MEC_main();
        $set            = $mainClass->get_settings();

        if (
            !function_exists('\et_builder_theme_or_plugin_updated_cb')
        ) return;

        $post_id = get_post_meta($event_id, 'single_modal_default_divi_builder', true);

        if (!$post_id || $post_id <= 0) {
            $post_id = (isset($set['single_modal_default_divi_builder']) && $set['single_modal_default_divi_builder']) ? $set['single_modal_default_divi_builder'] : false;
        }

        if (!$post_id || !get_post($post_id)) {
            $post_id = false;
            global $wpdb;
            $query = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = \'mec_esdb\' LIMIT 1 ';

            $wpdb->query($query);

            if ($wpdb->num_rows) {
                $post_id = $wpdb->get_var($query);
            }
            if (!$post_id || !get_post($post_id)) {
                echo __('Please select default builder for modal from MEC single page settings.', 'mec-divi-single-builder');
                return;
            }
        }

        global $post, $wp_the_query;
        $posts = get_posts([
            'post__in' => [$event_id],
            'numberposts' => 1,
            'order'            => 'DESC',
            'post_type' => 'mec-events'
        ]);
        $wp_the_query = false;

        global $post, $eventt;

        $eventt = get_post($event_id);
        foreach ($posts as $post) {
            setup_postdata($post);

            echo '<div class="mec-single-event mec-divi-single-builder-wrap"><div class="row mec-wrap"><div class="wn-single">';
            \et_theme_builder_frontend_render_layout('mec_esdb', $post_id);
            do_action('mec_esdb_content', $eventt);
            do_action('mec_schema', $eventt);
            echo '</div></div></div>';
        }

        $manager = \ET_Builder_Element::setup_advanced_styles_manager($post_id);
        wp_enqueue_style('mec-event-single-style', $manager['manager']->URL);

        die();
    }

    /**
     * Load the Builder
     *
     * @since     1.0.0
     */
    public static function load_the_builder($event)
    {

        $event_id = $event->ID;
        $mainClass      = new \MEC_main();
        $set            = $mainClass->get_settings();
        if(!isset($set['single_single_style'])) $set['single_single_style'] = 'default';
        if( $set['single_single_style'] != 'divi-builder' ) {
            return;
        }

        if (
            !function_exists('\et_builder_theme_or_plugin_updated_cb')
        ) return;

        $post_id = get_post_meta($event_id, 'single_divi_single_default_builder', true);

        if (!$post_id || $post_id <= 0) {
            $post_id = (isset($set['single_divi_single_default_builder']) && $set['single_divi_single_default_builder']) ? $set['single_divi_single_default_builder'] : false;
        }

        if (!$post_id || !get_post($post_id)) {
            $post_id = false;
            global $wpdb;
            $query = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = \'mec_esdb\' LIMIT 1 ';

            $wpdb->query($query);

            if ($wpdb->num_rows) {
                $post_id = $wpdb->get_var($query);
            }
            if (!$post_id || !get_post($post_id)) {
                echo __('Please select default builder for modal from MEC single page settings.', 'mec-divi-single-builder');
                return;
            }
        }

        echo '<div class="mec-wrap  mec-divi-single-builder-wrap"><div class="row mec-single-event"><div class="wn-single mec-single-event">';
        \et_theme_builder_frontend_render_layout('mec_esdb', $post_id);
        echo '</div></div></div>';
    }

    /**
     * Load the Styles
     *
     * @since     1.0.0
     */
    public function load_the_styles()
    {
        if (is_single(get_the_ID()) && get_post_type(get_the_ID()) == 'mec-events') {
            $event_id = get_the_ID();
            $mainClass      = new \MEC_main();
            $set            = $mainClass->get_settings();
            if(!isset($set['single_single_style'])) $set['single_single_style'] = 'default';
            if($set['single_single_style'] != 'divi-builder') {
                return;
            }

            if (
                !function_exists('\et_builder_theme_or_plugin_updated_cb')
            ) return;

            $post_id = get_post_meta($event_id, 'single_divi_single_default_builder', true);

            if (!$post_id || $post_id <= 0) {
                $post_id = (isset($set['single_divi_single_default_builder']) && $set['single_divi_single_default_builder']) ? $set['single_divi_single_default_builder'] : false;
            }

            if (!$post_id || !get_post($post_id)) {
                $post_id = false;
                global $wpdb;
                $query = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = \'mec_esdb\' LIMIT 1 ';
                $wpdb->query($query);

                if ($wpdb->num_rows) {
                    $post_id = $wpdb->get_var($query);
                }
                if (!$post_id || !get_post($post_id)) {
                    return;
                }
            }

            $manager = \ET_Builder_Element::setup_advanced_styles_manager($post_id);
            wp_enqueue_style('mec-event-single-style', $manager['manager']->URL);
            $width = get_post_meta($post_id, 'mec_divi_content_width', true);

            if($width) {
                wp_add_inline_style('mec-event-single-style', '.mec-container {width: ' . $width . '% !important;}');
            }
        }
    }

    /**
     * Render Event Description MetaBox
     * @param object $post
     */
    public function sb_metabox($post)
    {
        add_meta_box(
            'mec_esdb_choose_single_page',
            'Choose Single Template',
            [$this, 'renderEventDescriptionMetaBox'],
            'mec-events',
            'side',
            'default'
        );
    }

    /**
     * Content Width MetaBox
     * @param object $post
     */
    public function content_width_metabox($post)
    {
        add_meta_box(
            'mec_esdb_content_width',
            'Content Width',
            [$this, 'renderContentWidthMetaBoxContent'],
            'mec_esdb',
            'side',
            'default'
        );
    }

    /**
     * Render Event Description MetaBox
     * @param object $post
     */
    public function renderEventDescriptionMetaBox($post)
    {
        $mainClass      = new \MEC_main();
        $set            = $mainClass->get_settings();
        if(!isset($set['single_single_style'])) $set['single_single_style'] = 'default';
?>
        <?php if ($set['single_single_style'] != 'divi-builder') : ?>
            <label class="other-type-of-builder-title"><?php _e($set['single_single_style'] . ' Style', 'mec-divi-single-builder'); ?></label>
        <?php else : ?>
            <label class="post-attributes-label"><?php _e('Event single view.', 'mec-divi-single-builder'); ?></label>
            <div class="mec-form-row" id="mec_organizer_gateways_form_container">
                <select name="mec[single_divi_single_default_builder]" id="single_divi_single_default_builder">
                    <option value="-1"><?php echo __('Select'); ?></option>
                    <?php
                    $builders = get_posts([
                        'post_type' => 'mec_esdb',
                        'post_per_page' => -1
                    ]);
                    $selected_view = get_post_meta($post->ID, 'single_divi_single_default_builder', true);
                    foreach ($builders as $builder) {
                        $selected = $builder->ID == $selected_view ? ' selected="selected"' : '';
                        echo '<option value="' . $builder->ID . '"' . $selected . '>' . $builder->post_title . '</option>';
                    }
                    ?>
                </select>
            </div>
            <label class="post-attributes-label"><?php _e('Event modal view.', 'mec-divi-single-builder'); ?></label>
            <div class="mec-form-row" id="mec_organizer_gateways_form_container">
                <select name="mec[single_modal_default_divi_builder]" id="single_modal_default_divi_builder">
                    <option value="-1"><?php echo __('Select'); ?></option>
                    <?php
                    $builders = get_posts([
                        'post_type' => 'mec_esdb',
                        'post_per_page' => -1
                    ]);
                    $selected_view = get_post_meta($post->ID, 'single_modal_default_divi_builder', true);
                    foreach ($builders as $builder) {
                        $selected = $builder->ID == $selected_view ? ' selected="selected"' : '';
                        echo '<option value="' . $builder->ID . '"' . $selected . '>' . $builder->post_title . '</option>';
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="mec-esb-metabox-footer">
            <a href="<?php echo admin_url('admin.php?page=MEC-settings&tab=MEC-single#mec_settings_single_event_single_style'); ?>" target="_blank" class="mec-settings-btn"><?php echo __('Settings', 'mec-divi-single-builder'); ?></a>
            <?php if ($set['single_single_style'] == 'divi-builder') : ?>
                <a href="<?php echo admin_url('post-new.php?post_type=mec_esdb'); ?>" class="taxonomy-add-new">+ <?php echo __('Build new Single Design', 'mec-divi-single-builder'); ?></a>
            <?php endif; ?>
        </div>
    <?php
    }

    /**
     * Render Content Width MetaBox
     * @param object $post
     */
    public function renderContentWidthMetaBoxContent($post)
    {
        $width = get_post_meta(get_the_ID(), 'mec_divi_content_width', true);
    ?>
        <label>
            MEC-Container Width: (in %)
            <input type="number" min="0" max="100" name="mec_divi_content_width" value="<?php echo $width ?>" style="width:100%;margin-top: 10px;">
        </label>
    <?php
    }

    /**
     * Enqueue Editor Styles
     *
     * @return void
     */
    public function editor_styles()
    {
        if (get_post_type(get_the_ID()) == 'mec_esdb') {
            wp_enqueue_style('mec-divi-single-builder-editor', MECDSBASSETS . 'css/editor.css', [], '');
            wp_enqueue_script('mec-divi-single-builder-editor-js', MECDSBASSETS . 'js/editor.js', ['jquery'], '');
            if (!isset($_GET['et_bfb'])) {
                do_action('mec-divi-single-builder-editor-js', 'mec-divi-single-builder-editor-js');
                do_action('mec-divi-single-builder-editor-css');
                $post_id = get_the_ID( );
                $width = get_post_meta($post_id, 'mec_divi_content_width', true);
                if($width) {
                    wp_add_inline_style('mec-divi-single-builder-editor', '.mec-container {width: ' . $width . '% !important;}');
                }
            }
        }
    }

    /**
     * Enqueue Admin Styles
     *
     * @return void
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_style('mec-divi-single-builder-admin-css', MECDSBASSETS . 'css/backend/admin.css', [], '');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public static function getLastEventID()
    {
        if (get_post_type(get_the_ID()) == 'mec_events') {
            return get_the_ID();
        } else {
            $latest_cpt = get_posts("post_type=mec-events&numberposts=1");
            if (isset($latest_cpt[0]->ID)) {
                return $latest_cpt[0]->ID;
            }
            return false;
        }
    }

    /**
     * Add Single Builder Settings into Single Event Page Settings Section
     *
     * @since     1.0.0
     */
    public function add_settings($mec)
    {
        $settings = $mec->settings;
        $builders = get_posts([
            'post_type' => 'mec_esdb',
            'status' => 'published',
            'post_per_page' => -1
        ]);
    ?>
        <div class="mec-form-row" id="mec_settings_single_event_single_default_divi_builder_wrap" style="display:none;">
            <?php
            if (!$builders) {
                echo __('Please Create New Design for Single Event Page', 'mec-divi-single-builder');
                echo ' <a href="' . admin_url('post-new.php?post_type=mec_esdb') . '" class="taxonomy-add-new">' . __('Create new', 'mec-divi-single-builder') . '</a>';
            }
            ?>
            <label class="mec-col-3" for="mec_settings_single_event_single_default_divi_builder"><?php _e('Default Builder for Single Event', 'mec'); ?></label>
            <div class="mec-col-4">
                <select id="mec_settings_single_event_single_default_divi_builder" name="mec[settings][single_divi_single_default_builder]">
                    <?php foreach ($builders as $builder) : ?>
                        <option value="<?php echo $builder->ID ?>" <?php echo (isset($settings['single_divi_single_default_builder']) and $settings['single_divi_single_default_builder'] == $builder->ID) ? 'selected="selected"' : ''; ?>><?php echo $builder->post_title ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mec-form-row" id="mec_settings_single_event_divi_single_modal_default_builder_wrap" style="display:none;">
            <?php
            if (!$builders) {
                echo __('Please Create New Design for Single Event Page', 'mec-divi-single-builder');
                echo ' <a href="' . admin_url('post-new.php?post_type=mec_esdb') . '" class="taxonomy-add-new">' . __('Create new', 'mec-divi-single-builder') . '</a>';
            }
            ?>
            <label class="mec-col-3" for="mec_settings_single_event_divi_single_modal_default_builder"><?php _e('Default Builder for Modal View', 'mec'); ?></label>
            <div class="mec-col-4">
                <select id="mec_settings_single_event_divi_single_modal_default_builder" name="mec[settings][single_modal_default_divi_builder]">
                    <?php foreach ($builders as $builder) : ?>
                        <option value="<?php echo $builder->ID ?>" <?php echo (isset($settings['single_modal_default_divi_builder']) and $settings['single_modal_default_divi_builder'] == $builder->ID) ? 'selected="selected"' : ''; ?>><?php echo $builder->post_title ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <script>
            jQuery(document).ready(function() {
                if (jQuery('#mec_settings_single_event_single_style').val() == 'divi-builder') {
                    jQuery('#mec_settings_single_event_single_default_divi_builder_wrap').css('display', 'block');
                    jQuery('#mec_settings_single_event_divi_single_modal_default_builder_wrap').css('display', 'block');
                }

                jQuery('#mec_settings_single_event_single_style').on('change', function() {
                    if (jQuery(this).val() == 'divi-builder') {
                        jQuery('#mec_settings_single_event_single_default_divi_builder_wrap').css('display', 'block');
                        jQuery('#mec_settings_single_event_divi_single_modal_default_builder_wrap').css('display', 'block');
                    } else {
                        jQuery('#mec_settings_single_event_single_default_divi_builder_wrap').css('display', 'none');
                        jQuery('#mec_settings_single_event_divi_single_modal_default_builder_wrap').css('display', 'none');
                    }
                })
            })
        </script>

<?php
    }

    /**
     * Add Menu in WP Dashboard
     *
     * @return void
     */
    public function esdb_submenu()
    {
        if (!\MEC_DIVI_Single_Builder\Base::checkPlugins()) {
            return;
        }

        add_submenu_page('mec-intro', __('Divi Single Builder', 'mec-divi-single-builder'), __('Single Divi Builder', 'mec-divi-single-builder'), 'edit_posts', admin_url('edit.php?post_type=mec_esdb'));
    }
} //Admin

Admin::instance();
