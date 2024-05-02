<?php

namespace MEC_Advanced_Location\Core\Lib;

/**
 * Webnus MEC Featured, Backend ui class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Location_Lib_Backend_UI {

	public static $factory;

	public static $main;

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
		self::$main = \MEC::getInstance('app.libraries.main');
		self::$class = $class;
		self::$factory->filter('mec-settings-items-settings', array($class, 'add_setting_menu'), 1);
		self::$factory->action('mec-settings-page-before-form-end', array($class, 'setting_menu_content'));

		if( class_exists( \MEC\ImportExport\Terms::class ) ) {

			new \MEC\ImportExport\Terms( 'mec_location' );
		}
	}

	public static function edit_checkbox($term) {
		$featured = get_metadata('term', $term->term_id, 'featured', true);
		?>
        <tr class="form-field">
            <th scope="row">
                <label for="mec_featured"><?php _e('Featured', 'mec-advanced-location');?></label>
            </th>
            <td>
                <input type="checkbox" name="featured" id="mec_featured" value="1" <?php if ($featured == 1) {
			echo 'checked="checked"';
		}
		?> />
            </td>
        </tr>
		<?php
}

	public static function add_checkbox() {
		?>
		<div class="form-field">
            <label for="mec_featured" class="featured_addon_checbox_label"><?php _e('Featured', 'mec-advanced-location');?></label>
            <input type="checkbox" name="featured" id="mec_featured" value="1" />
        </div>
		<?php
}

	public static function save($term_id) {
		$featured = isset($_POST['featured']) ? sanitize_text_field($_POST['featured']) : '';
		update_term_meta($term_id, 'featured', $featured);

		$featured_map = isset($_POST['featured_map']) ? sanitize_text_field($_POST['featured_map']) : '0';
		update_term_meta($term_id, 'featured_map', $featured_map);
	}

	/**
	 * Load CSS Style
	 * @author Webnus <info@webnus.biz>
	 */
	public static function load_assets($class) {

		wp_enqueue_style('mec-featured-addon-style', MEC_ADVANCED_LOCATION_ASSETS . 'css/backend.css');

		return true;
	}

	public static function add_setting_menu($menus) {

		$title = sprintf(__('Advanced %s', 'mec-advanced-location'),self::$main->m('taxonomy_location', __('Location', 'mec-advanced-location')));

		$menus[$title] = 'advanced_location_option';
		return $menus;
	}

	public static function setting_menu_content($settings) {
		$main = self::$main;
		$ui = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'lib' . DS . 'settings.ui.php';
		include $ui;
	}
}