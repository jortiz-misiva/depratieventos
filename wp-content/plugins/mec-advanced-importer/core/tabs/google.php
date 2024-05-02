<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Google {
	public $name = 'Google';
	public $base_url = 'https://www.googleapis.com/calendar/v3';
	public $limit_fetch = 5;
	public $main;
	public $file;
	public $db;

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax_google_check_auth', array($this, 'check_auth'));
		add_action('wp_ajax_google_get_events', array($this, 'get_events'));
		add_action('wp_ajax_google_add_to_sync', array( \MEC_Advanced_Importer_Sync::class, 'add_to_auto_sync_by_ajax' ));

		add_action('wp_ajax__ajax_fetch_google_history', array($this, '_ajax_fetch_google_history_callback'));

	}

	function _ajax_fetch_google_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function public_vars() {
		return array(
			'batch' => null,
			'limit_fetch' => $this->limit_fetch,
			'title' => array(
				'needauthentication' => __('Needs authentication', 'mec-advanced-importer'),
				'authenticated' => __('Authentication successful', 'mec-advanced-importer'),
			),

		);
	}

	public function process_download_single_event($event_id, $category = array()) {
//        error_log("2222");
//        error_log($event_id);
//        error_log(print_r($category,true));
		$ex = explode('_', $event_id);
		if( empty( $ex[2] ) ){

			$ex[2] = '_' . $ex[3];
		}

		$post = array('importTypeVal' => $ex[2], 'selected_calendar' => $ex[1], 'selected_current' => $ex[0], 'importType' => 'single');
		$this->prepare_request($post);
//        error_log("77777");
//        error_log(print_r($post,true));
		$id = wp_insert_post(array(
			'post_title' => "EventID:{$ex[2]}",
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => "Download Single Event:{$ex[2]}",
		));
		add_post_meta($id, 'import_origin', $this->name);
		if (count($category) > 0) {
			add_post_meta($id, 'category', $category);
		}

		$response = wp_remote_get($post['url']);
//        error_log(print_r($post['url'],true));
//        error_log(print_r($post,true));
		if (isset($response->errors)) {
			add_post_meta($id, 'nothing_to_import', '1');
			return false;
		}
//        error_log("88888");
//        error_log(print_r($response,true));
		$body = wp_remote_retrieve_body($response);
		$body_response = json_decode($body);

//        error_log("222222");
//        error_log(print_r($body_response,true));

		$ret = $this->saved_single_event($body_response, $category);

		if (!$ret) {
			return false;
		}
//        error_log("10101010");
//        error_log(print_r($ret,true));
		if ($ret['is_new']) {
			add_post_meta($id, 'created', '1');
		} else {
			add_post_meta($id, 'updated', '1');
		}
//        error_log("@@@@@@@");
//        error_log(print_r($ret,true));
		return $ret;

	}

	public function download_single_event($event_id) {

		$category = isset($_POST['category']) ? json_decode(stripslashes($_POST['category']), true) : array();

		$ret = $this->process_download_single_event($event_id, $category);
		if ($ret == false) {
			return wp_send_json_error('Failed get Event');
		}
		return wp_send_json_success($ret, 200);

	}

	public function preload() {
		$cmd = isset($_GET['advimp_cmd']) ? $_GET['advimp_cmd'] : null;
		if ($cmd == 'google_auth') {
			$this->authorize_user();
		} else if ($cmd == 'google_callback') {
			$this->authorize_callback();
		}
	}

	/*
		* Authorize google user to get access token
	*/
	function authorize_user() {

		$id = isset($_GET['authid']) ? $_GET['authid'] : null;
		if ($id == null) {
			wp_die(__('Please Select account.', 'mec-advanced-importer'));
			return false;
		}

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'config', $id);

		$client_id = isset($s['client_id']) ? $s['client_id'] : null;
		$client_secret = isset($s['client_secret']) ? $s['client_secret'] : null;

		if (!$client_id || !$client_secret) {
			wp_die(__('Please insert Google Client ID and Secret.', 'mec-advanced-importer'));
			return false;
		}

		$redirect_url = admin_url(MEC_ADVANCED_IMPORTER_CALLBACK . 'google_callback');
		$param_url = urlencode($redirect_url);

		$scope1 = urlencode('https://www.googleapis.com/auth/calendar.events.readonly');
		$scope2 = urlencode('https://www.googleapis.com/auth/calendar');
		$scope3 = urlencode('https://www.googleapis.com/auth/calendar.events.readonly');
		$scope4 = urlencode('https://www.googleapis.com/auth/calendar.events');

		$scope = "{$scope1} {$scope2} {$scope3} {$scope4}";

		$dialog_url = 'https://accounts.google.com/o/oauth2/v2/auth?';
		$dialog_url .= "scope={$scope}&";
		$dialog_url .= 'access_type=offline&';
		$dialog_url .= 'include_granted_scopes=true&';
		$dialog_url .= 'response_type=code&';
		$dialog_url .= 'state=state_parameter_passthrough_value&';
		$dialog_url .= "redirect_uri={$param_url}&";
		$dialog_url .= "client_id={$client_id}";

		update_option('mec_advimp_google_current_request', $id);

		header("Location: " . $dialog_url);
		wp_redirect($dialog_url);
		exit;

	}

	/*
		* Authorize google user on callback to get access token
	*/
	function authorize_callback() {

		$user_token_options = array('authorize_status' => 0);
		$code = isset($_GET['code']) && !empty($_GET['code']) ? sanitize_text_field($_GET['code']) : null;

		if (empty($code)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'APP-ID or APP-Secret is null';
			wp_die(__('Response Failed, please try again.', 'mec-advanced-importer'));
		}

		$id = get_option('mec_advimp_google_current_request', null);
		if ($id == null) {
			wp_die(__('Please Select account.', 'mec-advanced-importer'));
			return false;
		}

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'config', $id);

		$client_id = isset($s['client_id']) ? $s['client_id'] : null;
		$client_secret = isset($s['client_secret']) ? $s['client_secret'] : null;

		$redirect_url = admin_url(MEC_ADVANCED_IMPORTER_CALLBACK . 'google_callback');
		$param_url = urlencode($redirect_url);

		$args = array(
			'method' => 'POST',
			'headers' => array(
				'Content-type: application/x-www-form-urlencoded',
			),
			'sslverify' => false,
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_type' => 'authorization_code',
				'redirect_uri' => $redirect_url,
				'code' => $code,
			),
		);

		$access_token = "";

		$response = wp_remote_post('https://oauth2.googleapis.com/token', $args);
		$body = wp_remote_retrieve_body($response);

		if (!$body || empty($body)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'Network Error';
			wp_die('Network Error');
			return false;
		}

		$b = json_decode($body);

		if (isset($b->error) && !empty($b->error)) {
			$msg = "<h3>Google Response Error!</h3>";
			$msg .= "{$b->error_description}";
			$msg .= "<br/>";
			$msg .= "{$b->error}";
			wp_die($msg);
			return false;
		}

		if (isset($b->access_token)) {

			$access_token = $b->access_token;
			$user_token_options['access_token'] = sanitize_text_field($access_token);
			$user_token_options['result'] = $b;
			$user_token_options['status'] = true;
			$user_token_options['request_at'] = time();
			$user_token_options['expires_in'] = time() + $b->expires_in;
			$user_token_options['refresh_token'] = $b->refresh_token;
		}

		$cur = get_option('mec_advimp_auth_google', array());
		$cur[$id] = $user_token_options;
		update_option('mec_advimp_auth_google', $cur);
		?>
		<script type="text/javascript">
			window.close();
		</script>
		<?php
	}

	public static function refresh_token($id) {
		$cur = get_option('mec_advimp_auth_google', array());
		// echo '<pre>';
		// print_r($cur[$id]['result']);
		// echo '</pre>';
		// die();
		$refresh_token = isset($cur[$id]['result']->refresh_token) ? $cur[$id]['result']->refresh_token : '';
		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'config', $id);
		//$auth = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'auth', $id);

		$client_id = isset($s['client_id']) ? $s['client_id'] : null;
		$client_secret = isset($s['client_secret']) ? $s['client_secret'] : null;

		$args = array(
			'method' => 'POST',
			'headers' => array(
				'Content-type: application/x-www-form-urlencoded',
			),
			'sslverify' => false,
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_type' => 'refresh_token',
				'refresh_token' => $refresh_token,
			),
		);

		$response = wp_remote_post('https://oauth2.googleapis.com/token', $args);
		$body = wp_remote_retrieve_body($response);
		// echo '<pre>';
		// print_r($body);
		// echo '</pre>';
		$auth = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'auth', $id);
		if (!$body || empty($body)) {
			return false;
		}

		$b = json_decode($body);

		if (isset($b->error) && !empty($b->error)) {
			return false;
		}

		if (isset($b->access_token)) {

			$access_token = $b->access_token;
			$user_token_options['access_token'] = sanitize_text_field($access_token);
			$user_token_options['result'] = $b;
			$user_token_options['result']->refresh_token = $refresh_token;
			$user_token_options['status'] = true;
			$user_token_options['request_at'] = time();
			$user_token_options['expires_in'] = time() + $b->expires_in;
			$user_token_options['refresh_token'] = $refresh_token;

		}

		$cur = get_option('mec_advimp_auth_google', array());
		// echo '<pre>';
		// print_r($user_token_options);
		// echo '</pre>';
		// die();
		$cur[$id] = $user_token_options;
		update_option('mec_advimp_auth_google', $cur);
		return true;

	}

	public function check_auth() {

		$id = isset($_POST['authid']) ? $_POST['authid'] : null;

		$option = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'auth', $id);
		if (!$option || count($option) == 0) {
			return wp_send_json_error('Google not response, App-ID or App-Secret is null');
		}

		if (!isset($option['status'])) {
			return wp_send_json_error('Result Failed!');
		}

		if ($option['status'] != true) {
			return wp_send_json_error($option['error_message']);
		}

		return wp_send_json_success('Success Authorized get events', 200);
	}

	public function get_pageid($post) {

		$url = "{$this->base_url}/me?access_token={$post['access_token']}";
		$response = wp_remote_get($url);

		if (isset($response->errors)) {

			return false;
		}

		$body = wp_remote_retrieve_body($response);
		return $body->id;

	}

	public function get_events_count_of_calendar($post, $calendar_id) {

		if (!isset($post['get_calendar_list_item'])) {
			return;
		}

		if (empty($post['get_calendar_list_item'])) {
			return;
		}

		$url = "{$this->base_url}/calendars/{$calendar_id}/events?maxResults=1000&orderBy=updated&access_token={$post['access_token']}";
		$url = $this->add_query_args($url,$post);
		$response = wp_remote_get($url);
		// error_log(print_r($url, true));

		if (isset($response->errors)) {

			return null;
		}
		$body = wp_remote_retrieve_body($response);

		$data = json_decode($body);

        if (!isset($data->items)) {
            return null;
        }

		foreach( $data->items as $k => $event ){

			if( 'cancelled' === $event->status ){
				unset( $data->items[ $k ] );
				continue;
			}
		}

		if (!isset($data->items)) {
			return null;
		}

		return count($data->items);

		// error_log(print_r($data, true));

	}

	public function get_calendar_list(&$post) {
		$cur = get_option('mec_advimp_auth_google', array());
		//var_dump($cur);
		// var_dump($post);
		// die();
		$url = "{$this->base_url}/users/me/calendarList?access_token={$post['access_token']}";

		$response = wp_remote_get($url);

		if (isset($response->errors)) {
			add_post_meta($post['id'], 'request', json_encode($response->errors), false);
			add_post_meta($post['id'], 'request', 'finish', false);
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		add_post_meta($post['id'], 'request', "Get Response From {$this->name}", false);
		$body_response = json_decode($body);

		if (!$body_response) {
			return array();
		}

		$ret = array();
		foreach ($body_response->items as $k => $v) {
			$ret[] = array(
				'ID' => "{$post['selected_current']}_{$v->id}",
				'title' => $v->summary,
				'start' => null,
				'end' => null,
				'link' => null,
				'events' => $this->get_events_count_of_calendar($post, $v->id),
				'uid' => md5("{$post['selected_current']}_{$v->id}"),
			);
		}

		return $ret;
	}

	public function prepare_request(&$post) {
		$base = "{$this->base_url}/";
		$val = isset($post['importTypeVal']) ? $post['importTypeVal'] : null;

		if( !isset( $post['access_token'] ) || empty( $post['access_token'] ) ){

			$post['access_token'] = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('google', 'auth', $post['selected_current'], 'access_token');
		}
		$selected_calendar = isset( $post['selected_calendar'] ) ? $post['selected_calendar'] : '';

		switch ($post['importType']) {
		case 'all':
			$base .= 'calendars/' . $selected_calendar . '/events';
			break;
		case 'single':

			// if (isset($val)) {
			$base .= 'calendars/' . $selected_calendar . '/events/' . $val;
			// $base .= $val;
			// } else if (isset($post['spec_event'])) {
			// $base .= $post['spec_event'];
			// }

			break;
		case 'page':
			$base .= $this->get_pageid($post) . '/events';
			break;

		case 'group':
			$base .= $val . '/events';
			break;

		default:
			$base .= 'calendars/' . $selected_calendar . '/events';
			break;
		}

		$post['url'] = "{$base}?maxResults=1000&orderBy=updated&access_token={$post['access_token']}";

		if (isset($post['start_date'])) {
			$d = new \DateTime($post['start_date']);
			$post['sdate'] = $d->getTimestamp();
		}

		if (isset($post['end_date'])) {
			$d = new \DateTime($post['end_date']);
			$post['edate'] = $d->getTimestamp();
		}

	}

	public function prepare_row($select, $event, $post) {

		$all_day = false;
//		error_log(print_r($event, true));

		if( 'cancelled' === $event->status ){
			return false;
		}

		// if (isset($post['status'])) {

		// 	if ($post['status'] == 'publish' && $event->is_draft == true) {
		// 		return false;
		// 	} else if ($post['status'] == 'canceled' && $event->is_canceled == false) {
		// 		return false;
		// 	} else if ($post['status'] == 'draft' && $event->is_draft == false) {
		// 		return false;
		// 	}
		// }

		// $stime = isset($event->start_time) ? $event->start_time : '2020-12-01T00:00:00+0000';
		// $zone = isset($event->timezone) ? $event->timezone : 'America/Bahia';
		if(!empty($event->start->dateTime)){

			$start_date = $event->start->dateTime;
		}elseif(!empty($event->start->date)){

			$start_date = $event->start->date;
			$all_day = true;
		}
		$sdate = new \DateTime($start_date);
		$unix_start = $sdate->getTimestamp();

		// $etime = isset($event->end_time) ? $event->end_time : '2020-12-01T00:00:00+0000';
		if(!empty($event->end->dateTime)){

			$end_date = $event->end->dateTime;
		}elseif(!empty($event->end->date)){

			$end_date = $event->end->date;
			$all_day = true;
		}
		$edate = new \DateTime($end_date);
		$unix_end = $edate->getTimestamp();

		// if (isset($post['sdate']) && $unix_start < $post['sdate']) {
		// 	return false;
		// }

		// if (isset($post['edate']) && $unix_end > $post['edate']) {
		// 	return false;
		// }
		$recurrence = isset($event->recurrence) && !empty($event->recurrence) ? true : false;

		return array(
			'ID' => "{$select}_{$post['selected_calendar']}_{$event->id}",
			'title' => isset($event->summary)?$event->summary:'' . ($recurrence ? ' -- <span style="color:red">'.__('repeating','mec-advanced-importer').'</span>' : ''),
			'start' => $start_date,
			'end' => $end_date,
			'link' => $event->htmlLink,
			'uid' => md5("{$select}_{$event->id}"),
			'recurrence' => $recurrence,
			'all_day' => $all_day
		);
	}

	public function schedule_events($event_ids, $scheduled, $scheduledType) {

		$ids = json_decode(stripslashes($event_ids), true);
		if (count($ids) == 0) {
			return wp_send_json_error(__('Nothing selected', 'mec-advanced-importer'), 200);
		}

		$date = new \DateTime();

		$interval = null;
		switch ($scheduledType) {
		case 'hourly':
			$interval = new \DateInterval('PT1H');
			break;

		case 'twicedaily':
			$interval = new \DateInterval('PT1H');
			break;

		case 'daily':
			$interval = new \DateInterval('P1D');
			break;

		case 'weekly':
			$interval = new \DateInterval('P1W');
			break;

		case 'monthly':
			$interval = new \DateInterval('P1M');
			break;
		}
		global $wpdb;
		foreach ($ids as $k => $id) {

			$date->add($interval);

			$ins_id = wp_insert_post(array(
				'post_title' => $id['title'],
				'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
				'post_content' => $id['link'],
				'post_date' => $date->format('Y-m-d H:i:s'),
				'meta_input' => array(
					'event_id' => $k,
					'event_class' => $this->name,
					'event_link' => $id['link'],
				),
				'post_status' => 'publish',

			));
		}

		return wp_send_json_success('Success Add', 200);

	}

	public function get_events() {

		$post = isset($_POST) ? $_POST : null;
		$post['selected'] = stripslashes($post['selected']);
		$selected = json_decode($post['selected'], true);
		$this->preview = isset($post['preview']) ? $post['preview'] == 1 : false;

		$req_id = md5(time() . print_r($post, true) . mt_rand(1, 9999));
		$id = wp_insert_post(array(
			'post_title' => $post['reqid'],
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => json_encode($post),
		));
		add_post_meta($id, 'import_origin', 'google');
		add_post_meta($id, 'selected', $selected);
		add_post_meta($id, 'selected_current', 0);
		add_post_meta($id, 'request_post', $post);
		$post['selected_current'] = $selected[0];
		$data_account = array();

		$calendar_list = isset($post['get_calendar_list']) ? (int) $post['get_calendar_list'] : null;
		$calendar_list_item = isset($post['get_calendar_list_item']) ? $post['get_calendar_list_item'] : null;

		if (count($selected) == 1 && strpos($post['selected_current'], '_') !== false) {

			$ex = explode('_', $post['selected_current']);

			$post['selected_current'] = $ex[0];
			$post['selected_calendar'] = $ex[1];

			$calendar_list = null;
			$calendar_list_item = null;
			$selected = array($ex[0]);

		}

		// on the new request generate base url
		// $this->prepare_request($post);

		$post['id'] = $id;

		$data = array('total_records' => 0, 'data' => array());
		$data_account = array();

		// on priview page, returned all data
		if ($this->preview == true) {
			foreach ($selected as $select) {

				$post['selected_current'] = $select;
				// on the new request generate base url
				$this->prepare_request($post);

				if (!isset($data_account[$select])) {
					$data_account[$select] = array('total_records' => 0, 'data' => array());
				}

				if ($calendar_list == 1) {
					$data_account[$select]['data'] = $this->get_calendar_list($post);
					$data_account[$select]['total_records'] += 1;
					$data['total_records'] += 1;
					continue;
				}

				$d = $this->get_all_events($post);

				if (!$d) {
					continue;
				}

				if ($post['importType'] == 'single') {
					$row = $this->prepare_row($select, $d, $post);
					if (!$row) {
						continue;
					}
					$data['data'][] = $row;
					$data['total_records'] += 1;

					$data_account[$select]['data'] = $row;
					$data_account[$select]['total_records'] += 1;

					continue;
				}

				if (isset($d->items) && !empty($d->items)) {

					foreach ($d->items as $key => $event) {

						$row = $this->prepare_row($select, $event, $post);
						if (!$row) {
							continue;
						}

						$data['data'][] = $row;
						$data['total_records'] += 1;

						$data_account[$select]['data'] = $row;
						$data_account[$select]['total_records'] += 1;
					}
				}
			}

			add_post_meta($post['id'], 'request', "finish", false);

			if ($data['total_records'] == 0) {
				return wp_send_json_error('Failed Request', 200);
			}

			if (!class_exists('WP_List_Table')) {
				require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			}

			$table = null;

			if ($calendar_list == 1) {

				include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-accounts-table.php';
				$table = new \MEC_Advanced_Importer_Preview_Accounts_Table();
				$table->enable_download_all = false;
				$data = array();

				if ($calendar_list_item == null) {
					foreach ($data_account as $k => $v) {

						$data[$k] = array(
							'id' => $k,
							'title' => null,
							'total_records' => count($v['data']),
						);
					}

					$table->action_call = 'MEC_ADVIMP_Accounts_Calendars';
					$table->event_name = 'Calendars';

				} else if (!empty($calendar_list_item)) {

					foreach ($data_account[$selected[0]]['data'] as $k => $v) {

						// error_log(print_r($v, true));

						// $id = "{$selected[0]}_{$v['ID']}";

						$data[$v['ID']] = array(
							// 'id' => $v['ID'],
							'title' => $v['title'],
							'total_records' => $v['events'],
							'is_calendar' => true,
							'calendar_list_item' => $calendar_list_item,
						);
					}

					// error_log(print_r($data,true));
				}

				$table->data = $data;
				$table->data_account = $data_account;

			} elseif (count($selected) > 1) {

				include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-accounts-table.php';
				$table = new \MEC_Advanced_Importer_Preview_Accounts_Table();
				$table->enable_download_all = false;
				$table->data = $data_account;

			} else {

				$table = new \MEC_Advanced_Importer_Preview_Table();
				$table->data = $data;
			}

			$table->page_section = strtolower($this->name);
			update_option('mec_advimp_' . $table->page_section . '_current_event', $data);
			$table->params = $post;
			$table->prepare_items();

			ob_start();
			$table->display();
			$display = ob_get_clean();

			return wp_send_json_success(array(
				'next' => false,
				'post_id' => $post['id'],
				'table' => $display,
			), 200);
		}

		if (!$this->get_all_events($post)) {
			return wp_send_json_error('Failed Request', 200);
		}

		$is_next = get_post_meta($post['id'], 'request_next', true);
		if ($is_next && !empty($is_next)) {
			return wp_send_json_success(array('next' => true, 'post_id' => $post['id']), 200);
		}

		return wp_send_json_success(array('next' => false, 'post_id' => $post['id']), 200);

	}

	public function add_query_args($url,$post){

		$datetime_format = 'Y-m-d\TH:i:s\Z';
		if(isset($post['start_date']) && !empty($post['start_date'])){

			$time_min = date_i18n($datetime_format,strtotime($post['start_date']));
			$url .= '&timeMin='.$time_min;
		}

		if(isset($post['end_date']) && !empty($post['end_date'])){

			$time_max = date_i18n($datetime_format,strtotime($post['end_date']));
			$url .= '&timeMax='.$time_max;
		}

		$timezone = date_default_timezone_get();
		if(!empty($timezone)){
			$url .= '&timeZone='.$timezone;
		}

		return $url;
	}

	public function get_all_events($post) {

		$post['url'] = $this->add_query_args($post['url'],$post);
		$url = $post['url'];

		try {
			add_post_meta($post['id'], 'request', "Send Request to Google", false);
			$response = wp_remote_get($url);

			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Google", false);
			$body_response = json_decode($body);

			if (isset($body_response->error)) {
				add_post_meta($post['id'], 'request', $body_response->error->message, false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			if ($this->preview == true) {
				return $body_response;
			}

			return $this->handle_get_events($body_response, $post);
		} catch (Exception $e) {

			add_post_meta($post['id'], 'request', 'Request Failed,', false);
			add_post_meta($post['id'], 'request', "finish", false);
		}
	}

	public function handle_get_events($events, $post) {

		if (!isset($events->data)) {
			add_post_meta($post['id'], 'request', "finish", false);
			return false;
		}

		$count = count($events->data);

		add_post_meta($post['id'], 'request', 'handle fetched events:' . $count, false);

		if ($count <= 0) {
			add_post_meta($post['id'], 'request', "finish", false);
			return true;
		}

		$ids = array();

		foreach ($events->data as $event) {
			$ret = $this->saved_single_event($event);
			if ($ret) {
				$link = '<a target="_blank" href="' . get_permalink($ret['post_id']) . '">' . $event->name . '</a>';
				$title_ins = $ret['is_new'] ? 'Add' : 'Update';
				add_post_meta($post['id'], 'request', "{$title_ins} Event:{$link}", false);
			}
		}

		$next = isset($events->paging) && isset($events->paging->next) ? $events->paging->next : null;
		if (!$next) {

			$selected = get_post_meta($post['id'], 'selected', true);
			$current = get_post_meta($post['id'], 'selected_current', true);

			// no any next account
			// 1. only selected one account
			// 2. many account and finished any selected account request
			if (count($selected) == 1 || count($selected) == $current + 1) {
				add_post_meta($post['id'], 'request', "finish", false);
				delete_post_meta($post['id'], 'request_next');

				return true;
			}

			// saved current request selected account
			update_post_meta($post['id'], 'selected_current', $current + 1);

			// get original base request, for generate next request by new account
			$base_request = get_post_meta($post['id'], 'request_post', true);

			$old = $selected[$current];
			$new = $selected[$current + 1];

			// set selected account hash(id on settings-table.php)
			$base_request['selected_current'] = $new;

			// regenerate url for next account
			$this->prepare_request($base_request);

			delete_post_meta($post['id'], 'request');
			delete_post_meta($post['id'], 'request_next');

			// next url is new account url
			update_post_meta($post['id'], 'request_next', $base_request['url']);
			update_post_meta($post['id'], 'request', "next", false);
			update_post_meta($post['id'], 'request', "change-account", false);

		} else {
			delete_post_meta($post['id'], 'request');
			delete_post_meta($post['id'], 'request_next');

			update_post_meta($post['id'], 'request_next', $next);
			update_post_meta($post['id'], 'request', "next", false);
		}

		return true;

	}

	public function get_location($event) {

		if (!isset($event->location)) {
			return null;
		}

		$location = array
			(
			'name' => trim($event->location),
			'address' => trim($event->location),
			'latitude' => '',
			'longitude' => '',
		);

		return $this->main->save_location($location);
	}

	public function get_organizer($event) {

		if (!isset($event->organizer)) {
			return null;
		}

		return $this->main->save_organizer(array
			(
				'name' => isset($event->organizer->displayName) ? trim($event->organizer->displayName) : null,
				'tel' => '',
				'url' => '',
				'email' => isset($event->organizer->email) ? trim($event->organizer->email) : null,
			));
	}

	public function saved_single_event($event, $category = array()) {
//        error_log("44444");
//        error_log(print_r($event,true));
//        error_log(print_r($category,true));
		$req_post_data = array();
		$request_id = isset( $_POST['req_id'] ) ? sanitize_text_field( $_POST['req_id'] ) : false;
		if( $request_id ){

			$req_post_data = get_post_meta( $request_id, 'request_post', true );
		}
		$link_to_main_event_status = isset( $req_post_data['linkmore'] ) ? (bool)$req_post_data['linkmore'] : false;

		$add_account_as_category = $req_post_data['add_account_as_category'] ?? '';
		$account_title = $req_post_data['account_title'] ?? '';
		if( '1' == $add_account_as_category && !empty( $account_title ) ){

			$account_category_id = $this->main->save_category(array(
				'name' => $account_title,
			));

			$category[] = $account_category_id;
		}

		/**
		 * TODO: Remove
		 */
		if(isset($event->items) && is_array($event->items)){
			$events = $event->items;
			$event = $events[0];
		}

		$start_datetime = isset($event->start) && isset($event->start->dateTime) ? $event->start->dateTime : null;
		$start_datetime = !$start_datetime && isset($event->start) && isset($event->start->date) ? $event->start->date : $start_datetime;

		$end_datetime = isset($event->end) && isset($event->end->dateTime) ? $event->end->dateTime : null;
		$end_datetime = !$end_datetime && isset($event->end) && isset($event->end->date) ? $event->end->date : $end_datetime;

		$allday = 0;
		if ($start_datetime == null || $end_datetime == null) {
			$allday = 1;
		}

		if (!isset($event->summary) || empty($event->summary)) {
			return false;
		}
//        error_log("******");
//        error_log(print_r($event->summary,true));
		$sdate = new \DateTime($start_datetime);
		$date_start = new \DateTime($sdate->format('Y-m-d G:i'));

		$day_time = (int)date('Hi',strtotime($end_datetime));
		$end_datetime = ($day_time > 0) ? $end_datetime : date('Y-m-d H:i:s',strtotime($end_datetime) -1 );

		$edate = new \DateTime($end_datetime);
		$date_end = new \DateTime($edate->format('Y-m-d G:i'));

		$location_id = $this->get_location($event);
		$organizer_id = $this->get_organizer($event);

		$start_date = $date_start->format('Y-m-d');
		$start_hour = $date_start->format('g');
		$start_minutes = $date_start->format('i');
		$start_ampm = $date_start->format('A');

		$end_timestamp = strtotime($end_datetime);

		$end_date = $end_timestamp ? $date_end->format('Y-m-d') : $start_date;
		$end_hour = $end_timestamp ? $date_end->format('g') : 8;
		$end_minutes = $end_timestamp ? $date_end->format('i') : '00';
		$end_ampm = $end_timestamp ? $date_end->format('A') : 'PM';

		// Import Google Link as Event Link
		$read_more = true === $link_to_main_event_status ? $event->htmlLink : '';

		// Import Google Link as More Info
		$more_info = $event->htmlLink;
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;

        $advanced_days = NULL;
        $repeat_status= NULL;
		$repeat_type= NULL;
		$interval= NULL;
		$finish= NULL;
		$year= NULL;
		$month= NULL;
		$day= NULL;
		$week= NULL;
		$weekday= NULL;
		$weekdays= NULL;
        $g_recurrence_rule = NULL;

		if(isset($event->recurrence) && is_array($event->recurrence)) {
            $repeat_status = 1;
            $r_rules = $event->recurrence;

            $i = 0;

            do {
                $g_recurrence_rule = $r_rules[$i];
                $main_rule_ex = explode(':', $g_recurrence_rule);
                $rules = explode(';', $main_rule_ex[1]);

                $i++;
            } while ($main_rule_ex[0] != 'RRULE' and isset($r_rules[$i]));

            $rule = array();
            foreach ($rules as $rule_row) {
                $ex = explode('=', $rule_row);
                $key = strtolower($ex[0]);
                $value = ($key == 'until' ? $ex[1] : strtolower($ex[1]));

                $rule[$key] = $value;
            }

            if ($rule['freq'] == 'daily') {
                $repeat_type = 'daily';
                $interval = isset($rule['interval']) ? $rule['interval'] : 1;
            } elseif ($rule['freq'] == 'weekly') {
                $repeat_type = 'weekly';
                $interval = isset($rule['interval']) ? $rule['interval'] * 7 : 7;
            } elseif ($rule['freq'] == 'monthly' and isset($rule['byday']) and trim($rule['byday'])) {
                $repeat_type = 'advanced';

                $adv_week = (isset($rule['bysetpos']) and trim($rule['bysetpos']) != '') ? $rule['bysetpos'] : (int)substr($rule['byday'], 0, -2);
                if ($adv_week < 0) $adv_week = 'l';

                $adv_day = str_replace($adv_week, '', $rule['byday']);

                $mec_adv_day = 'Sat';
                if ($adv_day == 'su') $mec_adv_day = 'Sun';
                elseif ($adv_day == 'mo') $mec_adv_day = 'Mon';
                elseif ($adv_day == 'tu') $mec_adv_day = 'Tue';
                elseif ($adv_day == 'we') $mec_adv_day = 'Wed';
                elseif ($adv_day == 'th') $mec_adv_day = 'Thu';
                elseif ($adv_day == 'fr') $mec_adv_day = 'Fri';

                $advanced_days = array($mec_adv_day . '.' . $adv_week);
            } elseif ($rule['freq'] == 'monthly') {
                $repeat_type = 'monthly';
                $interval = isset($rule['interval']) ? $rule['interval'] : 1;

                $year = '*';
                $month = '*';

                $s = $start_date;
                $e = $end_date;

                $_days = array();
                while (strtotime($s) <= strtotime($e)) {
                    $_days[] = date('d', strtotime($s));
                    $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
                }

                $day = ',' . implode(',', array_unique($_days)) . ',';

                $week = '*';
                $weekday = '*';
            } elseif ($rule['freq'] == 'yearly') {
                $repeat_type = 'yearly';

                $year = '*';

                $s = $start_date;
                $e = $end_date;

                $_months = array();
                $_days = array();
                while (strtotime($s) <= strtotime($e)) {
                    $_months[] = date('m', strtotime($s));
                    $_days[] = date('d', strtotime($s));

                    $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
                }

                $month = ',' . implode(',', array_unique($_months)) . ',';
                $day = ',' . implode(',', array_unique($_days)) . ',';

                $week = '*';
                $weekday = '*';
            } else {
                $repeat_type = '';
            }

            // Custom Week Days
            if ($repeat_type == 'weekly' and isset($rule['byday']) and count(explode(',', $rule['byday'])) > 1) {
                $g_week_days = explode(',', $rule['byday']);
                $week_day_mapping = array('mo' => 1, 'tu' => 2, 'we' => 3, 'th' => 4, 'fr' => 5, 'sa' => 6, 'su' => 7);

                $weekdays = '';
                foreach ($g_week_days as $g_week_day) $weekdays .= $week_day_mapping[$g_week_day] . ',';

                $weekdays = ',' . trim($weekdays, ', ') . ',';
                $interval = NULL;

                $repeat_type = 'certain_weekdays';
            }

            $finish = isset($rule['until']) ? date('Y-m-d', strtotime($rule['until'])) : NULL;
        }

		$args = array
			(
			'title' => $event->summary,
			'content' => isset($event->description) ? $event->description : null,
			'location_id' => $location_id,
			'organizer_id' => $organizer_id,
			'date' => array
			(
				'start' => array(
					'date' => $start_date,
					'hour' => $start_hour,
					'minutes' => $start_minutes,
					'ampm' => $start_ampm,
				),
				'end' => array(
					'date' => $end_date,
					'hour' => $end_hour,
					'minutes' => $end_minutes,
					'ampm' => $end_ampm,
				),
				'repeat' => array(),
				'allday' => $allday,
				'comment' => '',
				'hide_time' => 0,
				'hide_end_time' => 0,
			),
			'start' => $start_date,
			'start_time_hour' => $start_hour,
			'start_time_minutes' => $start_minutes,
			'start_time_ampm' => $start_ampm,
			'end' => $end_date,
			'end_time_hour' => $end_hour,
			'end_time_minutes' => $end_minutes,
			'end_time_ampm' => $end_ampm,
			'repeat_status' => (int)$repeat_status,
			'repeat_type' => $repeat_type,
			'interval' => $interval,
			'finish' => $finish,
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'week' => $week,
			'weekday' => $weekday,
			'weekdays' => $weekdays,
			'meta' => array(
				'mec_source' => 'google-calendar',
				'mec_advimp_google_event_id' => $event->id,
				'mec_gcal_id' => $event->id,
				'mec_g_recurrence_rule'=>$g_recurrence_rule,
				'mec_allday' => $allday,
				'mec_read_more' => $read_more,
				'mec_more_info' => $more_info,
				'mec_advanced_days'=>$advanced_days,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='{$event->id}' AND `meta_key`='mec_advimp_google_event_id'", 'loadResult');
		if ($post_id) {
			$ret['is_new'] = false;
		}
//        error_log("#####");
//        error_log(print_r($post_id,true));
//        error_log(print_r($args,true));
		// Insert the event into MEC
		$post_id = $this->main->save_event($args, $post_id);
		// Set categories to the post
		if (count($category)) {
			foreach ($category as $category_id) {
				wp_set_object_terms($post_id, (int) $category_id, 'mec_category', true);
			}
		}

		$ret['post_id'] = $post_id;

		// Set location to the post
		if ($location_id) {
			wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
		}

		// if (!has_post_thumbnail($post_id) and isset($event->cover)) {
		// 	$photo = $this->main->get_web_page($event->cover->source);
		// 	$file_name = md5($post_id) . '.' . $this->main->get_image_type_by_buffer($photo);

		// 	$path = rtrim($wp_upload_dir['path'], DS . ' ') . DS . $file_name;
		// 	$url = rtrim($wp_upload_dir['url'], '/ ') . '/' . $file_name;

		// 	$file->write($path, $photo);
		// 	$this->main->set_featured_image($url, $post_id);
		// }

		$ret['url'] = get_permalink($post_id);
		$ret['title'] = $args['title'];
//        error_log("33333");
//        error_log(print_r($ret,true));
		return $ret;

	}

	public function is_token_valid() {
		$option = get_option($this->option_name, array());
		if (isset($option['authorize_status']) && $option['authorize_status'] == 1) {
			if (isset($option['expire_at']) && ($option['expire_at'] - 500) > time()) {
				return true;
			}
		}

		return false;
	}

	public function content() {
		$url = admin_url('admin.php?page=MEC-advimp&advimp_cmd=google_auth');
		$getall = admin_url('admin.php?page=MEC-advimp&advimp_cmd=google_getall');
		$add_to_auto_sync = admin_url('admin.php?page=MEC-advimp&advimp_cmd=mec_add_to_auto_sync');

		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'google.php';
		include $content;

	}

}
