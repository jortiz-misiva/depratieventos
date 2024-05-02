<?php

namespace MEC_Fluent\Core\pluginBase;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * MecFluent.
 *
 * @author      Webnus
 * @package     MEC_Fluent
 * @since       1.0.0
 */
class MecFluent
{
    /**
     * Instance of this class.
     *
     * @since   1.0.0
     * @access  public
     * @var     MEC_Fluent
     */
    public static $instance;

    /**
     * The directory of the file.
     *
     * @access  public
     * @var     string
     */
    public static $dir;

    /**
     * The Args
     *
     * @access  public
     * @var     array
     */
    public static $args;

    /**
     * Provides access to a single instance of a module using the singleton pattern.
     *
     * @since   1.0.0
     * @return  object
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
        self::setHooks($this);
        self::addImageSizes();
        self::init();
    }

    /**
     * Set Hooks.
     *
     * @since   1.0.0
     */
    public static function setHooks($This)
    {
        // Monthly
        add_action('mec_monthly_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_monthly_view_end', [$This, 'monthlyViewSkinOptions'], 1, 1);
        // Yearly
        add_action('mec_yearly_skin_style_options', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_yearly_view_end', [$This, 'yearlyViewSkinOptions'], 1, 1);
        // Timetable
        add_action('mec_timetable_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_timetable_end', [$This, 'timetableSkinOptions'], 1, 1);
        // Daily
        add_action('mec_daily_view_skin_style_options', [$This, 'fluentStyleOption'], 1, 1);
        add_action('mec-daily-initialize-end', [$This, 'dailyInitialize'], 1, 1);
        add_action('mec_skin_options_daily_view_end', [$This, 'dailyViewSkinOptions'], 1, 1);
        // Tile
        add_action('mec_skin_options_tile_init', [$This, 'tileSkinOptions'], 1, 1);
        // Weekly
        add_action('mec_weekly_view_skin_style_options', [$This, 'fluentStyleOption'], 1, 1);
        add_action('mec_skin_options_weekly_view_end', [$This, 'weeklyViewSkinOptions'], 1, 1);
        // Agenda
        add_action('mec_agenda_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_agenda_end', [$This, 'agendaSkinOptions'], 1, 1);
        // List
        add_action('mec_list_skin_style_options', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_list_end', [$This, 'listSkinOptions'], 1, 1);
        // Grid
        add_action('mec_grid_skin_style_options', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_grid_end', [$This, 'gridSkinOptions'], 1, 1);
        // Masorny
        add_action('mec-masonry-initialize-end', [$This, 'masonryInitialize'], 1, 1);
        add_action('mec_skin_options_masonry_init', [$This, 'masonrySkinOptions'], 1, 1);
        add_action('mec_skin_options_masonry_end', [$This, 'masonryEndSkinOptions'], 1, 1);
        // Slider
        add_action('mec-slider-initialize-end', [$This, 'sliderInitialize'], 1, 1);
        add_action('mec_slider_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_slider_end', [$This, 'sliderSkinOptions'], 1, 1);
        // Carousel
        add_action('mec-carousel-initialize-end', [$This, 'carouselInitialize'], 1, 1);
        add_action('mec_carousel_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_carousel_end', [$This, 'carouselSkinOptions'], 1, 1);
        // Countdown
        add_action('mec_countdown_fluent', [$This, 'fluentStyleOption'], 99, 1);
        add_action('mec_skin_options_countdown_end', [$This, 'countdownEndSkinOptions'], 1, 1);
        // Cover
        add_action('mec_cover_fluent', [$This, 'fluentStyleOptionMultiple'], 99, 2);
        add_action('mec_skin_options_cover_end', [$This, 'coverEndSkinOptions'], 1, 1);
        // Available Spot
        add_action('mec_available_spot_skin_style_options', [$This, 'availableSpotStyleOption'], 99, 1);
        add_action('mec_skin_options_available_spot_init', [$This, 'availableSpotSkinOptions'], 1, 1);
        add_action('mec_skin_options_available_spot_end', [$This, 'availableSpotEndSkinOptions'], 1, 1);
        // Full Calendar
        add_action('mec_full_calendar_skin_style_options', [$This, 'fluentStyleOption'], 1, 1);
        add_action('mec-full-calendar-initialize-end', [$This, 'fullCalendarInitialize'], 1, 1);
        add_filter('mec-full-calendar-load-skin-yearly', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-monthly', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-weekly', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-daily', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-list', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-grid', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_filter('mec-full-calendar-load-skin-tile', [$This, 'fullCalendarLoadSkin'], 99, 3);
        add_action('mec_skin_options_full_calendar_end', [$This, 'fullCalendarEndSkinOptions'], 1, 1);
        add_action('mec_skin_options', [$This, 'customOptions'], 1, 1);
        // Common
        add_filter('mec_filter_fields_search_form', [$This, 'fieldsSearchForm'], 99, 2);
        add_filter('mec_get_skin_tpl_path', [$This, 'tplPath'], 99, 3);
        add_filter('mec_locolize_data', [$This, 'locolizeData'], 99, 1);
        add_action('mec_end_styling_settings', [$This, 'stylingSettings'], 1, 1);
        // Single Page
        add_action('mec_single_style', [$This, 'singleSettings'], 1, 2);
        add_action('mec_start_skin', function ($id) {
            \MEC_Fluent\Core\pluginBase\MecFluent::$args = $id;
        }, 1, 1);
        // add_filter('mec_get_module_booking_step_path', [$This, 'moduleBookingCheckoutPath'], 1, 2);

        add_filter( 'mec_shortcode_builder_style_options', array( __CLASS__, 'filter_shortcode_builder_style_options' ), 10, 2 );
    }

    public function stylingSettings($styling)
    {
?>
        <h4 class="mec-form-subtitle"><?php esc_html_e('Fluent-view Layout Styles', 'mec'); ?></h4>
        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_styling_disable_fluent_height_limitation"><?php _e('Disable MEC Fluent Layouts height limitation', 'mec-fl'); ?></label>
            <div class="mec-col-9">
                <input type="hidden" name="mec[styling][disable_fluent_height_limitation]" value="0" />
                <input value="1" type="checkbox" id="mec_styling_disable_fluent_height_limitation" name="mec[styling][disable_fluent_height_limitation]" <?php if (isset($styling['disable_fluent_height_limitation']) and $styling['disable_fluent_height_limitation']) echo 'checked="checked"'; ?> />
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-3">
                <span><?php esc_html_e('Main Color', 'mec'); ?></span>
            </div>
            <div class="mec-col-9">
                <input type="text" class="wp-color-picker-field" id="mec_fluent_settings_main_color" name="mec[styling][fluent_main_color]" value="<?php echo (isset($styling['fluent_main_color']) ? $styling['fluent_main_color'] : ''); ?>" data-default-color="" />
                <span class="mec-tooltip">
                    <div class="box top">
                        <h5 class="title"><?php _e('Main Color', 'mec'); ?></h5>
                        <div class="content">
                            <p><?php esc_attr_e('The main color is for fluent and includes the color of icons and hover titles', 'mec'); ?></p>
                        </div>
                    </div>
                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                </span>
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-3">
                <span><?php esc_html_e('Second Color', 'mec'); ?></span>
            </div>
            <div class="mec-col-9">
                <input type="text" class="wp-color-picker-field" id="mec_fluent_settings_bold_color" name="mec[styling][fluent_bold_color]" value="<?php echo (isset($styling['fluent_bold_color']) ? $styling['fluent_bold_color'] : ''); ?>" data-default-color="" />
                <span class="mec-tooltip">
                    <div class="box top">
                        <h5 class="title"><?php _e('Second Color', 'mec'); ?></h5>
                        <div class="content">
                            <p><?php esc_attr_e('The second color is for the main color but can be more pronounced. You can set it on that same range', 'mec'); ?></p>
                        </div>
                    </div>
                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                </span>
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-3">
                <span><?php esc_html_e('Button Hover Color', 'mec'); ?></span>
            </div>
            <div class="mec-col-9">
                <input type="text" class="wp-color-picker-field" id="mec_fluent_settings_bg_hover_color" name="mec[styling][fluent_bg_hover_color]" value="<?php echo (isset($styling['fluent_bg_hover_color']) ? $styling['fluent_bg_hover_color'] : ''); ?>" data-default-color="" />
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-12">
                <p><?php esc_attr_e("If you want to use the default color, you must clear the color of this item", 'mec'); ?></p>
            </div>
        </div>

        <hr />

        <div class="mec-form-row">
            <div class="mec-col-3">
                <span><?php esc_html_e('Background Color', 'mec'); ?></span>
            </div>
            <div class="mec-col-9">
                <input type="text" class="wp-color-picker-field" id="mec_fluent_settings_bg_color" name="mec[styling][fluent_bg_color]" value="<?php echo (isset($styling['fluent_bg_color']) ? $styling['fluent_bg_color'] : ''); ?>" data-default-color="" />
                <span class="mec-tooltip">
                    <div class="box top">
                        <h5 class="title"><?php _e('Bachground color', 'mec'); ?></h5>
                        <div class="content">
                            <p><?php esc_attr_e('It is the color inside the shortcode and the sides. In some places it is the normal color of the buttons.', 'mec'); ?></p>
                        </div>
                    </div>
                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                </span>
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-3">
                <span><?php esc_html_e('Second Background Color', 'mec'); ?></span>
            </div>
            <div class="mec-col-9">
                <input type="text" class="wp-color-picker-field" id="mec_fluent_settings_bg_color" name="mec[styling][fluent_second_bg_color]" value="<?php echo (isset($styling['fluent_second_bg_color']) ? $styling['fluent_second_bg_color'] : ''); ?>" data-default-color="" />
                <span class="mec-tooltip">
                    <div class="box top">
                        <h5 class="title"><?php _e('Second Bachground color', 'mec'); ?></h5>
                        <div class="content">
                            <p><?php esc_attr_e('Similar to background color but for filtering section - background hover buttons for the slider and similar things', 'mec'); ?></p>
                        </div>
                    </div>
                    <i title="" class="dashicons-before dashicons-editor-help"></i>
                </span>
            </div>
        </div>

        <div class="mec-form-row">
            <div class="mec-col-12">
                <p><?php esc_attr_e("If you want to use the default color, you must clear the color of this item - this is work on background inside of all shortcode", 'mec'); ?></p>
            </div>
        </div>


    <?php
    }

    public function moduleBookingCheckoutPath($next_step, $settings)
    {
        if (isset($settings['single_single_style']) && $settings['single_single_style'] == 'fluent') {
            if ($next_step == 'checkout') {
                return MECFLUENTDIR . 'core' . DS . 'modules' . DS . 'booking' . DS . 'steps' . DS . 'checkout.php';
            }
        }
        return $next_step;
    }

    /**
     * Single Settings
     *
     * @since   1.0.0
     */
    public function singleSettings($settings, $key = 'single_single_style')
    {
    ?>
        <option value="fluent" <?php echo (isset($settings[ $key ]) and $settings[ $key ] == 'fluent') ? 'selected="selected"' : ''; ?>><?php _e('Fluent Style', 'mec'); ?></option>
    <?php
    }

    /**
     * Add Fluent Style to Skin Options
     *
     * @since 1.0.0
     */
    public function fluentStyleOption($settings)
    {
    ?>
        <option value="fluent" <?php if (isset($settings) && $settings === 'fluent') {
                                    echo 'selected="selected"';
                                } ?>><?php _e('Fluent', 'mec-fl'); ?></option>
        <?php
    }

    /**
     * Add Multiple Fluent Style to Skin Options
     *
     * @since 1.0.0
     */
    public function fluentStyleOptionMultiple($settings, $types)
    {
        foreach ($types as $type) {
        ?>
            <option value="fluent-<?php echo esc_attr($type); ?>" <?php if (isset($settings) && $settings === 'fluent-' . $type) {
                                                                        echo 'selected="selected"';
                                                                    } ?>><?php _e('Fluent', 'mec-fl');
                                                                            echo ' ' . esc_html(ucfirst($type)); ?></option>
        <?php
        }
    }

    /**
     * Add Multiple Fluent Style to Skin Options
     *
     * @since 1.0.0
     */
    public function availableSpotStyleOption($style){

        ?>
            <option value="fluent-type1" <?php if ($style == 'fluent-type1') {
                                                echo 'selected="selected"';
                                            } ?>><?php _e('Fluent Type1', 'mec-fl'); ?></option>
            <option value="fluent-type2" <?php if ($style == 'fluent-type2') {
                                                echo 'selected="selected"';
                                            } ?>><?php _e('Fluent Type2', 'mec-fl'); ?></option>
        <?php
    }

    /**
     * Daily Initialize Method
     *
     * @since 1.0.0
     */
    public function dailyInitialize($This)
    {
        $This->style = isset($This->skin_options['style']) ? $This->skin_options['style'] : 'classic';
    }

    /**
     * Tile Skin Options
     *
     * @since 1.0.0
     */
    public function tileSkinOptions($sk_options_tile)
    {
    ?>
        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_skin_tile_style"><?php _e('Style', 'mec-fl'); ?></label>
            <select class="mec-col-4 wn-mec-select" name="mec[sk-options][tile][style]" id="mec_skin_tile_style" onchange="mec_skin_style_changed('tile', this.value);">
                <option value="classic" <?php if (isset($sk_options_tile['style']) and $sk_options_tile['style'] == 'classic') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Classic', 'mec-fl'); ?></option>
                <option value="fluent" <?php if (isset($sk_options_tile['style']) and $sk_options_tile['style'] == 'fluent') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Fluent', 'mec-fl'); ?></option>
            </select>
        </div>
    <?php
    }

    /**
     * Weekly Skin Options
     *
     * @since 1.0.0
     */
    public function weeklySkinOptions($sk_options_weekly_view)
    {
    ?>
        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_skin_weekly_view_style"><?php _e('Style', 'mec-fl'); ?></label>
            <select class="mec-col-4 wn-mec-select" name="mec[sk-options][weekly_view][style]" id="mec_skin_weekly_view_style" onchange="mec_skin_style_changed('weekly_view', this.value);">
                <option value="classic" <?php if (isset($sk_options_weekly_view['style']) and $sk_options_weekly_view['style'] == 'classic') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Classic', 'mec-fl'); ?></option>
                <option value="fluent" <?php if (isset($sk_options_weekly_view['style']) and $sk_options_weekly_view['style'] == 'fluent') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Fluent', 'mec-fl'); ?></option>
            </select>
        </div>
    <?php
    }

    /**
     * List Skin Options
     *
     * @since 1.0.0
     */
    public function listSkinOptions($sk_options_list)
    {
    ?>
        <div class="mec-list-fluent mec-form-row">
            <label class="mec-col-4" for="mec_skin_list_fluent_date_format1"><?php _e('Date Formats', 'mec-fl'); ?></label>
            <input type="text" class="mec-col-4" name="mec[sk-options][list][fluent_date_format1]" id="mec_skin_list_fluent_date_format1" value="<?php echo ((isset($sk_options_list['fluent_date_format1']) and trim($sk_options_list['fluent_date_format1']) != '') ? $sk_options_list['fluent_date_format1'] : 'F d, Y'); ?>" />
            <span class="mec-tooltip">
                <div class="box top">
                    <h5 class="title"><?php _e('Date Formats', 'mec-fl'); ?></h5>
                    <div class="content">
                        <p><?php esc_attr_e('Default value is "F d, Y"', 'mec-fl'); ?><a href="https://webnus.net/dox/modern-events-calendar/list-view-skin/" target="_blank"><?php _e('Read More', 'mec-fl'); ?></a></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
        <div class="mec-list-fluent mec-form-row mec-switcher">
            <div class="mec-col-4">
                <label for="mec_skin_list_display_available_tickets"><?php _e('Display Available Tickets', 'mec-fl'); ?></label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options][list][display_available_tickets]" value="0" />
                <input type="checkbox" name="mec[sk-options][list][display_available_tickets]" id="mec_skin_list_display_available_tickets" value="1" <?php if (isset($sk_options_list['display_available_tickets']) and $sk_options_list['display_available_tickets']) {
                                                                                                                                                            echo 'checked="checked"';
                                                                                                                                                        } ?> />
                <label for="mec_skin_list_display_available_tickets"></label>
            </div>
        </div>
        <div class="mec-form-row mec-list-fluent">
            <label class="mec-col-4" for="mec_skin_list_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_list_wrapper_bg_color" name="mec[sk-options][list][wrapper_bg_color]" value="<?php echo ((isset($sk_options_list['wrapper_bg_color']) and trim($sk_options_list['wrapper_bg_color']) != '') ? $sk_options_list['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Agenda Skin Options
     *
     * @since 1.0.0
     */
    public function agendaSkinOptions($sk_options_agenda)
    {
    ?>
        <div class="mec-form-row mec-agenda-fluent">
            <label class="mec-col-4" for="mec_skin_agenda_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_agenda_wrapper_bg_color" name="mec[sk-options][agenda][wrapper_bg_color]" value="<?php echo ((isset($sk_options_agenda['wrapper_bg_color']) and trim($sk_options_agenda['wrapper_bg_color']) != '') ? $sk_options_agenda['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Yearly View Skin Options
     *
     * @since 1.0.0
     */
    public function yearlyViewSkinOptions($sk_options_yearly_view)
    {
    ?>
        <div class="mec-form-row mec-yearly_view-fluent">
            <label class="mec-col-4" for="mec_skin_yearly_view_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_yearly_view_wrapper_bg_color" name="mec[sk-options][yearly_view][wrapper_bg_color]" value="<?php echo ((isset($sk_options_yearly_view['wrapper_bg_color']) and trim($sk_options_yearly_view['wrapper_bg_color']) != '') ? $sk_options_yearly_view['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Monthly View Skin Options
     *
     * @since 1.0.0
     */
    public function monthlyViewSkinOptions($sk_options_monthly_view)
    {
    ?>
        <div class="mec-form-row mec-monthly_view-fluent">
            <label class="mec-col-4" for="mec_skin_monthly_view_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_monthly_view_wrapper_bg_color" name="mec[sk-options][monthly_view][wrapper_bg_color]" value="<?php echo ((isset($sk_options_monthly_view['wrapper_bg_color']) and trim($sk_options_monthly_view['wrapper_bg_color']) != '') ? $sk_options_monthly_view['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Daily View Skin Options
     *
     * @since 1.0.0
     */
    public function dailyViewSkinOptions($sk_options_daily_view)
    {
    ?>
        <div class="mec-form-row mec-daily_view-fluent">
            <label class="mec-col-4" for="mec_skin_daily_view_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_daily_view_wrapper_bg_color" name="mec[sk-options][daily_view][wrapper_bg_color]" value="<?php echo ((isset($sk_options_daily_view['wrapper_bg_color']) and trim($sk_options_daily_view['wrapper_bg_color']) != '') ? $sk_options_daily_view['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Weekly View Skin Options
     *
     * @since 1.0.0
     */
    public function weeklyViewSkinOptions($sk_options_weekly_view)
    {
    ?>
        <div class="mec-form-row mec-weekly_view-fluent">
            <label class="mec-col-4" for="mec_skin_weekly_view_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_weekly_view_wrapper_bg_color" name="mec[sk-options][weekly_view][wrapper_bg_color]" value="<?php echo ((isset($sk_options_weekly_view['wrapper_bg_color']) and trim($sk_options_weekly_view['wrapper_bg_color']) != '') ? $sk_options_weekly_view['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Timetable Skin Options
     *
     * @since 1.0.0
     */
    public function timetableSkinOptions($sk_options_timetable)
    {
    ?>
        <div class="mec-form-row mec-timetable-fluent">
            <label class="mec-col-4" for="mec_skin_timetable_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_timetable_wrapper_bg_color" name="mec[sk-options][timetable][wrapper_bg_color]" value="<?php echo ((isset($sk_options_timetable['wrapper_bg_color']) and trim($sk_options_timetable['wrapper_bg_color']) != '') ? $sk_options_timetable['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Masonry Skin Options
     *
     * @since 1.0.0
     */
    public function masonryEndSkinOptions($sk_options_masonry)
    {
    ?>
        <div class="mec-form-row mec-masonry-fluent">
            <label class="mec-col-4" for="mec_skin_masonry_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_masonry_wrapper_bg_color" name="mec[sk-options][masonry][wrapper_bg_color]" value="<?php echo ((isset($sk_options_masonry['wrapper_bg_color']) and trim($sk_options_masonry['wrapper_bg_color']) != '') ? $sk_options_masonry['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Cover Skin Options
     *
     * @since 1.0.0
     */
    public function coverEndSkinOptions($sk_options_cover)
    {
    ?>
        <div class="mec-form-row mec-cover-fluent">
            <label class="mec-col-4" for="mec_skin_cover_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_cover_wrapper_bg_color" name="mec[sk-options][cover][wrapper_bg_color]" value="<?php echo ((isset($sk_options_cover['wrapper_bg_color']) and trim($sk_options_cover['wrapper_bg_color']) != '') ? $sk_options_cover['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Available Spot Skin Options
     *
     * @since 1.0.0
     */
    public function availableSpotEndSkinOptions($sk_options_available_spot)
    {
    ?>
        <div class="mec-form-row mec-available_spot-fluent">
            <label class="mec-col-4" for="mec_skin_available_spot_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_available_spot_wrapper_bg_color" name="mec[sk-options][available_spot][wrapper_bg_color]" value="<?php echo ((isset($sk_options_available_spot['wrapper_bg_color']) and trim($sk_options_available_spot['wrapper_bg_color']) != '') ? $sk_options_available_spot['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Countdown Skin Options
     *
     * @since 1.0.0
     */
    public function countdownEndSkinOptions($sk_options_countdown)
    {
    ?>
        <div class="mec-form-row mec-countdown-fluent">
            <label class="mec-col-4" for="mec_skin_countdown_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_countdown_wrapper_bg_color" name="mec[sk-options][countdown][wrapper_bg_color]" value="<?php echo ((isset($sk_options_countdown['wrapper_bg_color']) and trim($sk_options_countdown['wrapper_bg_color']) != '') ? $sk_options_countdown['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Full Calendar Skin Options
     *
     * @since 1.0.0
     */
    public function fullCalendarEndSkinOptions($sk_options_full_calendar)
    {
    ?>
        <div class="mec-form-row mec-full_calendar-fluent">
            <label class="mec-col-4" for="mec_skin_full_calendar_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_full_calendar_wrapper_bg_color" name="mec[sk-options][full_calendar][wrapper_bg_color]" value="<?php echo ((isset($sk_options_full_calendar['wrapper_bg_color']) and trim($sk_options_full_calendar['wrapper_bg_color']) != '') ? $sk_options_full_calendar['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }
    /**
     * Custom Options
     *
     * @since 1.0.0
     */
    public function customOptions($sk_options)
    {
    ?>
        <style>span.mts-width:after {content: "Width (px)";position: absolute;left: 5px;bottom: -10px;font-size: 12px;width: 100%;color: #c1c1c1;text-align: center;font-style: italic;}span.mts-height:after {content: "Height (px)";position: absolute;left: 5px;bottom: -10px;font-size: 12px;width: 100%;color: #c1c1c1;text-align: center;font-style: italic;}span.mts-height,span.mts-width {position: relative;display: inline-block;}</style>
        <div class="mec-form-row mec-full_calendar-fluent" id="mec_skin_thumbnail_size_wrap" style="display:none">
            <label class="mec-col-4" for="mec_skin_full_calendar_wrapper_bg_color"><?php _e('Thumbnail Size', 'mec'); ?></label>
            <span class="mts-width">
                <input type="text" value="<?php echo (isset($sk_options['thumbnail_size']['width']) ? $sk_options['thumbnail_size']['width'] : '' ); ?>" placeholder="Width" id="mec_skin_thumbnail_size_width" name="mec[sk-options][thumbnail_size][width]">
            </span>
            <span class="mts-height">
                <input type="text" value="<?php echo (isset($sk_options['thumbnail_size']['height']) ? $sk_options['thumbnail_size']['height'] : '' ); ?>" placeholder="Height" id="mec_skin_thumbnail_size_height" name="mec[sk-options][thumbnail_size][height]">
            </span>
        </div>

        <script>
            jQuery(document).ready(function() {
                jQuery(document).on('change', '.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]', function() {
                    var skin = jQuery('#mec_skin').val();
                    if ((jQuery(this).val() == 'fluent' || jQuery(this).val() == 'fluent-type1' ||  jQuery(this).val() == 'fluent-type2' ||  jQuery(this).val() == 'fluent-type3') && skin != 'agenda' && skin != 'yearly_view' && skin != 'monthly_view' && skin != 'daily_view' && skin != 'weekly_view' && skin != 'timetable' && skin != 'available_spot') {
                        jQuery('#mec_skin_thumbnail_size_wrap').show();
                    } else {
                        jQuery('#mec_skin_thumbnail_size_wrap').hide();
                    }
                })

                jQuery('#mec_skin').on('change', function() {
                    var skin = jQuery(this).val();
                    setTimeout(() => {
                        var style = jQuery('.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]').val();
                        if ((style == 'fluent' || style == 'fluent-type1' ||  style == 'fluent-type2' ||  style == 'fluent-type3') && skin != 'agenda' && skin != 'yearly_view' && skin != 'monthly_view' && skin != 'daily_view' && skin != 'weekly_view' && skin != 'timetable' && skin != 'available_spot') {
                            jQuery('#mec_skin_thumbnail_size_wrap').show();
                        } else {
                            jQuery('#mec_skin_thumbnail_size_wrap').hide();
                        }
                    }, 200);
                })

                var skin = jQuery('#mec_skin').val();
                var style = jQuery('.mec-skin-options-container[style$="block;"] select[id$="_style"][name^="mec[sk-options]"][name$="[style]"]').val();
                if ((style == 'fluent' || style == 'fluent-type1' ||  style == 'fluent-type2' ||  style == 'fluent-type3') && skin != 'agenda' && skin != 'yearly_view' && skin != 'monthly_view' && skin != 'daily_view' && skin != 'weekly_view' && skin != 'timetable' && skin != 'available_spot') {
                    jQuery('#mec_skin_thumbnail_size_wrap').show();
                } else {
                    jQuery('#mec_skin_thumbnail_size_wrap').hide();
                }
            })
        </script>
    <?php
    }

    /**
     * Grid Skin Options
     *
     * @since 1.0.0
     */
    public function gridSkinOptions($sk_options_grid)
    {
    ?>
        <div class="mec-grid-fluent mec-form-row">
            <label class="mec-col-4" for="mec_skin_grid_fluent_date_format1"><?php _e('Date Formats', 'mec-fl'); ?></label>
            <input type="text" class="mec-col-4" name="mec[sk-options][grid][fluent_date_format1]" id="mec_skin_grid_fluent_date_format1" value="<?php echo ((isset($sk_options_grid['fluent_date_format1']) and trim($sk_options_grid['fluent_date_format1']) != '') ? $sk_options_grid['fluent_date_format1'] : 'D, F d, Y'); ?>" />
            <span class="mec-tooltip">
                <div class="box top">
                    <h5 class="title"><?php _e('Date Formats', 'mec-fl'); ?></h5>
                    <div class="content">
                        <p><?php esc_attr_e('Default value is "D, F d, Y"', 'mec-fl'); ?><a href="https://webnus.net/dox/modern-events-calendar/grid-view-skin/" target="_blank"><?php _e('Read More', 'mec-fl'); ?></a></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
        <div class="mec-form-row mec-grid-fluent">
            <label class="mec-col-4" for="mec_skin_grid_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_grid_wrapper_bg_color" name="mec[sk-options][grid][wrapper_bg_color]" value="<?php echo ((isset($sk_options_grid['wrapper_bg_color']) and trim($sk_options_grid['wrapper_bg_color']) != '') ? $sk_options_grid['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Masonry Initialize Method
     *
     * @since 1.0.0
     */
    public function masonryInitialize($This)
    {
        $This->style = isset($This->skin_options['style']) ? $This->skin_options['style'] : 'classic';
    }

    /**
     * Masonry Skin Options
     *
     * @since 1.0.0
     */
    public function masonrySkinOptions($sk_options_masonry)
    {
    ?>
        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_skin_masonry_style"><?php _e('Style', 'mec-fl'); ?></label>
            <select class="mec-col-4 wn-mec-select" name="mec[sk-options][masonry][style]" id="mec_skin_masonry_style" onchange="mec_skin_style_changed('masonry', this.value);">
                <option value="classic" <?php if (isset($sk_options_masonry['style']) and $sk_options_masonry['style'] == 'classic') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Classic', 'mec-fl'); ?></option>
                <option value="fluent" <?php if (isset($sk_options_masonry['style']) and $sk_options_masonry['style'] == 'fluent') {
                                            echo 'selected="selected"';
                                        } ?>><?php _e('Fluent', 'mec-fl'); ?></option>
            </select>
        </div>
    <?php
    }

    /**
     * Slider Initialize Method
     *
     * @since 1.0.0
     */
    public function sliderInitialize($This)
    {
        $This->display_price = (isset($This->skin_options['display_price']) and trim($This->skin_options['display_price'])) ? true : false;
        $This->display_available_tickets = (isset($This->skin_options['display_available_tickets']) and trim($This->skin_options['display_available_tickets'])) ? $This->skin_options['display_available_tickets'] : '';
    }

    /**
     * Slider Skin Options
     *
     * @since 1.0.0
     */
    public function sliderSkinOptions($sk_options_slider)
    {
    ?>
        <div class="mec-form-row mec-switcher mec-slider-fluent">
            <div class="mec-col-4">
                <label for="mec_skin_slider_display_price"><?php _e('Display Event Price', 'mec-fl'); ?></label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options][slider][display_price]" value="0" />
                <input type="checkbox" name="mec[sk-options][slider][display_price]" id="mec_skin_slider_display_price" value="1" <?php if (isset($sk_options_slider['display_price']) and $sk_options_slider['display_price']) {
                                                                                                                                        echo 'checked="checked"';
                                                                                                                                    } ?> />
                <label for="mec_skin_slider_display_price"></label>
            </div>
        </div>
        <div class="mec-form-row mec-switcher mec-slider-fluent">
            <div class="mec-col-4">
                <label for="mec_skin_slider_display_available_tickets"><?php _e('Display Available Tickets', 'mec-fl'); ?></label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options][slider][display_available_tickets]" value="0" />
                <input type="checkbox" name="mec[sk-options][slider][display_available_tickets]" id="mec_skin_slider_display_available_tickets" value="1" <?php if (isset($sk_options_slider['display_available_tickets']) and $sk_options_slider['display_available_tickets']) {
                                                                                                                                                                echo 'checked="checked"';
                                                                                                                                                            } ?> />
                <label for="mec_skin_slider_display_available_tickets"></label>
            </div>
        </div>
        <div class="mec-form-row mec-slider-fluent">
            <label class="mec-col-4" for="mec_skin_slider_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_slider_wrapper_bg_color" name="mec[sk-options][slider][wrapper_bg_color]" value="<?php echo ((isset($sk_options_slider['wrapper_bg_color']) and trim($sk_options_slider['wrapper_bg_color']) != '') ? $sk_options_slider['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Carousel Initialize Method
     *
     * @since 1.0.0
     */
    public function carouselInitialize($This)
    {
        // Navigation
        $This->navigation = (isset($This->skin_options['navigation']) and trim($This->skin_options['navigation'])) ? $This->skin_options['navigation'] : false;
        // Dots Navigation
        $This->dots_navigation = (isset($This->skin_options['dots_navigation']) and trim($This->skin_options['dots_navigation'])) ? $This->skin_options['dots_navigation'] : false;
    }

    /**
     * Carousel Skin Options
     *
     * @since 1.0.0
     */
    public function carouselSkinOptions($sk_options_carousel)
    {
    ?>
        <div class="mec-form-row mec-switcher mec-carousel-fluent">
            <div class="mec-col-4">
                <label for="mec_skin_carousel_navigation"><?php _e('Display Navigation', 'mec-fl'); ?></label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options][carousel][navigation]" value="0" />
                <input type="checkbox" name="mec[sk-options][carousel][navigation]" id="mec_skin_carousel_navigation" value="1" <?php if (isset($sk_options_carousel['navigation']) and $sk_options_carousel['navigation']) {
                                                                                                                                    echo 'checked="checked"';
                                                                                                                                } ?> />
                <label for="mec_skin_carousel_navigation"></label>
            </div>
        </div>
        <div class="mec-form-row mec-switcher mec-carousel-fluent">
            <div class="mec-col-4">
                <label for="mec_skin_carousel_dots_navigation"><?php _e('Display Dots Navigation', 'mec-fl'); ?></label>
            </div>
            <div class="mec-col-4">
                <input type="hidden" name="mec[sk-options][carousel][dots_navigation]" value="0" />
                <input type="checkbox" name="mec[sk-options][carousel][dots_navigation]" id="mec_skin_carousel_dots_navigation" value="1" <?php if (isset($sk_options_carousel['dots_navigation']) and $sk_options_carousel['dots_navigation']) {
                                                                                                                                                echo 'checked="checked"';
                                                                                                                                            } ?> />
                <label for="mec_skin_carousel_dots_navigation"></label>
            </div>
        </div>
        <div class="mec-form-row mec-carousel-fluent">
            <label class="mec-col-4" for="mec_skin_carousel_wrapper_bg_color"><?php _e('Wrapper Background Color', 'mec'); ?></label>
            <input type="text" class="mec-col-4 mec-color-picker wp-color-picker-field" id="mec_skin_carousel_wrapper_bg_color" name="mec[sk-options][carousel][wrapper_bg_color]" value="<?php echo ((isset($sk_options_carousel['wrapper_bg_color']) and trim($sk_options_carousel['wrapper_bg_color']) != '') ? $sk_options_carousel['wrapper_bg_color'] : ''); ?>" data-default-color="">
        </div>
    <?php
    }

    /**
     * Available Spot Skin Options
     *
     * @since 1.0.0
     */
    public function availableSpotSkinOptions($sk_options_available_spot)
    {
    ?>
        <div class="mec-available_spot-fluent mec-form-row">
            <label class="mec-col-4" for="mec_skin_available_spot_fluent_date_format1"><?php _e('Date Formats', 'mec-fl'); ?></label>
            <input type="text" class="mec-col-4" name="mec[sk-options][available_spot][fluent_date_format1]" id="mec_skin_available_spot_fluent_date_format1" value="<?php echo ((isset($sk_options_available_spot['fluent_date_format1']) and trim($sk_options_available_spot['fluent_date_format1']) != '') ? $sk_options_available_spot['fluent_date_format1'] : 'F d'); ?>" />
            <span class="mec-tooltip">
                <div class="box top">
                    <h5 class="title"><?php _e('Date Formats', 'mec-fl'); ?></h5>
                    <div class="content">
                        <p><?php esc_attr_e('Default value is "F d"', 'mec-fl'); ?><a href="https://webnus.net/dox/modern-events-calendar/available_spot-view-skin/" target="_blank"><?php _e('Read More', 'mec-fl'); ?></a></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    <?php
    }

    /**
     * Full Calendar Initialize Method
     *
     * @since 1.0.0
     */
    public function fullCalendarInitialize($This)
    {
        $This->style = isset($This->skin_options['style']) ? $This->skin_options['style'] : 'classic';
    }

    /**
     * Full Calendar Load Skin Method
     *
     * @since 1.0.0
     */
    public function fullCalendarLoadSkin($atts, $This, $skin)
    {
        if (strpos($This->style, 'fluent') === false) {
            return $atts;
        }
        $atts['sf_status'] = $This->sf_status;
        $atts['sk-options'][$skin]['style'] = 'fluent';
        $atts['sk-options'][$skin]['wrapper_bg_color'] = '';
        $atts['sf-options'][$skin] = [
            'category' => (isset($This->sf_options['category']) ? $This->sf_options['category'] : array()),
            'location' => (isset($This->sf_options['location']) ? $This->sf_options['location'] : array()),
            'organizer' => (isset($This->sf_options['organizer']) ? $This->sf_options['organizer'] : array()),
            'speaker' => (isset($This->sf_options['speaker']) ? $This->sf_options['speaker'] : array()),
            'tag' => (isset($This->sf_options['tag']) ? $This->sf_options['tag'] : array()),
            'label' => (isset($This->sf_options['label']) ? $This->sf_options['label'] : array()),
            'month_filter' => (isset($This->sf_options['month_filter']) ? $This->sf_options['month_filter'] : array()),
            'text_search' => (isset($This->sf_options['text_search']) ? $This->sf_options['text_search'] : array()),
            'time_filter' => isset($This->sf_options['time_filter']) ? $This->sf_options['time_filter'] : array(),
            'event_cost' => isset($This->sf_options['event_cost']) ? $This->sf_options['event_cost'] : array(),
        ];
        if ($skin == 'list') {
            $atts['sk-options'][$skin]['fluent_date_format1'] = isset($This->skin_options['date_format_list']) ? $This->skin_options['date_format_list'] : 'd M';
        }

        return $atts;
    }

    public function fieldsSearchForm($oldFields, $This)
    {
        if (strpos($This->style, 'fluent') === false) {
            return $oldFields;
        }

        $newHTML = '';
        $fields = new \DOMDocument();
        $oldFields = mb_convert_encoding($oldFields, 'HTML-ENTITIES', "UTF-8");
        $fields->loadHTML($oldFields);
        $finder = new \DomXPath($fields);

        // Search Input
        $searchInputDoc = new \DOMDocument();
        $searchInputNodes = $finder->query("//*[contains(@class, 'mec-text-input-search')]");
        if ($searchInputNodes->length > 0) {
            $searchInput = $searchInputNodes->item(0);
            foreach ($searchInput->childNodes as $childNode) {
                if (isset($childNode->tagName) && $childNode->tagName === 'input') {
                    $childNode->setAttribute('placeholder', esc_html__('Search', 'mec-fl'));
                }
            }
            $searchInputDoc->appendChild($searchInputDoc->importNode($searchInput, true));
            $newHTML .= trim($searchInputDoc->saveHTML());
        }

        // Filter Icon
        $newHTML .= '<i class="mec-filter-icon mec-fa-filter"></i>';

        // Filter Content
        $newHTML .= '<div class="mec-filter-content">';
        // checkboxes
        $dropdownDoc = new \DOMDocument();
        $dropdownNodes = $finder->query("//*[contains(@class, 'mec-simple-checkboxes-search')]");
        if ($dropdownNodes->length > 0) {
            foreach ($dropdownNodes as $dropdownNode) {
                $dropdownDoc->appendChild($dropdownDoc->importNode($dropdownNode, true));
            }
            $newHTML .= trim($dropdownDoc->saveHTML());
        }

        // checkboxes
        $dropdownDoc = new \DOMDocument();
        $dropdownNodes = $finder->query("//*[contains(@class, 'mec-checkboxes-search')]");
        if ($dropdownNodes->length > 0) {
            foreach ($dropdownNodes as $dropdownNode) {
                $dropdownDoc->appendChild($dropdownDoc->importNode($dropdownNode, true));
            }
            $newHTML .= trim($dropdownDoc->saveHTML());
        }

        // Dropdown
        $dropdownDoc = new \DOMDocument();
        $dropdownNodes = $finder->query("//*[contains(@class, 'mec-dropdown-search')]");
        if ($dropdownNodes->length > 0) {
            foreach ($dropdownNodes as $dropdownNode) {
                $dropdownDoc->appendChild($dropdownDoc->importNode($dropdownNode, true));
            }
            $newHTML .= trim($dropdownDoc->saveHTML());
        }
        // Date
        $dateDoc = new \DOMDocument();
        $dateNodes = $finder->query("//*[contains(@class, 'mec-date-search')]");
        if ($dateNodes->length > 0) {
            $date = $dateNodes->item(0);
            $dateDoc->appendChild($dateDoc->importNode($date, true));
            $newHTML .= trim($dateDoc->saveHTML());
        }

        // Cost
        $dateDoc = new \DOMDocument();
        $dateNodes = $finder->query("//*[contains(@class, 'mec-minmax-event-cost')]");
        if ($dateNodes->length > 0) {
            $date = $dateNodes->item(0);
            $dateDoc->appendChild($dateDoc->importNode($date, true));
            $newHTML .= trim($dateDoc->saveHTML());
        }

        // time picker
        $dateDoc = new \DOMDocument();
        $dateNodes = $finder->query("//*[contains(@class, 'mec-time-picker-search')]");
        if ($dateNodes->length > 0) {
            $date = $dateNodes->item(0);
            $dateDoc->appendChild($dateDoc->importNode($date, true));
            $newHTML .= trim($dateDoc->saveHTML());
        }

        // Address
        $addressInputDoc = new \DOMDocument();
        $addressInputNodes = $finder->query("//*[contains(@class, 'mec-text-address-search')]");
        if ($addressInputNodes->length > 0) {
            $addressInput = $addressInputNodes->item(0);
            foreach ($addressInput->childNodes as $childNode) {
                if (isset($childNode->tagName) && $childNode->tagName === 'input') {
                    $childNode->setAttribute('placeholder', esc_html__('Address', 'mec-fl'));
                }
            }
            $addressInputDoc->appendChild($addressInputDoc->importNode($addressInput, true));
            $newHTML .= trim($addressInputDoc->saveHTML());
        }
        $newHTML .= '</div>';

        return $newHTML;
    }

    public function tplPath($skin, $style, $path)
    {
        if ( false !== strpos($style, 'fluent') && false === strpos($path, DS.'webnus'.DS.'modern-events-calendar') ) {

            return MECFLUENTDIR . 'core' . DS . 'skins' . DS . $skin . DS . 'tpl.php';
        }

        return $skin;
    }

    public function locolizeData($data)
    {
        $settings = \MEC::getInstance('app.libraries.main')->get_settings();
        $data['day'] = esc_html__('DAY', 'mec-fl');
        $data['days'] = esc_html__('DAY', 'mec-fl');
        $data['hour'] = esc_html__('HRS', 'mec-fl');
        $data['hours'] = esc_html__('HRS', 'mec-fl');
        $data['minute'] = esc_html__('MIN', 'mec-fl');
        $data['minutes'] = esc_html__('MIN', 'mec-fl');
        $data['second'] = esc_html__('SEC', 'mec-fl');
        $data['seconds'] = esc_html__('SEC', 'mec-fl');
        $data['enableSingleFluent'] = class_exists('MEC_Fluent\Core\pluginBase\MecFluent') && (isset($settings['single_single_style']) and $settings['single_single_style'] == 'fluent') ? true : false;
        return $data;
    }

    public static function addImageSizes()
    {
        add_action('get_header', function () {
            \MEC_Fluent\Core\pluginBase\MecFluent::$args = get_the_ID();
        });
        // Add to mec render data
        add_filter('mec-render-data-thumbnails', function ($data, $post_id) {
            if(get_post_type( \MEC_Fluent\Core\pluginBase\MecFluent::$args ) != 'mec_calendars') {
                $content = get_the_content(false, false, \MEC_Fluent\Core\pluginBase\MecFluent::$args);
                preg_match_all('/\[MEC id=\"(.*?)\"\]/', $content, $match);
                $sk_ID = false;
                if (isset($match[1][0])) {
                    $sk_ID = $match[1][0];
                }
            } else {
                $sk_ID = \MEC_Fluent\Core\pluginBase\MecFluent::$args;
            }
            $sk_options = get_post_meta($sk_ID, 'sk-options', true);
            $width = $height = false;
            if (isset($sk_options['thumbnail_size']) && $sk_options['thumbnail_size']) {
                $width = isset($sk_options['thumbnail_size']['width']) ? $sk_options['thumbnail_size']['width'] : false;
                $height = isset($sk_options['thumbnail_size']['height']) ? $sk_options['thumbnail_size']['height'] : false;
            }

            $data['mecFluentTile'] = $data['tileview'] = $data['thumblist'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 300), ($height ? $height : 268), true), ($width ? $width : 300), ($height ? $height : 268));
            $data['mecFluentThumb'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 58), ($height ? $height : 58), true), ($width ? $width : 58), ($height ? $height : 58));
            $data['mecFluentList'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 200), ($height ? $height : 140), true), ($width ? $width : 200), ($height ? $height : 140));
            $data['mecFluentGrid'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 262), ($height ? $height : 190), true), ($width ? $width : 262), ($height ? $height : 190));
            $data['mecFluentMasonry'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 322), ($height ? $height : 322), true), ($width ? $width : 322), ($height ? $height : 322));
            $data['mecFluentSlider'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 644), ($height ? $height : 447), true), ($width ? $width : 644), ($height ? $height : 447));
            $data['mecFluentCarousel'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 322), ($height ? $height : 250), true), ($width ? $width : 322), ($height ? $height : 250));
            $data['mecFluentCountdown'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 495), ($height ? $height : 466), true), ($width ? $width : 495), ($height ? $height : 466));
            $data['mecFluentAvailableSpot'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 513), ($height ? $height : 450), true), ($width ? $width : 513), ($height ? $height : 450));
            $data['mecFluentCover'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 1026), ($height ? $height : 550), true), ($width ? $width : 1026), ($height ? $height : 550));
            $data['mecFluentFull'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 1026)), ($width ? $width : 1026));
            $data['mecFluentSinglePage'] = static::generateThumbnail(static::generateThumbnailURL($post_id, ($width ? $width : 1026), ($height ? $height : 362), true), ($width ? $width : 1026), ($height ? $height : 362));

            return $data;
        }, 1, 2);
        add_filter('mec-render-data-featured-image', function ($data, $post_id) {
            $content = get_the_content(false, false, \MEC_Fluent\Core\pluginBase\MecFluent::$args);
            preg_match_all('/\[MEC id=\"(.*?)\"\]/', $content, $match);
            $sk_ID = false;
            if (isset($match[1][0])) {
                $sk_ID = $match[1][0];
            }
            $sk_options = get_post_meta($sk_ID, 'sk-options', true);
            $width = $height = false;
            if (isset($sk_options['thumbnail_size']) && $sk_options['thumbnail_size']) {
                $width = isset($sk_options['thumbnail_size']['width']) ? $sk_options['thumbnail_size']['width'] : false;
                $height = isset($sk_options['thumbnail_size']['height']) ? $sk_options['thumbnail_size']['height'] : false;
            }
            $data['mecFluentThumb'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 58), ($height ? $height : 58), true)['url']);
            $data['mecFluentList'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 200), ($height ? $height : 140), true)['url']);
            $data['mecFluentGrid'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 262), ($height ? $height : 190), true)['url']);
            $data['mecFluentMasonry'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 322), ($height ? $height : 322), true)['url']);
            $data['mecFluentSlider'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 644), ($height ? $height : 447), true)['url']);
            $data['mecFluentCarousel'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 322), ($height ? $height : 250), true)['url']);
            $data['mecFluentCountdown'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 495), ($height ? $height : 466), true)['url']);
            $data['mecFluentAvailableSpot'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 513), ($height ? $height : 450), true)['url']);
            $data['mecFluentCover'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 1026), ($height ? $height : 550), true)['url']);
            $data['mecFluentFull'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 1026))['url']);
            $data['mecFluentSinglePage'] = esc_url(static::generateThumbnailURL($post_id, ($width ? $width : 1026), ($height ? $height : 362), true)['url']);
            return $data;
        }, 1, 2);
    }

    /**
     * Generate Thumbnail URL
     *
     * @since   1.0.0
     */
    public static function generateThumbnailURL($post_id, $sizeX = '', $sizeY = '', $crop = true)
    {
        $attachmentId = get_post_thumbnail_id($post_id);
        $imageArr = wp_get_attachment_image_src($attachmentId, 'single-post-thumbnail');
        $imageArrSRC = get_attached_file($attachmentId, 'single-post-thumbnail');
        if (!isset($imageArr[0])) {
            return [
                'url' => '',
                'alt' => '',
            ];
        }
        $imageAlt = get_post_meta($attachmentId, '_wp_attachment_image_alt', true);
        $imageSrc = $imageArr[0];

        $sizeX = $sizeX ? $sizeX : $imageArr[1];
        $sizeY = $sizeY ? $sizeY : $imageArr[2];
        $imagePath = WP_CONTENT_DIR . '/uploads/ab_resized/' . basename($imageSrc);

        $name = basename($imagePath);
        $withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $name);

        $newImagePath = str_replace($withoutExt, $withoutExt . '-mec-thumb-' . $sizeX . '-' . $sizeY , $imagePath);
        $newImageSrc = str_replace($withoutExt, $withoutExt . '-mec-thumb-' . $sizeX . '-' . $sizeY , $imageSrc);

        if (!file_exists($newImagePath)) {
            $image = wp_get_image_editor($imageArrSRC);
            if (!is_wp_error($image)) {
                $image->resize($sizeX, $sizeY, $crop);
                if (!realpath(WP_CONTENT_DIR . '/uploads/ab_resized')) {
                    @mkdir(WP_CONTENT_DIR . '/uploads/ab_resized/');
                }

                $filename = $image->generate_filename('mec-thumb-' . $sizeX . '-' . $sizeY , WP_CONTENT_DIR . '/uploads/ab_resized/', NULL); //create the upload folder; here: ab_resized
                $image->save($filename);
                $new_img_name = basename($filename);
                $newImageSrc = get_site_url() . '/wp-content/uploads/ab_resized/' . $new_img_name;
            } else {
                $newImageSrc = "An error occured while saving the image";
            }
        } else {
            $newImageSrc = get_site_url() . '/wp-content/uploads/ab_resized/' . basename($newImagePath);
        }

        return [
            'url' => $newImageSrc,
            'alt' => $imageAlt,
        ];
    }

    /**
     * Generate Thumbnail URL
     *
     * @since   1.0.0
     */
    public static function generateCustomThumbnailURL($imageSrc, $sizeX = '', $sizeY = '', $crop = true)
    {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . wp_make_link_relative($imageSrc);
        $name = basename($imagePath);
        $newImagePath = str_replace($name, 'mec-thumb-' . $sizeX . '-' . $sizeY . '-' . $name, $imagePath);
        $newImageSrc = str_replace($name, 'mec-thumb-' . $sizeX . '-' . $sizeY . '-' . $name, $imageSrc);
        if (!file_exists($newImagePath)) {
            $image = wp_get_image_editor($imagePath);
            if (!is_wp_error($image)) {
                $image->set_quality(100);
                $image->resize($sizeX, $sizeY, $crop);
                $image->save($newImagePath);
            }
        }
        return $newImageSrc;
    }

    /**
     * Generate Thumbnail
     *
     * @since   1.0.0
     */
    public static function generateThumbnail($img = [], $width = '', $height = '')
    {
        if (isset($img['url']) && $img['url']) {
            $widthAttr = $width ? ' width="' . esc_attr($width) . '"' : '';
            $heightAttr = $height ? ' height="' . esc_attr($height) . '"' : '';
            return '<img class="wp-post-image" src="' . esc_url($img['url']) . '"' . $widthAttr . $heightAttr . ' alt="' . esc_attr($img['alt']) . '">';
        }
        return '';
    }

    /**
     * Filter shortcode builder style options
     *
     * @param  array $styles
     * @param  string $skin
     *
     * @return array
     */
    public static function filter_shortcode_builder_style_options( $styles, $skin ) {

        switch( $skin ) {
            case 'list':
            case 'grid':
            case 'agenda':
            case 'full_calendar':
            case 'yearly_view':
            case 'monthly_view':
            case 'daily_view':
            case 'weekly_view':
            case 'timetable':
            case 'masonry':
            // case 'map':
            case 'countdown':
            case 'carousel':
            case 'slider':
            case 'tile':

                $styles['fluent'] = __( 'Fluent', 'mec-fl' );
                break;
            case 'cover':

                $types = array(
                    'type1',
                    'type2',
                    'type3',
                    'type4'
                );

                foreach( $types as $type ) {

                    $styles['fluent-' . $type] = __('Fluent', 'mec-fl') . ' ' . esc_html( ucfirst($type) );
                }

                break;
            case 'available_spot':

                $types = array(
                    'type1',
                    'type2',
                );

                foreach( $types as $type ) {

                    $styles['fluent-' . $type] = __('Fluent', 'mec-fl') . ' ' . esc_html( ucfirst($type) );
                }

                break;
        }

        return $styles;
    }

    /**
     * Register Autoload Files
     *
     * @since     1.0.0
     */
    public static function init()
    {
        if (!class_exists('\MEC_Fluent\Autoloader')) {
            return;
        }
    }
} // MecFluent
