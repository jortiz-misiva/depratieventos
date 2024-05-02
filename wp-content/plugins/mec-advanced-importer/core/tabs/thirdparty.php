<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Thirdparty {
	public $name = 'Thirdparty';
	public $limit_fetch = 500;
	public $main;
	public $file;
	public $db;
	public $preview = true;
	public $category;
	public $category_selected;

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax_thirdparty_get_events', array($this, 'get_events'));

		add_action('wp_ajax__ajax_fetch_thirdparty_history', array($this, '_ajax_fetch_thirdparty_history_callback'));

	}

	function _ajax_fetch_thirdparty_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function process_download_single_event($event_id, $category = array()) {

		$ex = explode('_', $event_id);
		if (count($ex) < 2) {
			return false;
		}

		$event = null;
		$id = wp_insert_post(array(
			'post_title' => "EventID:{$ex[1]}",
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => "Download Single Event:{$ex[1]}",
		));
		if (count($category) > 0) {
			add_post_meta($id, 'category', $category);
		}

		switch ($ex[0]) {
		case 'eo':
			$event = eo_get_events(array('p' => $ex[1]));
			add_post_meta($id, 'import_origin', $this->name . ' EO');
			if ($event) {
				$location = eo_get_venue($ex[1]);

				if ($location) {
					$event->location_id = $this->get_location(
						array(
							'name' => eo_get_venue_name($location),
							'address' => eo_get_venue_address($location),
							'lat' => eo_get_venue_lat($location),
							'long' => eo_get_venue_lng($location),
						)
					);
				}

			}
			break;
		case 'myc':
			add_post_meta($id, 'import_origin', $this->name . ' MyCalendar');
			$event = mc_get_event($ex[1]);
			if (!$event) {
				return false;
			}

			$post = new \stdClass;
			$post->ID = $event->occur_event_id;
			$post->post_title = $event->event_title;
			$post->StartDate = $event->event_begin;
			$post->StartTime = $event->event_time;
			$post->EndDate = $event->event_end;
			$post->FinishTime = $event->event_endtime;
			$post->location_id = $this->get_location(
				array(
					'name' => $event->event_label,
					'address' => "{$event->event_country},{$event->event_street},{$event->event_region},{$event->event_city}",
					'lat' => $event->event_longitude,
					'long' => $event->event_latitude,
				)
			);
			$post->guid = get_permalink($event->event_post);
			$post->post_content = $event->event_desc;
			$post->image = $event->event_image;
			$event = $post;
			break;

		case 'eventum':
			add_post_meta($id, 'import_origin', $this->name . ' Eventum');
			$args = array(
				'post_type' => 'schedule',
				'order' => 'ASC',
				'posts_per_page' => -1,
				'p' => $ex[1],
			);

			$events = new \WP_Query($args);
			if ($events->post_count > 0) {

				$event = $events->post;

				$event->StartDate = date('Y-m-d');
				$event->StartTime = date('H:i:s');
				$event->EndDate = date('Y-m-d');
				$event->FinishTime = date('H:i:s');
				$event->location_id = null;
			}

			break;

		default:
			# code...
			break;
		}

		if ($event == null) {
			return false;
		}

		$event = is_array($event) && isset($event[0]) ? $event[0] : $event;

		$ret = $this->saved_single_event($event, $category);
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
		);
	}

	public function preload() {

	}

	public function prepare_row($select, $event, $post) {

		return array(
			'ID' => "{$select}_{$event->ID}",
			'title' => $event->post_title,
			'start' => "{$event->StartDate} {$event->StartTime}",
			'end' => "{$event->EndDate} {$event->FinishTime}",
			'link' => get_permalink($event->ID),
			'uid' => md5("{$select}_{$event->ID}"),
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
		add_post_meta($id, 'import_origin', 'thirdparty');
		add_post_meta($id, 'selected', $selected);
		add_post_meta($id, 'selected_current', 0);
		add_post_meta($id, 'request_post', $post);
		$post['selected_current'] = $selected[0];
		$select = $selected[0];

		$post['id'] = $id;

		$data = array('total_records' => 0, 'data' => array());
		$data_account = array();

		// on priview page, returned all data
		if ($this->preview == true) {

			if (!isset($data_account[$select])) {
				$data_account[$select] = array('total_records' => 0, 'data' => array());
			}

			$d = $this->get_all_events($post);
			add_post_meta($post['id'], 'request', 'finish', false);

			if (!$d) {
				return wp_send_json_error('Failed Request', 200);
			}

			if (count($d) == 0) {
				return wp_send_json_error('Failed Request', 200);
			}

			if ($post['importType'] == 'single') {
				$row = $this->prepare_row($select, $d, $post);
				if (!$row) {
					return wp_send_json_error('Failed Request', 200);
				}
				$data['data'][] = $row;
				$data['total_records'] += 1;
				$data['category'] = $this->category;
				$data['category_selected'] = $this->category_selected;

				$data_account[$select]['data'] = $row;
				$data_account[$select]['total_records'] += 1;
			}

			foreach ($d as $key => $event) {

				$row = $this->prepare_row($select, $event, $post);
				if (!$row) {
					continue;
				}

				$data['data'][] = $row;
				$data['total_records'] += 1;
				$data['category'] = $this->category;
				$data['category_selected'] = $this->category_selected;

				$data_account[$select]['data'][] = $row;
				$data_account[$select]['total_records'] += 1;

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

		$params = ['start' => date("Y-m-d H:i:s", strtotime("-1 year")), 'end' => date("Y-m-d H:i:s", strtotime("+1 year")), 'category' => null];

		if (isset($post['start_date']) || isset($post['end_date'])) {
			$params['start'] = !empty($post['start_date']) ? $post['start_date'] : null;
			$params['end'] = !empty($post['end_date']) ? $post['end_date'] : null;
		}

		$category = isset($post['categoryTop']) && !empty($post['categoryTop']) ? trim($post['categoryTop']) : null;
		if ($category == null) {
			$category = isset($post['categoryBottom']) && !empty($post['categoryBottom']) ? trim($post['categoryBottom']) : null;
		}

		switch ($post['selected_current']) {
		case 'eo':

			$query = [
				'event_start_after' => $params['start'],
				'event_end_before' => $params['end'],
			];

			if (!function_exists('eo_get_events')) {
				return false;
			}

			if ($category != null) {
				$query['event-category'] = $category;
				$this->category_selected = $category;
			}

			$category = get_categories(array(
				'post_type' => 'event',
				'taxonomy' => 'event-category',
			));

			foreach ($category as $k => $cat) {
				$this->category[$cat->slug] = $cat->name;
			}

			$evt = eo_get_events($query);

			add_post_meta($post['id'], 'request', "finish", false);

			return $evt;

			break;
		case 'myc':

			$query = array(
				'from' => $params['start'],
				'to' => $params['end'],
			);

			if ($category != null) {
				$query['category'] = $category;
				$this->category_selected = $category;
			}

			$all = my_calendar_get_events($query);

			$ret = [];
			foreach ($all as $k => $event) {
				$post = new \stdClass;
				$post->ID = $event->occur_event_id;
				$post->post_title = $event->event_title;
				$post->StartDate = $event->event_begin;
				$post->StartTime = $event->event_time;
				$post->EndDate = $event->event_end;
				$post->FinishTime = $event->event_endtime;

				$ret[] = $post;
			}

			global $wpdb;
			$category_db = $wpdb->get_results('SELECT * FROM ' . my_calendar_categories_table() . ' ORDER BY category_name ASC');

			foreach ($category_db as $key => $cat) {
				$this->category[$cat->category_term] = $cat->category_name;
			}

			return $ret;

			break;
		case 'eventum':

			$args = array(
				'post_type' => 'schedule',
				'order' => 'ASC',
				'posts_per_page' => -1,
			);

			$events = new \WP_Query($args);
			if ($events->post_count == 0) {
				return [];
			}

			$evts = $events->posts;

			foreach ($evts as $k => $event) {
				$evts[$k]->StartDate = date('Y-m-d');
				$evts[$k]->StartTime = date('H:i:s');
				$evts[$k]->EndDate = date('H:i:s');
				$evts[$k]->FinishTime = date('H:i:s');
			}

			return $evts;

			break;

		default:
			# code...
			break;
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

	public function get_location($data) {

		if (!isset($data['name'])) {
			return null;
		}

		$location = array
			(
			'name' => trim($data['name']),
			'address' => $data['address'],
			'latitude' => $data['lat'],
			'longitude' => $data['long'],
		);

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
		$stime = "{$event->StartDate} {$event->StartTime}";
		$sdate = new \DateTime($stime);
		$date_start = new \DateTime($sdate->format('Y-m-d G:i'));

		// Event End Date and Time
		$etime = "{$event->EndDate} {$event->FinishTime}";
		$edate = new \DateTime($etime);
		$date_end = new \DateTime($edate->format('Y-m-d G:i'));

		$location_id = $event->location_id;
		$organizer_id = null;

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

		$read_more = $event->guid;

		$more_info = $event->guid;
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;

		$args = array
			(
			'title' => $event->post_title,
			'content' => isset($event->post_content) ? $event->post_content : null,
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
				'mec_source' => 'thirdparty-calendar',
				'mec_advimp_thirdparty_event_id' => $event->ID,
				'mec_allday' => $allday,
				'mec_read_more' => $read_more,
				'mec_more_info' => $more_info,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true, 'url' => null, 'title' => null);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='{$event->ID}' AND `meta_key`='mec_advimp_thirdparty_event_id'", 'loadResult');
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

		$ret['url'] = get_permalink($post_id);
		$ret['title'] = $args['title'];

		return $ret;

	}

	public function content() {
		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'thirdparty.php';
		include $content;

	}

}
