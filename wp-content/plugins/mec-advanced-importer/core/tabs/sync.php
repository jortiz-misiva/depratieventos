<?php

use MEC\Singleton;
use MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Lib_Backend_UI;

/**
 * Advanced importer Sync class
 */
class MEC_Advanced_Importer_Sync extends Singleton {

	/**
	 * Return Account or calendar title
	 *
	 * @param int $id
	 * @param string $class
	 * @param array $post
	 *
	 * @return string
	 */
	public function get_title( $id, $class, $post ){

		$class = strtolower( $class );
		switch( $class ){

			case 'google':

				$calendar_list_item = isset( $post['calendar_list_item'] ) ? $post['calendar_list_item'] : '';
				$calendar_title = isset( $post['calendar_title'] ) ? $post['calendar_title'] : __( 'Undefined', 'mec-advanced-importer' );

				$account_title = '';
				if( $calendar_list_item ){

					$list = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::active_account( $class, true );
					$account_title = isset( $list[ $calendar_list_item ] ) ? $list[ $calendar_list_item ] : '';
				}

				$title = !empty( $account_title ) ? "$account_title - $calendar_title" : $calendar_title;
				break;

			case 'ics':

				$title = isset( $post['url'] ) ? $post['url'] : __( 'Undefined', 'mec-advanced-importer' );

				break;

			default:
				$list = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::active_account( $class );
				$title = isset( $list[ $id ] ) ? $list[ $id ] : __( 'Undefined', 'mec-advanced-importer' );
		}

		return $title;
	}

	/**
	  * Add to Auto Sync
	  *
	  * @param array $post
	  *
	  * @return int
	  */
	 public static function add_to_auto_sync_by_ajax(){

		$post = $_POST;

		$sync_id = self::getInstance()->add_to_sync( $post );

		if( $sync_id ){
			wp_send_json_success( __('Success Add.', 'mec-advanced-importer'), 200);
		}
	}

