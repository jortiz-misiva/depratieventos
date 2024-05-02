<?php

namespace MEC_Advanced_Location\Core\LocationWidget;

/**
 * Webnus MEC Location Backend Widget class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Location_LocationWidget_Backend {
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

		$backend_ui = \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Backend_UI::instance();

		self::$factory->action('mec_location_after_edit_form',array($backend_ui,'edit_checkbox'));
		self::$factory->action('mec_location_after_add_form',array($backend_ui,'add_checkbox'));

		self::$factory->action('mec_location_after_edit_form',array($class,'map_edit_checkbox'));
		self::$factory->action('mec_location_after_add_form',array($class,'map_add_checkbox'));

		self::$factory->action('mec_save_location_extra_fields',array($backend_ui,'save'));

	}

	public static function map_edit_checkbox($term){
		$featured_map = get_metadata('term', $term->term_id, 'featured_map', true);
		?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_featured_map"><?php _e('Map View on Featured', 'mec-advanced-location');?></label>
            </th>
            <td>
                <input type="checkbox" name="featured_map" id="mec_featured_map" value="1" <?php if ($featured_map == 1) {
			echo 'checked="checked"';
		}
		?> />
            </td>
        </tr>
		<?php
	}


	public static function map_add_checkbox(){
	?>
		<div class="form-field">
            <label for="mec_featured_map" class="featured_addon_checbox_label"><?php _e('Map View on Featured', 'mec-advanced-location');?></label>
            <input type="checkbox" name="featured_map" id="mec_featured_map" value="1" />
        </div>
		<?php
	}


	public static function add_widget(){
		register_widget( '\MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Widget' );
	}

}