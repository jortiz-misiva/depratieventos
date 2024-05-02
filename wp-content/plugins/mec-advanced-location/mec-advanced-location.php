<?php
/**
 * Plugin Name: MEC Advanced Location
 * Plugin URI: http://webnus.net/modern-events-calendar/
 * Description: This addon gives you an exclusive page for every Location and you can have a list of locations wherever you like using a shortcode. You can also display locations in a slider format.
 * Author: Webnus
 * Version: 1.3.2
 * Text Domain: mec-advanced-location
 * Domain Path: /languages
 * Author URI: http://webnus.net
 **/
namespace MEC_Advanced_Location;

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
class MEC_Advanced_Location_Base {

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

	public function init(){

		add_filter('mec_location_single_page_link',[__CLASS__,'location_single_page_link'],10,2);
	}


	public static function load_lang(){
			$lang_dir = dirname(__FILE__) . '/languages/';
			$mofile = "{$lang_dir}/mec-advanced-location-" . get_locale() . ".mo";
			load_textdomain('mec-advanced-location', $mofile);
	}

	public static function install() {

		$options = get_option('mec_options', array());
		$featured = isset($options['settings']['advanced_location']) ? $options['settings']['advanced_location'] : array();

		if (!isset($featured['single_page'])) {
			$page = array(
				'post_type' => 'page',
				'post_title' => __('MEC Location Details Single Page', 'mec-advanced-location'),
				'post_content' => '[advanced-location-single-public]',
				'post_status' => 'publish',
				'post_author' => 1,
			);
			$id = wp_insert_post($page);

			if(!isset($options['settings'])){
				$options = array('settings'=>null);
			}

			if (!isset($options['settings']['advanced_location'])) {
				$options['settings']['advanced_location'] = array(
					'single_page' => $id,
					'location_skin' => 'list',
					'location_event_skin' => 'list',
					'location_event_limit' => 12,
					'location_limit' => 12,
					'location_cols' => 3,
					'location_load_more' => 1,
					'location_detaile' => 'option_website',
					'location_show_event_list' => 1
				);
			} else {
				$options['settings']['advanced_location']['single_page'] = $id;
			}

			update_option('mec_options', $options);
		}

	}

	public function __construct() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (!function_exists('is_plugin_active') || (!is_plugin_active('modern-events-calendar/mec.php') && !is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php'))) {
			return;
		}

		self::settingUp();
		self::preLoad();
		self::setHooks($this);

		do_action('MEC_Advanced_Location_init');

	}

	/**
	 * Global Variables.
	 *
	 * @since   0.0.1
	 */
	public static function settingUp() {
		define('MEC_ADVANCED_LOCATION_VERSION', '1.3.2');
		define('MEC_ADVANCED_LOCATION_DIR', plugin_dir_path(__FILE__));
		define('MEC_ADVANCED_LOCATION_URL', plugin_dir_url(__FILE__));
		define('MEC_ADVANCED_LOCATION_ASSETS', MEC_ADVANCED_LOCATION_URL . 'assets/');
		define('MEC_ADVANCED_LOCATION_NAME' , 'Advanced Location');
		define('MEC_ADVANCED_LOCATION_SLUG' , 'mec-advanced-location');
		define('MEC_ADVANCED_LOCATION_OPTIONS' , 'mec_advanced_location_options');
		define('MEC_ADVANCED_LOCATION_TEXTDOMAIN' , 'mec-advanced-location');
		define('MEC_ADVANCED_LOCATION_ROWS_LIMIT', 12);
		define('MEC_ADVANCED_LOCATION_MAINFILEPATH' ,__FILE__);
		define('MEC_ADVANCED_LOCATION_PABSPATH', dirname(__FILE__));

		if (!defined('DS')) {
			define('DS', DIRECTORY_SEPARATOR);
		}
	}

	/**
	 * Set Hooks
	 *
	 * @since     0.0.1
	 */
	public static function setHooks($This) {
	}

	/**
	 * preLoad
	 *
	 * @since     1.0.0
	 */
	public static function preLoad() {
		include_once MEC_ADVANCED_LOCATION_DIR . DS . 'core' . DS . 'autoloader.php';
		add_action('mec_shortcode_list_terms_init',[__CLASS__,'load_taxonomy_searchbar']);
	}

	/**load class Mec_Taxonomy_Search_Bar
	 *
	 * @since 1.0.1
	 * @return void
	 */
	public static function load_taxonomy_searchbar(){
		if(!class_exists('Mec_Taxonomy_Search_Bar')){

			include_once __DIR__ .'/core/taxonomy-searchbar.php';
			\MEC_Taxonomy_Search_Bar::getInstance()->init();
		}
	}

	public static function location_single_page_link($location_link,$location_id){

		$skin = new Core\Lib\MEC_Advanced_Location_Lib_Skin();
		$location_link = $skin->single_page_url($location_id);

		return $location_link;
	}

} //MEC_Advanced_Location_Base

$mec_advanced_location_addon_init = MEC_Advanced_Location_Base::instance();
$mec_advanced_location_addon_init->init();

register_activation_hook(__FILE__, array($mec_advanced_location_addon_init, 'install'));
add_action('init',array($mec_advanced_location_addon_init,'load_lang'),1,1);
