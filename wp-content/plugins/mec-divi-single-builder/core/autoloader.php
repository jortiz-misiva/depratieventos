<?php

namespace MEC_DIVI_Single_Builder\Core;
// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}
/**
* Loader.
*
* @author      Webnus <info@webnus.biz>
* @package     Modern Events Calendar
* @since       1.0.0
**/
class Loader
{

    /**
    *  Instance of this class.
    *
    *  @since   1.0.0
    *  @access  public
    *  @var     MEC_DIVI_Single_Builder
    */
    public static $instance;

   /**
    *  The directory of this file
    *
    *  @access  public
    *  @var     string
    */
    public static $dir;

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
        if (self::$instance === null) {
            self::$instance = $this;
        }
        self::settingUp();
        self::preLoad();
        self::setHooks();
        self::registerAutoloadFiles();
        self::loadInits();
    }

   /**
    *  Global Variables.
    *
    *  @since   1.0.0
    */
    public static function settingUp()
    {
        self::$dir     = MECDSBDIR . 'core';
    }

   /**
    *  Hooks
    *
    *  @since     1.0.0
    */
    public static function setHooks()
    {
        add_action('admin_init', function () {
            \MEC_DIVI_Single_Builder\Autoloader::load('MEC_DIVI_Single_Builder\Core\checkLicense\AddonSetOptions');
            \MEC_DIVI_Single_Builder\Autoloader::load('MEC_DIVI_Single_Builder\Core\checkLicense\AddonCheckActivation');
        });
    }

   /**
    *  preLoad
    *
    *  @since     1.0.0
    */
    public static function preLoad()
    {
        include_once self::$dir . DS . 'autoloader' . DS . 'autoloader.php';
    }

   /**
    *  Register Autoload Files
    *
    *  @since     1.0.0
    */
    public static function registerAutoloadFiles()
    {
        if (!class_exists('\MEC_DIVI_Single_Builder\Autoloader')) {
            return;
        }

        \MEC_DIVI_Single_Builder\Autoloader::addClasses(
            [


                // Post Type
                'MEC_DIVI_Single_Builder\\Core\\PostTypes\\MEC_ESDB' => self::$dir . '/postTypes/mec-esdb.php',

                // Manager
                'MEC_DIVI_Single_Builder\\Core\\Controller\\Admin' => self::$dir . '/controller/admin.php',

                // Licensing
                'MEC_DIVI_Single_Builder\\Core\\checkLicense\\AddonSetOptions' => self::$dir . '/checkLicense/set-options.php',
                'MEC_DIVI_Single_Builder\\Core\\checkLicense\\AddonCheckActivation' => self::$dir . '/checkLicense/check-activation.php',
                'MEC_DIVI_Single_Builder\\Core\\checkLicense\\AddonLicense' => self::$dir . '/checkLicense/get-license.php',
            ]
        );
    }

   /**
    *  Load Init
    *
    *  @since     1.0.0
    */
    public static function loadInits()
    {
        \MEC_DIVI_Single_Builder\Autoloader::load('MEC_DIVI_Single_Builder\Core\PostTypes\MEC_ESDB');
        \MEC_DIVI_Single_Builder\Autoloader::load('MEC_DIVI_Single_Builder\Core\Controller\Admin');
    }
} //Loader

Loader::instance();
