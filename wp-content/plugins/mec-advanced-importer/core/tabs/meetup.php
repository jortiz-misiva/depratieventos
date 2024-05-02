<?php
namespace MEC_Advanced_Importer\Core\Tabs;

use DateInterval;

class Meetup {
	public $name = 'Meetup';
	public $base_url = 'https://api.meetup.com/gql';
	public $option_name = 'advimp_user_token_options';
	public $batch = array('my', 'page', 'group');
	public $limit_fetch = 500;
	public $main;
	public $file;
	public $db;
	public $preview = true;

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax_meetup_check_auth', array($this, 'check_auth'));

		add_action('wp_ajax_meetup_get_events', array($this, 'get_events'));
		add_action('wp_ajax_meetup_add_to_sync', array( \MEC_Advanced_Importer_Sync::class, 'add_to_auto_sync_by_ajax' ));

		add_action('wp_ajax__ajax_fetch_meetup_history', array($this, '_ajax_fetch_meetup_history_callback'));

	}

	public function _ajax_fetch_meetup_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function process_download_single_event($event_id, $category = array()) {

		$ex = explode('_', $event_id);
		$post = array('spec_event' => $ex[1], 'selected_current' => $ex[0], 'group_id' => $ex[2], 'importType' => 'single');
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

		$url = $post['url'];
		$data = is_array( $post['meetup_request_body'] ) ? json_encode( $post['meetup_request_body'] ) : $post['meetup_request_body'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $post['access_token'],
				'Content-Type'  => 'application/json',
			),
			'body' => $data,
		);

		$response = wp_remote_post($url, $args);

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

		if ($cmd == 'meetup_auth') {
			$this->authorize_user();
		}

		if ($cmd == 'meetup_callback') {
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

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('meetup', 'config', $id);

		$key = isset($s['key']) ? $s['key'] : null;
		$secret = isset($s['secret']) ? $s['secret'] : null;

		if (!$key || !$secret) {
			wp_die(__('Please insert Meetup App ID and Secret.', 'mec-advanced-importer'));
			return false;
		}

		$redirect_url = admin_url('admin.php') . '?page=MEC-advimp&advimp_cmd=meetup_callback';

		$dialog_url = add_query_arg(
			array(
				'display' => 'popup',
				'client_id' => $key,
				'redirect_uri' => urlencode( $redirect_url ),
				'response_type' => 'code',
			),
			'https://secure.meetup.com/oauth2/authorize'
		);

		update_option('mec_advimp_meetup_current_request', $id);

		header("Location: " . $dialog_url);
		wp_redirect($dialog_url);
		exit;
	}

	/*
		* Authorize facebook user on callback to get access token
	*/
	function authorize_callback() {

		$code = isset($_GET['code']) ? $_GET['code'] : null;
		if (!$code) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'APP-ID or APP-Secret is null';
			wp_die(__('Response Failed, please try again.', 'mec-advanced-importer'));
		}

		$id = get_option('mec_advimp_meetup_current_request', null);
		if ($id == null) {
			wp_die(__('Please Select account.', 'mec-advanced-importer'));
			return false;
		}

		$s = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('meetup', 'config', $id);

		$key = isset($s['key']) ? $s['key'] : null;
		$secret = isset($s['secret']) ? $s['secret'] : null;
		$title = $s['title'];

		if (!$key || !$secret) {
			wp_die(__('Please insert Meetup App ID and Secret.', 'mec-advanced-importer'));
			return false;
		}

		// on the urlencode error failed the url, then in the post request ignore urlencode
		$redirect_url = admin_url('admin.php') . '?page=MEC-advimp&advimp_cmd=meetup_callback';

		$args = array(
			'method' => 'POST',
			'headers' => array(
				'Content-type: application/x-www-form-urlencoded',
			),
			'sslverify' => false,
			'body' => array(
				'grant_type' => 'authorization_code',
				'client_id' => $key,
				'client_secret' => $secret,
				'code' => $code,
				'redirect_uri' => $redirect_url,
			),
		);

		$response = wp_remote_post('https://secure.meetup.com/oauth2/access', $args);
		$body = wp_remote_retrieve_body($response);

		if (!$body || empty($body)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'Network Error';
			wp_die($response);
		}

		$b = json_decode($body);
		error_log(print_r($b, true));

		if (!isset($b->access_token)) {
			$user_token_options['authorize_status'] = 0;
			$user_token_options['error_message'] = 'Network Error';
			wp_die('Access Token not found!');
		}

		$access_token = $b->access_token;
		$user_token_options['access_token'] = sanitize_text_field($access_token);
		$user_token_options['result'] = $b;
		$user_token_options['status'] = true;
		$user_token_options['request_at'] = time();
		$user_token_options['expire_at'] = time() + $b->expires_in;
		$user_token_options['title'] = $title;

		$cur = get_option('mec_advimp_auth_meetup', array());
		$cur[$id] = $user_token_options;
		update_option('mec_advimp_auth_meetup', $cur);

		?>
		<script type="text/javascript">
			window.close();
		</script>
		<?php

	}

	public function check_auth() {

		$id = isset($_POST['authid']) ? $_POST['authid'] : null;

		$option = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('meetup', 'auth', $id);

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

	public function prepare_request(&$post) {

		$val = isset($post['importTypeVal']) ? $post['importTypeVal'] : null;

		$post['access_token'] = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('meetup', 'auth', $post['selected_current'], 'access_token');

		$post['meetup_request_body'] = false;
		switch ($post['importType']) {

			case 'single':

				$post['meetup_request_body'] = array(
					'query' => 'query ($eventId: ID) {
						event(id: $eventId) {
							id
							title
							description
							eventType
							eventUrl
							host {
							id
							email
							name
							memberPhoto {
								baseUrl
							}
							}
							hosts {
							id
							email
							name
							memberPhoto {
								baseUrl
							}
							}
							group{
							id
							name
							urlname
							}
							venue {
							name
							address
							city
							state
							lat
							lng
							}
							onlineVenue {
							type
							url
							}
							status
							dateTime
							duration
							timeStatus
							timezone
							endTime
							createdAt
							priceTier
							fees {
							processingFee {
								type
								amount
							}
							serviceFee {
								type
								amount
							}
							tax {
								type
								amount
							}
							}
							taxType
							isOnline
							imageUrl
							maxTickets
						}
						}',
					'variables' => array(
						'eventId' => $val ? $val : $post['spec_event'],
					)
				);

				break;

			case 'group':

				//, $cursor: String!
				//, after: $cursor

				$post['meetup_request_body'] = array(
					'query' => 'query ($urlname: String!, $itemsNum: Int!) {
						groupByUrlname(urlname: $urlname) {
							unifiedEvents(input: {first: $itemsNum}) {
								count
								pageInfo {
									endCursor
								}
								edges {
									node {
										id
										title
										dateTime
										timezone
										duration
										status
									}
								}
							}
						}
					}',
					'variables' => array(
						'urlname' => $val ? $val : $post['spec_event'],
						'itemsNum' => 500,
						// 'cursor' => null,
					)
				);
				break;
			case 'all':
			default:
				//, $cursor: String!
				//, after: $cursor

				$post['meetup_request_body'] = array(
					'query' => 'query ($itemsNum: Int!) {
						self {
							hostedEvents(input: {first: $itemsNum}) {
								count
								pageInfo {
									endCursor
								}
								edges {
								node {
									id
									title
									dateTime
									timezone
									duration
									status
								}
							}
							}
						}
					}',
					'variables' => array(
						'itemsNum' => 500,
						// 'cursor' => null,
					)
				);

				break;
		}

		$post['url'] = "{$this->base_url}";


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

		if( !$event ){

			return false;
		}

		$zone = $event->timezone;

		$sdate = new \DateTime($event->dateTime, new \DateTimeZone($zone));
		$sdate->setTimezone(wp_timezone());
		$start_datetime = $sdate->format('Y-m-d H:i');
		$start_date = $sdate->format('Y-m-d');

		$interval = new DateInterval($event->duration);
		$sdate->add( $interval );
		$end_datetime = $sdate->format('Y-m-d H:i');
		$end_date = $sdate->format('Y-m-d');

		$event_id = str_replace('!chp', '', $event->id);

		if( $post['status'] !== 'all' && strtolower( $event->status ) !== $post['status'] ){

			return false;
		}

		if( !( $post['sdate'] <= strtotime( $start_date ) && $post['edate'] >= strtotime( $end_date ) ) ){

			return false;
		}

		return array(
			'ID' => "{$select}_{$event_id}_{$event->group->urlname}",
			'title' => $event->title,
			'start' => $start_datetime,
			'end' => $end_datetime,
			'link' => $event->eventUrl,
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
		error_log($date->format('Y-m-d H:i:s'));
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
		add_post_meta($id, 'import_origin', lcfirst($this->name));
		add_post_meta($id, 'selected', $selected);
		add_post_meta($id, 'selected_current', 0);
		add_post_meta($id, 'request_post', $post);
		$post['selected_current'] = $selected[0];

		// on the new request generate base url
		$this->prepare_request($post);

		$post['id'] = $id;

		$data = array('total_records' => 0, 'data' => array());
		$data_account = array();

		// on priview page, returned all data
		if ($this->preview == true) {
			foreach ($selected as $select) {

				$post['selected_current'] = $select;

				// on the new request generate base url
				$this->prepare_request($post);
				$d = $this->get_all_events($post);

				if (!$d) {
					continue;
				}

				if ($post['importType'] == 'single') {
					$single_d = isset( $d->data->event ) ? $d->data->event : $d;
					$row = $this->prepare_row($select, $single_d, $post);
					if (!$row) {
						continue;
					}
					$data['total_records'] += 1;
					$data['data'][] = $row;

					$data_account[$select]['data'] = $row;
					$data_account[$select]['total_records'] += 1;

					continue;
				}

				if (isset($d->data) && !empty($d->data)) {

					foreach ($d->data as $key => $event) {
						$event = isset( $event->event ) ? $event->event : $event;

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

			// include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-table.php';
			// $table = new \MEC_Advanced_Importer_Preview_Table();
			// $table->page_section = lcfirst($this->name);
			// $table->data = $data;
			// $table->params = $post;
			// $table->prepare_items();

			// ob_start();
			// $table->display();
			// $display = ob_get_clean();

			// return wp_send_json_success(array(
			// 	'next' => false,
			// 	'post_id' => $post['id'],
			// 	'table' => $display,
			// ), 200);
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
		$data = is_array( $post['meetup_request_body'] ) ? json_encode( $post['meetup_request_body'] ) : $post['meetup_request_body'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $post['access_token'],
				'Content-Type'  => 'application/json',
			),
			'body' => $data,
		);

		try {
			add_post_meta($post['id'], 'request', "Send Request to {$this->name}", false);

			$response = wp_remote_post($url, $args);

			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Meetup", false);
			$body_response = json_decode($body);

			switch ($post['importType']) {

				case 'single':

					break;
				case 'group':

					$events = array();
					$events_data = isset( $body_response->data->groupByUrlname->unifiedEvents ) ? $body_response->data->groupByUrlname->unifiedEvents : array();

					if( $events_data->count ){

						foreach( $events_data->edges as $event ){

							$events[] = $event->node;
						}
					}
					// print_r($events);
					$body_response = new \stdClass();
					$body_response->data = $events;

					break;
				case 'all':
				default:

					$events = array();
					$events_data = isset( $body_response->data->self->hostedEvents ) ? $body_response->data->self->hostedEvents : array();

					if( $events_data->count ){

						foreach( $events_data->edges as $event ){

							$events[] = $event->node;
						}
					}
					// print_r($events);
					$body_response = new \stdClass();
					$body_response->data = $events;

					break;
			}

			// error_log(print_r($body_response, true));

			if (isset($body_response->errors)) {
				add_post_meta($post['id'], 'request', 'Error!!!' . $body_response->errors[0]->message, false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			if ($this->preview == true) {
				return $body_response;
			}

			return $this->handle_get_events($body_response, $post);
		} catch (\Exception $e) {
			error_log($e->getMessage());
			add_post_meta($post['id'], 'request', 'Request Failed,', false);
			add_post_meta($post['id'], 'request', "finish", false);
		}
	}

	public function handle_get_events($events, $post) {

		// error_log(print_r($events,true));

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

			error_log(print_r($selected, true));
			error_log("Selected===*{$current}*");

			// no any next account
			// 1. only selected one account
			// 2. many account and finished any selected account request
			if (count($selected) == 1 || count($selected) == $current + 1) {
				add_post_meta($post['id'], 'request', "finish", false);
				delete_post_meta($post['id'], 'request_next');
				error_log("finish");
				return true;
			}

			// saved current request selected account
			update_post_meta($post['id'], 'selected_current', $current + 1);

			// get original base request, for generate next request by new account
			$base_request = get_post_meta($post['id'], 'request_post', true);

			$old = $selected[$current];
			$new = $selected[$current + 1];
			error_log("*********************old:{$old}, new:{$new}");

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

		if (!isset($event->venue)) {
			return null;
		}

		if (!isset($event->venue->name)) {
			return null;
		}

		$location = array(
			'name' => trim($event->venue->name),
			'address' => $event->venue->address,
			'latitude' => $event->venue->lat,
			'longitude' => $event->venue->lon,
		);

		$address = array();

		if (isset($event->venue->country) && !empty($event->venue->country)) {
			array_push($address, $event->venue->country);
		}

		if (isset($event->venue->city) && !empty($event->venue->city)) {
			array_push($address, $event->venue->city);
		}

		if (isset($event->venue->localized_country_name) && !empty($event->venue->localized_country_name)) {
			array_push($address, $event->venue->localized_country_name);
		}

		if (isset($event->venue->lat) && !empty($event->venue->lat)) {
			$location['latitude'] = $event->venue->lat;
		}

		if (isset($event->venue->lon) && !empty($event->venue->lon)) {
			$location['longitude'] = $event->venue->lon;
		}

		if (count($address) > 0) {
			$location['address'] = implode(',', $address);
		}

		return $this->main->save_location($location);
	}

	public function get_organizer($event) {

		if (!isset($event->group)) {
			return null;
		}

		return $this->main->save_organizer(array(
			'name' => trim($event->group->name),
			'tel' => '',
			'url' => '',
		));
	}

	public function get_speakers($event) {

		if (!isset($event->hosts)) {
			return null;
		}

		$speaker_ids = array();

		foreach( $event->hosts as $host ){

			$speaker_ids[] = $this->main->save_speaker(array(
				'name' => trim($host->name),
				'email' => $host->email,
			));
		}

		return $speaker_ids;
	}

	public function saved_single_event($event, $category = array()) {

		$event = isset( $event->data->event ) ? $event->data->event : $event;
		$event_id = str_replace('!chp', '', $event->id);
		$s = $event->dateTime;

		$zone = $event->timezone;

		$sdate = new \DateTime($s, new \DateTimeZone($zone));
		$sdate->setTimezone(wp_timezone());

		// Event End Date and Time
		$interval = new DateInterval($event->duration);
		$edate = new \DateTime($sdate->format('Y-m-d H:i:s'));
		$edate->add( $interval );
		$etime = $edate->format('Y-m-d H:i:s');

		$location_id = $this->get_location($event);
		$organizer_id = $this->get_organizer($event);
		$speaker_ids = $this->get_speakers($event);

		$start_date = $sdate->format('Y-m-d');
		$start_hour = $sdate->format('g');
		$start_minutes = $sdate->format('i');
		$start_ampm = $sdate->format('A');

		$end_timestamp = strtotime($etime);

		$end_date = $end_timestamp ? $edate->format('Y-m-d') : $start_date;
		$end_hour = $end_timestamp ? $edate->format('g') : 8;
		$end_minutes = $end_timestamp ? $edate->format('i') : '00';
		$end_ampm = $end_timestamp ? $edate->format('A') : 'PM';

		// Event Time Options
		$allday = 0;

		// Import Facebook Link as Event Link
		$read_more = $event->eventUrl;

		// Import Facebook Link as More Info
		$more_info = $event->eventUrl;
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;

		$args = array(
			'title' => $event->title,
			'content' => isset($event->description) ? $event->description : null,
			'location_id' => $location_id,
			'organizer_id' => $organizer_id,
			'date' => array(
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
			'meta' => array(
				'mec_source' => 'meetup-calendar',
				// 'mec_facebook_page_id' => $fb_page_id,
				'mec_advimp_meetup_event_id' => $event_id,
				'mec_allday' => $allday,
				'mec_read_more' => $read_more,
				'mec_more_info' => $more_info,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='{$event_id}' AND `meta_key`='mec_advimp_meetup_event_id'", 'loadResult');
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

		if (count($speaker_ids) && get_taxonomy('mec_speaker')) {
			foreach ($speaker_ids as $speaker_id) {
				wp_set_object_terms($post_id, (int) $speaker_id, 'mec_speaker', true);
			}
		}

		$ret['post_id'] = $post_id;

		// Set location to the post
		if ($location_id) {
			wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
		}

		if (!has_post_thumbnail($post_id) and isset($event->imageUrl)) {
			$photo = $this->main->get_web_page($event->imageUrl);
			$format = end( explode('.',$event->imageUrl) );
			$file_name = md5($post_id) . '.' . $format;

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

		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'meetup.php';
		include $content;

	}

}