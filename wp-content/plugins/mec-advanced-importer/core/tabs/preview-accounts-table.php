<?php

/**
 * Advanced importer Table show class
 */
class MEC_Advanced_Importer_Preview_Accounts_Table extends WP_List_Table {

	public $data;
	public $page_section;
	public $params;
	public $action_call = 'MEC_ADVIMP_Accounts_Events';
	public $event_name = 'Events';
	public $all_data;
	public $data_account = array();
	public $enable_download_all = true;

	public function __construct() {

		global $status, $page;
		parent::__construct(array(
			'singular' => 'account',
			'plural' => 'accounts',
			'ajax' => true,
		));
	}

	function column_default($item, $column_name) {
		return $item[$column_name];
	}

	function column_title($item) {

		return '<a href="#" onclick="return ' . $this->action_call . '(\'' . $item['ID'] . '\', this);" id="mec-advimp-accounts-privew-' . $item['ID'] . '">' . $item['title'] . '</a>';
	}
	function column_events($item) {

		return '<a href="#" onclick="return ' . $this->action_call . '(\'' . $item['ID'] . '\', this);" id="mec-advimp-accounts-privew-' . $item['ID'] . '">' . $item['events'] . '</a>';
	}

	protected function extra_tablenav($which) {

		if ($which == 'top') {
			error_log('add the nav');
			?>
			<script type="text/javascript">
				window.mev_advimp_all_data_accounts_events = <?php echo json_encode($this->data); ?>;
				window.mev_advimp_all_data_accounts_events_implement = <?php echo json_encode($this->data_account); ?>;

			</script>

			<?php
		}
	}

	function column_action($item) {
		$ret = '<button type="button" class="button button-primary" onclick="return ' . $this->action_call . '(\'' . $item['ID'] . '\', this);">
               Get ' . $this->event_name . '
            </button>';

		if( $item['is_calendar'] ){

			$ret .= ' <button onclick="return MEC_ADVIMP_Add_Accounts_to_Sync(this);" type="button" class="button button-primary mec-advimp-action" data-calendar-id="'. $item['ID'] .'" data-calendar-title="'. $item['title'] .'" data-calendar-list-item="'. esc_attr( $item['calendar_list_item'] ) .'" data-action="add-to-schedule" data-section="eventbrite" id="mec-advimp-add-to-sync">
				'. __('Add to auto sync','mec-advanced-importer') .'
			</button>';
		}

		if ($this->event_name == 'Events' && $this->enable_download_all == true) {
			$ret .= '<button style="margin-left:7px;" type="button" class="button button-primary" onclick="return MEC_ADVIMP_Download_Accounts(\''
			. $this->page_section . '\',this,\''.$item['ID'].'\');">
               ' . __('Download All Events','mec-advanced-importer') . '
            </button>';
		}

		return $ret;
	}

	function column_cb($item) {
		return;
	}

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
			// 'cb' => '<input type="checkbox" />',
			'title' => __('Account Name', 'mec-advanced-importer'),
			'events' => __($this->event_name, 'mec-advanced-importer'),
			'action' => __('Action', 'mec-advanced-importer'),

		);
		return $columns;
	}

	function display() {

		wp_nonce_field('ajax-custom-list-nonce', '_ajax_custom_list_nonce');

		parent::display();
	}

	function prepare_items() {

		$per_page = 2000;
		$columns = $this->get_columns();
		$hidden = array('ID');
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$total_items = 0;

		foreach ($this->data as $k => $v) {
			$title = null;

			if (!isset($v['title'])) {
				$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings($this->page_section, 'config', $k);

				$title = $s['title'];
			} else {
				$title = $v['title'];
			}

			$this->items[] = array(
				'ID' => $k,
				'title' => $title,
				'events' => $v['total_records'],
				'is_calendar' => isset( $v['is_calendar'] ) && $v['is_calendar'] ? true : false,
				'calendar_list_item' => isset( $v['calendar_list_item'] ) ? $v['calendar_list_item'] : '',
			);

			$total_items += 1;
		}

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page),
		));

	}
}
