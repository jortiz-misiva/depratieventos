<?php
/**
 * MEC Sync
 *
 * Plugin Name: Multisite Event Sync for MEC
 * Plugin URI: http://webnus.net/modern-events-calendar/
 * Description: MEC Event sync from parent to blogs.
 * Version:     1.1.0
 * Author:      Webnus
 * Author URI: http://webnus.net
 * Text Domain: mec-sync
 * Domain Path: /languages
 *
 */
if (!defined('ABSPATH')) {
	exit('restricted access');
}

define('MEC_SYNC_DIR', plugin_dir_path(__FILE__));
define('MEC_SYNC_URL', plugins_url('/', __FILE__));
define('MEC_SYNC_VERSION', '1.1.0');
define('MEC_SYNC_ETYPE', 'mec-events'); // event type of mec-events
define('MEC_SYNC_PLUGIN', plugin_basename(__FILE__));
define('MEC_SYNC_OPTIONS', 'mec-sync_options' );
define('MEC_SYNC_ORG_NAME', 'Multisite Event Sync' );
define('MEC_SYNC_SLUG' , 'mec-multisite-event-sync');
define('MEC_SYNC_TEXT_DOMAIN', 'mec-sync' );
define('MEC_SYNC_MAINFILEPATH' ,__FILE__);
define('MEC_SYNC_PABSPATH', dirname(__FILE__));

require MEC_SYNC_DIR . 'include/main.php';
require MEC_SYNC_DIR . 'include/libs.php';
require MEC_SYNC_DIR . 'include/admin.php';
require MEC_SYNC_DIR . 'include/ajax.php';
require_once MEC_SYNC_DIR . 'include/controller/update-activation.php';

/**
 * main load mec-sync
 * 1. checked is current blog is parent blog
 * 2. checked is admin area and load admin class
 * 3. main class any load on all pages
 * @return bool true on success
 */
function mec_sync_load_plugin() {

	if (defined('DOING_AJAX') && DOING_AJAX) {
		/**
		 * init ajax requests
		 * @var MEC_Sync_Ajax
		 * @since 0.0.1
		 */
		$ajax = new MEC_Sync_Ajax();
		$ajax->init();
	}

	// if (MEC_Sync_Libs::is_parent_blog() == false) {
	// 	add_filter('all_plugins', 'mec_sync_ignore_show');
	// }

	if (is_admin()) {

		/**
		 * init admin menu
		 * @var MEC_Sync_Admin Class
		 * @since  0.0.1
		 */
		$admin = new MEC_Sync_Admin();
		$admin->init();

	}

	$main = new MEC_Sync_Main();
	$main->init();

	return true;
}
add_action('plugins_loaded', 'mec_sync_load_plugin', 8); // callback for read to load plugin

/**
 * ready to install
 * @return void
 * @author Webnus <info@webnus.biz>
 */
function mec_sync_plugin_install() {
	MEC_Sync_Libs::dbinstall();
}
register_activation_hook(__FILE__, 'mec_sync_plugin_install');