	/**
	 * Return date object
	 *
	 * @param string $scheduledType
	 *
	 * @return DateTime
	 */
	public static function get_schedule_datetime( $scheduledType, $timestamp = 'now' ){

		$date = new \DateTime('now', wp_timezone());
		if( is_numeric( $timestamp ) ){

			$date->setTimestamp( $timestamp );
		}

		$interval = null;
		switch ($scheduledType) {
			case 'hourly':
				$interval = new \DateInterval('PT1H');
				break;

			case 'twicedaily':
				$interval = new \DateInterval('PT12H');
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

		$date = $date->add($interval);

		return $date;
	}

	/**
	 * Add to Auto Sync
	*
	* @param array $post
	*
	* @return int
	*/
	public function add_to_sync( $post ){

		$class = isset($post['class']) ? $post['class'] : null;
		$scheduled = isset($post['scheduled']) ? $post['scheduled'] : null;
		$scheduledType = isset($post['scheduledType']) ? $post['scheduledType'] : null;

        if (is_array($post['category'])){
            $category = isset($post['category']) ? $post['category'] : array();
        }else{
            $category = isset($post['category']) ? json_decode(stripslashes($post['category']), true) : array();
        }

		$selected_ids = isset($post['selected']) ? json_decode(stripslashes($post['selected']), true) : array();

		$date = self::get_schedule_datetime( $scheduledType );

	   	foreach( $selected_ids as $selected ){

		  	$title = $this->get_title( $selected, $class, $post );
			$datetime = $date->format('Y-m-d H:i:s');
			$args = array(
				'post_title' => $title,
				'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
				'post_content' => strtotime($datetime),
				'post_date' => $date->format('Y-m-d H:i:s'),
				'meta_input' => array(
					'id' => $selected, //account_id or calendar_id
					'sync_class' => 'Ics' === $class ? strtoupper( $class ) : ucfirst($class),
					'event_category' => $category,
					'mec_sync_args' => $post,
				),
				'post_status' => 'publish',
			);


			$sync_id = wp_insert_post( $args );
	   	}

	   return $sync_id;
	}

	/**
	 * Return event_ids
	 *
	 * @param array|object $events
	 * @param string $class
	 *
	 * @return array
	 */
	public static function get_events_data( &$events, $class ){

		$class = strtolower( $class );
		$events_data = array();
		switch( $class ){

			case 'google':

				$events_data = $events->items;
				break;

			case 'mecapi':

				$events_data = $events;
				break;

			case 'eventbrite':

				$events_data = $events->events;
				break;

			case 'meetup':
			case 'facebook':

				$events_data = $events->data;
				break;
			case 'ics':

				$events_data = $events;
				break;
		}

		return is_array( $events_data ) ? $events_data : array();
	}

	/**
	 * Return event_ids
	 *
	 * @param array|object $events
	 * @param string $class
	 *
	 * @return array
	 */
	public static function get_event_ids( &$events, $class ){

		$class = strtolower( $class );
		$ids = array();
		switch( $class ){

			case 'google':
				if( is_array( $events->items ) ){

					foreach( $events->items as $event ){

						$id = $event->id;
						$ids[ $id ] = $id;
					}
				}
				break;

			case 'mecapi':

				foreach( $events as $event ){

					$id = $event->ID;
					$ids[ $id ] = $id;
				}
				break;

			case 'eventbrite':

				foreach( $events->events as $event ){

					$id = $event->id;
					$ids[ $id ] = $id;
				}
				break;

			case 'meetup':
			case 'facebook':

				foreach( $events->data as $event ){

					$id = $event->id;
					$ids[ $id ] = $id;
				}
				break;
			case 'ics':

				foreach( $events as $event ){

					$id = $event['ID'];
					$ids[ $id ] = $id;
				}
				break;
		}

		return $ids;
	}

	/**
	 * Return event_ids
	 *
	 * @param array|object $event_ids
	 * @param string $class
	 *
	 * @return array
	 */
	public static function remove_exists_event_ids( $event_ids, $class ){

		$class = strtolower( $class );

		$where_in = "('" . implode( "','", $event_ids ) . "')";
		global $wpdb;
		$sql = "SELECT DISTINCT `meta_value` FROM {$wpdb->postmeta} WHERE ( `meta_key` = 'mec_advimp_{$class}_event_id' || `meta_key` = 'mec_source_event_id' ) && `meta_value` IN {$where_in}";

		$ids = $wpdb->get_col( $sql );
		$event_ids = array_diff( $event_ids, $ids );

		return $event_ids;
	}

	/**
	 * Return event_ids after add prefix "account_id or calendar_id"
	 *
	 * @param array|object $event_ids
	 * @param string $class
	 * @param string $select
	 *
	 * @return array
	 */
	public static function add_prefix_id_to_event_ids( $event_ids, $class, $select ){

		$ids = array();
		foreach( $event_ids as $event_id ){

			switch( $class ){
				case 'ICS':
					$k_event_id = "{$event_id}";
					$ids[ $k_event_id ] = $event_id;
					break;
				default:
					$k_event_id = "{$select}_{$event_id}";
					$ids[ $k_event_id ] = $event_id;
			}

		}

		return $ids;
	}

	/**
	 * Return event_ids after add prefix "account_id or calendar_id"
	 *
	 * @param array|object $events
	 * @param string $class
	 * @param string $select
	 *
	 * @return array
	 */
	public static function add_events_to_schedule_import( $events, $class, $select, $sync_args ){

		$event_ids = self::get_event_ids( $events, $class );
		if( empty($event_ids) ){

			return;
		}

		if( !$sync_args['update'] ){

			$event_ids = self::remove_exists_event_ids( $event_ids, $class );
		}
		$event_ids = self::add_prefix_id_to_event_ids( $event_ids, $class, $select );

		if( empty($event_ids) ){

			return;
		}

		$_POST = $sync_args;
		$_POST['event_ids'] = json_encode($event_ids);
		$_POST['event_class'] = $class;

		$ids = MEC_Advanced_Importer_Lib_Backend_UI::schedule_events( false, $events );
	}

	/**
	 * Check Args
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function check_args( $args ){

		unset( $args['preview'] );

		$class = isset( $args['class'] ) ? strtolower( $args['class'] ) : '';
		$calendar_list_item = isset( $args['calendar_list_item'] ) ? $args['calendar_list_item'] : false;
		$args['update'] = isset( $args['update'] ) && 'yes' === $args['update'] ?  : false;

		if( 'google' === $class ){

			if( $calendar_list_item && empty( $args['access_token'] ) ){

				$settings = get_option( "mec_advimp_auth_{$class}", array() );
				$args['access_token'] = isset( $settings[ $calendar_list_item ]['access_token'] ) ? $settings[ $calendar_list_item ]['access_token'] : '';
			}

			$ex = explode('_', $args['selected_current']);
			$args['selected_calendar'] = $ex[1];
		}



		return $args;
	}

	/**
	 * Check and Run Sync
	 *
	 * @return void
	 */
	public static function run_sync(){

		$q_args = array(
			'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => 'sync_class',
					'compare' => 'EXISTS',
				)
			),
			'post_status' => array(
				'publish',
				'future', //TODO: remove
			)
		);

