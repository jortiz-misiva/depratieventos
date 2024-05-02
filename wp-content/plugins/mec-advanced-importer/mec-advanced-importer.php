<?php
/**
* Plugin Name: MEC Advanced Importer
* Plugin URI: http://webnus.net/modern-events-calendar/
* Description: With this addon, you can connect to Facebook, Google, Meetup, and Eventbrite and etc… unlimitedly. This makes the import process so easy. You should authenticate your account with just a few clicks and plan the import process. Also, with this plugin you can have more third-party * plugins for importing. You should initiate the process from a WordPress on which MEC is installed.
* Author: Webnus
* Version: 1.3.0
* Text Domain: mec-advanced-importer
* Domain Path: /languages
* Author URI: http://webnus.net
 **/
namespace MEC_Advanced_Importer;

// don't load directly.
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}
/**
 * Base.
 *
 * @author     Webnus Team
 * @package     package
 * @since     1.0.0
 */
class MEC_Advanced_Importer_Base {

	/**
	 * Instance of this class.
	 *
	 * @since   0.0.1
	 * @access  public
	 * @var     MEC_Invoice
	 */
	public static $instance;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   0.0.1
	 * @return  object
	 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function load_lang() {
		$lang_dir = dirname(__FILE__) . '/languages/';
		$mofile = "{$lang_dir}/mec-advanced-importer-" . get_locale() . ".mo";
		load_textdomain('mec-advanced-importer', $mofile);
	}

	public static function install() {

		$options = get_option('mec_options', array());
		$featured = isset($options['settings']['advanced_importer']) ? $options['settings']['advanced_importer'] : array();

		update_option('mec_options', $options);

	}

	public function __construct() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (!function_exists('is_plugin_active') || !(is_plugin_active('modern-events-calendar/mec.php') || is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php'))) {
			return;
		}

		self::settingUp();
		self::preLoad();
		self::setHooks($this);

		do_action('MEC_Advanced_Importer_init');

	}

	/**
	 * Global Variables.
	 *
	 * @since   0.0.1
	 */
	public static function settingUp() {

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}

		define('MEC_ADVANCED_IMPORTER_VERSION', '1.3.0');
		define('MEC_ADVANCED_IMPORTER_DIR', plugin_dir_path(__FILE__));
		define('MEC_ADVANCED_IMPORTER_URL', plugin_dir_url(__FILE__));
		define('MEC_ADVANCED_IMPORTER_ASSETS', MEC_ADVANCED_IMPORTER_URL . '/assets/');
		define('MEC_ADVANCED_IMPORTER_NAME', 'Advanced Importer');
		define('MEC_ADVIMP', 'mec-advanced-importer');
		define('MEC_ADVANCED_IMPORTER_CALLBACK', 'admin.php?page=MEC-advimp&advimp_cmd=');
		define('MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE', 'mec_advimp_scheduled');
		define('MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE', 'mec_advimp_history');
		define('MEC_ADVANCED_IMPORTER_CONTENET_DIR', MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents');
		define('MECADVANCEDIMPORTERSLUG' , 'mec-advanced-importer');
		define('MECADVANCEDIMPORTEROPTIONS' , 'mec_advanced_importer_options');
		define('MECADVANCEDIMPORTERTEXTDOMAIN' , 'mec-advanced-importer');
		define('MECADVANCEDIMPORTERMAINFILEPATH' ,__FILE__);
		define('MECADVANCEDIMPORTERPABSPATH', dirname(__FILE__));

	}

	/**
	 * Load Translation Languages
	 *
	 * @return void
	 */
	public function load_languages() {
		$locale = apply_filters('plugin_locale', get_locale(), 'mec-advanced-importer');

		// WordPress language directory /wp-content/languages/mec-en_US.mo
		$language_filepath = MEC_ADVANCED_IMPORTER_DIR . 'languages' . DIRECTORY_SEPARATOR . 'mec-advanced-importer-' . $locale . '.mo';
		// If language file exists on WordPress language directory use it
		if (file_exists($language_filepath)) {
			load_textdomain('mec-advanced-importer', $language_filepath);
		} else {
			load_plugin_textdomain('mec-advanced-importer', false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * Set Hooks
	 *
	 * @since     0.0.1
	 */
	public static function setHooks($This) {
		add_action( 'wp_loaded', [ $This, 'load_languages' ] );
	}

	/**
	 * preLoad
	 *
	 * @since     1.0.0
	 */
	public static function preLoad() {
		include_once MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'autoloader.php';
	}

} //MEC_Advanced_Importer_Base

function mec_advanced_importer_init(){

	$mec_advanced_importer_addon_init = MEC_Advanced_Importer_Base::instance();
	register_activation_hook(__FILE__, array($mec_advanced_importer_addon_init, 'install'));
	add_action('init', array($mec_advanced_importer_addon_init, 'load_lang'), 1, 1);
}

add_action('mec_init', '\MEC_Advanced_Importer\mec_advanced_importer_init' );
