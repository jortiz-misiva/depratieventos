<?php

namespace MEC_DIVI_Single_Builder\Core\checkLicense;

// don't load directly.
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
 * AddonSetOptions.
 *
 * @author      Webnus <info@webnus.biz>
 * @package     Modern Events Calendar
 * @since       1.0.0
 */
class AddonSetOptions
{

    /**
     * Instance of this class.
     *
     * @since   1.0.0
     * @access  public
     * @var     MEC_DIVI_Single_Builder
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
        self::init();
    }

    /**
     * Set Hooks.
     *
     * @since   1.0.0
     */
    public static function setHooks($This)
    {
        add_action('admin_init', [$This,'add_license_options'] , 999);
    }

    /**
     * Booking metabox menu item (login)
     *
     * @since     1.0.0
     */
    public function add_license_options()
    {
        $addon_information = array(
			'product_name' => '',
			'purchase_code' => '',
		);

		$has_option = get_option( MECDSBOPTIONS , 'false');

		if ( $has_option == 'false' )
		{
			add_option( MECDSBOPTIONS, $addon_information);
		}
    }


    /**
     * Register Autoload Files
     *
     * @since     1.0.0
     */
    public function init()
    {
        if (!class_exists('\MEC_DIVI_Single_Builder\Autoloader')) {
            return;
        }
    }
} //MECDSB
