<?php

namespace MEC_Map\Core;
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
class Loader
{

    /**
     * Instance of this class.
     *
     * @since   1.0.0
     * @access  public
     * @var     MEC_Map
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
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct()
    {
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
    public static function settingUp()
    {
        self::$dir     = MECMAPDIR . 'core';
    }

    /**
     * Hooks
     *
     * @since     1.0.0
     */
    public static function setHooks()
    {
        add_action('admin_init', function () {
            \MEC_Map\Autoloader::load('MEC_Map\Core\checkLicense\AdvancedMapAddonUpdateActivation');
        });
    }

    /**
     * preLoad
     *
     * @since     1.0.0
     */
    public static function preLoad()
    {
        include_once self::$dir . DS . 'autoloader' . DS . 'autoloader.php';
    }

    /**
     * Register Autoload Files
     *
     * @since     1.0.0
     */
    public static function registerAutoloadFiles()
    {
        if (!class_exists('\MEC_Map\Autoloader')) {
            return;
        }

        \MEC_Map\Autoloader::addClasses(
            [
                'MEC_Map\\Core\\Admin\\MecAdmin' => self::$dir . '/admin/admin.php',
                'MEC_Map\\Core\\Ui\\MecUi' => self::$dir . '/ui/ui.php',
                'MEC_Map\\Core\\checkLicense\\AdvancedMapAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',

            ]
        );
    }

    /**
     * Load Init
     *
     * @since     1.0.0
     */
    public static function loadInits()
    {
        \MEC_Map\Autoloader::load('MEC_Map\Core\Admin\MecAdmin');
        \MEC_Map\Autoloader::load('MEC_Map\Core\Ui\MecUi');

    }
} //Loader

Loader::instance();
