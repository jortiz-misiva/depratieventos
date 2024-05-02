<?php

namespace MEC_Advanced_Speaker\Core\SpeakerWidget;

/**
 * Webnus MEC Speaker Backend Widget class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Speaker_SpeakerWidget_Backend {
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

		add_action('widgets_init',array($this,'add_widget'));

		add_action('init', function () {
			self::init($this);
		}, 10, 1);
	}

	public static function init($class) {

		// Import MEC Factory
		self::$factory = \MEC::getInstance('app.libraries.factory');

		$backend_ui = \MEC_Advanced_Speaker\Core\Lib\MEC_Advanced_Speaker_Lib_Backend_UI::instance();

		self::$factory->action('mec_edit_speaker_extra_fields', array($backend_ui, 'edit_checkbox'));
		self::$factory->action('mec_add_speaker_extra_fields', array($backend_ui, 'add_checkbox'));
		self::$factory->action('mec_save_speaker_extra_fields', array($backend_ui, 'save'));

	}

	public static function add_widget(){
		register_widget( '\MEC_Advanced_Speaker\Core\Lib\MEC_Advanced_Speaker_Lib_Widget' );
	}

}