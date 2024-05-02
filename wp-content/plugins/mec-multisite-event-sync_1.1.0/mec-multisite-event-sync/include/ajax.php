<?php

/**
 * Webnus MEC_Sync ajax class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Sync_Ajax {

	/**
	 * direct bootstrap
	 * @return json detect the saved setting and return json
	 */
	function init() {

		$current_blog_id = get_current_blog_id();
		if ($current_blog_id == 1) {

			add_action('wp_ajax_mec_sync_event', array($this, 'sync'));

			return true;
		}

		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
		if ("{$action}" === "mec_save_settings") {

			$sync_sites = MEC_Sync_Libs::get_sync_sites_status();
			if(array_key_exists($current_blog_id,$sync_sites) && $sync_sites[$current_blog_id]){

				return wp_send_json_error();
				exit();
			}
		}
	}

	public function sync() {
		$post_id = isset($_POST['id']) ? $_POST['id'] : null;
		if (!$post_id) {
			return wp_send_json_error();
		}

		$post = get_post($post_id);

		// Post is not exists
		if (!$post) {
			return wp_send_json_error();
		}

		global $wpdb;
		$upload_dir = wp_upload_dir();

		$taxonomies = get_object_taxonomies($post->post_type);

		$sites = MEC_Sync_Libs::sync_sites();

		$thumbnail_id = get_post_meta($post_id, '_thumbnail_id', true);

		// duplicate all post meta
		$post_metas = $wpdb->get_results("SELECT `meta_id`, `meta_key`, `meta_value` FROM `{$wpdb->base_prefix}postmeta` WHERE `post_id`='$post_id'");

		// Mec Dates
		$post_dates = $wpdb->get_results("SELECT * FROM `{$wpdb->base_prefix}mec_dates` WHERE `post_id`='$post_id'");

		// Mec Occurrences
		$post_occurrences = $wpdb->get_results("SELECT * FROM `{$wpdb->base_prefix}mec_occurrences` WHERE `post_id`='$post_id'");

		// duplicate all post tags
		$taxonomies = get_taxonomies('', 'names');
		$taxonomies['mec_category'] = 'mec_category';
		$taxonomies['mec_organizer'] = 'mec_organizer';
		$taxonomies['mec_location'] = 'mec_location';

		$main      = new \MEC_main();
		$settings = $main->get_settings();
		$speakers_status = (!isset($settings['speakers_status']) or (isset($settings['speakers_status']) and !$settings['speakers_status'])) ? false : true;
		if($speakers_status) {
			$taxonomies['mec_speaker'] = 'mec_speaker';
		}

		$post_tax_terms = wp_get_post_terms($post_id, $taxonomies,  array("fields" => "all",'orderby' => 'parent'));
		$mec_data = $wpdb->get_row("SELECT * FROM `{$wpdb->base_prefix}mec_events` WHERE `post_id`='$post_id'");

		foreach ($sites as $b) {
			// ignore parent save
			if ($b->blog_id === 1) {
				continue;
			}

			$last_inserted = MEC_Sync_Libs::is_exists($post_id, $b->blog_id);

			switch_to_blog($b->blog_id);

			$this->check_tables();
			//new post data array
			$args = array
				(
				'comment_status' => $post->comment_status,
				'ping_status' => $post->ping_status,
				'post_author' => $post->post_author,
				'post_content' => $post->post_content,
				'post_excerpt' => $post->post_excerpt,
				'post_name' => $post->post_name,
				'post_parent' => $post->post_parent,
				'post_password' => $post->post_password,
				'post_status' => $post->post_status,
				'post_title' => $post->post_title,
				'post_type' => $post->post_type,
				'to_ping' => $post->to_ping,
				'menu_order' => $post->menu_order,
			);

			if($last_inserted && get_post_status($last_inserted)) {
				// Update the new post
				$args['ID'] = $last_inserted;
				wp_update_post($args);
				$new_post_id = $last_inserted;
			} else {
				// insert the new post
				$new_post_id = wp_insert_post($args);
			}

			if ($post_metas) {
				foreach ($post_metas as $meta_info) {
					$sql_query = "INSERT INTO `{$wpdb->prefix}postmeta` (post_id, meta_key, meta_value)VALUES ";
					$sql_query_sel = [];
					$meta_key = $meta_info->meta_key;
					$meta_value = addslashes($meta_info->meta_value);

					$sql_query .= "({$new_post_id},'{$meta_key}','{$meta_value}')";
					$wpdb->query($sql_query);
				}

			}

			if ($post_occurrences) {
				$wpdb->delete("{$wpdb->prefix}mec_occurrences", array('post_id' => $new_post_id));
				$po_sql_query = "INSERT INTO `{$wpdb->prefix}mec_occurrences` (post_id, occurrence, params)VALUES ";

				$sql_query_sel = [];

				foreach ($post_occurrences as $post_date) {
					$sql_query_sel[] = "({$new_post_id},'{$post_date->occurrence}','{$post_date->params}')";
				}

				$po_sql_query .= implode(" , ", $sql_query_sel);
				$wpdb->query($po_sql_query);
			}

			if ($post_dates) {
				$wpdb->delete("{$wpdb->prefix}mec_dates", array('post_id' => $new_post_id));
				$pd_sql_query = "INSERT INTO `{$wpdb->prefix}mec_dates` (post_id, dstart, dend, tstart, tend)VALUES ";

				$sql_query_sel = [];

				foreach ($post_dates as $post_date) {
					$sql_query_sel[] = "({$new_post_id},'{$post_date->dstart}','{$post_date->dend}','{$post_date->tstart}','{$post_date->tend}')";
				}

				$pd_sql_query .= implode(" , ", $sql_query_sel);
				$wpdb->query($pd_sql_query);
			}

			if ($post_tax_terms) {
				$wpdb->delete("{$wpdb->prefix}term_relationships", array('object_id' => $new_post_id));
				foreach ($post_tax_terms as $tag_info) {
					if(empty($tag_info )) {
						continue;
					}

					$sql_query = "INSERT INTO `{$wpdb->prefix}terms` (name, slug, term_group)VALUES ";
					$tax_sql_query = "INSERT INTO `{$wpdb->prefix}term_taxonomy` (term_id, taxonomy, description,parent,count)VALUES ";
					$rel_sql_query = "INSERT INTO `{$wpdb->prefix}term_relationships` (object_id, term_taxonomy_id, term_order) VALUES ";

					$sql_query_sel = [];
					$tax_sql_query_sel = [];
					$rel_sql_query_sel = [];

					$name = $tag_info->name;
					$slug = $tag_info->slug;
					$term_group = $tag_info->term_group;

					// Taxonomies
					$taxonomy = $tag_info->taxonomy;
					$description = $tag_info->description;
					$parent = $tag_info->parent;
					$count = $tag_info->count;
					$term = get_term_by('slug', $slug, $taxonomy);

					if (!$term) {
						$sql_query_sel[] = "('{$name}','{$slug}','{$term_group}')";
						$sql_query .= implode(" , ", $sql_query_sel);
						$wpdb->query($sql_query);
						$term_id = $wpdb->insert_id;

						$tax_sql_query_sel[] = "('{$term_id}','{$taxonomy}','{$description}','{$parent}','{$count}')";
						$tax_sql_query .= implode(" , ", $tax_sql_query_sel);
						$wpdb->query($tax_sql_query);
						$term_taxonomy_id = $wpdb->insert_id;
					} else {
						$term_id = $term->term_id;
						$term_taxonomy_id = $term->term_taxonomy_id;
					}

					if( $term_id !== $tag_info->term_id ){

						foreach ($post_tax_terms as $__k => $__tag_info) {

							if(empty($__tag_info )) {
								continue;
							}

							if( $__tag_info->parent === $tag_info->term_id ){

								$post_tax_terms[ $__k ]->parent = $term_id;
							}
						}
					}

					$wpdb->delete("{$wpdb->prefix}termmeta", array('term_id' => $tag_info->term_id));
					$term_meta = $wpdb->get_results("SELECT `meta_key`, `meta_value` FROM `{$wpdb->base_prefix}termmeta` WHERE `term_id`='$tag_info->term_id'");
					$meta_sql_query  = "INSERT INTO `{$wpdb->prefix}termmeta` (term_id, meta_key, meta_value)VALUES ";
					$meta_sql_query_ = [];
					foreach ($term_meta as $term_info) {
						$meta_sql_query_[] = "({$term_id},'{$term_info->meta_key}','{$term_info->meta_value}')";
					}

					$wpdb->query($meta_sql_query. implode(', ', $meta_sql_query_));
					$order = 0;
					// Relations
					$rel_sql_query_sel[] = "({$new_post_id},'{$term_taxonomy_id}','{$order}')";
					$rel_sql_query .= implode(" , ", $rel_sql_query_sel);
					$wpdb->query($rel_sql_query);

					update_post_meta( $new_post_id, $taxonomy . '_id', $term_id);

					clean_term_cache( $term_id );
				}
			}

			$q1 = "";
			$q2 = "";

			foreach ($mec_data as $key => $value) {

				if (in_array($key, array('id', 'post_id'))) {
					continue;
				}

				$q1 .= "`$key`,";
				$q2 .= "'$value',";
			}

			$sql = "INSERT INTO `{$wpdb->prefix}mec_events` (`post_id`," . trim($q1, ', ') . ") VALUES ('$new_post_id'," . trim($q2, ', ') . ")";
			$wpdb->delete("{$wpdb->prefix}mec_events", array('post_id' => $new_post_id));
			$wpdb->query($sql);

			// Update Schedule
			$schedule = MEC::getInstance('app.libraries.schedule');
			$schedule->reschedule($new_post_id);

			if ($thumbnail_id) {

				MEC_Sync_Libs::attach_file($post_id, $new_post_id, $thumbnail_id, $upload_dir);
			}

			MEC_Sync_Libs::register_synced($post_id, $b->blog_id, $new_post_id);

			restore_current_blog();

		}

		return wp_send_json_success(['id'=>$post_id]);

	}

	/**
	* Check Tables
	*
	* @since     1.0.0
	*/
	public function check_tables () {
		global $wpdb;

		$db = \MEC::getInstance('app.libraries.db');

		if($db->q("SHOW TABLES LIKE '{$wpdb->prefix}mec_events'")) {
			return false;
		}

		$file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
 		$query_file = MEC_ABSPATH . 'assets' . DS . 'sql' . DS . 'tables.sql';
		if ($file->exists($query_file)) {
			$queries = $file->read($query_file);
			$queries = str_replace('#__', $wpdb->prefix, $queries);
			$sqls = explode(';', $queries);

			foreach ($sqls as $sql) {
				$sql = trim($sql, '; ');
				if (trim($sql) == '') continue;

				$sql .= ';';

				try {
					$db->q($sql);
				} catch (Exception $e) {
				}
			}
		}
	}

}