<?php

/**
 * Advanced importer Table show class
 */
class MEC_Advanced_Importer_Preview_Table extends WP_List_Table {

	public $data;
	public $page_section;
	public $params;
	public $all_data = array();

	public function __construct() {

		global $status, $page;
		parent::__construct(array(
			'singular' => 'event',
			'plural' => 'events',
			'ajax' => true,
		));
	}

	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	function column_title($item) {

		return '<a href="' . $item['link'] . '" target="_blank" id="mec-advimp-privew-' . md5($item['ID']) . '">' . $item['title'] . '</a>';
	}

	function column_action($item) {

	}

	function column_dstatus($item) {
		return '<span id="mec-advimp-download-status-' . md5($item['ID']) . '"></span>';
	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" data-id="%3$s" class="advimp-select" />',
			/*$1%s*/$this->_args['singular'],
			/*$2%s*/$item['ID'],
			isset($item['uid']) ? $item['uid'] : md5($item['ID'])
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
			'title' => __('Event Name', 'mec-advanced-importer'),
			'start' => __('Start Time', 'mec-advanced-importer'),
			'end' => __('End Time', 'mec-advanced-importer'),
			'dstatus' => __('Download Status', 'mec-advanced-importer'),
		);
		return $columns;
	}

	protected function extra_tablenav($which) {

		if ($which == 'top') {
			?>
			<script type="text/javascript">
				var mev_advimp_all_data_events = <?php echo json_encode($this->all_data); ?>;
			</script>

			<?php
		}

		if (isset($this->params['scheduled']) && $this->params['scheduled'] == 'onestep') {
			$ret = submit_button(__('Download All Events'), '', 'mec_advimp_download_events'.$which, false,
				array(
					'onClick' => 'return MEC_ADVIMP_Download("' . $this->page_section . '",this);',
					'class'=>'button mecadvimp-btn-import'
				)
			);

			if (isset($this->data['category'])) {
				$html = '<select id="mecadvimp-selectcategory-' . $which . '"><option value="">' . __('Select Category', 'mec-advanced-importer') . '</option>';
				foreach ($this->data['category'] as $cat_id => $cat_name) {
					$selected = $this->data['category_selected'] == $cat_id ? 'selected="selected"' : '';
					$html .= '<option value="' . $cat_id . '" ' . $selected . '>' . $cat_name . '</option>';
				}
				$html .= '</select>';
				echo $html;

			}

			$ret .= '&nbsp;' . submit_button(__('Download Selected Events'), '', 'mec_advimp_download_selected_events'.$which, false,
				array(
					'onClick' => 'return MEC_ADVIMP_Download("' . $this->page_section . '",this,"selected");',
					'style' => 'display:none;margin-left:7px;',
					'class'=>'button mecadvimp-btn-import'
				)
			);
			$ret = '';

			return $ret;
		} else {
			return submit_button(__('Add To Schedule'), '', 'mec_advimp_download_events', false,
				array(
					'onClick' => 'return MEC_ADVIMP_Schedule("' . $this->page_section . '",this);',
				)

			);
		}

	}

	function display() {

		wp_nonce_field('ajax-custom-list-nonce', '_ajax_custom_list_nonce');

		$order = isset($this->_pagination_args['order']) ? $this->_pagination_args['order'] : '';
		$order_by = isset($this->_pagination_args['orderby']) ? $this->_pagination_args['orderby'] : '';

		/**
		 * Adds field order and orderby
		 */
		echo '<input type="hidden" id="order" name="order" value="' . $order . '" />';
		echo '<input type="hidden" id="orderby" name="orderby" value="' . $order_by . '" />';

		parent::display();
	}

	function prepare_items() {

		$general = get_option('mecadvimpgeneral');

		$per_page = isset($general['perpage']) ? $general['perpage'] : 5;
		$columns = $this->get_columns();
		$hidden = array('ID');
		$sortable = $this->get_sortable_columns();
		$current_page = $this->get_pagenum();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = get_option('mec_advimp_' . $this->page_section . '_current_event');

		$total_items = $data['total_records'];
		$this->all_data = $data['data'];
		$this->items = array_slice($data['data'], (($current_page - 1) * $per_page), $per_page);

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page),
		));

	}

	function ajax_response() {

		check_ajax_referer('ajax-custom-list-nonce', '_ajax_custom_list_nonce');

		$this->prepare_items();

		extract($this->_args);
		extract($this->_pagination_args, EXTR_SKIP);

		ob_start();
		if (!empty($_REQUEST['no_placeholder'])) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();

		ob_start();
		$this->print_column_headers();
		$headers = ob_get_clean();

		ob_start();
		$this->pagination('top');
		$pagination_top = ob_get_clean();

		ob_start();
		$this->pagination('bottom');
		$pagination_bottom = ob_get_clean();

		$response = array('rows' => $rows);
		$response['pagination']['top'] = $pagination_top;
		$response['pagination']['bottom'] = $pagination_bottom;
		$response['column_headers'] = $headers;

		if (isset($total_items)) {
			$response['total_items_i18n'] = sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items));
		}

		if (isset($total_pages)) {
			$response['total_pages'] = $total_pages;
			$response['total_pages_i18n'] = number_format_i18n($total_pages);
		}

		die(json_encode($response));
	}
}
