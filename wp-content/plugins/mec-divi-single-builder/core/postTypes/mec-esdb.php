<?php

namespace MEC_DIVI_Single_Builder\Core\PostTypes;

// Don't load directly
if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

/**
*   MEC_ESDB.
*
*   @author      Webnus <info@webnus.biz>
*   @package     Modern Events Calendar
*   @since       1.0.0
*/
class MEC_ESDB
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
    *  The Args
    *
    *  @access  public
    *  @var     array
    */
    public static $args;

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
        self::setHooks($this);
        self::init();
    }

   /**
    *  Set Hooks.
    *
    *  @since   1.0.0
    */
    public static function setHooks($This)
    {
        add_action('init', [$This, 'postTypeInit'], 99);
    }

   /**
    *  Global Variables.
    *
    *  @since   1.0.0
    */
    public static function settingUp()
    {

        self::$dir  = MECDSBDIR . 'core' . DS . 'postTypes';

        // Set UI labels for Custom Post Type
        $labels = array(
                'name'                => _x('Divi Single Builders', 'Post Type General Name', 'mec-divi-single-builder'),
                'singular_name'       => _x('Divi Single Builder', 'Post Type Singular Name', 'mec-divi-single-builder'),
                'menu_name'           => __('Divi Single Builders', 'mec-divi-single-builder'),
                'parent_item_colon'   => __('Parent Divi Single Builder', 'mec-divi-single-builder'),
                'all_items'           => __('All Divi Single Builders', 'mec-divi-single-builder'),
                'view_item'           => __('View Divi Single Builder', 'mec-divi-single-builder'),
                'add_new_item'        => __('Add New Divi Single Builder', 'mec-divi-single-builder'),
                'add_new'             => __('Add New', 'mec-divi-single-builder'),
                'edit_item'           => __('Edit Divi Single Builder', 'mec-divi-single-builder'),
                'update_item'         => __('Update Divi Single Builder', 'mec-divi-single-builder'),
                'search_items'        => __('Search Divi Single Builder', 'mec-divi-single-builder'),
                'not_found'           => __('Not Found', 'mec-divi-single-builder'),
                'not_found_in_trash'  => __('Not found in Trash', 'mec-divi-single-builder'),
            );

        self::$args = [
            'label'               => __('Divi Single Builders', 'mec-divi-single-builder'),
            'description'         => __('Divi Single Builder news and reviews', 'mec-divi-single-builder'),
            'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'query_var'          => true,
			'has_archive'        => false,
			'hierarchical'       => false,
            'rewrite'            => ['slug' => 'mec_esdb'],
            'supports'           => ['title', 'editor', 'divi'],
            'labels'              => $labels,
            'exclude_from_search' => true
        ];
    }

   /**
    *  Post Type Init
    *
    *  @since     1.0.0
    */
    public function postTypeInit () {
        register_post_type('mec_esdb', self::$args);
    }

   /**
    *  Init Function
    *
    *  @since     1.0.0
    */
    public function init()
    {
        if (!class_exists('\MEC_DIVI_Single_Builder\Autoloader')) {
            return;
        }
    }
} //MEC_ESDB
