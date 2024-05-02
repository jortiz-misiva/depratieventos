<?php

/**
 * Advanced importer Table show class
 */
class MEC_Advanced_Importer_Schedule_Table extends WP_List_Table {

	public $event_class;

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => 'shcedule',
			'plural' => 'shcedules',
			'ajax' => false,
		));
	}

	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	function column_post_title($item) {

		return '<a href="' . $item['post_content'] . '" target="_blank" id="mec-advimp-schedule-' . $item['ID'] . '">' . $item['post_title'] . '</a>';
	}

	function column_action($item) {

	}

	public function get_bulk_actions() {

		return array(
			'delete' => __('Delete', 'mec-advanced-importer'),
			'deleteall' => __('Delete All', 'mec-advanced-importer'),
		);
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

			$args = $this->get_query_args();
			$importdata_query = new WP_Query($args);
			$posts = $importdata_query->posts;
			foreach( $posts as $post ){
				wp_delete_post( $post->ID );
			}
		}

		if ($action != 'delete') {
			return;
		}

		$ids = isset($_POST['shcedule']) ? $_POST['shcedule'] : null;
		if ($ids == null || !is_array($ids)) {
			return;
		}

		$args = $this->get_query_args();
		$args['post__in'] = $ids;
		$importdata_query = new WP_Query($args);
		$posts = $importdata_query->posts;
		foreach( $posts as $post ){
			wp_delete_post( $post->ID );
		}
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
			'post_title' => __('Title', 'mec-advanced-importer'),
			'post_date' => __('Import On', 'mec-advanced-importer'),
			// 'status' => __('Status', 'mec-advanced-importer'),
			'action' => __('Action', 'mec-advanced-importer'),
		);
		return $columns;
	}

	public function get_query_args( $per_page = -1, $current_page = 1 ){

		$query_args = array(
			'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		$query_args['meta_key'] = 'event_class';
		$query_args['meta_value'] = esc_attr($this->event_class);

		return $query_args;
	}

	function prepare_items() {

		$per_page = 10;
		$current_page = $this->get_pagenum();

		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();


		$query_args = $this->get_query_args( $per_page, $current_page );
		$importdata_query = new WP_Query($query_args);
		$posts = $importdata_query->posts;

		foreach ($posts as $post) {

			$this->items[] = array(
				'ID' => $post->ID,
				'post_title' => $post->post_title,
				'post_content' => $post->post_content,
				'post_date' => date_i18n('Y-m-d H:i', $post->post_content),
			);
		}

		$this->set_pagination_args(array(
			'total_items' => $importdata_query->found_posts,
			'per_page' => $per_page,
			'total_pages' => ceil($importdata_query->found_posts / $per_page),
		));
	}

}
