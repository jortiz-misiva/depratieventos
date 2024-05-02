<?php

namespace MEC_Advanced_Speaker\Core\Lib;

/**
 * Webnus MEC Featured, Backend ui class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Speaker_Lib_Frontend_UI {

	public static $factory;

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Advanced_Speaker
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

	public static $class;

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

		add_action('init', function () {
			self::init($this);
		}, 10, 1);

	}

	private static function init($class) {
		self::$factory = \MEC::getInstance('app.libraries.factory');
		self::$class = $class;

		self::$factory->shortcode('advanced-speaker-single-public', array($class, 'single_public_content'));

		self::$factory->filter('mec_event_dates_search', array($class, 'change_dates'), 1, 4);
	}

	public static function single_public_content($atts = null) {

		$edit = isset($_GET['action']) ? trim($_GET['action']) : null;
		$post = isset($_GET['post']) ? trim($_GET['post']) : null;
		$rest_route = isset($_GET['rest_route']) ? $_GET['rest_route'] : null;
		$_locale = isset($_GET['_locale'])?$_GET['_locale']:null;

		/**
		 * Current Wordpress gutenberg Bug on shortcode
		 * @link(stackoverflow, https://wordpress.stackexchange.com/questions/360279)
		 * @link(wordpress, https://wordpress.org/support/topic/fatal-error-with-wordpress-5-0-2-guteberg-editor/)
		 *
		 * @todo after filxed bug on next version remove the checked!
		 */
		ob_start();
		if ((is_admin() && $edit == 'edit' && $post != null) || $rest_route != null || $_locale!=null) {
			// cannot load
		} else {

			$section = isset($_GET['fesection']) ? trim($_GET['fesection']) : null;
			$id = isset($_GET['feparam']) ? trim($_GET['feparam']) : null;

			if (empty($section) || empty($id)) {
				return __('<h3>Modern Event Calendar Advanced Speaker Single Page</h3>', 'mec-advanced-speaker');
			}

			if ($atts == null) {
				$atts = array();
			}

			$atts = array('id' => $id);

			switch ($section) {
			case 'speaker':
				echo \MEC_Advanced_Speaker\Core\SpeakerWidget\MEC_Advanced_Speaker_SpeakerWidget_Frontend::single_speaker_content($atts);
				break;
			}
		}

		return ob_get_clean();
	}

	public static function change_dates($dates, $start, $end, $skin) {

		if (!isset($skin->atts['from_advanced_speaker_addon'])) {
			return $dates;
		}

		$start = date('Y-m-d');
		$skin->args['mec-past-events'] = false;

		if ($skin->atts['from_advanced_speaker_addon'] === 'count_ongoing') {
			$skin->show_ongoing_events = true;
			$dates = $skin->period($start, $start, true);
			return $dates;
		}

		if ($skin->load_more_button != true) {

			$skin->show_ongoing_events = true;
			$dates_1 = $skin->period($start, $end, true);
			$skin->show_ongoing_events = false;
			$dates_2 = $skin->period($start, $end, true);
			$dates = array_merge($dates_1, $dates_2);
		}
		return $dates;
	}

}