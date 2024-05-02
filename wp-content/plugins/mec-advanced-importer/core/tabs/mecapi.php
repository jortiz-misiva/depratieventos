<?php
namespace MEC_Advanced_Importer\Core\Tabs;

class Mecapi {
	public $name = 'Mecapi';
	public $limit_fetch = 500;
	public $main;
	public $file;
	public $db;
	public $preview = true;

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax_mecapi_get_events', array($this, 'get_events'));
		add_action('wp_ajax_mecapi_add_to_sync', array( \MEC_Advanced_Importer_Sync::class, 'add_to_auto_sync_by_ajax' ));

		add_action('wp_ajax__ajax_fetch_mecapi_history', array($this, '_ajax_fetch_mecapi_history_callback'));

	}

	public function _ajax_fetch_mecapi_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function process_download_single_event($event_id, $category = array()) {
		$ex = explode('_', $event_id);
		$post = array('importTypeVal' => $ex[1], 'selected_current' => $ex[0], 'importType' => 'single');
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

		$response = wp_remote_get($post['url'], array('sslverify' => false));

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
			'batch' => null,
			'limit_fetch' => $this->limit_fetch,

		);
	}

	public function get_pageid($post) {

		$url = "{$this->base_url}/me?access_token={$post['access_token']}";
		$response = wp_remote_get($url, array('sslverify' => false));

		if (isset($response->errors)) {

			return false;
		}

		$body = wp_remote_retrieve_body($response);
		return $body->id;

	}

	public function prepare_request(&$post) {

		$val = isset($post['importTypeVal']) ? $post['importTypeVal'] : null;

		$config = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::settings('mecapi', 'config', $post['selected_current']);

		switch ($post['importType']) {
		case 'all':
			$post['url'] = "{$config['address']}/wp-json/mecapi/v1/events?access_token={$config['token']}";
			break;
		case 'single':

			$post['url'] = "{$config['address']}/wp-json/mecapi/v1/event/{$val}/?access_token={$config['token']}";
			break;

		default:
			$post['url'] = "{$config['address']}/wp-json/mecapi/v1/events?access_token={$config['token']}";
			break;
		}

		if (isset($post['start_date'])) {
			$d = new \DateTime($post['start_date']);
			$post['sdate'] = $d->getTimestamp();
		}

		if (isset($post['end_date'])) {
			$d = new \DateTime($post['end_date']);
			$post['edate'] = $d->getTimestamp();
		}

		error_log(print_r($post, true));

	}

	public function prepare_row($select, $event, $post) {

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
		// $sdate = new \DateTime($stime, new \DateTimeZone($zone));
		// $unix_start = $sdate->getTimestamp();

		// $etime = isset($event->end_time) ? $event->end_time : '2020-12-01T00:00:00+0000';
		// $edate = new \DateTime($etime, new \DateTimeZone($zone));
		// $unix_end = $edate->getTimestamp();

		// if (isset($post['sdate']) && $unix_start < $post['sdate']) {
		// 	return false;
		// }

		// if (isset($post['edate']) && $unix_end > $post['edate']) {
		// 	return false;
		// }

		return array(
			'ID' => "{$select}_{$event->ID}",
			'title' => $event->title,
			'start' => "{$event->meta->mec_start_date} {$event->meta->mec_start_time_hour}:{$event->meta->mec_start_time_minutes} {$event->meta->mec_start_time_ampm}",
			'end' => "{$event->meta->mec_end_date} {$event->meta->mec_end_time_hour}:{$event->meta->mec_end_time_minutes} {$event->meta->mec_end_time_ampm}",
			'link' => $event->permalink,
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
		add_post_meta($id, 'import_origin', 'mecapi');
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

				if (!isset($data_account[$select])) {
					$data_account[$select] = array('total_records' => 0, 'data' => array());
				}

				// on the new request generate base url
				$this->prepare_request($post);
				$d = $this->get_all_events($post);

				// error_log(print_r($d, true));

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

				if (count($d) > 0) {

					foreach ($d as $key => $event) {

						$row = $this->prepare_row($select, $event, $post);
						if (!$row) {
							error_log('cannot parse');
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

			// error_log(print_r($data,true));

			// add_post_meta($post['id'], 'request', "finish", false);

			// if ($data['total_records'] == 0) {
			// 	return wp_send_json_error('Failed Request', 200);
			// }

			// if (!class_exists('WP_List_Table')) {
			// 	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			// }

			// if (count($selected) > 1) {

			// 	include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-accounts-table.php';
			// 	$table = new \MEC_Advanced_Importer_Preview_Accounts_Table();
			// 	$table->data = $data_account;

			// } else {
			// 	include MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'preview-table.php';
			// 	$table = new \MEC_Advanced_Importer_Preview_Table();
			// 	$table->data = $data;
			// 	// error_log(print_r($data,true));
			// }

			// $table->page_section = strtolower($this->name);
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

		try {
			add_post_meta($post['id'], 'request', "Send Request to MEC Site", false);

			$args = array(
				'sslverify' => false,
			);

			$response = wp_remote_get($url, $args);

			if (isset($response->errors)) {
				add_post_meta($post['id'], 'request', json_encode($response->errors), false);
				add_post_meta($post['id'], 'request', 'finish', false);
				return false;
			}

			$body = wp_remote_retrieve_body($response);
			add_post_meta($post['id'], 'request', "Get Response From {$this->name}", false);
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

		error_log(print_r($events, true));
		return;

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

		@ini_set('memory_limit', '1024M');
		@ini_set('max_execution_time', 300);
		$wp_upload_dir = wp_upload_dir();
		$file = $this->file;

		/**
		 * @var MEC_db
		 */
		$db = $this->db;

		/**
		 * @var MEC_main
		 */
		$main = $this->main;

		$feed_event_id = (int) $event->ID;

		// Event Data
		$meta = $event->meta;
		$mec = $event->mec;

		// Event location
		$location = (isset($event->locations) ? $event->locations->item[0] : NULL);
		$location_id = ($location and isset($location->name)) ? $main->save_location(array
			(
				'name' => trim((string) $location->name),
				'address' => (string) $location->address,
				'latitude' => (string) $location->latitude,
				'longitude' => (string) $location->longitude,
				'thumbnail' => (string) $location->thumbnail,
			)) : 1;

		// Event Organizer
		$organizer = (isset($event->organizers) ? $event->organizers->item[0] : NULL);
		$organizer_id = ($organizer and isset($organizer->name)) ? $main->save_organizer(array
			(
				'name' => trim((string) $organizer->name),
				'email' => (string) $organizer->email,
				'tel' => (string) $organizer->tel,
				'url' => (string) $organizer->url,
				'thumbnail' => (string) $organizer->thumbnail,
			)) : 1;

		// Event Tags
		$tag_ids = array();
		if (isset($event->tags)) {
			foreach ($event->tags->children() as $tag) {
				$tag_id = $main->save_tag(array
					(
						'name' => trim((string) $tag->name),
					));

				if ($tag_id) {
					$tag_ids[] = $tag_id;
				}

			}
		}

		// Event Labels
		$label_ids = array();
		if (isset($event->labels)) {
			foreach ($event->labels->children() as $label) {
				$label_id = $main->save_label(array
					(
						'name' => trim((string) $label->name),
						'color' => (string) $label->color,
					));

				if ($label_id) {
					$label_ids[] = $label_id;
				}

			}
		}

		// Start
		$start_date = (string) $meta->mec_date->start->date;
		$start_hour = (int) $meta->mec_date->start->hour;
		$start_minutes = (int) $meta->mec_date->start->minutes;
		$start_ampm = (string) $meta->mec_date->start->ampm;

		// End
		$end_date = (string) $meta->mec_date->end->date;
		$end_hour = (int) $meta->mec_date->end->hour;
		$end_minutes = (int) $meta->mec_date->end->minutes;
		$end_ampm = (string) $meta->mec_date->end->ampm;

		// Time Options
		$allday = (string) $meta->mec_date->allday;
		$time_comment = (string) $meta->mec_date->comment;
		$hide_time = (string) $meta->mec_date->hide_time;
		$hide_end_time = (string) $meta->mec_date->hide_end_time;

		// Repeat Options
		$repeat_status = (int) $meta->mec_repeat_status;
		$repeat_type = (string) $meta->mec_repeat_type;
		$repeat_interval = (int) $meta->mec_repeat_interval;
		$finish = (string) $mec->end;
		$year = (string) $mec->year;
		$month = (string) $mec->month;
		$day = (string) $mec->day;
		$week = (string) $mec->week;
		$weekday = (string) $mec->weekday;
		$weekdays = (string) $mec->weekdays;
		$days = (string) $mec->days;
		$not_in_days = (string) $mec->not_in_days;

		$additional_organizer_ids = array();
		if (isset($meta->mec_additional_organizer_ids) && method_exists($meta->mec_additional_organizer_ids,'children')) {
			foreach ($meta->mec_additional_organizer_ids->children() as $o) {
				$additional_organizer_ids[] = (int) $o;
			}
		}

		$hourly_schedules = array();
		if (isset($meta->mec_hourly_schedules) && method_exists($meta->mec_hourly_schedules,'children')) {
			foreach ($meta->mec_hourly_schedules->children() as $s) {
				$hourly_schedules[] = array
					(
					'from' => (string) $s->from,
					'to' => (string) $s->to,
					'title' => (string) $s->title,
					'description' => (string) $s->description,
				);
			}
		}

		$tickets = array();
		if (isset($meta->mec_tickets) && method_exists($meta->mec_tickets,'children')) {
			foreach ($meta->mec_tickets->children() as $t) {
				$tickets[] = array
					(
					'name' => (string) $t->name,
					'description' => (string) $t->description,
					'price' => (string) $t->price,
					'price_label' => (string) $t->price_label,
					'limit' => (int) $t->limit,
					'unlimited' => (int) $t->unlimited,
				);
			}
		}

		$fees = array();
		if (isset($meta->mec_fees) && method_exists($meta->mec_fees,'children')) {
			foreach ($meta->mec_fees->children() as $f) {
				if ($f->getName() !== 'item') {
					continue;
				}

				$fees[] = array
					(
					'title' => (string) $f->title,
					'amount' => (string) $f->amount,
					'type' => (string) $f->type,
				);
			}
		}

		$reg_fields = array();
		if (isset($meta->mec_reg_fields) && method_exists($meta->mec_reg_fields,'children')) {
			foreach ($meta->mec_reg_fields->children() as $r) {
				if ($r->getName() !== 'item') {
					continue;
				}

				$options = array();
				foreach ($r->options->children() as $o) {
					$options[] = (string) $o->label;
				}

				$reg_fields[] = array
					(
					'mandatory' => (int) $r->mandatory,
					'type' => (string) $r->type,
					'label' => (string) $r->label,
					'options' => $options,
				);
			}
		}

		$args = array
			(
			'title' => (string) $event->title,
			'content' => (string) $event->content,
			'status' => (string) ($event->post ? $event->post->post_status : 'publish'),
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
				'comment' => $time_comment,
				'hide_time' => $hide_time,
				'hide_end_time' => $hide_end_time,
			),
			'start' => $start_date,
			'start_time_hour' => $start_hour,
			'start_time_minutes' => $start_minutes,
			'start_time_ampm' => $start_ampm,
			'end' => $end_date,
			'end_time_hour' => $end_hour,
			'end_time_minutes' => $end_minutes,
			'end_time_ampm' => $end_ampm,
			'repeat_status' => $repeat_status,
			'repeat_type' => $repeat_type,
			'interval' => $repeat_interval,
			'finish' => $finish,
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'week' => $week,
			'weekday' => $weekday,
			'weekdays' => $weekdays,
			'days' => $days,
			'not_in_days' => $not_in_days,
			'meta' => array
			(
				'mec_source' => 'mec-calendar',
				'mec_feed_event_id' => $feed_event_id,
				'mec_dont_show_map' => (int) $meta->mec_dont_show_map,
				'mec_color' => (string) $meta->mec_color,
				'mec_read_more' => (string) $meta->mec_read_more,
				'mec_more_info' => (string) $meta->mec_more_info,
				'mec_more_info_title' => (string) $meta->mec_more_info_title,
				'mec_more_info_target' => (string) $meta->mec_more_info_target,
				'mec_cost' => (string) $meta->mec_cost,
				'mec_additional_organizer_ids' => $additional_organizer_ids,
				'mec_repeat' => array
				(
					'status' => isset($meta->mec_repeat->status) ? (int) $meta->mec_repeat->status : 0,
					'type' => (string) $meta->mec_repeat->type,
					'interval' => (int) $meta->mec_repeat->interval,
					'end' => (string) $meta->mec_repeat->end,
					'end_at_date' => (string) $meta->mec_repeat->end_at_date,
					'end_at_occurrences' => (string) $meta->mec_repeat->end_at_occurrences,
				),
				'mec_allday' => $allday,
				'mec_hide_time' => $hide_time,
				'mec_hide_end_time' => $hide_end_time,
				'mec_comment' => $time_comment,
				'mec_repeat_end' => (string) $meta->mec_repeat_end,
				'mec_repeat_end_at_occurrences' => (string) $meta->mec_repeat_end_at_occurrences,
				'mec_repeat_end_at_date' => (string) $meta->mec_repeat_end_at_date,
				'mec_in_days' => (string) $meta->mec_in_days,
				'mec_not_in_days' => (string) $meta->mec_not_in_days,
				'mec_hourly_schedules' => $hourly_schedules,
				'mec_booking' => array
				(
					'bookings_limit_unlimited' => (int) $meta->mec_booking->bookings_limit_unlimited,
					'bookings_limit' => (int) $meta->mec_booking->bookings_limit,
				),
				'mec_tickets' => $tickets,
				'mec_fees_global_inheritance' => (int) $meta->mec_fees_global_inheritance,
				'mec_fees' => $fees,
				'mec_reg_fields_global_inheritance' => (int) $meta->mec_reg_fields_global_inheritance,
				'mec_reg_fields' => $reg_fields,
			),
		);

		$ret = array('post_id' => null, 'is_new' => true, 'url' => null, 'title' => null);

		$post_id = $db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$feed_event_id' AND `meta_key`='mec_feed_event_id'", 'loadResult');
		if ($post_id) {
			$ret['is_new'] = false;
		}

		// Insert the event into MEC
		$post_id = $main->save_event($args, $post_id);

		// Add it to the imported posts
		$posts[] = $post_id;

		$ret['post_id'] = $post_id;

		// Set location to the post
		if ($location_id) {
			wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
		}

		// Set organizer to the post
		if ($organizer_id) {
			wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');
		}

		// Set categories to the post
		if (is_array($category) && count($category)) {
			foreach ($category as $category_id) {
				wp_set_object_terms($post_id, (int) $category_id, 'mec_category', true);
			}
		}

		// Set tags to the post
		if (count($tag_ids)) {
			foreach ($tag_ids as $tag_id) {
				wp_set_object_terms($post_id, (int) $tag_id, 'post_tag', true);
			}
		}

		// Set labels to the post
		if (count($label_ids)) {
			foreach ($label_ids as $label_id) {
				wp_set_object_terms($post_id, (int) $label_id, 'mec_label', true);
			}
		}

		// Featured Image
		$featured_image = isset($event->featured_image) ? (string) $event->featured_image->full : '';
		if (!has_post_thumbnail($post_id) and trim($featured_image)) {
			$file_name = basename($featured_image);

			$path = rtrim($wp_upload_dir['path'], DS . ' ') . DS . $file_name;
			$url = rtrim($wp_upload_dir['url'], '/ ') . '/' . $file_name;

			// Download Image
			$buffer = $main->get_web_page($featured_image);

			$file->write($path, $buffer);
			$main->set_featured_image($url, $post_id);
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

		$getall = admin_url('admin.php?page=MEC-advimp&advimp_cmd=mecapi_getall');
		$add_to_auto_sync = admin_url('admin.php?page=MEC-advimp&advimp_cmd=mec_add_to_auto_sync');
		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'mecapi.php';
		include $content;

	}

}
