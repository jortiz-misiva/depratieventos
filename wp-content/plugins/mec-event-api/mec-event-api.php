<?php
/**
 * Plugin Name:     Event API for MEC
 * Plugin URI:      https://webnus.net/modern-events-calendar/event-api/
 * Description:     This addon enables you to display your website events (shortcodes/single event) on other websites without the need for a plug-in. You can even use json output which is one of its features to make your Apps compatible to MEC.
 * Author:          Webnus
 * Author URI:      https://webnus.net
 * Text Domain:     mec-event-API
 * Domain Path:     /languages
 * Version:         1.2.2
 */

defined('ABSPATH') || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugin_data = get_plugin_data(__FILE__);

define('MEC_EXT_PTYPE', 'mec-events'); /* mec post type, the right way extract from  $this->PT = $this->main->get_main_post_type(); */
define('MEC_EXT_CTYPE', 'mec_calendars');

define('MEC_EXT_URL', plugins_url('/', __FILE__));
define('MEC_EXT_PATH', plugin_dir_path(__FILE__));
define('MEC_EXT_PLUGIN', plugin_basename(__FILE__));
define('API_PLUGIN_VERSION', '1.2.2');
define('API_PLUGIN_OPTIONS', 'mec_event_api_options' );
define('API_PLUGIN_ORG_NAME', 'Event API' );
define('API_PLUGIN_TEXT_DOMAIN', 'mec-event-API' );
define('API_PLUGIN_SLUG', 'mec-event-api' );
define('API_PLUGIN_MAIN_FILE_PATH' ,__FILE__);
define('API_PLUGIN_ABSPATH', dirname(__FILE__));

require_once MEC_EXT_PATH . 'include/admin.php';
require_once MEC_EXT_PATH . 'include/public.php';
require_once MEC_EXT_PATH . 'include/ajax.php';
require_once MEC_EXT_PATH . 'include/libs.php';
require_once MEC_EXT_PATH . 'include/class-sites-model.php';
require_once MEC_EXT_PATH . 'include/class-rest.php';

add_action( 'admin_init', 'mec_event_api_license' );

if( !function_exists( 'mec_event_api_license' ) ) {

	function mec_event_api_license() {
		if (!defined('MEC_API_UPDATE')) return;
		require_once MEC_EXT_PATH . 'include/controller/update-activation.php';;
	}
}


add_filter('pll_get_post_types', 'mec_event_api_filter_pll_get_post_types',10,2);
function mec_event_api_filter_pll_get_post_types ( $post_types, $is_settings ){

    if ( $is_settings ) {
        unset( $post_types['mec_calendars'] );
    } else {
        $post_types['mec_calendars'] = 'mec_calendars';
    }

    return $post_types;
}


function mec_external_plugin_install() {
	MDEVLibs::dbinstall();
}
register_activation_hook(__FILE__, 'mec_external_plugin_install');


function mec_external_plugin_uninstall(){
	$path = get_template_directory();

	$them_file = "{$path}/mec-external.php";
	if (file_exists($them_file)) {
		unlink($them_file);
	}

	$single = "{$path}/single-mec-events-external.php";
	if (file_exists($single)) {
		unlink($single);
	}
}
register_deactivation_hook( __FILE__, 'mec_external_plugin_uninstall' );

function add_script_to_single_event( $name ) {

	echo '<script type="text/javascript" src="'.MEC_EXT_URL.'assets/iframeResizer.contentWindow.min.js"></script>';
}
add_action( 'wp_head', 'add_script_to_single_event' );


function mec_external_load_plugin() {

	if (is_admin()) {

		/**
		 * init admin menu and showed result of courses
		 * @var MEC_External_Admin Class
		 * @since  0.0.1
		 */
		$admin = new MEC_External_Admin();
		$admin->init();

	} else {

		/**
		 * is not admin area load other nedded user area
		 * @var MEC_External_Public
		 */
		$public = new MEC_External_Public();
		$public->init();
	}

	if (defined('DOING_AJAX') && DOING_AJAX) {
		/**
		 * init ajax requests
		 * @var MEC_External_Ajax
		 * @since 0.0.1
		 */
		$ajax = new MEC_External_Ajax();
		$ajax->init();
	}


	$rest = new MEC_External_Rest();
	$rest->init();

	if( 'yes' !== get_option('mec_api_remove_template_files', false) ){

		mec_external_plugin_uninstall();
		update_option('mec_api_remove_template_files', 'yes');
	}

	return true;
}
add_action('plugins_loaded', 'mec_external_load_plugin', 8);