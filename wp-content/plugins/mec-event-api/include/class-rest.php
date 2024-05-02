<?php

/**
 *	mec external api rest api submit
 *	on the request for event/calendar extract data and r4eturned objects
 *
 * @category mec-external-admin
 * @package mec-external
 * @author Webnus Team <info@webnus.net>
 * @version 0.0.1
 */
class MEC_External_Rest {

	public function init() {
		add_action('rest_api_init', function () {
			register_rest_route('mecexternal/v1', '/event/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'events'),
			));

			register_rest_route('mecexternal/v1', '/calendar/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'Calendars'),
			));

			register_rest_route('mecexternal/v1', '/events/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'calendar_events'),
			));
		});
	}

	public static function get_post_meta($post_id, $skip = false){
        // Cache
        $cache = \MEC\Base::get_main()->getCache();

        // Return From Cache
        return $cache->rememberOnce('meta-pm-'.$post_id.'-'.($skip ? 1 : 0), function() use($post_id, $skip)
        {
            $raw_data = get_post_meta($post_id, '', true);
            $data = array();

            // Invalid Raw Data
            if(!is_array($raw_data)) return $data;

            foreach($raw_data as $key=>$val)
            {
                $data[$key] = isset($val[0]) ? (!is_serialized($val[0]) ? $val[0] : unserialize($val[0])) : NULL;
            }

            return $data;
        });
    }

	/**
	 * the request type is event
	 * @param  array $data requested paramer
	 * @return object       event data object
	 */
	function events($data) {

		add_filter( 'mec_render_event_data', array( __CLASS__, 'filter_render_event_data' ), 10, 2 );

		$event_id = $data['id'];
		$render = \MEC::getInstance('app.libraries.render');
		$ret =  $render->data( $event_id );

		remove_filter( 'mec_render_event_data', array( __CLASS__, 'filter_render_event_data' ) );

		return apply_filters( 'mec_external_event', $ret );

	}

	/**
	 * the request type is calendar
	 * @param  array $data requested parameter
	 * @return object       calendar object
	 */
	function Calendars($data) {

		add_filter( 'mec_calendar_atts', array( __CLASS__, 'filter_calendar_atts' ) );

		add_filter( 'mec_render_event_data', array( __CLASS__, 'filter_render_event_data' ), 10, 2 );

		$render = MEC::getInstance('app.libraries.render');
		$ret =  $render->shortcode_json($data);

		$ret = apply_filters( 'mec_external_calendar', $ret );

		remove_filter( 'mec_calendar_atts', array( __CLASS__, 'filter_calendar_atts' ) );
		remove_filter( 'mec_render_event_data', array( __CLASS__, 'filter_render_event_data' ) );

		return $ret;

	}

	/**
	 * the request type is calendar events
	 * @param  array $data requested parameter
	 * @return array       calendar object
	 */
	public function calendar_events($data) {

		add_filter( 'mec_calendar_atts', array( __CLASS__, 'filter_calendar_atts' ) );

		$render = MEC::getInstance('app.libraries.render');
		$ret =  $render->shortcode_json($data);

		$ret = apply_filters( 'mec_external_calendar', $ret );

        $events = array();
		$json = is_array( $ret['content_json'] ) ? $ret['content_json'] : array();
        foreach( $json as $date => $_events ){

            $events = array_merge( $events, $_events );
        }

        foreach( $events as $k => $event ){

            $event_data = array_merge( (array)$event->data, (array)$event->date );

            $events[ $k ] = $event_data;
        }

		remove_filter( 'mec_calendar_atts', array( __CLASS__, 'filter_calendar_atts' ) );

		return $events;
	}

	/**
	 * Return skin atts
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	public static function filter_calendar_atts( $atts ){

		$settings = \MEC\Settings\Settings::getInstance()->get_settings('event_api');
		$default_limit = isset( $settings['limit'] ) && $settings['limit'] ? $settings['limit'] : 12;

		$limit = isset( $_REQUEST['limit'] ) && is_numeric( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : $default_limit;
		$paged = isset( $_REQUEST['paged'] ) && absint( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;

		foreach( $atts['sk-options'] as $skin => $args ){

			$atts['sk-options'][ $skin ]['limit'] = $limit;
			$atts['sk-options'][ $skin ]['paged'] = $paged;
		}

		return $atts;
	}

	/**
	 * Return event data
	 *
	 * @param object $data
	 * @param int $event_id
	 *
	 * @return array
	 */
	public static function filter_render_event_data( $data, $event_id ){

		$data->meta = self::get_post_meta( $event_id );

		return $data;
	}
}