<?php

namespace MEC_Advanced_Importer\Core\Tabs;

use MEC_Advanced_Importer_Sync;

class ICS {
	public $name = 'ICS';
	public $option_name = 'advimp_user_token_options';
	public $batch = array('my', 'page', 'group');
	public $limit_fetch = 5;
	public $main;
	public $file;
	public $db;
	public $table = null;

	public $error = null;
	public $table_ajax = array();

	function __construct() {

		$this->main = \MEC::getInstance('app.libraries.main');
		$this->file = \MEC::getInstance('app.libraries.filesystem', 'MEC_file');
		$this->db = \MEC::getInstance('app.libraries.db');

		add_action('wp_ajax__ajax_fetch_ics_history', array($this, '_ajax_fetch_ics_history_callback'));

	}

	function _ajax_fetch_ics_history_callback() {

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$table->page_section = strtolower($this->name);
		$table->ajax_response();

	}

	public function get_terms(){

		return array(
			'mec_category' => 'Categories',
			'post_tag' => 'Tags',
			'mec_speaker' => 'Speakers',
			'mec_label' => 'Labels',
			'mec_organizer' => 'Organizer',
			'mec_location' => 'Location',
		);
	}

	public function process_download_single_event($event_id, $category = array()) {

		$ex = explode('_', $event_id);
		$ics = ABSPATH . 'wp-content/uploads/' . $ex[0] . '.ics';
		$json = ABSPATH . 'wp-content/uploads/' . $ex[0] . '.json';

		$is_ics = false;
		$event = null;
		if (file_exists($ics)) {
			require_once MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'lib' . DS . 'ICal' . DS . 'ICal.php';
			$ical = new \MEC_ADVIMP_ICal($ics, array(
				'defaultSpan' => 2, // Default value
				'defaultTimeZone' => 'UTC',
				'defaultWeekStart' => 'MO', // Default value
				'disableCharacterReplacement' => false, // Default value
				'skipRecurrence' => false, // Default value
				'useTimeZoneWithRRules' => false, // Default value
			));
			$events = $ical->events();
			$event = $events[ end($ex) ];
			$is_ics = true;
		} else if (file_exists($json)) {

			$events = json_decode(file_get_contents($json, true));
			$row = isset($events[$ex[2]]) ? $events[$ex[2]] : null;

			if (!$row) {
				return false;
			}

			$options = get_option( 'mec_advimp_' .  strtolower($this->name) . '_current_event');
			$columns = isset($options['columns'])?$options['columns']:$this->extract_column_by_title(array());

			$date_start = isset($row[$columns['StartDate']]) ? $row[$columns['StartDate']] : null;
			$time_start = isset($row[$columns['StartTime']]) ? $row[$columns['StartTime']] : null;
			$date_end = isset($row[$columns['EndDate']]) ? $row[$columns['EndDate']] : null;
			$time_end = isset($row[$columns['EndTime']]) ? $row[$columns['EndTime']] : null;
			$thumbnail_url = isset($row[$columns['FeaturedImage']]) ? $row[$columns['FeaturedImage']] : null;

			$event = new \stdClass();

			foreach( $columns as $column_key => $column_id ){

				$event->{$column_key} = $row[$column_id] ?? '';
			}

			$event->summary = $row[$columns['Title']];
			$event->description = $row[$columns['Description']];
			$event->dtstart = "{$date_start} {$time_start}";
			$event->dtend = "{$date_end} {$time_end}";
			$event->attach = $thumbnail_url;
			$event->id = $row[$columns['ID']];

			$terms = $this->get_terms();
			if( !empty( $category ) ){
				unset( $terms['mec_category'] );
			}

			foreach( $terms as $term ){

				$ob_key = strtolower($term);
				if( ! isset( $columns[$term] ) ){
					continue;
				}
				$event->{$ob_key} = isset($row[$columns[$term]]) ? $row[$columns[$term]] : null;
			}
		}

		$ret = $this->saved_single_event($event, $category, $is_ics);

		$id = wp_insert_post(array(
			'post_title' => "EventID:{$ex[2]}",
			'post_type' => MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE,
			'post_content' => "Download Single Event:{$ex[2]}",
		));
		add_post_meta($id, 'import_origin', $this->name);
		if (count($category) > 0) {
			add_post_meta($id, 'category', $category);
		}
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

	public static function get_datetime( $event, $key, $current_timezone, $convert_to = true ){

		$datetime = $event->{$key} ?? false;
		if( !$datetime ){

			return false;
		}

		$datetime_format = 'Y-m-d H:i';
		$a_key = "{$key}_array";
		$event_timezone = isset( $event->{$a_key}[0]['TZID'] ) && !empty( $event->{$a_key}[0]['TZID'] ) ? $event->{$a_key}[0]['TZID'] : false;

		$date = new \DateTime( $datetime );
		$timestamp = $date->getTimestamp();
		$time = (int) $date->format('Hi');
		if( $convert_to && ( !$event_timezone || $event_timezone !== $current_timezone ) ){

			$date->setTimezone( new \DateTimeZone( $current_timezone ) );
		}
		$local_timestamp = strtotime( $date->format('Y-m-d') );

		if( $timestamp === $local_timestamp ){

			$datetime_format = 'Y-m-d';
		}

		if( 'dtend' === $key && 0 === $time ){

			$interval = new \DateInterval('P1D');
			$date->sub( $interval );
		}

		return $date->format( $datetime_format );
	}

	public function get_events( $ics, $fid ){

		$timezone = $this->main->get_timezone();

		$data = array();
		$ids = array();
		try {
			$ical = new \MEC_ADVIMP_ICal($ics, array(
				'defaultSpan' => 2, // Default value
				'defaultTimeZone' => 'UTC',
				'defaultWeekStart' => 'MO', // Default value
				'disableCharacterReplacement' => false, // Default value
				'skipRecurrence' => false, // Default value
				'useTimeZoneWithRRules' => false, // Default value
			));

			$events = $ical->events();
			$total = 0;
			foreach ($events as $k => $event) {
				if (isset($ids[$event->uid])) {
					continue;
				}

				$title = !empty($event->summary) ? $event->summary : $event->description;
				if (empty($title)) {
					continue;
				}

				$start = static::get_datetime( $event, 'dtstart', $timezone );
				$end = static::get_datetime( $event, 'dtend', $timezone );
				$end = $end ? $end : $start;

				$data[] = array(
					'ID' => "{$fid}_{$event->uid}_{$k}",
					'title' => $title,
					'start' => $start,
					'end' => $end,
					'link' => null,
				);

				$ids[$event->uid] = $event;
			}

			return $data;
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}

		return $data;
	}

	private function parse_local_ics_file($ics, $fid, $return_data = false) {

		$data = $this->get_events( $ics, $fid );

		if( !$data ){
			return false;
		}

		if( $return_data ){

			return $data;
		}

		require_once MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'lib' . DS . 'ICal' . DS . 'ICal.php';

		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$table = new \MEC_Advanced_Importer_Preview_Table();

		$total = count( $data );
		$table->data = array('data' => $data, 'total_records' => $total);
		$table->page_section = strtolower($this->name);
		$table->params = array('scheduled' => 'onestep');
		update_option('mec_advimp_' . $table->page_section . '_current_event', $table->data);
		$table->prepare_items();

		ob_start();
		$table->display();
		$display = ob_get_clean();

		$this->table_ajax = array('success' => true, 'data' => array(
			'next' => false,
			'post_id' => 1,
			'table' => $display,
		));
	}

	private function extract_column_by_title($titles) {
		$column_ids = array(
			'ID',
			'Title',
			'Description',
			'StartDate',
			'StartTime',
			'EndDate',
			'EndTime',
			'Link',
			'Location',
			'Address',
			'Organizer',
			'OrganizerTel',
			'OrganizerEmail',
			'EventCost',
			'FeaturedImage',
			'Text',
			'Date',
			'Labels',
			'Tags',
			'Categories',
			'Speakers',
		);

		$ret = array();

		foreach ($titles as $k => $title) {

			$column = str_replace(' ', '', trim($title));


			if(in_array("{$column}", $column_ids)){
				$ret[$column] = $k;
			}
		}

		if(count($ret)>0){
			return $ret;

		}

		return $column_ids;

	}

	private function parse_local_csv($events, $fid, $title = null) {
		require_once MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'lib' . DS . 'ICal' . DS . 'ICal.php';

		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$table = new \MEC_Advanced_Importer_Preview_Table();
		$data = array();
		$ids = array();

		$columns = $this->extract_column_by_title($title);

		try {

			$total = 0;
			foreach ($events as $k => $event) {

				$id = isset($event[$columns['ID']]) ? $event[$columns['ID']] : null;
				$title = isset($event[$columns['Title']]) ? $event[$columns['Title']] : null;
				$date_start = isset($event[$columns['StartDate']]) ? $event[$columns['StartDate']] : null;
				$time_start = isset($event[$columns['StartTime']]) ? $event[$columns['StartTime']] : null;
				$date_end = isset($event[$columns['EndDate']]) ? $event[$columns['EndDate']] : null;
				$time_end = isset($event[$columns['EndTime']]) ? $event[$columns['EndTime']] : null;
				$url = isset($event[$columns['Link']]) ? $event[$columns['Link']] : null;

				if ($id == null) {
					continue;
				}

				$start = date('Y-m-d H:i:s', strtotime("{$date_start} {$time_start}"));
				$end = date('Y-m-d H:i:s', strtotime("{$date_end} {$time_end}"));

				$data[] = array(
					'ID' => "{$fid}_{$id}_{$k}",
					'title' => $title,
					'start' => $start,
					'end' => $end,
					'link' => $url,
				);
				$ids[$id] = $event;
				$total += 1;
			}

			$table->data = array('data' => $data, 'total_records' => $total,'columns'=>$columns);
			$table->page_section = strtolower($this->name);
			$table->params = array('scheduled' => 'onestep');
			update_option('mec_advimp_' . $table->page_section . '_current_event', $table->data);
			$table->prepare_items();

			ob_start();
			$table->display();
			$display = ob_get_clean();

			$this->table_ajax = array('success' => true, 'data' => array(
				'next' => false,
				'post_id' => 1,
				'table' => $display,
			));
		} catch (\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function parse_url($url , $return_data = false) {

		$url = trim($url);

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			$this->error = __('URL is not valid!', 'mec-advanced-importer');
			return false;
		}

		if (!function_exists('download_url')) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$tmp_file = download_url($url);

		if (isset($tmp_file->errors)) {
			$msg = '';
			foreach ($tmp_file->errors as $k => $v) {
				$msg .= "[$k] {$v[0]}\n";
			}

			$this->error = $msg;
			return false;
		}

		$fid = md5(mt_rand(1, 100) . time());

		$type = mime_content_type($tmp_file);

		$file_path = '';
		if ("{$type}" == 'text/calendar') {
			$file_path = ABSPATH . 'wp-content/uploads/' . $fid . '.ics';
			copy($tmp_file, $file_path);
			@unlink($tmp_file);
			return $this->parse_local_ics_file($file_path, $fid, $return_data);
		} else if (in_array("{$type}", array('text/csv', 'application/vnd.ms-excel', 'text/plain'))) {
			$file_path = ABSPATH . 'wp-content/uploads/' . $fid . '.csv';
			copy($tmp_file, $file_path);
			@unlink($tmp_file);
			return $this->parse_local_csv_file($file_path, $fid);
		}

		return false;

	}

	public function parse_local_csv_file($target_path, $fid) {
		$info = pathinfo($target_path);

		$target_path_json = $info['dirname'] . '/' . $fid . '.json';
		$data_save = [];
		$title_column = null;
		if (($h = fopen($target_path, 'r')) !== false) {
			while (($row = fgetcsv($h, 10000, ",")) !== false) {
				if ($title_column === null) {

					foreach( $row as $k => $r ){

						$row[$k] = preg_replace('/[^A-Za-z0-9\-]/', '', $r);
					}
					$title_column = $row;
					continue;
				}

				$data_save[] = $row;

			}
			fclose($h);
			file_put_contents($target_path_json, json_encode($data_save));

			return $this->parse_local_csv($data_save, $fid, $title_column);
		}

		return false;

	}

	public function parse_upload($file) {

		if (!isset($file['name']) or (isset($file['name']) and trim($file['name']) == '')) {
			return false;
		}

		// detect csv file
		if (isset($file['type']) && (isset($file['type']) && in_array(strtolower($file['type']), array('text/csv', 'application/vnd.ms-excel')))) {

			// Upload the File
			$upload_dir = wp_upload_dir();

			$fid = md5(mt_rand(1, 100) . $file['name'] . time());

			$target_path = $upload_dir['basedir'] . '/' . $fid . '.csv';

			$uploaded = move_uploaded_file($file['tmp_name'], $target_path);

			if (!$uploaded) {
				return false;
			}

			return $this->parse_local_csv_file($target_path, $fid);

		}

		$fid = md5(mt_rand(1, 100) . $file['name'] . time());
		$ics = ABSPATH . 'wp-content/uploads/' . $fid . '.ics';
		$uploaded = move_uploaded_file($file['tmp_name'], $ics);
		if (!$uploaded) {
			return false;
		}

		$this->parse_local_ics_file($ics, $fid);
		return true;

	}

	public function saved_single_event($event, $category = array(), $is_ics = true ) {

		$feed_event_id = $event->id;
		$timezone = $this->main->get_timezone();

		// Event location
		$location = isset($event->location)?$event->location:null;
		$address = isset($event->Address)?$event->Address:null;
		$location_id = trim($location) ? $this->main->save_location(array
			(
				'name' => trim((string) $location),
				'address' => trim((string) $address),
			)) : 1;

		// Event Organizer
		$organizer = isset($event->organizer_array) ? $event->organizer_array : array();
		$organizer = empty( $organizer ) && isset( $event->organizer ) ? explode(',',$event->organizer) : $organizer;

		$organizer_id = (isset($organizer[0]) and isset($organizer[0])) ? $this->main->save_organizer(array
			(
				'name' => trim((string) $organizer[0]),
				'tel' => isset( $event->OrganizerTel ) ? $event->OrganizerTel : '',
				'email' => isset( $event->OrganizerEmail ) ? $event->OrganizerEmail : '',
			)) : 1;

		// Event Categories
		$category_ids = array();
		if (isset($event->categories) and trim($event->categories)) {
			$cats = explode(',', $event->categories);
			foreach ($cats as $cat) {
				$category_id = $this->main->save_category(array
					(
						'name' => trim((string) $cat),
					));

				if ($category_id) {
					$category_ids[] = $category_id;
				}

			}
		}

		$start = static::get_datetime( $event, 'dtstart', $timezone, $is_ics ? true : false );
		$end = static::get_datetime( $event, 'dtend', $timezone, $is_ics ? true : false );
		$end = $end ? $end : $start;

		$date_start = new \DateTime($start);
		$start_date = $date_start->format('Y-m-d');
		$start_hour = $date_start->format('g');
		$start_minutes = $date_start->format('i');
		$start_ampm = $date_start->format('A');

		$date_end = NULL;



		$date_end = $end ? new \DateTime($end) : '';
		if( $end && $end === $date_end->format('Y-m-d') ){

			$date_end = new \DateTime( "$end 12:00 pm");
		}
		$end_date = $end ? $date_end->format('Y-m-d') : $start_date;
		$end_hour = $end ? $date_end->format('g') : 8;
		$end_minutes = $end ? $date_end->format('i') : '00';
		$end_ampm = $end ? $date_end->format('A') : 'PM';

		// Time Options
		$allday = 0;
		$time_comment = '';
		$hide_time = 0;
		$hide_end_time = 0;

		// Repeat Options
		$repeat_status = 0;
		$repeat_type = '';
		$repeat_interval = NULL;
		$finish = $end_date;
		$year = NULL;
		$month = NULL;
		$day = NULL;
		$week = NULL;
		$weekday = NULL;
		$weekdays = NULL;
		$days = NULL;
		$not_in_days = NULL;
		$advanced_days = NULL;

		// Recurring Event
		$rrule = (isset($event->rrule) and trim($event->rrule)) ? $event->rrule : '';
		if (trim($rrule) != '') {
			$ex1 = explode(';', $rrule);

			$rule = array();
			foreach ($ex1 as $r) {
				$ex2 = explode('=', $r);
				$rule[strtolower($ex2[0])] = strtolower($ex2[1]);
			}

			$repeat_status = 1;
			$repeat_until = isset($rule['until']) ? $rule['until'] : '';
			$repeat_count = isset($rule['count']) ? $rule['count'] : '';
			$repeat_end_at_date = null;
			$repeat_end_at_occurrences = null;
			$repeat_end = 'never';
			if( !empty( $repeat_until ) ) {

				$repeat_end_at_date = date_i18n( 'Y-m-d', strtotime( $repeat_until ) );
				$repeat_end = 'date';
			}elseif( $repeat_count ) {

				$repeat_end_at_occurrences = $repeat_count;
				$repeat_end = 'occurrences';
			}

			if ($rule['freq'] == 'daily') {
				$repeat_type = 'daily';
				$repeat_interval = isset($rule['interval']) ? $rule['interval'] : 1;
			} elseif ($rule['freq'] == 'weekly') {
				$repeat_type = 'weekly';
				$repeat_interval = isset($rule['interval']) ? $rule['interval'] * 7 : 7;
			} elseif($rule['freq'] == 'monthly' and isset($rule['byday']) and trim($rule['byday'])) {

				$repeat_type = 'advanced';

                $adv_week = (isset($rule['bysetpos']) and trim($rule['bysetpos']) != '') ? $rule['bysetpos'] : (int) substr($rule['byday'], 0, -2);
                $adv_day = str_replace($adv_week, '', $rule['byday']);

                $mec_adv_day = 'Sat';
                if($adv_day == 'su') $mec_adv_day = 'Sun';
                elseif($adv_day == 'mo') $mec_adv_day = 'Mon';
                elseif($adv_day == 'tu') $mec_adv_day = 'Tue';
                elseif($adv_day == 'we') $mec_adv_day = 'Wed';
                elseif($adv_day == 'th') $mec_adv_day = 'Thu';
                elseif($adv_day == 'fr') $mec_adv_day = 'Fri';

                if($adv_week < 0) $adv_week = 'l';
                $advanced_days = array($mec_adv_day.'.'.$adv_week);
            } elseif($rule['freq'] == 'monthly') {

                $repeat_type = 'monthly';
                $repeat_interval = isset($rule['interval']) ? $rule['interval'] : 1;

                $year = '*';
                $month = '*';

                $s = $start_date;
                $e = $end_date;

                $_days = array();
                while(strtotime($s) <= strtotime($e))
                {
                    $_days[] = date('d', strtotime($s));
                    $s = date('Y-m-d', strtotime('+1 Day', strtotime($s)));
                }

                $day = ','.implode(',', array_unique($_days)).',';

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
				foreach ($g_week_days as $g_week_day) {
					$weekdays .= $week_day_mapping[$g_week_day] . ',';
				}

				$weekdays = ',' . trim($weekdays, ', ') . ',';
				$interval = NULL;

				$repeat_type = 'certain_weekdays';
			}

			$finish = isset($rule['until']) ? date('Y-m-d', strtotime($rule['until'])) : NULL;
		}

		$additional_organizer_ids = array();
		$hourly_schedules = array();
		$tickets = array();
		$fees = array();
		$reg_fields = array();

		$args = array
			(
			'title' => (string) $event->summary,
			'content' => (string) $event->description,
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
				'mec_source' => 'ics-calendar',
				'mec_feed_event_id' => $feed_event_id,
				'mec_advimp_ics_event_id' => $feed_event_id,
				'mec_dont_show_map' => 0,
				'mec_additional_organizer_ids' => $additional_organizer_ids,
				'mec_repeat' => array
				(
					'status' => $repeat_status,
					'type' => $repeat_type,
					'interval' => $repeat_interval,
					'end' => $repeat_end,
					'end_at_date' => $repeat_end_at_date,
					'end_at_occurrences' => $repeat_end_at_occurrences,
				),
				'mec_allday' => $allday,
				'mec_hide_time' => $hide_time,
				'mec_hide_end_time' => $hide_end_time,
				'mec_comment' => $time_comment,
				'mec_repeat_end' => $repeat_end,
				'mec_repeat_end_at_occurrences' => $repeat_end_at_occurrences,
				'mec_repeat_end_at_date' => $repeat_end_at_date,
				'mec_in_days' => $days,
				'mec_not_in_days' => $not_in_days,
				'mec_hourly_schedules' => $hourly_schedules,
				'mec_tickets' => $tickets,
				'mec_fees_global_inheritance' => 1,
				'mec_fees' => $fees,
				'mec_reg_fields_global_inheritance' => 1,
				'mec_reg_fields' => $reg_fields,
				'mec_advanced_days'=>$advanced_days,
				'mec_cost' => isset( $event->EventCost ) ? $event->EventCost : '',
			),
		);

		$ret = array('post_id' => null, 'is_new' => true, 'url' => null, 'title' => null);

		$post_id = $this->db->select("SELECT `post_id` FROM `#__postmeta` WHERE `meta_value`='$feed_event_id' AND `meta_key`='mec_advimp_ics_event_id'", 'loadResult');
		if ($post_id) {
			$ret['is_new'] = false;
		}

		// Insert the event into MEC
		$post_id = $this->main->save_event($args, $post_id);

		// Set categories to the post

		if (is_array($category) && count($category)) {
			foreach ($category as $category_id) {
				wp_set_object_terms($post_id, (int) $category_id, 'mec_category', true);
			}
		}

		// Add it to the imported posts
		$posts[] = $post_id;

		// Set location to the post
		if ($location_id) {
			wp_set_object_terms($post_id, (int) $location_id, 'mec_location');
		}

		// Set organizer to the post
		if ($organizer_id) {
			wp_set_object_terms($post_id, (int) $organizer_id, 'mec_organizer');
		}

		// Set terms to the post
		$terms = $this->get_terms();
		foreach( $terms as $taxonomy => $term ){

			$ob_key = strtolower($term);
			if( !property_exists( $event, $ob_key ) ){
				continue;
			}

			$terms_array = explode(',',$event->{$ob_key});

			wp_set_object_terms( $post_id, $terms_array, $taxonomy, true );
		}


		$wp_upload_dir = wp_upload_dir();
		// Featured Image
		$featured_image = isset($event->attach) ? (string) $event->attach : '';
		if (!has_post_thumbnail($post_id) and trim($featured_image)) {
			$file_name = basename($featured_image);

			$path = rtrim($wp_upload_dir['path'], DS . ' ') . DS . $file_name;
			$url = rtrim($wp_upload_dir['url'], '/ ') . '/' . $file_name;

			// Download Image
			$buffer = $this->main->get_web_page($featured_image);

			$this->file->write($path, $buffer);
			$this->main->set_featured_image($url, $post_id);
		}

		$ret['url'] = get_permalink($post_id);
		$ret['title'] = $args['title'];
		$ret['post_id'] = $post_id;

		return $ret;
	}

	public function prepare_request(){

	}

	public function prepare_row( $select, $event, $args){

		return $event;
	}

	public function get_all_events(){

		return $this->parse(true);
	}

	public function parse($return_data = false) {

		require_once MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'lib' . DS . 'ICal' . DS . 'ICal.php';

		$url = isset($_POST['mec-advimp-ics-url']) ? $_POST['mec-advimp-ics-url'] : null;
		$category = isset($_POST['mecadvimp-category']) ? $_POST['mecadvimp-category'] : array();
		update_option('mec_advimp_current_category_' . $this->name, $category);

		$add_to_sync = isset($_POST['mec-advimp-import-add-to-sync']) && '1' == $_POST['mec-advimp-import-add-to-sync'] ? true : false;
		if( $add_to_sync && !empty( $url ) ){

			$scheduled = isset($_POST['mec-advimp-import-type-inp']) ? $_POST['mec-advimp-import-type-inp'] : 'sheduled';
			$scheduled_type = isset($_POST['mec-advimp-import-type-scheduled-inp']) ? $_POST['mec-advimp-import-type-scheduled-inp'] : 'hourly';
			$post = $_POST;

			$post['class'] = $this->name;
			$post['category'] = json_encode($category);
			$post['selected'] = json_encode(array($url));
			$post['url'] = $url;
			$post['scheduled'] = $scheduled;
			$post['scheduledType'] = $scheduled_type;
			unset( $post['mec-advimp-import-add-to-sync'] );

			$sync_id = MEC_Advanced_Importer_Sync::getInstance()->add_to_sync( $post );
		}

		if ($url) {
			return $this->parse_url($url, $return_data);
		}

		$file = isset($_FILES['mec-advimp-ics']) ? $_FILES['mec-advimp-ics'] : null;
		if ($file && isset($file['error']) && $file['error'] == 0) {
			$this->parse_upload($file);
		}

	}

	public function content() {

		if (isset($_POST['mec-advimp-action']) && $_POST['mec-advimp-action'] == 'import-ics') {
			$this->parse();
		}

		$content = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'tabs' . DS . 'contents' . DS . 'ics.php';
		include $content;
	}

}