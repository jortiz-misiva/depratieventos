<?php
/**
 * Libs class
 */
class MDEVLibs {

	public static function wlog($msg, $level = 'DEBUG') {

		if (!WP_DEBUG) {
			return;
		}

		$p = MEC_EXT_PATH . '/trace.log';
		$trace = debug_backtrace();
		$m = "{$level} " . date('Ymdhi') . ' ' . "{$msg}\n";
		error_log($m, 3, $p);
	}

	public static function dbinstall() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$table = $wpdb->prefix . 'mec_external_sites';
		$sql = "CREATE TABLE `$table` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `post_id` int(11) NOT NULL,
			  `domain` char(200) NOT NULL,
			  `created_at` datetime NOT NULL,
			  `type` char(10) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4";
		dbDelta($sql);

		$addon_information = array(
			'product_name' => '',
			'purchase_code' => '',
		);

		$has_option = get_option( API_PLUGIN_OPTIONS , 'false');

		if ( $has_option == 'false' )
		{
			add_option( API_PLUGIN_OPTIONS, $addon_information);
		}

	}

	public static function dbuninstall() {
		global $wpdb;

	}

	public static function get_domain($url) {
		$pieces = parse_url($url);
		$domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,20})$/i', $domain, $regs)) {
			return  strtolower($regs['domain']);
		}
		return false;
	}
}
?>