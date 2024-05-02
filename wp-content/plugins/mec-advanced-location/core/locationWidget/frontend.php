<?php

namespace MEC_Advanced_Location\Core\LocationWidget;

use \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Skin;
use \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Factory;

/**
 * Webnus MEC Location Backend Widget class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Location_LocationWidget_Frontend {
	public static $factory;

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Featured
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

	/**
	 * Constructor method
	 * @author Webnus <info@webnus.biz>
	 */
	public function __construct() {

		self::$dir = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'locationWidget' . DS;

		add_action('init', function () {
			self::init($this);
		}, 10, 1);
	}

	public static function init($class) {
		// Import MEC Factory
		self::$factory = \MEC::getInstance('app.libraries.factory');

		$edit = isset($_GET['action'])? trim($_GET['action']) : null;
		$post = isset($_GET['post']) && is_string($_GET['post']) ? trim($_GET['post']) : null;
		$rest_route = isset($_GET['rest_route']) ? $_GET['rest_route'] : null;

		/**
		 * Current Wordpress gutenberg Bug on shortcode
		 * @link(stackoverflow, https://wordpress.stackexchange.com/questions/360279)
		 * @link(wordpress, https://wordpress.org/support/topic/fatal-error-with-wordpress-5-0-2-guteberg-editor/)
		 *
		 * @todo after filxed bug on next version remove the checked!
		 */
		 if( (is_admin() && $edit=='edit' && $post!=null) || $rest_route != null){
			// cannot load
		}else{
			self::$factory->shortcode('mec-location', array($class, 'single_location_content'));
			self::$factory->shortcode('location-list', array($class, 'location_list_content'));
			self::$factory->shortcode('location-featured', array($class, 'location_featured_content'));
		}



		self::$factory->action('wp_ajax_mec_featured_location_load_more', array($class, 'load_more'));
		self::$factory->action('wp_ajax_nopriv_mec_featured_location_load_more', array($class, 'load_more'));

	}

	public static function single_location_content($atts = null) {

		$atts = MEC_Advanced_Location_Lib_Factory::extract_attrs($atts, true, 'location');

		$term = get_term($atts['id'], 'mec_location', ARRAY_A);
		$meta = MEC_Advanced_Location_Lib_Factory::get_meta_key_val($atts['id'],$atts['exclude_details']);

		$featured = get_metadata('term', $atts['id'], 'featured', true) == 1;

		if (!$term || count($term) == 0) {
			echo '<p>'.__( 'Location Not Found!', 'mec-advanced-location' ).'</p>';
			return null;
		}

		if ($atts['events_style'] == 'grid') {
			$path = \MEC::import('app.skins.tile', true, true);
			include_once $path;
			$skin_class_name = 'MEC_skin_tile';
			$atts['skin'] = 'tile';

		} else {
			$path = \MEC::import('app.skins.list', true, true);
			include_once $path;
			$skin_class_name = 'MEC_skin_list';
			$atts['skin'] = 'list';
			$atts['sk-options']['list']['style'] = 'standard';
		}

		$atts['sk-options'][$atts['skin']]['next_previous_button'] = false;

		// Create Skin Object Class
		$SKO = new $skin_class_name();
		$SKO->skin = $atts['skin'];

		$atts['location'] = $atts['id'];
		if ($atts['only_ongoing_events'] != true) {
			$atts['from_advanced_location_addon'] = true;
		}

		// Initialize the skin
		$SKO->initialize($atts);

		$SKO->load_more_button = false;
		if ($atts['load_more'] == true) {
			$SKO->load_more_button = true;
		}

		$SKO->load_method = 'list';

		if ($atts['only_ongoing_events'] == true) {
			$SKO->show_ongoing_events = true;
		}

		if ($atts['events_style'] == 'list') {
			$SKO->style = 'standard';
		}

		$meta['section'] = 'location';

		// Fetch the events
		$SKO->fetch();

		$theme_path = get_template_directory() .DS. 'webnus' .DS. 'mec-advanced-location' . DS . 'skins' . DS;

		$custom_tpl_path = $theme_path . 'single.php';
		if( file_exists( $custom_tpl_path ) ) {

			$path = $custom_tpl_path;
		} else {

			$path = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'skins' . DS . 'single.php';
		}
		ob_start();
		include $path;
		return ob_get_clean();

	}

	public function location_list_content($atts = null) {

		$atts = MEC_Advanced_Location_Lib_Factory::extract_attrs($atts, false, 'location');

		$skin = new MEC_Advanced_Location_Lib_Skin();
		$skin->section = 'location';

		// Initialize the skin
		$skin->initialize($atts);

		// Fetch the events
		$skin->fetch();
		return $skin->output();
	}

	public function load_more() {

		$skin = new MEC_Advanced_Location_Lib_Skin();
		$skin->dir = self::$dir;
		$skin->section = 'location';
		return $skin->load_more();
	}

	public function location_featured_content($atts) {

		if ($atts == null) {
			$atts = array();
		}

		$atts = MEC_Advanced_Location_Lib_Factory::extract_attrs($atts, false, 'location');
		$main = \MEC::getInstance('app.libraries.main');

		$main->load_owl_assets();

		$skin = new MEC_Advanced_Location_Lib_Skin();
		$skin->section = 'location';
		$atts['out_style'] = 'featured';

		// Initialize the skin
		$skin->initialize($atts);

		// Fetch the events
		$skin->fetch();

		$theme_path = get_template_directory() .DS. 'webnus' .DS. 'mec-advanced-location' . DS . 'skins' . DS;

		$custom_tpl_path = $theme_path . 'slider.php';
		if( file_exists( $custom_tpl_path ) ) {

			$path = $custom_tpl_path;
		} else {

			$path = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'skins' . DS . 'slider.php';
		}
		ob_start();
		include $path;
		return ob_get_clean();
	}

}
