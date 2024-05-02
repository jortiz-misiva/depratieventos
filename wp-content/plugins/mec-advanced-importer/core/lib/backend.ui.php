<?php

namespace MEC_Advanced_Importer\Core\Lib;

// use MEC_Advanced_Importer\Core\Apis\Facebook;

/**
 * Webnus MEC Featured, Backend ui class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Importer_Lib_Backend_UI {

	public static $factory;

	public static $main;

	public static $tabs;

	public static $tabs_namespace = 'MEC_Advanced_Importer\\Core\Tabs\\';

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Featured
	 */
	public static $instance;

	/**
	 * The directory of the file.
	 *
	 * @access  public
	 * @var     string
	 */
	public static $dir;

	/**
	 * The Args
	 *
	 * @access  public
	 * @var     array
	 */
	public static $args;

	public static $class;

	public static $loaded_class = array();

	/**
	 * Provides access to a single instance of a module using the singleton pattern.
	 *
	 * @since   1.0.0
	 * @return  object
	 */
	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self();
		}

		return self::$instance;
	}

/*public static function my_run_only_once() {

        if( ! get_option('my_run_only_once13') ) {
//            \MEC_Advanced_Importer_Sync::run_sync();

         /*   $query_args = array(
                'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
                'posts_per_page' => -1,
            );

            $importdata_query = new \WP_Query($query_args);

            $posts = $importdata_query->posts;
                error_log('1111');
                error_log(print_r($posts,true));
            foreach ($posts as $post) {
                error_log('++++++');
                error_log(print_r($post,true));
                $on = $post->post_content;
                $date_current = new \DateTime('now', wp_timezone());
                $current = strtotime($date_current->format("Y-m-d H:i:s"));
//					$current = current_time('timestamp');
                error_log('####### :' . date('Y-m-d H:i:s', $on).'---current:'. date('Y-m-d H:i:s', $current));
                if ($on > $current) {
                    error_log('The event imported on:' . date('Y-m-d H:i:s', $on).'---current:'. date('Y-m-d H:i:s', $current));
//						continue;
                }

                $meta = get_post_meta($post->ID);

                $class = isset($meta['event_class']) ? $meta['event_class'][0] : null;
                $event_id = isset($meta['event_id']) ? $meta['event_id'][0] : null;
//                    error_log('-------');
//                    error_log(print_r($meta,true));
//                    error_log(print_r($class,true));
//                    error_log(print_r($event_id,true));
                if (!$class || !$event_id) {
                    continue;
                }

                $category = isset($meta['event_category']) ? unserialize($meta['event_category'][0]) : array();
//                    error_log('00000');
//                    error_log(print_r($category,true));
                $cname = '\\MEC_Advanced_Importer\\Core\\Tabs\\' . $class;
                $c = new $cname();
                $ret = $c->process_download_single_event($event_id, $category);
                error_log('3333');
                error_log(print_r($ret,true));
                if ($ret == true) {
                    wp_delete_post($post->ID, true);
                }
            }

            update_option( 'my_run_only_once13', true );
        }///
    }*/

	public function __construct() {

		self::$tabs = array(
			'facebook' => array(
				'title' => __('Facebook', 'mec-advanced-importer'),
				'link' => 'Facebook',
			),
			'eventbrite' => array(
				'title' => __('Eventbrite', 'mec-advanced-importer'),
				'link' => 'Eventbrite',
			),
			'meetup' => array(
				'title' => __('Meetup', 'mec-advanced-importer'),
				'link' => 'Meetup',
			),
			'google' => array(
				'title' => __('Google', 'mec-advanced-importer'),
				'link' => 'Google',
			),
			'mecapi' => array(
				'title' => __('MEC API', 'mec-advanced-importer'),
				'link' => 'Mecapi',
			),
			'thirdparty' => array(
				'title' => __('Third Party', 'mec-advanced-importer'),
				'link' => 'Thirdparty',
			),
			'ics' => array(
				'title' => __('ICS / CSV', 'mec-advanced-importer'),
				'link' => 'ICS',
			),

			'history' => array(
				'title' => __('Import History', 'mec-advanced-importer'),
				'link' => 'History',
			),
			'settings' => array(
				'title' => __('Settings', 'mec-advanced-importer'),
				'link' => 'Settings',
			),

		);


		add_action('init', function () {
//            self::my_run_only_once();
			add_action('bl_cron_hook_advimp', function () {

				\MEC_Advanced_Importer_Sync::run_sync();

				$query_args = array(
					'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
					'posts_per_page' => -1,
				);

				$importdata_query = new \WP_Query($query_args);

				$posts = $importdata_query->posts;
//                error_log('1111');
//                error_log(print_r($posts,true));
				foreach ($posts as $post) {

					$on = $post->post_content;
                    $date_current = new \DateTime('now', wp_timezone());
					$current = strtotime($date_current->format("Y-m-d H:i:s"));
//					$current = current_time('timestamp');
                    error_log('####### :' . date('Y-m-d H:i:s', $on).'---current:'. date('Y-m-d H:i:s', $current).'-----------'.date('Y-m-d H:i:s', current_time('timestamp')).'--------'.$post->post_title);
                    if ($on > $current) {
						error_log('The event imported on:' . date('Y-m-d H:i:s', $on).'---current:'. date('Y-m-d H:i:s', $current));
//						continue;
					}

					$meta = get_post_meta($post->ID);

					$class = isset($meta['event_class']) ? $meta['event_class'][0] : null;
					$event_id = isset($meta['event_id']) ? $meta['event_id'][0] : null;
//                    error_log('-------');
//                    error_log(print_r($meta,true));
//                    error_log(print_r($class,true));
//                    error_log(print_r($event_id,true));
                    if (!$class || !$event_id) {
						continue;
					}

					$category = isset($meta['event_category']) ? unserialize($meta['event_category'][0]) : array();
//                    error_log('00000');
//                    error_log(print_r($category,true));
					$cname = '\\MEC_Advanced_Importer\\Core\\Tabs\\' . $class;
					$c = new $cname();
					$ret = $c->process_download_single_event($event_id, $category);
//                    error_log('3333');
//                    error_log(print_r($ret,true));
					if ($ret == true) {
						wp_delete_post($post->ID, true);
					}
				}

			}, 10, 1);

			// for test the event import
			// uncomment for run on five secend
			// change system date:time and every five secends run the action
			// for not run on five secend,
			// add below line to wp-config.php
			// define('ALTERNATE_WP_CRON', true);
			//
			// after uncomment and config
			// change wp_schedule_event to five_seconds, example:
//			 wp_schedule_event( time(), 'five_seconds', 'bl_cron_hook_advimp' );


//				add_filter('cron_schedules', function ($schedules) {
//					$schedules['five_seconds'] = array(
//						'interval' => 5,
//						'display' => esc_html__('Every Five Seconds'));
//					return $schedules;
//				});


			if (!wp_next_scheduled('bl_cron_hook_advimp')) {
				wp_schedule_event(time(), 'hourly', 'bl_cron_hook_advimp');
//				 wp_schedule_event(time(), 'five_seconds', 'bl_cron_hook_advimp');
			}

			self::preload();

			self::register_scheduled_import();
			self::register_history();

			add_action('wp_ajax_mec_advimp_download_single_event', array($this, 'download_single_event'));
			add_action('wp_ajax_mec_advimp_schedule_events', array($this, 'schedule_events'));

			self::init($this);

			add_action('admin_enqueue_scripts', array($this, 'backend_load_assets'), 11, 1);
		}, 1, 1);

		add_action('rest_api_init', function () {
			register_rest_route('mecapi/v1', '/events', array(
				'methods' => 'GET',
				'callback' => array($this, 'rest_api_events'),
			));
		});

		add_action('rest_api_init', function () {
			register_rest_route('mecapi/v1', '/event/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'rest_api_event'),
			));
		});
	}

	public static function rest_api_events($request) {
		$token = md5(get_bloginfo('url'));

		// Or via the helper method:
		$param = $request->get_params();

		if (!isset($param['access_token'])) {
			return new \WP_REST_Response(array('error' => 'token not defined'), 401);
		}

		if ("{$param['access_token']}" != "{$token}") {
			return new \WP_REST_Response(array('error' => 'token failed'), 403);
		}

		$main = \MEC::getInstance('app.libraries.main');

		$events = $main->get_events('-1');
		$output = array();
		foreach ($events as $event) {
			$output[] = $main->export_single($event->ID);
		}

		return new \WP_REST_Response($output, 200);

	}

	/**
	 * return single event by id
	 * @param  [type] $request requested, access_token,id
	 * @return [type]          json
	 * @link(single-event, https://mec.dev-ops.ir/wp-json/mecapi/v1/event/1012/?access_token=ddd27556c7003038c4397d24b175bf74)
	 */
	public static function rest_api_event($request) {
		$token = md5(get_bloginfo('url'));

		// Or via the helper method:
		$param = $request->get_params();

		if (!isset($param['access_token'])) {
			return new \WP_REST_Response(array('error' => 'token not defined'), 400);
		}

		if ("{$param['access_token']}" != "{$token}") {
			return new \WP_REST_Response(array('error' => 'token failed'), 403);
		}

		$id = isset($request['id']) ? $request['id'] : null;
		if (!$id || !is_numeric($id)) {
			return new \WP_REST_Response(array('error' => 'id is not set'), 400);
		}

		$main = \MEC::getInstance('app.libraries.main');

		// $events = $main->get_events('-1');
		// $output = array();
		// foreach ($events as $event) {
		$output = $main->export_single($id);
		// }

		return new \WP_REST_Response($output, 200);

	}

	public static function download_single_event() {
		$event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;
		$class = isset($_POST['class']) ? $_POST['class'] : null;

		$cname = '\\MEC_Advanced_Importer\\Core\\Tabs\\' . ucfirst($class);
		$c = new $cname();
		$c->download_single_event($event_id);
	}

	public static function schedule_events( $return_json = true ) {
		$event_ids = isset($_POST['event_ids']) ? $_POST['event_ids'] : null;
		$class = isset($_POST['class']) ? $_POST['class'] : null;
		$scheduled = isset($_POST['scheduled']) ? $_POST['scheduled'] : null;
		$scheduledType = isset($_POST['scheduledType']) ? $_POST['scheduledType'] : null;
//		$category = isset($_POST['category']) ? json_decode(stripslashes($_POST['category']), true) : array();
        if (is_array($_POST['category'])){
            $category = isset($_POST['category']) ? $_POST['category'] : array();
        }else{
            $category = isset($_POST['category']) ? json_decode(stripslashes($_POST['category']), true) : array();
        }

		$event_ids = json_decode(stripslashes($event_ids), true);
		if (count($event_ids) == 0) {
			return wp_send_json_error(__('Nothing selected', 'mec-advanced-importer'), 200);
		}

		$data = get_option('mec_advimp_' . strtolower($class) . '_current_event');
		if (!$data || !is_array($data) || count($data) == 0) {
			return wp_send_json_error(__('Data failed', 'mec-advanced-importer'), 200);
		}

		$date = new \DateTime( 'now', wp_timezone() );

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

		$ins_id = [];
		$ids = array_keys($event_ids);

		foreach ($data['data'] as $k => $v) {

			if (in_array($v['ID'], $ids)) {

				$date->add($interval);

				$next_datetime = $date->format('Y-m-d H:i:s');
				$next_timestamp = $date->getTimestamp();

				$ins_id[] = wp_insert_post(array(
					'post_title' => $v['title'],
					'post_type' => MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE,
					'post_content' => strtotime($next_datetime),
					'post_date' => date('Y-m-d H:i:s', $next_timestamp),
					'meta_input' => array(
						'event_id' => $v['ID'],
						'mec_source_event_id' => $event_ids[ $v['ID'] ],
						'event_class' => ucfirst($class),
						'event_link' => $v['link'],
						'event_category' => $category,
					),
					'post_status' => 'publish',
				));
			}
		}

		return $return_json ? wp_send_json_success('Success Add', 200) : $ins_id;
	}

	public static function backend_load_assets() {

		$reset = '?r=' . time();

		wp_enqueue_style('mec-advanced-importer-style', MEC_ADVANCED_IMPORTER_ASSETS . 'css/backend.css' . $reset);

		wp_register_script('mec-advanced-importer-script', MEC_ADVANCED_IMPORTER_ASSETS . 'js/backend.js' . $reset, array('jquery', 'jquery-ui-datepicker'));

		// Public variable send to page, js file usage the data
		$vars = array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'title' => array(
				'needauthentication' => __('Needs authentication', 'mec-advanced-importer'),
				'authenticated' => __('Authentication successful', 'mec-advanced-importer'),
			),
			'mev_advimp_all_data_accounts_events' => array(),
			'mev_advimp_all_data_accounts_events_implement' => array(),
		);
		foreach (self::$loaded_class as $class) {
			if (method_exists($class, 'public_vars')) {
				$vars[$class->name] = $class->public_vars();
			}
		}
		wp_localize_script('mec-advanced-importer-script', 'MEC_ADVIMP_VARS', $vars);

		wp_enqueue_script('mec-advanced-importer-script');

		//Include Select2
		wp_enqueue_script('mec-select2-script', self::$main->asset('packages/select2/select2.full.min.js'));
		wp_enqueue_style('mec-select2-style', self::$main->asset('packages/select2/select2.min.css'));

	}

	function preload() {
		$facebook = new \MEC_Advanced_Importer\Core\Tabs\Facebook();
		$facebook->preload();
		array_push(self::$loaded_class, $facebook);

		$meetup = new \MEC_Advanced_Importer\Core\Tabs\Meetup();
		$meetup->preload();
		array_push(self::$loaded_class, $meetup);

		$google = new \MEC_Advanced_Importer\Core\Tabs\Google();
		$google->preload();
		array_push(self::$loaded_class, $google);

		$eventbrite = new \MEC_Advanced_Importer\Core\Tabs\Eventbrite();
		array_push(self::$loaded_class, $eventbrite);

		$mecapi = new \MEC_Advanced_Importer\Core\Tabs\Mecapi();
		array_push(self::$loaded_class, $mecapi);

		$thirdparty = new \MEC_Advanced_Importer\Core\Tabs\Thirdparty();
		array_push(self::$loaded_class, $thirdparty);

		$ics = new \MEC_Advanced_Importer\Core\Tabs\ICS();
		// array_push(self::$loaded_class, $ics);
	}

	public function register_scheduled_import() {
		$labels = array(
			'name' => _x('Scheduled Import', 'post type general name', 'mec-advanced-importer'),
			'singular_name' => _x('Scheduled Import', 'post type singular name', 'mec-advanced-importer'),
			'menu_name' => _x('Scheduled Imports', 'admin menu', 'mec-advanced-importer'),
			'name_admin_bar' => _x('Scheduled Import', 'add new on admin bar', 'mec-advanced-importer'),
			'add_new' => _x('Add New', 'book', 'mec-advanced-importer'),
			'add_new_item' => __('Add New Import', 'mec-advanced-importer'),
			'new_item' => __('New Import', 'mec-advanced-importer'),
			'edit_item' => __('Edit Import', 'mec-advanced-importer'),
			'view_item' => __('View Import', 'mec-advanced-importer'),
			'all_items' => __('All Scheduled Imports', 'mec-advanced-importer'),
			'search_items' => __('Search Scheduled Imports', 'mec-advanced-importer'),
			'parent_item_colon' => __('Parent Imports:', 'mec-advanced-importer'),
			'not_found' => __('No imports found.', 'mec-advanced-importer'),
			'not_found_in_trash' => __('No Imports found in Trash.', 'mec-advanced-importer'),
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Scheduled Imports.', 'mec-advanced-importer'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'can_export' => false,
			'rewrite' => false,
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => array('title'),
			'menu_position' => 5,
		);

		register_post_type(MEC_ADVANCED_IMPORTER_SCHEDULED_POST_TYPE, $args);
	}

	public function register_history() {
		$labels = array(
			'name' => _x('Import History', 'post type general name', 'mec-advanced-importer'),
			'singular_name' => _x('Import History', 'post type singular name', 'mec-advanced-importer'),
			'menu_name' => _x('Import History', 'admin menu', 'mec-advanced-importer'),
			'name_admin_bar' => _x('Import History', 'add new on admin bar', 'mec-advanced-importer'),
			'add_new' => _x('Add New', 'book', 'mec-advanced-importer'),
			'add_new_item' => __('Add New', 'mec-advanced-importer'),
			'new_item' => __('New History', 'mec-advanced-importer'),
			'edit_item' => __('Edit History', 'mec-advanced-importer'),
			'view_item' => __('View History', 'mec-advanced-importer'),
			'all_items' => __('All Import History', 'mec-advanced-importer'),
			'search_items' => __('Search History', 'mec-advanced-importer'),
			'parent_item_colon' => __('Parent History:', 'mec-advanced-importer'),
			'not_found' => __('No History found.', 'mec-advanced-importer'),
			'not_found_in_trash' => __('No History found in Trash.', 'mec-advanced-importer'),
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Import History', 'mec-advanced-importer'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'show_in_nav_menus' => false,
			'can_export' => false,
			'rewrite' => false,
			'capability_type' => 'page',
			'has_archive' => false,
			'hierarchical' => false,
			'supports' => array('title'),
			'menu_position' => 5,
		);

		register_post_type(MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE, $args);
	}

	private static function init($class) {

		self::$factory = \MEC::getInstance('app.libraries.factory');
		self::$main = \MEC::getInstance('app.libraries.main');

		self::$class = $class;
		self::$factory->action('admin_menu', array($class, 'menus'), 20);
		self::$factory->action('wp_ajax_mec_advimp_check_request', array($class, 'check_request'));

	}

	public static function get_complete_meta($post_id, $meta_key) {
		global $wpdb;
		$mid = $wpdb->get_results($wpdb->prepare("SELECT `meta_id`,`meta_value` FROM $wpdb->postmeta WHERE post_id=%d AND meta_key=%s ORDER BY meta_id DESC", $post_id, $meta_key));
		if ($mid != '') {
			return $mid;
		}

		return false;
	}

	public static function check_request() {

		$reqid = isset($_POST['reqid']) ? trim($_POST['reqid']) : null;
		$seqid = isset($_POST['seqid']) ? trim($_POST['seqid']) : null;

		$req = get_page_by_title($reqid, OBJECT, MEC_ADVANCED_IMPORTER_HISTORY_POST_TYPE);
		$all = self::get_complete_meta($req->ID, 'request', false);

		if (!$all && $seqid > 10) {
			return wp_send_json_success(array(1 => 'finish ...'), 200);
		}

		if (!$all) {
			return wp_send_json_success(array(1 => 'waite ...'), 200);
		}

		$ret = array();
		foreach ($all as $k => $v) {
			$ret["_{$v->meta_id}"] = $v->meta_value;
		}

		return wp_send_json_success($ret, 200);
	}

	/**
	 * Load CSS Style
	 * @author Webnus <info@webnus.biz>
	 */
	public static function load_assets($class) {

		wp_enqueue_style('mec-featured-addon-style', MEC_ADVANCED_IMPORTER_ASSETS . 'css/backend.css');

		return true;
	}

	public static function menus() {
		add_submenu_page('mec-intro', __('MEC Advanced Import', 'mec'), __('MEC Advanced Import', 'mec'), 'manage_options', 'MEC-advimp', array(self::$class, 'advimp'));
	}

	public function advimp() {

		if (!is_admin()) {
			return false;
		}

		$arg = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'MEC-Facebook';

		$class_ex = explode('-', $arg);
		if (!$class_ex || count($class_ex) < 1) {
			error_log('class not explode');
			return false;
		}

		$className = self::$tabs_namespace . $class_ex[1];

		$tab = new $className();

		$path = MEC_ADVANCED_IMPORTER_DIR . DS . 'core' . DS . 'lib' . DS . 'main_ui.php';

		ob_start();
		include $path;
		echo $output = ob_get_clean();

	}

}
