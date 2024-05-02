<?php

namespace MEC_Fluent\Core;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
 * Loader.
 *
 * @author      Webnus
 * @package     MEC_Fluent
 * @since       1.0.0
 */
class Loader
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
        self::$dir     = MECFLUENTDIR . 'core';
    }

    /**
     * Hooks
     *
     * @since     1.0.0
     */
    public static function setHooks()
    {
        add_action('admin_init', function () {
            if (!defined('MEC_API_ACTIVATION')) return; 
            \MEC_Fluent\Autoloader::load('MEC_Fluent\Core\checkLicense\FluentAddonUpdateActivation');
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
        if (!class_exists('\MEC_Fluent\Autoloader')) {
            return;
        }

        \MEC_Fluent\Autoloader::addClasses(
            [
                'MEC_Fluent\\Core\\pluginBase\\MecFluent' => self::$dir . '/pluginBase/mec-fluent.php',
                'MEC_Fluent\\Core\\checkLicense\\FluentAddonUpdateActivation' => self::$dir . '/checkLicense/update-activation.php',
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
        \MEC_Fluent\Autoloader::load('MEC_Fluent\Core\pluginBase\MecFluent');
    }
} //Loader

Loader::instance();
