<?php

/**
 * Webnus MEC_Sync main class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Sync_Main
{

	private $is_parent = false;

	private $upload_dir = null;

	/**
	 * direct boostrap
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	public function init()
	{

		$this->is_parent = MEC_Sync_Libs::is_parent_blog();

		if (MEC_Sync_Libs::is_event_sync() && $this->is_parent == true) {
			add_action('save_post', array($this, 'mec_sync_event'), 90);
			add_action('wp_trash_post', array($this, 'all_child_post_trash'));
			add_action('after_delete_post', array($this, 'all_child_post_delete'));
		}

		if (MEC_Sync_Libs::is_setting_sync() && $this->is_parent == true) {
			add_action('update_option_mec_options', array($this, 'mec_sync_settings'), 10, 2);
		}

		if (!$this->is_parent) {
			add_filter('all_plugins', array($this, 'ignore_show'));

			add_filter('post_row_actions', array($this, 'action_row'), 10, 2);

			add_action('wp_trash_post', array($this, 'post_trash'));

			add_action('load-post.php', array($this, 'post_edit'));
		}
	}

	/**
	 * callback when the save event
	 * @author Webnus <info@webnus.biz>
	 * @param  integer $source_post_id source post is on the parent site
	 * @return void
	 */
	function mec_sync_event($source_post_id)
	{
		// Check if our nonce is set.
		if (!isset($_POST['mec_event_nonce'])) {
			return;
		}

		// Verify that the nonce is valid.
		if (!wp_verify_nonce(sanitize_text_field($_POST['mec_event_nonce']), 'mec_event_data')) {
			return;
		}

		// ignore on the blogs add event
		if (MEC_Sync_Libs::is_parent_blog() == false) {

			return true;
		}

		global $wpdb;
		$this->upload_dir = wp_upload_dir();
		$tags = wp_get_post_tags($source_post_id, array('fields' => 'names'));

		$category_o = get_the_terms($source_post_id, 'mec_category');
		$category = array();
		if ($category_o) {
			foreach ($category_o as $k => $v) {
				array_push($category, $v->name);
			}
		}

		$taxonomies = get_object_taxonomies(get_post_type($source_post_id));
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
		$post_tax_terms = wp_get_post_terms($source_post_id, $taxonomies,  array("fields" => "all"));

		$post_metas = $wpdb->get_results("SELECT `meta_key`, `meta_value` FROM `{$wpdb->base_prefix}postmeta` WHERE `post_id`='$source_post_id'");

		// Mec Dates
		$post_dates = $wpdb->get_results("SELECT * FROM `{$wpdb->base_prefix}mec_dates` WHERE `post_id`='$source_post_id'");

		// Mec Occurrences
		$post_occurrences = $wpdb->get_results("SELECT * FROM `{$wpdb->base_prefix}mec_occurrences` WHERE `post_id`='$source_post_id'");

		// the parent post, get for saved on child
		$post = get_post($source_post_id);

		$sites = MEC_Sync_Libs::sync_sites();
		if (count($sites) == 0) {
			return;
		}

		$thumbnail_id = get_post_meta($source_post_id, '_thumbnail_id', true);

		foreach ($sites as $b) {

			$dest_post = null;

			// ignore parent save
			if ($b->blog_id == 1) {
				continue;
			}

			switch_to_blog($b->blog_id);

			$this->check_tables();
			// on the found last post_id exists, updated mode
			$post_id = MEC_Sync_Libs::is_exists($source_post_id, $b->blog_id);
			if ($post_id == NULL || !get_post_status($post_id)) {
				$post_id = $this->new_post($post);
				MEC_Sync_Libs::register_synced($source_post_id, $b->blog_id, $post_id);
			} else {
				$dest_post = get_post($post_id);
				$dest_post = $post;
				$dest_post->ID = $post_id;

				wp_update_post($dest_post);
			}

			$locations = MEC::getInstance('app.features.locations', 'MEC_feature_locations');
			$locations->save_event($post_id);

			$organizers = MEC::getInstance('app.features.organizers', 'MEC_feature_organizers');
			$organizers->save_event($post_id);

			$labels = MEC::getInstance('app.features.labels', 'MEC_feature_labels');
			$labels->save_event($post_id);

			$colors = MEC::getInstance('app.features.colors', 'MEC_feature_colors');
			$colors->save_event($post_id);

			$events = MEC::getInstance('app.features.events', 'MEC_feature_events');
			$events->save_event($post_id);

			if ($post_metas) {
				$sql_query = "INSERT INTO `{$wpdb->prefix}postmeta` (post_id, meta_key, meta_value)VALUES ";

				$sql_query_sel = [];

				foreach ($post_metas as $meta_info) {
					$meta_key = $meta_info->meta_key;
					$meta_value = addslashes($meta_info->meta_value);

					$sql_query_sel[] = "({$post_id},'{$meta_key}','{$meta_value}')";
				}

				$sql_query .= implode(" , ", $sql_query_sel);
				$wpdb->query($sql_query);
			}

			if ($post_occurrences) {
				$wpdb->delete("{$wpdb->prefix}mec_occurrences", array('post_id' => $post_id));
				$po_sql_query = "INSERT INTO `{$wpdb->prefix}mec_occurrences` (post_id, occurrence, params)VALUES ";

				$sql_query_sel = [];

				foreach ($post_occurrences as $post_date) {
					$sql_query_sel[] = "({$post_id},'{$post_date->occurrence}','{$post_date->params}')";
				}

				$po_sql_query .= implode(" , ", $sql_query_sel);
				$wpdb->query($po_sql_query);
			}

			if ($post_dates) {
				$wpdb->delete("{$wpdb->prefix}mec_dates", array('post_id' => $post_id));
				$pd_sql_query = "INSERT INTO `{$wpdb->prefix}mec_dates` (post_id, dstart, dend, tstart, tend)VALUES ";

				$sql_query_sel = [];

				foreach ($post_dates as $post_date) {
					$sql_query_sel[] = "({$post_id},'{$post_date->dstart}','{$post_date->dend}','{$post_date->tstart}','{$post_date->tend}')";
				}

				$pd_sql_query .= implode(" , ", $sql_query_sel);
				$wpdb->query($pd_sql_query);
			}

			if ($post_tax_terms) {
				$wpdb->delete("{$wpdb->prefix}term_relationships", array('object_id' => $post_id));
				foreach ($post_tax_terms as $tag_info) {
					$sql_query = "INSERT INTO `{$wpdb->prefix}terms` (name, slug, term_group)VALUES ";
					$tax_sql_query = "INSERT INTO `{$wpdb->prefix}term_taxonomy` (term_id, taxonomy, description,parent,count)VALUES ";
					$rel_sql_query = "INSERT INTO `{$wpdb->prefix}term_relationships` (object_id, term_taxonomy_id, term_order) VALUES ";

					$sql_query_sel = [];
					$tax_sql_query_sel = [];
					$rel_sql_query_sel = [];

					// $term_id = $tag_info->term_id;
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
					$rel_sql_query_sel[] = "({$post_id},'{$term_taxonomy_id}','{$order}')";
					$rel_sql_query .= implode(" , ", $rel_sql_query_sel);
					$wpdb->query($rel_sql_query);

					update_post_meta($post_id, $taxonomy . '_id', $term_id);
				}
			}

			if ($thumbnail_id) {

				MEC_Sync_Libs::attach_file($source_post_id, $post_id, $thumbnail_id, $this->upload_dir);
			}

			restore_current_blog();
		}
	}

	/**
	 * on the post not found sub-blog, create new post
	 * @author Webnus <info@webnus.biz>
	 * @param  Object $post post object from parent blog
	 * @return integer       post_id
	 */
	private function new_post($post)
	{
		$p = array(
			'post_title' => $post->post_title,
			'post_content' => $post->post_content,
			'post_status' => $post->post_status,
			'comment_status' => 'open',
			'ping_status' => 'closed',
			'post_author' => '1',
			'post_type' => $post->post_type,
			'filter' => 'raw',
			'post_parent' => 0,
			'post_name' => $post->post_name,
		);
		return wp_insert_post($p);
	}

	/**
	 * on the setting save, sync to any subsites
	 * @author Webnus <info@webnus.biz>
	 * @param  array $old_value old value befor save
	 * @param  array $value     current value for save
	 * @return void
	 */
	public function mec_sync_settings($old_value, $value)
	{

		$sites = MEC_Sync_Libs::sync_sites();
		if (count($sites) == 0) {
			return;
		}

		foreach ($sites as $b) {

			// ignore parent save
			if ($b->blog_id == 1) {
				continue;
			}

			switch_to_blog($b->blog_id);

			update_option('mec_options', $value);

			restore_current_blog();
		}
	}

	/**
	 * Check Tables
	 *
	 * @since     1.0.0
	 */
	public function check_tables()
	{
		global $wpdb;

		$db = \MEC::getInstance('app.libraries.db');

		if ($db->q("SHOW TABLES LIKE '{$wpdb->prefix}mec_events'")) {
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


	/**
	 * ignore show mec-sync plugin on sub-sites
	 * @param  array $plugins all plugins
	 * @return array          after filter plugins list
	 */
	public function ignore_show($plugins)
	{

		// checked exists mec-sync and ignore show
		if (isset($plugins['mec-sync/mec-sync.php'])) {
			unset($plugins['mec-sync/mec-sync.php']);
		}

		return $plugins;
	}

	/**
	 * callbacl function action hook for wp_DataTable
	 * @param  array $actions action row
	 * @param  object $post    post object row on row
	 * @return  array         custome action button
	 */
	public function action_row($actions, $post)
	{

		if ($post->post_type == MEC_SYNC_ETYPE) {

			error_log(print_r($actions, true));

			if (MEC_Sync_Libs::is_synced_post($post->ID) != null) {
				unset($actions['edit']);
				unset($actions['trash']);
				unset($actions['inline hide-if-no-js']);
				unset($actions['mec-duplicate']);
			}
		}

		return $actions;
	}

	public function post_trash($post_id)
	{

		error_log("the send to trash:{$post_id}");

		if (MEC_Sync_Libs::is_synced_post($post_id) != null) {
			wp_die(__('Cannot delete the master event!', 'mec'));
		}
	}

	public function post_edit()
	{

		$post_id = isset($_GET['post']) ? $_GET['post'] : null;

		// detect the post is new or update
		// on the new post, post parameter is null
		if (!$post_id) {
			return;
		}

		if (MEC_Sync_Libs::is_synced_post($post_id) != null) {
			wp_die(__('Cannot edit the master event!', 'mec'));
		}
	}

	public function all_child_post_trash($source_post_id)
	{

		if (!MEC_Sync_Libs::is_parent_blog()) {
			return;
		}

		global $wpdb;
		$sql = "SELECT blog_id,destination_post_id FROM {$wpdb->base_prefix}mec_sync WHERE source_post_id={$source_post_id}";
		$all = $wpdb->get_results($sql);

		if (!$all) {
			return;
		}

		foreach ($all as $k => $v) {
			switch_to_blog($v->blog_id);
			wp_trash_post($v->destination_post_id);
			restore_current_blog();
		}
	}

	public function all_child_post_delete($source_post_id)
	{
		global $wpdb;
		$sql = "SELECT id,blog_id,destination_post_id FROM {$wpdb->base_prefix}mec_sync WHERE source_post_id={$source_post_id}";
		$all = $wpdb->get_results($sql);

		if (!$all) {
			return;
		}

		foreach ($all as $k => $v) {
			switch_to_blog($v->blog_id);
			wp_delete_post($v->destination_post_id, true);
			restore_current_blog();

			$sql = "DELETE FROM {$wpdb->base_prefix}mec_sync WHERE id={$v->id}";
			$wpdb->query($sql);
		}
	}
}
