<?php

/**
 * Webnus MEC_Sync libs class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Sync_Libs {

	/**
	 * install required table if not exists
	 * @return void
	 */
	public static function dbinstall() {

		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset_collate = $wpdb->get_charset_collate();
		$table = $wpdb->prefix . 'mec_sync';
		$sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
				`id` bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`source_post_id` bigint NOT NULL,
				`blog_id` bigint NOT NULL,
				`destination_post_id` bigint NOT NULL,
				`created_at` datetime NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		dbDelta($sql);

	}


	/**
	 * detect the config is event sync
	 * @return boolean true is event sync
	 */
	public static function is_event_sync() {
		$options = get_option('mec_sync_settings');
		return isset($options['sync_events']) && (int) $options['sync_events'] == 1 ? true : false;
	}

	/**
	 * detect the config is setting sync
	 * @return boolean true is setting sync
	 */
	public static function is_setting_sync() {
		$options = get_option('mec_sync_settings');
		return isset($options['sync_settings']) && (int) $options['sync_settings'] == 1 ? true : false;
	}

	/**
	 * checked the current blog is parent or sub-site
	 * @return boolean true is parent blog
	 */
	public static function is_parent_blog() {

		if ( get_current_blog_id() !== get_main_site_id() ) {

			return false;
		}

		return true;
	}

	public static function get_sync_sites_status(){

		$options = get_blog_option(SITE_ID_CURRENT_SITE,'mec_sync_settings');
		return isset($options['sites']) ? $options['sites'] : array();
	}

	public static function sync_sites() {

		$sites = static::get_sync_sites_status();
		$blog_ids = get_sites();

		$ret = array();

		foreach ($blog_ids as $b) {
			if (array_key_exists($b->blog_id, $sites)) {
				array_push($ret, $b);
			}
		}

		return $ret;
	}

	public static function is_synced_post($destination_post_id = null, $source_post_id = null, $field = 'id', $blog_id = null) {
		global $wpdb;

		if ($blog_id == null) {
			$blog_id = get_current_blog_id();
		}

		$where = null;

		if ($destination_post_id != null) {
			$where = " AND destination_post_id=$destination_post_id ";
		} else if ($source_post_id != null) {
			$where = " AND source_post_id=$source_post_id ";
		}

		$sql = "SELECT {$field} FROM {$wpdb->base_prefix}mec_sync WHERE blog_id=$blog_id {$where} LIMIT 1";
		return $wpdb->get_var($sql);
	}

	public static function attach_file($source_post_id, $destination_post_id, $attached_post_id, $source_upload_dir = null) {

		$upload_dir = wp_upload_dir();

		$post_attachments = get_children( [
			'post_parent' => $destination_post_id
		]);

		foreach ($post_attachments as $attachment) {
			wp_delete_attachment($attachment->ID, true);
		}

		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->base_prefix}posts WHERE ID={$attached_post_id} LIMIT 1";
		$post = $wpdb->get_row($sql);

		$sql = "SELECT meta_value,meta_key FROM {$wpdb->base_prefix}postmeta WHERE post_id={$attached_post_id}";
		$rows = $wpdb->get_results($sql);
		$file = null;
		$description = null;

		foreach ($rows as $k => $v) {
			if ($v->meta_key == '_wp_attached_file') {
				$file = $v->meta_value;
			}

			if ($v->meta_key == '_wp_attachment_metadata') {
				$description = $v->meta_value;
			}
		}

		$full = $source_upload_dir['basedir'] . '/' . $file;
		$basename = basename($file);

		$path = $upload_dir['path'];
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}

		$url = $upload_dir['url'] . '/' . $basename;
		$saved_path = $upload_dir['subdir'] . '/' . $basename;

		$dest = $path . '/' . $basename;
		if (file_exists($full) && !file_exists($dest)) {
			copy($full, $dest);
		}

		$attachment = array(
			'guid' => $url,
			'post_mime_type' => $post->post_mime_type,
			'post_title' => $post->post_title,
			'post_content' => '',
			'post_status' => $post->post_status,
		);

		$attach_id = wp_insert_attachment($attachment, $saved_path, $destination_post_id);

		foreach (array('_wp_attached_file' => $saved_path, '_wp_attachment_metadata' => $description) as $k => $v) {
			if (!add_post_meta($attach_id, $k, $v, true)) {
				update_post_meta($attach_id, $k, $v);
			}
		}
		foreach ($rows as $meta) {
			if($meta->meta_key == '_wp_attachment_metadata') continue;
			if($meta->meta_key == '_wp_attached_file') continue;
			update_post_meta( $attach_id, $meta->meta_key, $meta->meta_value );
		}

		set_post_thumbnail($destination_post_id, $attach_id);
	}

		/**
	 * saved sync to database. on the update detected synced last post id
	 * @author Webnus <info@webnus.biz>
	 * @param  integer $source_post_id      parent blog event post_id
	 * @param  integer $blog_id             sub-blog id
	 * @param  integer $destination_post_id sub-blog event post_id
	 * @return void
	 */
	public static function register_synced($source_post_id, $blog_id, $destination_post_id) {
		global $wpdb;
		$wpdb->delete(
			"{$wpdb->base_prefix}mec_sync",
			array(
				'source_post_id' => $source_post_id,
				'blog_id' => $blog_id,
			)
		);
		$wpdb->insert(
			"{$wpdb->base_prefix}mec_sync",
			array(
				'source_post_id' => $source_post_id,
				'blog_id' => $blog_id,
				'destination_post_id' => $destination_post_id,
				'created_at' => current_time('mysql'),
			)
		);
	}

	/**
	 * checking database for event last saved to sub-blogs
	 * @author Webnus <info@webnus.biz>
	 * @param  inreger  $source_post_id parent blog event id
	 * @param  integer  $blog_id        sub blog id
	 * @return string                 NULL when the not found, post_id on the found
	 */
	public static function is_exists($source_post_id, $blog_id) {
		global $wpdb;

		$sql = "SELECT destination_post_id FROM {$wpdb->base_prefix}mec_sync";
		$sql .= " WHERE source_post_id={$source_post_id} AND blog_id={$blog_id} LIMIT 1 ";

		return $wpdb->get_var($sql);

	}

}