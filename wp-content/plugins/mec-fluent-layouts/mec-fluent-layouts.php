<?php
/*
    Plugin Name: MEC Fluent-view Layouts
    Plugin URI: http://webnus.net/modern-events-calendar/
    Description: You will have some new designs after activate it.
    Author: Webnus
    Version: 1.6.0
    Text Domain: mec-fl
    Domain Path: /languages
    Author URI: http://webnus.net
 */
namespace MEC_Fluent;

// don't load directly.
if (! defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * Base.
 *
 * @author     Webnus
 * @package    MEC_Fluent
 * @since      1.0.0
 */
class Base
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
        if (defined('MECFLUENTVERSION')) {
            return;
        }
        self::settingUp();
        self::preLoad();
        self::setHooks($this);

        do_action('MEC_Fluent_init');
    }

    /**
     * Global Variables.
     *
     * @since   1.0.0
     */
    public static function settingUp()
    {
        define('MECFLUENTVERSION', '1.6.0');
        define('MECFLUENTDIR', plugin_dir_path(__FILE__));
        define('MECFLUENTURL', plugin_dir_url(__FILE__));
        define('MECFLUENTDASSETS', MECFLUENTURL . 'assets/');
        define('MECFLUENTNAME', 'Fluent-view Layouts');
        define('MECFLUENTSLUG', 'mec-fluent-layouts');
        define('MECFLUENTOPTIONS', 'mec_fluent_options');
        define('MECFLUENTTEXTDOMAIN', 'mec-fl');
        define('MECFLUENTMAINFILEPATH' ,__FILE__);
        define('MECFLUENTABSPATH', dirname(__FILE__));

        if (! defined('DS')) {
            define('DS', DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Set Hooks
     *
     * @since     1.0.0
     */
    public static function setHooks($This)
    {
        add_action('wp_enqueue_scripts', [$This, 'frontendScriptsPlugins'], 0);
        add_action('wp_enqueue_scripts', [$This, 'frontendScripts']);
        add_action('wp_footer', [$This, 'frontendScriptsPlugins'], 0);
        add_action('wp_footer', [$This, 'frontendScripts']);

        add_action('init', [$This, 'loadLanguages']);
    }

    /**
     * Load MEC Fluent Layouts language file from plugin language directory or WordPress language directory
     *
     * @since   1.0.0
     */
    public function loadLanguages()
    {
        load_plugin_textdomain('mec-fl', false, 'mec-fluent-layouts/languages');
    }

    /**
     **  MEC Version Admin Notice
     **
     **  @since     1.0.0
     */
    public function MECVersionAdminNotice($type = false)
    {
        $screen = get_current_screen();
        if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
            return;
        }

        $plugin = 'modern-events-calendar/mec.php';

        if (!current_user_can('install_plugins')) {
            return;
        }

        $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=modern-events-calendar'), 'install-plugin_' . $plugin);
        $message     = '<p>' . __('MEC Fluent Layouts is not working because you need to install latest version of Modern Events Calendar plugin', 'mec-fl') . '</p>';
        $message    .= esc_html__('Minimum version required') . ': <b> 5.4.0 </b>';
        $message    .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Update Modern Events Calendar Now', 'mec-fl')) . '</p>';
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

        /**
     **  MEC Version Admin Notice
     **
     **  @since     1.0.0
     */
    public function MECLiteVersionAdminNotice($type = false)
    {
        $screen = get_current_screen();
        if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
            return;
        }

        $plugin = 'modern-events-calendar-lite/modern-events-calendar-lite.php';

        if (!current_user_can('install_plugins')) {
            return;
        }

        $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=modern-events-calendar-lite'), 'install-plugin_' . $plugin);
        $message     = '<p>' . __('MEC Fluent Layouts is not working because you need to install latest version of Modern Events Calendar plugin', 'mec-fl') . '</p>';
        $message    .= esc_html__('Minimum version required') . ': <b> 5.4.0 </b>';
        $message    .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Update Modern Events Calendar Now', 'mec-fl')) . '</p>';
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }

    /**
     * Plugin Requirements Check
     *
     * @since 1.0.0
     */
    public static function checkPlugins()
    {
        if (!function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!self::$instance) {
            self::$instance = static::instance();
        }

        if (!is_plugin_active('modern-events-calendar/mec.php') && !is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php')) {
            add_action('admin_notices', [self::$instance, 'MECNotice']);
            return false;
        } elseif (is_plugin_active('modern-events-calendar/mec.php')) {
            $plugin_data = get_plugin_data(realpath(WP_PLUGIN_DIR . '/modern-events-calendar/mec.php'));
            $version     = str_replace('.', '', $plugin_data['Version']);
            if ($version < 540) {
                add_action('admin_notices', [self::$instance, 'MECVersionAdminNotice'], 'version');
                return false;
            }
        } elseif (is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php')) {
            $plugin_data = get_plugin_data(realpath(WP_PLUGIN_DIR . '/modern-events-calendar-lite/modern-events-calendar-lite.php'));
            $version     = str_replace('.', '', $plugin_data['Version']);
            if ($version < 540) {
                add_action('admin_notices', [self::$instance, 'MECLiteVersionAdminNotice'], 'version');
                return false;
            }
        }

        return true;
    }

    /**
     ** Send Admin Notice (MEC)
     **
     ** @since 1.0.0
     **/
    public function MECNotice($type = false)
    {
        $screen = get_current_screen();
        if (isset($screen->parent_file) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id) {
            return;
        }

        $plugin = 'modern-events-calendar-lite/modern-events-calendar-lite.php';
        if ($this->is_mec_installed()) {
            if (!current_user_can('activate_plugins')) {
                return;
            }
            $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin);
            $message        = '<p>' . __('MEC Fluent-view Layouts is not working because you need to activate the Modern Events Calendar plugin.', 'mec-fl') . '</p>';
            $message       .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $activation_url, __('Activate Modern Events Calendar Now', 'mec-fl')) . '</p>';
        } else {
            if (!current_user_can('install_plugins')) {
                return;
            }
            $install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=modern-events-calendar-lite'), 'install-plugin_modern-events-calendar-lite');
            $message     = '<p>' . __('MEC Fluent-view Layouts is not working because you need to install the Modern Events Calendar plugin', 'mec-fl') . '</p>';
            $message    .= '<p>' . sprintf('<a href="%s" class="button-primary">%s</a>', $install_url, __('Install Modern Events Calendar Now', 'mec-fl')) . '</p>';
        }
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }


    /**
     * Is MEC installed?
     *
     * @since     1.0.0
     */
    public function is_mec_installed()
    {
        $file_path         = 'modern-events-calendar-lite/modern-events-calendar-lite.php';
        $installed_plugins = get_plugins();
        return isset($installed_plugins[$file_path]);
    }

    /**
     * preLoad
     *
     * @since     1.0.0
     */
    public static function preLoad()
    {
        if (static::checkPlugins()) {
            include_once MECFLUENTDIR . DS . 'core' . DS . 'autoloader.php';
        }
    }

    public function can_enqueue_scripts(){

        $assets_in_footer_status = \MEC\Base::is_include_assets_in_footer();
        if(
            ( !$assets_in_footer_status && 'wp_footer' === current_action() )
            ||
            ( $assets_in_footer_status && 'wp_enqueue_scripts' === current_action() )
            ){

            return false;
        }

        return \MEC\Base::should_include_assets();
    }

    public function frontendScriptsPlugins()
    {

        if( $this->can_enqueue_scripts() ){

            wp_enqueue_script('date.format', MECFLUENTDASSETS . 'libs/date.format.min.js', [], '1.2.8', false);
            wp_enqueue_script('jquery.nicescroll', MECFLUENTDASSETS . 'libs/jquery.nicescroll.min.js', ['jquery'], '3.7.6', false);
            wp_enqueue_script('jquery.nice-select', MECFLUENTDASSETS . 'libs/jquery.nice-select.min.js', ['jquery'], '1.1.0', false);
            wp_enqueue_style('jquery.nice-select', MECFLUENTDASSETS . 'libs/nice-select.min.css', [], '1.1.0', 'all');
        }
    }

    public function frontendScripts()
    {

        if( $this->can_enqueue_scripts() ){
            wp_enqueue_script('mec-fluent-layouts', MECFLUENTDASSETS . 'mec-fluent-layouts.min.js', ['jquery'], MECFLUENTVERSION, true);
            wp_enqueue_style('mec-fluent-layouts', MECFLUENTDASSETS . 'mec-fluent-layouts.min.css', [], '1.0.0', 'all');
            wp_enqueue_style('mec-fluent-layouts-single', MECFLUENTDASSETS . 'mec-fluent-layouts-single.min.css', [], '1.0.0', 'all');

            $disabled_google_fonts = (bool)\MEC\Settings\Settings::getInstance()->get_styling('disable_gfonts');
            if( !$disabled_google_fonts ){

                wp_enqueue_style('mec-fluent-layouts-google-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&display=swap');
            }
        }
    }
} // Base

Base::instance();
