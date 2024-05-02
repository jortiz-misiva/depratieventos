<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Eventbrite {
	public $name = 'Eventbrite';
	public $base_url = 'https://www.eventbriteapi.com/v3';
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

		add_action('wp_ajax_eventbrite_get_events', array($this, 'get_events'));
		add_action('wp_ajax_eventbrite_add_to_sync', array( \MEC_Advanced_Importer_Sync::class, 'add_to_auto_sync_by_ajax' ));
		add_action('wp_ajax__ajax_fetch_eventbrite_history', array($this, '_ajax_fetch_eventbrite_history_callback'));

	}

	public function _ajax_fetch_eventbrite_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function process_download_single_event($event_id, $category = array()) {
		$ex = explode('_', $event_id);
		$post = array('spec_event' => $ex[1], 'selected_current' => $ex[0], 'importType' => 'single');

		$id = wp_insert_post(array(
			'post_title' => "EventID:{$ex[1]}",
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => "Download Single Event:{$ex[1]}",
		));
		add_post_meta($id, 'import_origin', $this->name);
		if (count($category) > 0) {
			add_post_meta($id, 'category', $category);
		}
		$post['id'] = $id;

		$this->prepare_request($post);

		$response = wp_remote_get($post['url']);

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
		);
	}

	public function get_pageid($post) {

		$url = "https://graph.facebook.com/v8.0/me?access_token={$post['access_token']}";
		$response = wp_remote_get($url);

		if (isset($response->errors)) {

			return false;
		}

		$body = wp_remote_retrieve_body($response);
		return $body->id;

	}

	public function get_userid($post) {
		$url = "{$this->base_url}/users/me/?token={$post['access_token']}";
		try {
			$response = wp_remote_get($url);
			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Eventbrite", false);
			$body_response = json_decode($body);

			if (!isset($body_response->id)) {
				add_post_meta($post['id'], 'request', 'Failed Detect the AccountId', false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			if (empty($body_response->id)) {
				add_post_meta($post['id'], 'request', 'Failed Detect the AccountId, is empty or not exists', false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			return $body_response->id;

		} catch (Exception $e) {
			error_log(print_r($e, true));
		}

	}

	public function get_organizers($post) {
		$url = "{$this->base_url}/users/{$post['user_id']}/organizations/?token={$post['access_token']}";
		try {
			$response = wp_remote_get($url);
			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Eventbrite", false);
			$body_response = json_decode($body);

			if (!isset($body_response->organizations)) {
				add_post_meta($post['id'], 'request', 'Failed Detect the Organizer From Eventbrite', false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			if (count($body_response->organizations) == 0) {
				add_post_meta($post['id'], 'request', 'Failed Detect the Organization on Eventbrite, is empty or not exists', false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			return $body_response->organizations[0]->id;

		} catch (Exception $e) {
			error_log(print_r($e, true));
		}
	}

	public function prepare_request(&$post) {

		$val = isset($post['importTypeVal']) ? $post['importTypeVal'] : null;

		// $url = "https://www.eventbriteapi.com//v3/organizations/{$post['organizers']}/events?token={$this->token}";

		$post['access_token'] = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::active_account('eventbrite', null, $post['selected_current'], 'token');
		$req = 0;
		while ($req <= 3) {
			$post['user_id'] = $this->get_userid($post);
			if ($post['user_id']) {
				break;
				// return wp_send_json_error('Failed Request', 200);
			}

			$req += 1;
		}

		$req = 0;
		while ($req <= 3) {
			$post['organizers'] = $this->get_organizers($post);
			if ($post['organizers']) {
				break;
				// return wp_send_json_error('Failed Request', 200);
			}

			$req += 1;
		}

		$post['url'] = '';
		switch ($post['importType']) {
		case 'all':
			$post['url'] = "{$this->base_url}/organizations/{$post['organizers']}/events?token={$post['access_token']}";
			break;
		case 'single':

			if (isset($val)) {
				$post['url'] = "{$this->base_url}/events/{$val}/?expand=venue,organizer,category,subcategory,ticket_availability,checkout_settings";
			} else if (isset($post['spec_event'])) {
				$post['url'] = "{$this->base_url}/events/{$post['spec_event']}/?expand=venue,organizer,category,subcategory,ticket_availability,checkout_settings";
			}

			break;

		case 'single-series':

			if (isset($val)) {
				$post['url'] = "{$this->base_url}/series/{$val}/events/";
			} else if (isset($post['spec_event'])) {
				$post['url'] = "{$this->base_url}/events/{$post['spec_event']}";
			}

			break;
		// case 'page':
		// 	$base .= $this->get_pageid($post) . '/events';
		// 	break;

		// case 'group':
		// 	$base .= $val . '/events';
		// 	break;

		default:
			$post['url'] = "{$this->base_url}/organizations/{$post['organizers']}/events";
			break;
		}

		$post['url'] = add_query_arg(array(
				'token' => $post['access_token']
			),
			$post['url']
		);

		if (isset($post['start_date'])) {
			$d = new \DateTime($post['start_date']);
			$post['sdate'] = $d->getTimestamp();
		}

		if (isset($post['end_date'])) {
			$d = new \DateTime($post['end_date'] . ' 23:59:59');
			$post['edate'] = $d->getTimestamp();
		}

	}

	public function prepare_row($select, $event, $post) {

		$stime = isset($event->start->local) ? $event->start->local : '2020-10-01T19:00:00';
		$zone = isset($event->start->timezone) ? $event->start->timezone : 'America/Bahia';

		$sdate = new \DateTime($stime, new \DateTimeZone($zone));
		$unix_start = $sdate->getTimestamp();

		$etime = isset($event->end->local) ? $event->end->local : '2020-12-01T00:00:00+0000';
		$edate = new \DateTime($etime, new \DateTimeZone($zone));
		$unix_end = $edate->getTimestamp();

		if (isset($post['sdate']) && $unix_start < $post['sdate']) {
			return false;
		}

		if (isset($post['edate']) && $unix_end > $post['edate']) {
			return false;
		}

		return array(
			'ID' => "{$select}_{$event->id}",
			'title' => $event->name->text,
			'start' => $event->start->local,
			'end' => $event->end->local,
			'link' => $event->url,
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
		add_post_meta($id, 'import_origin', lcfirst($this->name));
		add_post_meta($id, 'selected', $selected);
		add_post_meta($id, 'selected_current', 0);
		add_post_meta($id, 'request_post', $post);
		$post['selected_current'] = $selected[0];

		$post['id'] = $id;

		$data = array('total_records' => 0, 'data' => array());
		$data_account = array();

		// on preview page, returned all data
		if ($this->preview == true) {
			foreach ($selected as $select) {

				$post['selected_current'] = $select;

				// on the new request generate base url
				$this->prepare_request($post);
				$d = $this->get_all_events($post);

				if (!$d) {
					continue;
				}

				if ($post['importType'] == 'single' && !isset( $d->events )) {
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

				if (isset($d->events) && !empty($d->events)) {

					foreach ($d->events as $key => $event) {

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

	public function add_query_args($url,$post){

		$url .= '&page_size='.$this->limit_fetch;

		if( 'single-series' === $post['importType'] ){

			if(isset($post['start_date']) && !empty($post['start_date'])){
				$url .= '&start_date.range_start='.$post['start_date'];
			}

			if(isset($post['end_date']) && !empty($post['end_date'])){
				$url .= '&start_date.range_end='.$post['end_date'];
			}
		}elseif( 'my' === $post['importType'] ){

			if(isset($post['start_date']) && !empty($post['start_date'])){
				$url .= '&start_date.range_start='.$post['start_date'];
			}

			if(isset($post['end_date']) && !empty($post['end_date'])){
				$url .= '&start_date.range_end='.$post['end_date'];
			}

			if(isset($post['status']) && !empty($post['status'])){
				$url .= '&status='.$post['status'];
			}
		}

		return $url;
	}

	public function get_all_events($post) {
		$post['url'] = $this->add_query_args($post['url'],$post);
		$url = $post['url'];

		try {
			add_post_meta($post['id'], 'request', "Send Request to Eventbrite", false);
			$response = wp_remote_get($url);

			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From Eventbrite", false);
			$body_response = json_decode($body);

			if( 'single' === $post['importType'] && isset( $body_response->is_series ) && $body_response->is_series ){

				$post['importType'] = 'single-series';
				$this->prepare_request( $post );
				$events =  $this->get_all_events( $post );

				return $events;
			}

			error_log(print_r($body_response, true));

			if ( !isset($body_response->events) && 'single' !== $post['importType'] ) {
				add_post_meta($post['id'], 'request', 'Error Failed', false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			if ($this->preview == true) {
				return $body_response;
			}

			return $this->handle_get_events($body_response, $post);

			// return $this->handle_get_events($body_response, $post);
		} catch (Exception $e) {
			error_log($e->getMessage());
			add_post_meta($post['id'], 'request', 'Request Failed,', false);
			add_post_meta($post['id'], 'request', "finish", false);
		}
	}

	public function handle_get_events($events, $post) {

		if (!isset($events->events)) {
			add_post_meta($post['id'], 'request', "finish", false);
			return false;
		}

		error_log(print_r($events, true));

		$count = isset($events->pagination) && isset($events->pagination->object_count) ? $events->pagination->object_count : 0;

		add_post_meta($post['id'], 'request', 'handle fetched events:' . $count, false);

		if ($count <= 0) {
			add_post_meta($post['id'], 'request', "finish", false);
			return true;
		}

		$ids = array();

		foreach ($events->events as $event) {
			$ret = $this->saved_single_event($event);
			if ($ret) {
				$link = '<a target="_blank" href="' . get_permalink($ret['post_id']) . '">' . $event->name->text . '</a>';
				$title_ins = $ret['is_new'] ? 'Add' : 'Update';
				add_post_meta($post['id'], 'request', "{$title_ins} Event:{$link}", false);
			}
		}

		// $next = isset($events->paging) && isset($events->paging->next) ? $events->paging->next : null;
		// if (!$next) {
		add_post_meta($post['id'], 'request', "finish", false);
		delete_post_meta($post['id'], 'request_next');
		// } else {
		// 	update_post_meta($post['id'], 'request_next', $next);
		// 	update_post_meta($post['id'], 'request', "next", false);
		// }

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
			'address' => '',
			'latitude' => '',
			'longitude' => '',
		);

		if (isset($event->venue->address)) {

			if (isset($event->venue->address->country) && !empty($event->venue->address->country)) {
				array_push($location, $event->venue->address->country);
			}

			if (isset($event->venue->address->state) && !empty($event->venue->address->state)) {
				array_push($location, $event->venue->address->state);
			}

			if (isset($event->venue->address->city) && !empty($event->venue->address->city)) {
				array_push($location, $event->venue->address->city);
			}

			if (isset($event->venue->address->street) && !empty($event->venue->address->street)) {
				array_push($location, $event->venue->address->street);
			}

			if (isset($event->venue->address->zip) && !empty($event->venue->address->zip)) {
				array_push($location, $event->venue->address->zip);
			}

			if (isset($event->venue->address->latitude) && !empty($event->venue->address->latitude)) {
				$location['latitude'] = $event->venue->address->latitude;
			}

			if (isset($event->venue->address->longitude) && !empty($event->venue->address->longitude)) {
				$location['longitude'] = $event->venue->address->longitude;
			}

			if (count($location) > 0) {
				$location['address'] = implode(',', $location);
			}

		}

		return $this->main->save_location($location);
	}

	public function get_organizer($event) {

		if (!isset($event->organizer)) {
			return null;
		}

		return $this->main->save_organizer(array
			(
				'name' => trim($event->organizer->name),
				'tel' => '',
				'url' => '',
			));
	}

	public function get_categories($event) {

		$category_ids = array();
		if (isset($event->category->name)) {

			$term = term_exists( $event->category->name, 'mec_category' );
			if( is_wp_error( $term ) || !$term ){

				$term = wp_insert_term( $event->category->name, 'mec_category' );
			}

			$category_ids[] = $term['term_id'];
		}

		if (isset($event->subcategory->name)) {

			$term = term_exists( $event->subcategory->name, 'mec_category' );
			if( is_wp_error( $term ) || !$term ){

				$term = wp_insert_term( $event->subcategory->name, 'mec_category' );
			}

			$category_ids[] = $term['term_id'];
		}

		return $category_ids;
	}

	public function saved_single_event($event, $category = array()) {

		// Event Start Date and Time
		$stime = isset($event->start->local) ? $event->start->local : '2020-10-01T19:00:00';
		$szone = isset($event->start->timezone) ? $event->start->timezone : 'America/Bahia';

		$sdate = new \DateTime($stime, new \DateTimeZone($szone));
		$date_start = new \DateTime($sdate->format('Y-m-d G:i'));

		// Event End Date and Time
		$etime = isset($event->end->local) ? $event->end->local : '';
		$ezone = isset($event->end->timezone) ? $event->end->timezone : 'America/Bahia';
		$edate = new \DateTime($etime, new \DateTimeZone($ezone));
		$date_end = new \DateTime($edate->format('Y-m-d G:i'));

		$location_id = $this->get_location($event);
		$organizer_id = $this->get_organizer($event);
		$category_ids = $this->get_categories($event);

		$category = array_merge(
			$category_ids,
			$category
		);

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
		$read_more = isset($event->url) ? $event->url : '';

		// Import Facebook Link as More Info
		$more_info = isset($event->url) ? $event->url : '';
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;
		$cost = isset( $event->ticket_availability->minimum_ticket_price->major_value ) ? $event->ticket_availability->minimum_ticket_price->major_value : '';

		$summary = isset($event->summary) ? $event->summary : null;
		$args = array
			(
			'title' => $event->name->text,
			'content' => isset($event->description->text) ? $event->description->text : $summary,
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
				'mec_source' => 'eventribe-calendar',
				// 'mec_facebook_page_id' => $fb_page_id,
				'mec_advimp_eventribte_event_id' => $event->id,
				'mec_allday' => $allday,
				'mec_read_more' => $read_more,
				'mec_more_info' => $more_info,
				'mec_cost' => $cost,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='{$event->id}' AND `meta_key`='mec_advimp_eventribte_event_id'", 'loadResult');
		if ($post_id) {
			$ret['is_new'] = false;
		}

		// Insert the event into MEC
		$post_id = $this->main->save_event($args, $post_id);

		$ret['post_id'] = $post_id;

		// Set location to the post
		if ($location_id) {
			wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
		}

		// Set categories to the post
		if (count($category)) {
			foreach ($category as $category_id) {
				wp_set_object_terms($post_id, (int) $category_id, 'mec_category', true);
			}
		}

		if (!has_post_thumbnail($post_id) and isset($event->logo) && isset($event->logo->url)) {
			$photo = $this->main->get_web_page($event->logo->url);
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

		$getall = admin_url('admin.php?page=MEC-advimp&advimp_cmd=facebook_getall');
		$add_to_auto_sync = admin_url('admin.php?page=MEC-advimp&advimp_cmd=mec_add_to_auto_sync');
		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'eventbrite.php';
		include $content;

	}

}
