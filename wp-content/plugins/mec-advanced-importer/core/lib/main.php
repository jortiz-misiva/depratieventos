<?php

namespace MEC_Advanced_Importer\Core\Lib;

/**
 * Webnus MEC featured class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Importer_Main {

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Advanced_Importer
	 */
	public static $instance;

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

	public static function settings($section = 'facebook', $type = 'auth', $id = null, $key = null) {
		$s = get_option("mec_advimp_{$type}_{$section}", array());
		if (!$s || count($s) == 0) {
			return array();
		}

		if ($id != null && isset($s[$id]) && $key == null) {
			return $s[$id];
		}

		if ($key != null && isset($s[$id][$key])) {
			return $s[$id][$key];
		}

		return array();
	}

	public static function download_event_image($image_url, $parent_id) {

		$image = $image_url;

		$get = wp_remote_get($image);

		$type = wp_remote_retrieve_header($get, 'content-type');

		if (!$type) {
			return false;
		}

		$mirror = wp_upload_bits(basename($image), '', wp_remote_retrieve_body($get));

		$attachment = array(
			'post_title' => basename($image),
			'post_mime_type' => $type,
		);

		$attach_id = wp_insert_attachment($attachment, $mirror['file'], $parent_id);

		require_once ABSPATH . 'wp-admin/includes/image.php';

		$attach_data = wp_generate_attachment_metadata($attach_id, $mirror['file']);

		wp_update_attachment_metadata($attach_id, $attach_data);

		return $attach_id;

	}

	public static function active_account($section, $oauth = false, $account = null, $key = null,&$status='') {

		$auth = array();
		$config = get_option('mec_advimp_config_' . $section, array());

		if ($oauth == true) {
			$auth = get_option('mec_advimp_auth_' . $section, array());
		}

		if (count($config) == 0) {
			return null;
		}

		$ret = array();
		foreach ($config as $k => $v) {

			if ($v['active'] != 1) {
				continue;
			}

			if ($oauth == false) {
				$ret[$k] = $v['title'];
			} else if ($oauth == true && isset($auth[$k]) && isset($auth[$k]['status']) && $auth[$k]['status'] == true) {

				$expired = isset($auth[$k]['expires_in']) ? $auth[$k]['expires_in'] : null;
				if( $section == 'google'){
					$is_expired = false;
				} else {
					$is_expired = $expired != null && $expired < time() ? true : false;
				}
				
				if ($section == 'google' && $expired != null && $is_expired == false) {
					\MEC_Advanced_Importer\Core\Tabs\Google::refresh_token($k);
				}
				if ($is_expired) {
					$status .= "<strong>{$v['title']}</strong> Expired please Authentication<br/>";
					continue;
				}

				$ret[$k] = $v['title'];
			}
		}

		if (count($ret) == 0) {
			return null;
		}

		if ($account != null && $key == null && isset($config[$account])) {
			return $config[$account];
		}

		if ($key != null && isset($config[$account]) && isset($config[$account][$key])) {
			return $config[$account][$key];
		}

		return $ret;

	}

}