		$importdata_query = new WP_Query($q_args);
		$posts = $importdata_query->posts;
		foreach ( $posts as $post ) {

			$on = $post->post_content;
			$current = current_time('timestamp');

			// if ($on > $current) {
			// 	error_log('The event imported on:' . date('Y-m-d H:i:s', $on));
			// 	continue;
			// }

			$meta = get_post_meta($post->ID);
			$select = isset($meta['id']) ? $meta['id'][0] : null;
			$class = isset($meta['sync_class']) ? $meta['sync_class'][0] : null;

			$sync_args = isset($meta['mec_sync_args']) ? maybe_unserialize( $meta['mec_sync_args'][0] ) : null;
			$sync_args['selected_current'] = $select;

			$scheduledType = isset($sync_args['scheduledType']) ? $sync_args['scheduledType'] : null;

			if (!$class || !$sync_args) {
				continue;
			}

			$category = isset($meta['event_category']) ? unserialize($meta['event_category'][0]) : array();

			$sync_args = self::check_args( $sync_args );
			$_POST = $sync_args;
			$cname = '\\MEC_Advanced_Importer\\Core\\Tabs\\' . $class;
			$c = new $cname();
			$c->prepare_request($sync_args);
			$sync_args['id'] = $post->ID;

			if( 'Google' == $class ){
				$c->preview = true;
			}

			$events = $c->get_all_events( $sync_args );

			$events_data = self::get_events_data( $events, $class );
			$data = array(
				'data' => array(),
				'total_records' => 0,
			);

			foreach ($events_data as $key => $event) {

				$row = $c->prepare_row($select, $event, $sync_args);
				if (!$row) {
					continue;
				}

				$row['title'] = wp_strip_all_tags( $row['title'] );
				$row['title'] = str_replace(
					' -- ' . __('repeating','mec-advanced-importer'),
					'',
					$row['title']
				);

				if( !empty( $sync_args['selected_calendar'] ) ){

					$row['ID'] = str_replace(
						"{$sync_args['selected_calendar']}_{$sync_args['selected_calendar']}",
						"{$sync_args['selected_calendar']}",
						$row['ID']
					);
				}

				$data['data'][] = $row;
				$data['total_records'] += 1;
			}

			update_option( 'mec_advimp_' . strtolower($class) .  '_current_event', $data );

			// if( 'Google' == $class ){
			// 	wp_die('Events:' . print_r($events,true));
			// }

			self::add_events_to_schedule_import( $events, $class, $select, $sync_args );

			// $ret = $c->process_download_single_event($event_id, $category);
			$datetime = strtotime($post->post_date);
			$date = self::get_schedule_datetime( $scheduledType, $datetime );

			$next_datetime = $date->format('Y-m-d H:i:s');
			$next_timestamp = $date->getTimestamp();

			if ( $next_datetime === $post->post_date ) {

				wp_delete_post($post->ID, true);
			}else{

				$post->post_content = strtotime($next_datetime);
				$post->post_date = date('Y-m-d H:i:s', $next_timestamp);
				wp_update_post( (array) $post );
			}
		}
	}

	public static function display_sync_table_list( $class ){

		if (!class_exists('WP_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}

		$table = new \MEC_Advanced_Importer_Sync_Table();
		$table->event_class = $class;
		$table->prepare_items();
		echo $table->display();
	}
 }
