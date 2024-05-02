<?php

/**
 * Advanced importer Table show class
 */
class MEC_Advanced_Importer_History_Table extends WP_List_Table {

	public $category = array();

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => 'import_history',
			'plural' => 'import_histories',
			'ajax' => false,
		));
	}

	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	function column_title($item) {

		$wpea_url_delete_args = array(
			'page' => sanitize_text_field(wp_unslash($_REQUEST['page'])),
			'tab' => sanitize_text_field(wp_unslash($_REQUEST['tab'])),
			'mec_advimp_action' => 'mec_advimp_history_delete',
			'history_id' => absint($item['ID']),
		);
		// Build row actions.
		$actions = array(
			'delete' => sprintf('<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this import history? Import history will be permanatly deleted.\')">%2$s</a>', esc_url(wp_nonce_url(add_query_arg($wpea_url_delete_args), 'mec_advimp_delete_history_nonce')), esc_html__('Delete', 'mec-advanced-importer')),
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%3$s</span> %2$s',
			$item['title'],
			$this->row_actions($actions),
			__('Origin', 'mec-advanced-importer') . ': <strong>' . ucfirst(get_post_meta($item['ID'], 'import_origin', true)) . '</strong>'
		);
	}

	function column_stats($item) {

		$created = get_post_meta($item['ID'], 'created', true);
		$updated = get_post_meta($item['ID'], 'updated', true);
		$skipped = get_post_meta($item['ID'], 'skipped', true);
		$nothing_to_import = get_post_meta($item['ID'], 'nothing_to_import', true);

		$success_message = '<span style="color: silver"><strong>';
		if ($created > 0) {
			$success_message .= sprintf(__('%d Created', 'mec-advanced-importer'), $created) . "<br>";
		}
		if ($updated > 0) {
			$success_message .= sprintf(__('%d Updated', 'mec-advanced-importer'), $updated) . "<br>";
		}
		if ($skipped > 0) {
			$success_message .= sprintf(__('%d Skipped', 'mec-advanced-importer'), $skipped) . "<br>";
		}
		if ($nothing_to_import) {
			$success_message .= __('No events are imported.', 'mec-advanced-importer') . '<br>';
		}
		$success_message .= "</strong></span>";

		// Return the title contents.
		return $success_message;
	}

	function column_import_category($item) {
		$category = get_post_meta($item['ID'], 'category');
		if (!is_array($category)) {
			return null;
		}

		if (count($category) == 0) {
			return null;
		}

		$ret = [];
		foreach ($category[0] as $cat) {
			if (isset($this->category[$cat])) {
				array_push($ret, $this->category[$cat]);
			}
		}

		if (count($ret) == 0) {
			return null;
		}

		return implode(',', $ret);
	}

	public function process_bulk_action() {

		// security check!
		if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

			$nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
			$action = 'bulk-' . $this->_args['plural'];

			if (!wp_verify_nonce($nonce, $action)) {
				wp_die('Nope! Security check failed!');
			}

		}

		$action = $this->current_action();
		if ($action == 'deleteall') {
			global $wpdb;
			$sql = "DELETE FROM {$wpdb->prefix}posts WHERE post_type='" . MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE . "'";
			$wpdb->query($sql);

		}

		if ($action != 'delete') {
			return;
		}

		$ids = isset($_POST['import_history']) ? $_POST['import_history'] : null;
		if ($ids == null || !is_array($ids)) {
			return;
		}

		$ids = implode(',', $ids);

		global $wpdb;
		$sql = "DELETE FROM {$wpdb->prefix}posts WHERE ID IN({$ids})";
		$wpdb->query($sql);
	}

	function process_single_action(){
		$action = isset($_GET['mec_advimp_action']) ? $_GET['mec_advimp_action'] : null;
		$id = isset($_GET['history_id']) ? $_GET['history_id'] : null;
				// security check!
		if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

			$nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
			$action = 'bulk-' . $this->_args['plural'];

			if (!wp_verify_nonce($nonce, $action)) {
				wp_die('Nope! Security check failed!');
			}

		}

		if($action != 'mec_advimp_history_delete'){
			return;
		}

		global $wpdb;
		$sql = "DELETE FROM {$wpdb->prefix}posts WHERE ID={$id} AND post_type='" . MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE . "'";
		$wpdb->query($sql);

	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/$this->_args['singular'],
			/*$2%s*/$item['ID']
		);
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Import', 'mec-advanced-importer'),
			'import_category' => __('Import Category', 'mec-advanced-importer'),
			'import_date' => __('Import Date', 'mec-advanced-importer'),
			'stats' => __('Import Stats', 'mec-advanced-importer'),
			// 'action' => __('Action', 'mec-advanced-importer'),
		);
		return $columns;
	}

	public function get_bulk_actions() {

		return array(
			'delete' => __('Delete', 'mec-advanced-importer'),
			'deleteall' => __('Delete All', 'mec-advanced-importer'),
		);
	}

	function prepare_items() {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array('ID');
		$sortable = $this->get_sortable_columns();

		$all_cate = get_categories(array(
			'taxonomy' => 'mec_category',
			'hide_empty' => 0,
			'hierarchical' => true,
			'post_type' => 'mec-events',
		));
		if (count($all_cate) > 0) {
			foreach ($all_cate as $k => $cat) {
				$this->category[$cat->term_id] = $cat->name;
			}
		}

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();
		$this->process_single_action();

		$data = $this->get_import_history_data();

		if (!empty($data)) {
			$total_items = ($data['total_records']) ? (int) $data['total_records'] : 0;
			$this->items = ($data['import_data']) ? $data['import_data'] : array();

			$this->set_pagination_args(array(
				'total_items' => $total_items,
				'per_page' => $per_page,
				'total_pages' => ceil($total_items / $per_page),
			));
		}
	}

	function get_import_history_data($origin = '') {
		global $importevents;

		$scheduled_import_data = array('total_records' => 0, 'import_data' => array());
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		if ($origin != '') {
			$query_args['meta_key'] = 'import_origin';
			$query_args['meta_value'] = esc_attr($origin);
		}

		$importdata_query = new WP_Query($query_args);
		$scheduled_import_data['total_records'] = ($importdata_query->found_posts) ? (int) $importdata_query->found_posts : 0;
		// The Loop.
		if ($importdata_query->have_posts()) {
			while ($importdata_query->have_posts()) {
				$importdata_query->the_post();

				$import_id = get_the_ID();
				$import_data = get_post_meta($import_id, 'import_data', true);
				$import_origin = get_post_meta($import_id, 'import_origin', true);
				$import_plugin = isset($import_data['import_into']) ? $import_data['import_into'] : '';

				$term_names = array();
				$import_terms = isset($import_data['event_cats']) ? $import_data['event_cats'] : array();

				if ($import_terms && !empty($import_terms)) {
					foreach ($import_terms as $term) {
						$get_term = '';
						if ($import_plugin != '' && !empty($importevents->$import_plugin)) {
							$get_term = get_term($term, $importevents->$import_plugin->get_taxonomy());
						}

						if (!is_wp_error($get_term) && !empty($get_term)) {
							$term_names[] = $get_term->name;
						}
					}
				}

				$scheduled_import_data['import_data'][] = array(
					'ID' => $import_id,
					'title' => get_the_title(),
					'import_category' => implode(', ', $term_names),
					'import_date' => get_the_date("F j Y, h:i A"),
				);
			}
		}

		wp_reset_postdata();
		return $scheduled_import_data;
	}
}
