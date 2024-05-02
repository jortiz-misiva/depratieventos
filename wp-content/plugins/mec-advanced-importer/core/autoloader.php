<?php
namespace MEC_Advanced_Importer\Core;
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
		self::$dir = MEC_ADVANCED_IMPORTER_DIR . 'core';
	}

	/**
	 * Hooks
	 *
	 * @since     1.0.0
	 */
	public static function setHooks() {
		add_action('admin_init', function () {
			if (!defined('MEC_API_UPDATE')) return;
            \MEC_Advanced_Importer\Autoloader::load('MEC_Advanced_Importer\Core\checkLicense\AdvancedImporterAddonUpdateActivation');
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
		if (!class_exists('\MEC_Advanced_Importer\Autoloader')) {
			return;
		}

		\MEC_Advanced_Importer\Autoloader::addClasses(
			[
				'MEC_Advanced_Importer\\Core\\Lib\\MEC_Advanced_Importer_Lib_Backend_UI' => self::$dir . '/lib/backend.ui.php',
				'MEC_Advanced_Importer\\Core\\Lib\\MEC_Advanced_Importer_Main' => self::$dir . '/lib/main.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Facebook' => self::$dir . '/tabs/facebook.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Eventbrite' => self::$dir . '/tabs/eventbrite.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Meetup' => self::$dir . '/tabs/meetup.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Google' => self::$dir . '/tabs/google.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Mecapi' => self::$dir . '/tabs/mecapi.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Thirdparty' => self::$dir . '/tabs/thirdparty.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\ICS' => self::$dir . '/tabs/ics.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\History' => self::$dir . '/tabs/history.php',
				'MEC_Advanced_Importer\\Core\\Tabs\\Settings' => self::$dir . '/tabs/settings.php',

				'MEC_Advanced_Importer_Sync' => self::$dir . '/tabs/sync.php',
				'MEC_Advanced_Importer_Preview_Table' => self::$dir . '/tabs/preview-table.php',
				'MEC_Advanced_Importer_Sync_Table' => self::$dir . '/tabs/sync-table.php',
				'MEC_Advanced_Importer_Schedule_Table' => self::$dir . '/tabs/schedule-table.php',
				'MEC_Advanced_Importer_Preview_Accounts_Table' => self::$dir . '/tabs/preview-account-table.php',

                // License
                'MEC_Advanced_Importer\\Core\\checkLicense\\AdvancedImporterAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',

			]
		);
	}

	/**
	 * Load Init
	 *
	 * @since     1.0.0
	 */
	public static function loadInits() {
		\MEC_Advanced_Importer\Autoloader::load('MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Lib_Backend_UI');
		\MEC_Advanced_Importer\Autoloader::load('MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main');
	}
} //Loader

Loader::instance();
