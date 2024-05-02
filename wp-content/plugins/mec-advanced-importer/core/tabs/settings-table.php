<?php

/**
 * Advanced importer Table show class
 */
class MEC_Advanced_Importer_Settings_Table extends WP_List_Table {

	public $section;
	public $setting_class;

	public function __construct() {
		global $status, $page;
		parent::__construct(array(
			'singular' => 'setting',
			'plural' => 'settings',
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
			'mec_advimp_action' => 'mec_advimp_setting_delete',
			'id' => $item['ID'],
		);
		$wpea_url_edit_args = array(
			'page' => sanitize_text_field(wp_unslash($_REQUEST['page'])),
			'tab' => sanitize_text_field(wp_unslash($_REQUEST['tab'])),
			'mec_advimp_action' => 'mec_advimp_setting_edit',
			'id' => $item['ID'],
		);
		// Build row actions.
		$actions = array(
			'delete' => sprintf('<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this account? Account history will be permanatly deleted.\')">%2$s</a>', esc_url(wp_nonce_url(add_query_arg($wpea_url_delete_args), 'mec_advimp_delete_account_nonce')), esc_html__('Delete', 'mec-advanced-importer')),
			'edit' => sprintf('<a href="%1$s">%2$s</a>', esc_url(wp_nonce_url(add_query_arg($wpea_url_edit_args), 'mec_advimp_edit_account_nonce')), esc_html__('Edit', 'mec-advanced-importer')),
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%3$s</span> %2$s',
			$item['title'],
			$this->row_actions($actions),
			''
		);
	}

	function column_action($item) {
		$url = add_query_arg(array(
			'action' => 'mec_advimp_view_account',
			'account' => $item['ID'],
			'TB_iframe' => 'true',
			'width' => '800',
			'height' => '500',
		), admin_url('admin.php'));

		$ret = sprintf(
			'<div style="display:none" id="item-'.$item['ID'].'">'.json_encode($item).'</div><button title="%1$s" class="button button-primary" onclick="return MADVIMP_Show_Account(\''.$item['ID'].'\',this)">%2$s</button>',
			$item['title'],
			__('View Account', 'mec-advanced-importer')
		);

		if ($item['need_auth'] == 1) {

			$color = $item['auth_status'] == true?'success':'info';
			$title = $item['auth_status'] == true?__('Authentication successful', 'mec-advanced-importer'):__('Needs authentication', 'mec-advanced-importer');


			$auth_url = admin_url('admin.php?page=MEC-advimp&advimp_cmd='.$this->section.'_auth');
			$auth_url .= '&authid='.$item['ID'];

			$ret .= '&nbsp;'.sprintf(
				'<a href="#" data-url="%1$s" data-action="auth" data-section="%2$s" data-authid="%3$s" title="%4$s" class="mec-advimp-action open-acount-details-modal button button-%5$s ">%6$s</a>',
				$auth_url,
				$this->section,
				$item['ID'],
				$item['title'],
				$color,
				$title
			);
		}

		return $ret;

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
			'title' => __('Account', 'mec-advanced-importer'),
			'active' => __('Active/Deactive', 'mec-advanced-importer'),
			// 'status' => __('Status', 'mec-advanced-importer'),
			'action' => __('Action', 'mec-advanced-importer'),
		);
		return $columns;
	}

	public function get_bulk_actions() {

		return array(
			'delete' => __('Delete', 'mec-advanced-importer'),
		);

	}

	 public function process_bulk_action() {

        // security check!
        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $action = 'bulk-' . $this->_args['plural'];

            if ( ! wp_verify_nonce( $nonce, $action ) )
                wp_die( 'Nope! Security check failed!' );

        }

        $action = $this->current_action();
        if($action != 'delete'){
        	return;
        }

        $ids = isset($_POST['setting'])?$_POST['setting']:null;
        if($ids == null || !is_array($ids)){
        	return;
        }

        foreach ($ids as $id) {
        	$this->setting_class->delete_account($id);
        }

    }

	function prepare_items() {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array('ID');
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->process_bulk_action();

		$data = $this->get_account_data();

		$total_items = $data['total_records'];
		$this->items = $data['data'];

		$this->set_pagination_args(array(
			'total_items' => $total_items,
			'per_page' => $per_page,
			'total_pages' => ceil($total_items / $per_page),
		));

	}

	function get_account_data() {

		$ret = array('total_records' => 0, 'data' => array());
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$option = get_option("mec_advimp_config_{$this->section}", array());

		$ret['total_records'] = count($option);
		foreach ($option as $k => $v) {

			$auth_config = get_option("mec_advimp_auth_{$this->section}", array());
			error_log(print_r($auth_config,true));

			if ( $this->section == 'google' ){
				$is_expired = false;
			} else {
				$is_expired =isset($auth_config[$v['id']]) && isset($auth_config[$v['id']]['expires_in']) && $auth_config[$v['id']]['expires_in'] < time()?true:false;
			}

			$auth_status = isset($auth_config[$v['id']]) && isset($auth_config[$v['id']]['status'])?$auth_config[$v['id']]['status']:false;
			if($is_expired == true){
				$auth_status = false; 
			}


			$ret['data'][] = array(
				'ID' => $v['id'],
				'title' => $v['title'],
				'active' => isset($v['active']) ? $v['active'] : '',
				'need_auth' => isset($v['need_auth']) && $v['need_auth']==1 ?true: false,
				'auth_status'=>$auth_status,
				'config'=>$v
			);
		}

		wp_reset_postdata();
		return $ret;
	}
}
