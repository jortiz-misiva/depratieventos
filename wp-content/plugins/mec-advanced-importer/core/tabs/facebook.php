<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Facebook {
	public $name = 'Facebook';
	public $option_name = 'advimp_user_token_options';
	public $batch = array('my', 'page', 'group');
	public $limit_fetch = 500;
	public $timeout = 120;
	public $main;
	public $file;
	public $db;
	public $preview = true;
	public $base_url = 'https://graph.facebook.com/v8.0';
	public $base_url_login = 'https://facebook.com/v8.0';

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax_facebook_check_auth', array($this, 'check_auth'));
		add_action('wp_ajax_facebook_get_events', array($this, 'get_events'));
		add_action('wp_ajax_facebook_add_to_sync', array( \MEC_Advanced_Importer_Sync::class, 'add_to_auto_sync_by_ajax' ));

		add_action('wp_ajax__ajax_fetch_facebook_history', array($this, '_ajax_fetch_facebook_history_callback'));

	}

	function _ajax_fetch_facebook_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function process_download_single_event($event_id, $category = array()) {

		$ex = explode('_', $event_id);

		$post = array('spec_event' => $ex[1], 'selected_current' => $ex[0], 'importType' => 'single');
		$this->prepare_request($post);

		$id = wp_insert_post(array(
			'post_title' => "EventID:{$ex[1]}",
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => "Download Single Event:{$ex[1]}",
		));
		add_post_meta($id, 'import_origin', $this->name);
		if (count($category) > 0) {
			add_post_meta($id, 'category', $category);
		}

		$response = wp_remote_get($post['url'],array(
			'timeout' => $this->timeout
		));

		if (isset($response->errors)) {
			add_post_meta($id, 'nothing_to_import', '1');
			return false;
		}

		$body = wp_remote_retrieve_body($response);
		$body_response = json_decode($body);

		$ret = $this->saved_single_event($body_response, $category);
		if ($ret['is_new']) {
			add_post_meta($id, 'created', '1');
		} else {
			add_post_meta($id, 'updated', '1');
		}

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

	public function public_vars() {
		return array(
			'batch' => $this->batch,
			'limit_fetch' => $this->limit_fetch,

		);
	}

	public function preload() {
		$cmd = isset($_GET['advimp_cmd']) ? $_GET['advimp_cmd'] : null;
		if ($cmd == 'facebook_auth') {
			$this->authorize_user();
		} else if ($cmd == 'facebook_callback') {
			$this->authorize_callback();
		}
	}

	/*
		* Authorize facebook user to get access token
	*/
	function authorize_user() {

		$id = isset($_GET['authid']) ? $_GET['authid'] : null;
		if ($id == null) {
			wp_die(__('Please Select account.', 'mec-advanced-importer'));
			return false;
		}

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('facebook', 'config', $id);

		$app_id = isset($s['app_id']) ? $s['app_id'] : null;
		$app_secret = isset($s['app_secret']) ? $s['app_secret'] : null;

		if (!$app_id || !$app_secret) {
			wp_die(__('Please insert Facebook App ID and Secret.', 'mec-advanced-importer'));
			return false;
		}

		$redirect_url = admin_url(MEC_ADVANCED_IMPORTER_CALLBACK . 'facebook_callback');
		$param_url = urlencode($redirect_url);

		update_option($this->option_name, array());

		$scope_business = 'pages_read_engagement,pages_show_list,public_profile,page_events';
		$scope_normal = 'public_profile,user_managed_groups,user_events,read_page_mailboxes,pages_show_list,pages_read_user_content,pages_read_engagement,groups_show_list,groups_access_member_info,page_events';

		$dialog_url = $this->base_url_login . '/dialog/oauth?display=popup&client_id=' . $app_id . '&redirect_uri=' . $param_url . '&auth_type=rerequest&scope=';

		if (isset($s['businessapp']) && $s['businessapp'] == 1) {

			$dialog_url .= urlencode( $scope_business );
		} else {

			$dialog_url .= urlencode( $scope_normal );
		}

		update_option('mec_advimp_facebook_current_request', $id);

		header("Location: " . $dialog_url);
		wp_redirect($dialog_url);
		exit;
	}

	public function getEventPageToken($access_token) {

		$token_url = "{$this->base_url}/me?fields=id&access_token={$access_token}";
		$response = wp_remote_get($token_url,array(
			'timeout' => $this->timeout
		));
		$body = wp_remote_retrieve_body($response);

		if (!$body || empty($body)) {
			return false;
		} else {
			$b = json_decode($body);

			$token_url = "{$this->base_url}/{$b->id}/accounts?";
			$token_url .= "fields=name,access_token&";
			$token_url .= "access_token={$access_token}";

			$response = wp_remote_get($token_url,array(
				'timeout' => $this->timeout
			));
			$body = wp_remote_retrieve_body($response);

			if (!$body || empty($body)) {
				return false;
			} else {
				$b = json_decode($body);
				if (!$b) {
					return false;
				}

				if (!isset($b->data)) {
					return false;
				}

				foreach ($b->data as $k => $page) {

					if (isset($page->name) && "{$page->name}" == 'Event Pages') {
						return $page;
					}
				}
			}
		}
	}

	/*
		* Authorize facebook user on callback to get access token
	*/
	function authorize_callback() {

		$user_token_options = array('authorize_status' => 0);
		$code = isset($_GET['code']) && !empty($_GET['code']) ? sanitize_text_field($_GET['code']) : null;

		if (empty($code)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'APP-ID or APP-Secret is null';
			wp_die(__('Response Failed, please try again.', 'mec-advanced-importer'));
		}

		$id = get_option('mec_advimp_facebook_current_request', null);
		if ($id == null) {
			wp_die(__('Please Select account.', 'mec-advanced-importer'));
			return false;
		}

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('facebook', 'config', $id);

		$app_id = isset($s['app_id']) ? $s['app_id'] : '';
		$app_secret = isset($s['app_secret']) ? $s['app_secret'] : '';
		$title = $s['title'];

		$redirect_url = admin_url(MEC_ADVANCED_IMPORTER_CALLBACK . 'facebook_callback');

		$param_url = urlencode($redirect_url);

		$token_url = "{$this->base_url}/oauth/access_token?"
			. "client_id=" . $app_id . "&redirect_uri=" . $param_url
			. "&client_secret=" . $app_secret . "&code=" . $code;

		$access_token = "";

		$response = wp_remote_get($token_url,array(
			'timeout' => $this->timeout
		));
		$body = wp_remote_retrieve_body($response);

		if (!$body || empty($body)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'Network Error';
			wp_die($response);
		} else {
			$b = json_decode($body);

			if (isset($b->error) && isset($b->error->message)) {
				$msg = "<h3>Facebook Response Error!</h3>";
				$msg .= "{$b->error->code}:{$b->error->message}";
				$msg .= "<br/>";
				$msg .= "{$b->error->type}";
				wp_die($msg);
				return false;
			}

			if (isset($b->access_token)) {

				$base_expire = 60*3600;

				$expire_at = isset($b->expires_in)?$b->expires_in:$base_expire;

				if (isset($s['businessapp']) && $s['businessapp'] == 1) {
					// $event_page_token = $this->getEventPageToken($b->access_token);
					// if(!$event_page_token){
					// 	wp_die('Failed Get Event Page token!');
					// 	return false;
					// }

					// if(!isset($event_page_token->access_token)){
					// 	wp_die('Failed Get Event Page Access token!');
					// 	return false;
					// }

					// $expire_at = isset($event_page_token->expires_in)?$event_page_token->expires_in:$base_expire;
				}

				$access_token = $b->access_token;
				$user_token_options['access_token'] = sanitize_text_field($access_token);
				$user_token_options['result'] = $b;
				$user_token_options['status'] = true;
				$user_token_options['request_at'] = time();
				$user_token_options['expire_at'] = time() + $expire_at;
				$user_token_options['title'] = $title;
				// $user_token_options['event_page_token'] = $event_page_token;
				// $user_token_options['event_page_access_token'] = $event_page_token->access_token;

				error_log("pageAccessToken==={$user_token_options['event_page_access_token']}");

			}

			$cur = get_option('mec_advimp_auth_facebook', array());
			$cur[$id] = $user_token_options;
			update_option('mec_advimp_auth_facebook', $cur);
		}

		?>
		<script type="text/javascript">
			window.close();
		</script>
		<?php

	}

	public function check_auth() {

		$id = isset($_POST['authid']) ? $_POST['authid'] : null;

		$option = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('facebook', 'auth', $id);

		if (!$option || count($option) == 0) {
			return wp_send_json_error('Facebook not response, App-ID or App-Secret is null');
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
		$response = wp_remote_get($url,array(
			'timeout' => $this->timeout
		));

		if (isset($response->errors)) {

			return false;
		}

		$body = json_decode( wp_remote_retrieve_body($response) );

		return $body->id;
	}

	public function extract_page_id($post, $val = null) {
		if (!$val) {
			return $this->get_pageid($post);
		}

		if (is_numeric($val)) {
			return $val;
		}

		$ex = explode('/', $val);
		$en = end($ex);
		if (is_numeric($en)) {
			return $en;
		}

		$ex = explode('-', $en);
		$end_f = end($ex);
		if (is_numeric($end_f)) {
			return $end_f;
		}

		return $val;
	}

	public function prepare_request(&$post) {
		$base = "{$this->base_url}/";
		$val = isset($post['importTypeVal']) ? $post['importTypeVal'] : null;

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('facebook', 'config', $post['selected_current']);

		$key_access_token = 'access_token';
		if(isset($s['businessapp']) && $s['businessapp']==1){
			// $key_access_token = 'event_page_access_token';
			error_log("business Account");
		}

		$post['access_token'] = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('facebook', 'auth', $post['selected_current'], $key_access_token);


		switch ($post['importType']) {
		case 'all':
			$base .= 'me/events';
			break;
		case 'single':

			if (isset($val)) {
				$base .= $val;
			} else if (isset($post['spec_event'])) {
				$base .= $post['spec_event'];
			}

			break;
		case 'page':
			$base .= $this->extract_page_id($post, $val);
			$base .= '/events';

			break;

		case 'group':
			$base .= $val . '/events';
			break;

		default:
			$base .= 'me/events';
			break;
		}

		$params = '?fields=is_online,is_draft,attending_count,can_guests_invite,category,cover,declined_count,description,discount_code_enabled,end_time,event_times,guest_list_enabled,id,interested_count,is_page_owned,is_canceled,maybe_count,name,noreply_count,online_event_format,online_event_third_party_url,owner,parent_group,place,scheduled_publish_time,ticket_uri,ticket_uri_start_sales_time,start_time,ticketing_privacy_uri,timezone,ticketing_terms_uri,type,updated_time,comments,photos,picture,posts,videos,live_videos,feed,roles&limit=' . $this->limit_fetch . "&access_token={$post['access_token']}";
		$post['url'] = "{$base}/{$params}";

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

		if (isset($post['status'])) {

			if ($post['status'] == 'publish' && $event->is_draft == true) {
				error_log('status is draft!');
				return false;
			} else if ($post['status'] == 'canceled' && $event->is_canceled == false) {
				error_log('status is canceled!');
				return false;
			} else if ($post['status'] == 'draft' && $event->is_draft == false) {
				error_log('status is not draft!');
				return false;
			}
		}

		$stime = isset($event->start_time) ? $event->start_time : '2020-12-01T00:00:00+0000';
		$zone = isset($event->timezone) ? $event->timezone : 'America/Bahia';
		$sdate = new \DateTime($stime, new \DateTimeZone($zone));
		$unix_start = $sdate->getTimestamp();

		$etime = isset($event->end_time) ? $event->end_time : '2020-12-01T00:00:00+0000';
		$edate = new \DateTime($etime, new \DateTimeZone($zone));
		$unix_end = $edate->getTimestamp();

		if (isset($post['sdate']) && $unix_start < $post['sdate']) {
			error_log('Date is not equal 1');
			return false;
		}

		if (isset($post['edate']) && $unix_end > $post['edate']) {
			error_log('Date is not equal 1');
			return false;
		}

		return array(
			'ID' => "{$select}_{$event->id}",
			'title' => $event->name,
			'start' => $event->start_time,
			'end' => isset( $event->end_time ) ? $event->end_time : '',
			'link' => 'https://www.facebook.com/events/' . $event->id . '/',
			'uid' => md5("{$select}_{$event->id}"),
		);
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
		add_post_meta($id, 'import_origin', $this->name);
		add_post_meta($id, 'selected', $selected);
		add_post_meta($id, 'selected_current', 0);
		add_post_meta($id, 'request_post', $post);
		$post['selected_current'] = $selected[0];

		// on the new request generate base url
		$this->prepare_request($post);

		$post['id'] = $id;

		$data = array('total_records' => 0, 'data' => array());
		$data_account = array();

		// on preview page, returned all data
		if ($this->preview == true) {
			foreach ($selected as $select) {

				$post['selected_current'] = $select;

				if (!isset($data_account[$select])) {
					$data_account[$select] = array('total_records' => 0, 'data' => array());
				}

				// on the new request generate base url
				$this->prepare_request($post);
				$d = $this->get_all_events($post);

				if (!$d) {
					error_log("Cannot get Events");
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

				if (isset($d->data) && !empty($d->data)) {

					foreach ($d->data as $key => $event) {

						error_log(print_r($event,true));

						$row = $this->prepare_row($select, $event, $post);
						if (!$row) {
							continue;
						}

						$data['data'][] = $row;
						$data['total_records'] += 1;

						$data_account[$select]['data'][] = $row;
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

			if (count($selected) > 1) {

				include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-accounts-table.php';
				$table = new \MEC_Advanced_Importer_Preview_Accounts_Table();
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

	public function get_all_events($post) {

		$url = $post['url'];

		try {
			add_post_meta($post['id'], 'request', "Send Request to Facebook", false);

			$response = wp_remote_get($url,array(
				'timeout' => $this->timeout
			));

			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Facebook", false);
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
			error_log($e->getMessage());
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

		if (!isset($event->place)) {
			return null;
		}

		if (!isset($event->place->name)) {
			return null;
		}

		$location = array
			(
			'name' => trim($event->place->name),
			'address' => '',
			'latitude' => '',
			'longitude' => '',
		);

		if (isset($event->place->location)) {

			$address = array();

			if (isset($event->place->location->country) && !empty($event->place->location->country)) {
				array_push($address, $event->place->location->country);
			}

			if (isset($event->place->location->state) && !empty($event->place->location->state)) {
				array_push($address, $event->place->location->state);
			}

			if (isset($event->place->location->city) && !empty($event->place->location->city)) {
				array_push($address, $event->place->location->city);
			}

			if (isset($event->place->location->street) && !empty($event->place->location->street)) {
				array_push($address, $event->place->location->street);
			}

			if (isset($event->place->location->zip) && !empty($event->place->location->zip)) {
				array_push($address, $event->place->location->zip);
			}

			if (isset($event->place->location->latitude) && !empty($event->place->location->latitude)) {
				$location['latitude'] = $event->place->location->latitude;
			}

			if (isset($event->place->location->longitude) && !empty($event->place->location->longitude)) {
				$location['longitude'] = $event->place->location->longitude;
			}

			if (count($address) > 0) {
				$location['address'] = implode(',', $address);
			}

		}

		return $this->main->save_location($location);
	}

	public function get_organizer($event) {

		if (!isset($event->owner)) {
			return null;
		}

		return $this->main->save_organizer(array
			(
				'name' => trim($event->owner->name),
				'tel' => '',
				'url' => '',
			));
	}

	public function saved_single_event($event, $category = array()) {

		// Event Start Date and Time
		$stime = isset($event->start_time) ? $event->start_time : '2020-12-01T00:00:00+0000';
		$zone = isset($event->timezone) ? $event->timezone : 'America/Bahia';

		$sdate = new \DateTime($stime, new \DateTimeZone($zone));
		$date_start = new \DateTime($sdate->format('Y-m-d G:i'));

		// Event End Date and Time
		$etime = isset($event->end_time) ? $event->end_time : '';
		$edate = new \DateTime($etime, new \DateTimeZone($zone));
		$date_end = new \DateTime($edate->format('Y-m-d G:i'));

		$location_id = $this->get_location($event);
		$organizer_id = $this->get_organizer($event);

		$start_date = $date_start->format('Y-m-d');
		$start_hour = $date_start->format('g');
		$start_minutes = $date_start->format('i');
		$start_ampm = $date_start->format('A');

		$end_timestamp = strtotime($etime);

		$end_date = $end_timestamp ? $date_end->format('Y-m-d') : $start_date;
		$end_hour = $end_timestamp ? $date_end->format('g') : 8;
		$end_minutes = $end_timestamp ? $date_end->format('i') : '00';
		$end_ampm = $end_timestamp ? $date_end->format('A') : 'PM';

		// Event Time Options
		$allday = 0;

		// Import Facebook Link as Event Link
		$read_more = 'https://www.facebook.com/events/' . $event->id . '/';

		// Import Facebook Link as More Info
		$more_info = 'https://www.facebook.com/events/' . $event->id . '/';
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;

		$args = array
			(
			'title' => $event->name,
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
			'repeat_status' => 0,
			'repeat_type' => '',
			'interval' => NULL,
			'finish' => $end_date,
			'year' => NULL,
			'month' => NULL,
			'day' => NULL,
			'week' => NULL,
			'weekday' => NULL,
			'weekdays' => NULL,
			'meta' => array
			(
				'mec_source' => 'facebook-calendar',
				'mec_advimp_facebook_event_id' => $event->id,
				'mec_allday' => $allday,
				'mec_read_more' => $read_more,
				'mec_more_info' => $more_info,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true, 'url' => null, 'title' => null);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='{$event->id}' AND `meta_key`='mec_advimp_facebook_event_id'", 'loadResult');
		if ($post_id) {
			$ret['is_new'] = false;
		}

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

		if (!has_post_thumbnail($post_id) and isset($event->cover)) {
			$photo = $this->main->get_web_page($event->cover->source);
			$file_name = md5($post_id) . '.' . $this->main->get_image_type_by_buffer($photo);

			$path = rtrim($wp_upload_dir['path'], DS . ' ') . DS . $file_name;
			$url = rtrim($wp_upload_dir['url'], '/ ') . '/' . $file_name;

			$file->write($path, $photo);
			$this->main->set_featured_image($url, $post_id);
		}

		$ret['url'] = get_permalink($post_id);
		$ret['title'] = $args['title'];

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
		$url = admin_url('admin.php?page=MEC-advimp&advimp_cmd=facebook_auth');
		$getall = admin_url('admin.php?page=MEC-advimp&advimp_cmd=facebook_getall');
		$add_to_auto_sync = admin_url('admin.php?page=MEC-advimp&advimp_cmd=mec_add_to_auto_sync');

		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'facebook.php';
		include $content;

	}

}