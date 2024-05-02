<?php

namespace MEC_Advanced_Speaker\Core\Lib;

/**
 * Webnus MEC featured class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Speaker_Lib_Factory {

	/**
	 * Instance of this class.
	 *
	 * @since   1.0.0
	 * @access  public
	 * @var     MEC_Advanced_Speaker
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

	/**
	 * Constructor method
	 * @author Webnus <info@webnus.biz>
	 */
	public function __construct() {
		add_action('init', function () {
			add_action('admin_enqueue_scripts', array($this, 'backend_load_assets'), 11, 1);

			self::frontend_load_assets($this);

		}, 10, 1);
	}

	/**
	 * Loaded assets
	 * @author Webnus <info@webnus.biz>
	 */
	public static function backend_load_assets() {

		wp_enqueue_style('mec-advanced-addon-style', MEC_ADVANCED_SPEAKER_ASSETS . 'css/backend.css');
        wp_enqueue_script( 'mec-featured-addon-script', MEC_ADVANCED_SPEAKER_ASSETS . 'js/backend.js', array( 'jquery' ), MEC_ADVANCED_SPEAKER_VERSION, true );

	}

	/**
	 * Loaded assets
	 * @author Webnus <info@webnus.biz>
	 */
	public static function frontend_load_assets() {

		$reset = '?r=' . time();
		wp_enqueue_style('mec-advanced-speaker-style', MEC_ADVANCED_SPEAKER_ASSETS . 'css/frontend.css' . $reset);
		wp_enqueue_script('mec-advanced-speaker-frontend-script', MEC_ADVANCED_SPEAKER_ASSETS . 'js/frontend.js' . $reset, array('jquery'));

	}

	public static function extract_attrs($atts = null, $for_mec = false, $section = 'speaker') {

		$options = get_option('mec_options', array());
		$settings = array();

		if ($atts == null || empty($atts)) {
			$atts = array();
		}

		if (isset($options['settings']) && isset($options['settings']['advanced_speaker'])) {
			$settings = $options['settings']['advanced_speaker'];
		}

		if (
			!isset($atts['limit_events'])
			&& ($for_mec === true)
			&& isset($settings["{$section}_event_limit"])
		) {
			$atts['limit'] = $settings["{$section}_event_limit"];
			$atts['limit_events'] = $settings["{$section}_event_limit"];
			$sec = "{$section}_event_limit";
		}

		// limit from db saved config
		if (!isset($atts['limit_events']) && !isset($atts['out_style']) && isset($settings["{$section}_limit"])) {
			$atts['limit_events'] = $settings["{$section}_limit"];
		}
		if (!isset($atts['limit']) && !isset($atts['out_style']) && isset($settings["{$section}_limit"])) {
			$atts['limit'] = $settings["{$section}_limit"];
		}

		if(!isset($atts['events_style']) && $for_mec==true && isset($settings["{$section}_event_skin"])){
			$atts['events_style'] = $settings["{$section}_event_skin"];
		}

		// showed style from db saved config
		if (!isset($atts['events_style']) && isset($settings["{$section}_skin"])) {
			$atts['events_style'] = $settings["{$section}_skin"];
		}
		if (!isset($atts['display_style']) && isset($settings["{$section}_skin"])) {
			$atts['display_style'] = $settings["{$section}_skin"];
		}

		// cols per row from db saved config
		if (!isset($atts['cols']) && isset($settings["{$section}_cols"])) {
			$atts['cols'] = $settings["{$section}_cols"];
		}

		// load more button from db saved config
		if (!isset($atts['load_more']) && isset($settings["{$section}_load_more"])) {
			$atts['load_more'] = $settings["{$section}_load_more"] == '1' ? 'true' : 'false';
		}


		if(!isset($atts['show_event_list']) && isset($settings["{$section}_show_event_list"])){
			$atts['show_event_list'] = $settings["{$section}_show_event_list"]=='0'?'false':'true';
		}

		$ret = array(
			'id' => isset($atts['id']) ? trim($atts['id']) : mt_rand(1,999),
			'limit_events' => isset($atts['limit_events']) ? trim($atts['limit_events']) : MEC_ADVANCED_SPEAKER_ROWS_LIMIT,
			'limit' => isset($atts['limit']) ? trim($atts['limit']) : MEC_ADVANCED_SPEAKER_ROWS_LIMIT,
			'only_ongoing_events' => isset($atts['only_ongoing_events']) ? trim($atts['only_ongoing_events']) == 'true' : false,
			'load_more' => isset($atts['load_more']) ? trim($atts['load_more']) == 'true' : false,
			'events_style' => isset($atts['events_style']) ? trim($atts['events_style']) : 'list',
			'exclude_details' => isset($atts['exclude_details']) ? explode(',', trim($atts['exclude_details'])) : array(),
			'display_style' => isset($atts['display_style']) ? trim($atts['display_style']) : 'list',
			'exclude' => isset($atts['exclude']) ? explode(',', trim($atts['exclude'])) : array(),
			'cols' => isset($atts['cols']) ? trim($atts['cols']) : 3,
			'show_event_list' => isset($atts['show_event_list']) && trim($atts['show_event_list'])=='false'?false:true,
			'html_option'=>isset($atts['html_option'])?$atts['html_option']:'',
			'show_only_past_events' => isset($atts['show_only_past_events']) ? 'true' == $atts['show_only_past_events'] : false,

			'filter' => isset($atts['filter']) ? 'true' == $atts['filter'] : false,
			'search' => isset($atts['search']) ? 'true' == $atts['search'] : false,

			'order' => $atts['order'] ?? 'DESC',
			'order_by' => $atts['order_by'] ?? 'id',
		);

		switch($ret['order_by']){
			case 'name':
				$ret['order_by'] = 'name';
				break;
			case 'added_date':
				$ret['order_by'] = 'term_id';
				break;
			case 'ongoing_events':

				break;
			case 'all_events':
				$ret['order_by'] = 'count';
				break;
		}

		$ret['count'] = $ret['cols'];

		$exclude_details = array();

		foreach ($ret['exclude_details'] as $k) {
			$exclude_details[$k] = "{$k}";
		}

		$ret['exclude_details'] = $exclude_details;

		if ($for_mec == true) {

			$key = 'list';

			if ($ret['events_style'] == 'grid') {
				$key = 'tile';
			}

			$ret['sk-options'] = array(
				"{$key}" => array(
					'load_more_button' => $atts['load_more'],
					'count' => isset($atts['cols']) ? $atts['cols'] : 3,
				),
			);
			$ret['sk-options'][$key]['limit'] = $ret['limit_events'];
			$ret['limit'] =$ret['limit_events'];

		}

		return $ret;
	}

	public static function get_meta_key_val($id,$exclude=array()) {
		$meta = get_term_meta($id, '', true);

		$ret = array(
			'job_title',
			'tel',
			'email',
			'website',
			'url',
			'facebook',
			'twitter',
			'instagram',
			'linkedin',
			'thumbnail',
			'featured',
			'address'
		);

		if (!$meta) {
			return $ret;
		}

		foreach ($meta as $k => $v) {

			if(in_array("{$k}", (array)$exclude)){
				unset($ret[$k]);
				continue;
			}

			$ret[$k] = $v[0];
		}

		if (isset($ret['url']) && !empty($ret['url']) &&  !isset($exclude['website'])  ) {
			$ret['website'] = $ret['url'];
		}

		return $ret;

	}


}
