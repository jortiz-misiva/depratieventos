<?php
/**
* Plugin Name: MEC Advanced Map
* Plugin URI: https://webnus.net/modern-events-calendar/
* Description: Now you can use OpenStreetMap instead of Google Maps and enjoy new features such as Street, Region, etc. for detailed and precise filtering. Display filters and events next to the map in a new view.
* Author: Webnus
* Version: 1.0.8
* Text Domain: mec-map
* Domain Path: /languages
* Author URI: https://webnus.net
*/

namespace MEC_Map;

// don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit;
}
/**
 * Base.
 *
 * @author    Webnus Team
 * @package   package
 * @since     1.0.0
 */
class Base {

	/**
	 * Instance of this class.
	 *
	 * @since   0.0.1
	 * @access  public
	 * @var     MEC_Map
	 */
	public static $instance;

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   0.0.1
	 * @return  object
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function __construct() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		if (!function_exists('is_plugin_active') || !(is_plugin_active('modern-events-calendar/mec.php') || is_plugin_active('modern-events-calendar-lite/modern-events-calendar-lite.php'))) {
			return;
		}

		self::settingUp();
		self::preLoad();
		self::setHooks($this);

		do_action( 'MEC_Map_init' );
	}

	/**
	 * Global Variables.
	 *
	 * @since   0.0.1
	 */
	public static function settingUp() {
		define('MECMAPVERSION' , '1.0.8');
		define('MECMAPDIR' , plugin_dir_path(__FILE__));
		define('MECMAPURL' , plugin_dir_url(__FILE__));
		define('MECMAPASSETS' , MECMAPURL . 'assets/' );
		define('MECMAPNAME' , 'Advanced Map');
		define('MECMAPSLUG' , 'mec-advanced-map');
		define('MECMAPOPTIONS' , 'mec_advanced_map_options');
		define('MECMAPTEXTDOMAIN' , 'mec-map');
		define('MECMAPMAINFILEPATH' ,__FILE__);
		define('MECMAPPABSPATH', dirname(__FILE__));

		if ( ! defined( 'DS' ) ) {
			define( 'DS', DIRECTORY_SEPARATOR );
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
		include_once MECMAPDIR . DS . 'core' . DS . 'autoloader.php';
	}

	public function load_languages() {

		$locale = apply_filters('plugin_locale', get_locale(), 'mec-map');

		// WordPress language directory /wp-content/languages/mec-en_US.mo
		$language_filepath = MECMAPDIR . 'languages/mec-map-' . $locale . '.mo';
		// If language file exists on WordPress language directory use it
		if (file_exists($language_filepath)) {
			load_textdomain('mec-map', $language_filepath);
		} else {
			load_plugin_textdomain('mec-map', false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR);
		}
	}

	public static function get_map_fields(){

		return [
			'state' => __('State','mec-map'),
			'city' => __('City','mec-map'),
			'region' => __('Region','mec-map'),
			'street' => __('Street','mec-map'),
			'postal_code' => __('Postal code','mec-map'),
		];

	}

} //Base

Base::instance();
