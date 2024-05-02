<?php

namespace MEC_Advanced_Location\Core;
// don't load directly.
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}
/**
 * Loader.
 *
 * @author      author
 * @package     package
 * @since       1.0.0
 */
class Loader {

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Invoice
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
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		self::settingUp();
		self::preLoad();
		self::setHooks();
		self::registerAutoloadFiles();
		self::loadInits();
	}

	/**
	 * Global Variables.
	 *
	 * @since   1.0.0
	 */
	public static function settingUp() {
		self::$dir = MEC_ADVANCED_LOCATION_DIR . 'core';
	}

	/**
	 * Hooks
	 *
	 * @since     1.0.0
	 */
	public static function setHooks() {
		add_action('admin_init', function () {
            \MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\checkLicense\AdvancedLocationAddonUpdateActivation');
        });
	}

	/**
	 * preLoad
	 *
	 * @since     1.0.0
	 */
	public static function preLoad() {
		include_once self::$dir . DS . 'autoloader' . DS . 'autoloader.php';
	}

	/**
	 * Register Autoload Files
	 *
	 * @since     1.0.0
	 */
	public static function registerAutoloadFiles() {
		if (!class_exists('\MEC_Advanced_Location\Autoloader')) {
			return;
		}

		\MEC_Advanced_Location\Autoloader::addClasses(
			[
				'MEC_Advanced_Location\\Core\\Lib\\MEC_Advanced_Location_Lib_Backend_UI' => self::$dir . '/lib/backend.ui.php',
				'MEC_Advanced_Location\\Core\\Lib\\MEC_Advanced_Location_Lib_Frontend_UI' => self::$dir . '/lib/frontend.ui.php',

				'MEC_Advanced_Location\\Core\\Lib\\MEC_Advanced_Location_Lib_Factory' => self::$dir . '/lib/factory.php',
				'MEC_Advanced_Location\\Core\\Lib\\MEC_Advanced_Location_Lib_Skin' => self::$dir . '/lib/skin.php',
				'MEC_Advanced_Location\\Core\\Lib\\MEC_Advanced_Location_Lib_Widget' => self::$dir . '/lib/widget.php',

				'MEC_Advanced_Location\\Core\\LocationWidget\\MEC_Advanced_Location_LocationWidget_Backend' => self::$dir . '/locationWidget/backend.php',
				'MEC_Advanced_Location\\Core\\LocationWidget\\MEC_Advanced_Location_LocationWidget_Frontend' => self::$dir . '/locationWidget/frontend.php',
				'MEC_Advanced_Location\\Core\\checkLicense\\AdvancedLocationAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',

			]
		);
	}

	/**
	 * Load Init
	 *
	 * @since     1.0.0
	 */
	public static function loadInits() {
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Backend_UI');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Frontend_UI');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Factory');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Skin');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Widget');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\LocationWidget\MEC_Advanced_Location_LocationWidget_Backend');
		\MEC_Advanced_Location\Autoloader::load('MEC_Advanced_Location\Core\LocationWidget\MEC_Advanced_Location_LocationWidget_Frontend');

	}
} //Loader

Loader::instance();
