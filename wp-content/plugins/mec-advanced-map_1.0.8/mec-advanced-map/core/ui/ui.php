<?php

namespace MEC_Map\Core\Ui;

use MEC_Map\Base;

// don't load directly.
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}

/**
 * UI show wp class.
 *
 * @author      author
 * @package     package
 * @since       1.0.0
 */
class MecUi {

	/**
	* Instance of this class.
	*
	* @since   1.0.0
	* @access  public
	* @var     MEC_Map
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

	public function __construct() {
		self::init($this);
	}

	private static function init($class) {

		add_filter( 'mec_map_load_location_terms',  array(__CLASS__, 'location_load_terms'), 1, 3 );

		add_filter( 'mec_location_load_additional', array(__CLASS__, 'location_addational'), 1, 3 );

		add_filter('mec_filter_fields_search_form', array(__CLASS__, 'location_shortcode_filter_show'), 1, 2);

		add_filter('mec_map_assets_include', array(__CLASS__, 'load_map_assets'), 1, 3);

		add_filter('mec_map_load_script', array(__CLASS__, 'load_script'), 1, 3);

		add_filter('mec_map_tax_query', array(__CLASS__, 'tax_query'), 1, 3);

		add_action('mec_map_inner_element_tools', array(__CLASS__, 'tools_buttons'), 10, 1);

		add_filter('add_to_search_box_query', array(__CLASS__, 'search_box_query'), 1, 3);

		add_filter('mec_google_map_scroll_wheel', array(__CLASS__, 'filter_google_map_scroll_wheel'), 1, 3);

		add_filter('mec_get_marker_lightbox',array( __CLASS__, 'filter_get_marker_lightbox' ), 10, 3);

	}

	public static function filter_google_map_scroll_wheel( $status ){

		return (bool)\MEC\Settings\Settings::getInstance()->get_settings('default_maps_scrollwheel');
	}

	public static function location_load_terms($locations,$term){

		$map_fields = Base::get_map_fields();
		foreach ($map_fields as $k => $title) {
			$locations[$k] = get_metadata('term', $term->term_id, $k, true);
		}

		return $locations;
	}


	public static function location_addational( $current_events,$additional_location_ids,$event_locations){
		$additional_events = array();

		$current_event = $current_events[0];

		if(!is_array($additional_location_ids) || count($additional_location_ids)==0){
			return $current_events;
		}

	    $location_ids = array_unique(array_merge($event_locations,$additional_location_ids));

	    \MEC::import('app.skins.map', true);
	    $map = new \MEC_skin_map();
	    $map->args =  array(
	        'mec-skin'=>'map',
	        'post_type'=>'mec-events',
	        'post_status'=>'publish',
	        'tax_query'=>array(
	            'relation'=>'OR',
	             array(
	                'taxonomy'=>'mec_location',
	                'field'=>'term_id',
	                'terms'=>$location_ids
	            )
	        ),
	        'meta_key'=>'mec_start_day_seconds'
	    );
	    $additional_events = $map->search();

		if(count($additional_events)==0){
			return $current_events;
		}

	    $additional_locations = array();
	    foreach ($additional_events as $ka => $va) {

	       if(
	        isset($va->data->meta['mec_location_id']) &&
	        $va->data->meta['mec_location_id']!=$current_event->data->meta['mec_location_id']
	        ){
	            $additional_locations[$va->data->meta['mec_location_id']]=$va->data->locations[$va->data->meta['mec_location_id']];
	       }
	    }

	    $events = [$current_event];

	    foreach ($additional_locations as $ka => $va) {
	        $add = unserialize(serialize($current_event));
	        $add->data->meta['mec_location_id'] = $ka;
	        $add->data->locations = array($ka=>$va);
	        array_push($events, $add);
	    }

	    return $events;
	}

	public static function location_shortcode_filter_show($base_fields, $class) {

		$map_fields = Base::get_map_fields();

		$sf = (isset($_POST['sf']) ? $_POST['sf'] : array());
		$fields = '';
		$options = isset($class->sf_options) && isset($class->sf_options['location'])?$class->sf_options['location']:null;
		if($options==null){
			return $base_fields;
		}

		if(!isset($options['type'])){
			return $base_fields;
		}

		if($options['type']!='dropdown'){
			return $base_fields;
		}

		$fields_arr = [];
		$selected = array();

		//init the base fields selected
		foreach ($map_fields as $ki => $title) {
			if(isset($options[$ki]) && $options[$ki]=='dropdown' )
			{
				$fields_arr[$ki] = [];
				array_push($selected, $ki);
			}
		}

		$locations = get_terms(array(
			'hide_empty' => true,
			'taxonomy' => 'mec_location',
			'fields' => 'id=>name',
		));

		foreach( $locations as $location_id => $location_name ){

			foreach ( $fields_arr as $meta_key => $r ) {

				$location_meta_value = get_term_meta( $location_id, $meta_key, true );

				if( empty( $location_meta_value ) ){
					continue;
				}

				$ukey = $location_meta_value;

				$fields_arr[$meta_key][$ukey][ $location_id ] = $location_id;
			}

		}

		$fields .= '<div class="mec-dropdown-wrap">';

		global $wpdb;

		foreach ($fields_arr as $k => $v) {

			$label = isset( $map_fields[$k] ) ? $map_fields[$k] : '';

			$id = 'mec_sf_' . $k . '_' . $class->id;
			$fields .= '<div class="mec-dropdown-search">';
			$fields .= $class->sf_display_label == 1 ? '<label for="'. $id .'">'. __( $label, 'mec-map' ) .': </label>' : '';
			$fields .= '<i class="mec-sl-location-pin"></i>';
			$fields .= '<select  id="'. $id .'" class="postform" >';////
			$fields .= '<option value="">' . ucwords(str_replace('_', ' ', __( $label, 'mec-map' ))) . '</option>';

			if(count($v)>0){
				foreach ($v as $title => $t_ids) {

					$op_value = implode( ',', $t_ids );
					$selected_value = isset( $sf[ $k ] ) ? $sf[ $k ] : '';
					$selected = $selected_value === $op_value ? true : false;

					$fields .= '<option class="level-0" value="' . $op_value . '" '. selected( $selected , true, false ) .'>' . $title . '</option>';
				}
			}
			$fields .= '</select></div>';

		}

		$fields .= '</div>';

		return "{$fields}{$base_fields}";

	}

	public static function load_map_assets($base_assets, $class, $define_settings=null) {

		$css = array();

		$psettings = $class->get_settings();
		$default_maps_view = isset($psettings['default_maps_view'])?$psettings['default_maps_view']:'google';
		$map = isset($define_settings['map'])&&!empty($define_settings['map'])?$define_settings['map']:$default_maps_view;

		if ($map == 'openstreetmap') {

			$css = array(
					MECMAPASSETS . 'vendore/leaflet/leaflet.css',
					MECMAPASSETS . 'vendore/leaflet-marker-cluster/MarkerCluster.css',
					MECMAPASSETS . 'vendore/leaflet-marker-cluster/MarkerCluster.Default.css',
					MECMAPASSETS . 'vendore/leaflet-fullscreen/leaflet.fullscreen.css',
					MECMAPASSETS . 'css/openstreetmap.css');


			$base_assets = array(
				'js' => array(
					MECMAPASSETS . 'vendore/leaflet/leaflet.js',
					MECMAPASSETS . 'vendore/leaflet-marker-cluster/leaflet.markercluster.js',
					MECMAPASSETS . 'vendore/leaflet-fullscreen/Leaflet.fullscreen.min.js',
					MECMAPASSETS . 'js/openstreetmap.js',
				),
			);

		}else{
			$base_assets['js']['mec-googlemap-script'] = MECMAPASSETS . 'js/googlemap.js';
		}

		if(isset($define_settings['view_mode']) && $define_settings['view_mode']=='side'){
			array_push($css, MECMAPASSETS . 'css/bootstrap-grid.css');
			array_push($css, MECMAPASSETS . 'css/side.css');
			array_push($css, MECMAPASSETS . 'css/googlemap-side.css');
		}
		array_push($css, MECMAPASSETS . 'css/map-main.css');
		array_push($css, MECMAPASSETS . 'css/googlemap.css');
		$base_assets['css'] = $css;

		return $base_assets;
	}

	public static function load_script($javascript, $class, $define_settings) {

		$setting_zoom = isset($define_settings['google_maps_zoomlevel']) ? $define_settings['google_maps_zoomlevel'] : 14;
		$zoom = isset($class->atts['location_map_zoom']) && !empty($class->atts['location_map_zoom']) ? $class->atts['location_map_zoom'] : $setting_zoom;

		$lat = isset($class->atts['location_center_lat']) && !empty($class->atts['location_center_lat']) ? $class->atts['location_center_lat'] : "";
		$long = isset($class->atts['location_center_long']) && !empty($class->atts['location_center_long']) ? $class->atts['location_center_long'] : "";

		$define_map = isset($define_settings['map'])?$define_settings['map']:null;
		$map = isset($define_settings['default_maps_view'])?$define_settings['default_maps_view']:$define_map;

		$use_orig_map = isset($class->atts['use_orig_map'])?$class->atts['use_orig_map']:false;
		$scrollwheel = isset($define_settings['default_maps_scrollwheel']) && (int)$define_settings['default_maps_scrollwheel']==1 ? 'true' : 'false';

		if ($map == 'openstreetmap') {

			$javascript = '<script type="text/javascript">
		    jQuery(document).ready(function()
		    {
		        jQuery("#mec_map_canvas' . $class->id . '").mecOpenstreetMaps(
		        {
					show_on_openstreetmap_text: "'. __('Show on OpenstreetMap','mec-map') .'",
		            id: "' . $class->id . '",
		            atts: "' . http_build_query(array('atts' => $class->atts), '', '&') . '",
		            zoom: ' . $zoom . ',
		            scrollwheel: '.$scrollwheel.',
		            markers: ' . json_encode($class->render->markers($class->events)) . ',
		            HTML5geolocation: "' . $class->geolocation . '",
		            ajax_url: "' . admin_url('admin-ajax.php', NULL) . '",
		            sf:
		            {
		                container: "' . ($class->sf_status ? '#mec_search_form_' . $class->id : '') . '",
		            },
		            latitude: "' . $lat . '",
		            longitude: "' . $long . '",
		            fields: ' . json_encode(array_keys(Base::get_map_fields())) . '
		        });
		    });
		    </script>';
		}else if($use_orig_map == false){
			$javascript = '<script type="text/javascript">
		    jQuery(document).ready(function()
		    {
		        jQuery("#mec_map_canvas'.$class->id.'").mecGoogleMaps(
		        {
					show_on_map_text: "'. __('Show on Google Map','mec-map') .'",
		            id: "'.$class->id.'",
		            atts: "'.http_build_query(array('atts'=>$class->atts), '', '&').'",
		            zoom: '.$zoom.',
		            scrollwheel: '.$scrollwheel.',
		            icon: "'.apply_filters('mec_marker_icon', $class->main->asset('img/m-04.png')).'",
		            styles: '.((isset($define_settings['google_maps_style']) and trim($define_settings['google_maps_style']) != '') ? $class->main->get_googlemap_style($define_settings['google_maps_style']) : "''").',
		            markers: '.json_encode($class->render->markers($class->events)).',
		            HTML5geolocation: "'.$class->geolocation.'",
		            clustering_images: "'.$class->main->asset('img/cluster1/m').'",
		            getDirection: 0,
		            ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
		            sf:
		            {
		                container: "'.($class->sf_status ? '#mec_search_form_'.$class->id : '').'",
		            },
		            latitude: "' . $lat . '",
		            longitude: "' . $long . '",
		            fields: ' . json_encode(array_keys(Base::get_map_fields())) . '
		        });
		    });
		    </script>';
		}

		return $javascript;

	}

	public static function tools_buttons($define_settings) {

		if (isset($define_settings['map']) && $define_settings['map'] == 'google') {
			return;
		}
		?>

		<div class="leaflet-top leaflet-right">
			<button id="mec-map-next" class="mec-map-btn leaflet-control"><span><?php _e('Next','mec-map'); ?></span>
		    	<i class="fa fa-chevron-right"></i>
		    </button>
	       	<button id="mec-map-prev" class="mec-map-btn leaflet-control"><i class="fa fa-chevron-left"></i>
		    	<span><?php _e('Prev','mec-map'); ?></span>
		    </button>

		    <button id="mec-map-myposition" class="mec-map-btn leaflet-control"><span><?php _e('My Position','mec-map'); ?></span>
		    	<i class="fa fa-chevron-right"></i>
		    </button>
	    </div>

	    <!-- <div class="leaflet-top leaflet-right mec-leaflet-searchbox">
	    	 <input type="text" id="map-search" value="" onclick="" class="mec-map-input leaflet-control" placeholder="Search" />
	    </div> -->
		<?php

	}

	public static function search_box_query($atts, $sf) {

		foreach (Base::get_map_fields() as $k => $title) {
			if (isset($sf[$k]) && !empty($sf[$k])) {
				$atts[$k] = trim($sf[$k]);
			}
		}

		return $atts;

	}

	public static function tax_query($tax_query, $atts) {

		foreach (Base::get_map_fields() as $k => $title) {

			if (isset($atts[$k]) && !empty($atts[$k]) ) {

				$tax_query[] = array(
					'taxonomy' => 'mec_location',
					'field' => 'term_id',
					'terms' => is_array($atts[$k]) ? $atts[$k] : explode(',',$atts[$k]),
				);
			}
		}

		return $tax_query;

	}

	public static function filter_get_marker_lightbox( $content, $event, $date_format, $skin_style = 'classic' ){

		if( false === strpos( $skin_style, 'liquid' ) ){

			return $content;
		}

		$mainClass = \MEC\Base::get_main();
		$link = $mainClass->get_event_date_permalink($event, (isset($event->date['start']) ? $event->date['start']['date'] : NULL));
		$infowindow_thumb = trim($event->data->featured_image['thumbnail']) ? '<div class="mec-event-image"><a data-event-id="'.esc_attr($event->data->ID).'" href="'.esc_url($link).'"><img src="'.esc_url($event->data->featured_image['thumbnail']).'" alt="'.esc_attr($event->data->title).'" /></a></div>' : '';
		$event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
		$event_start_date_day = !empty($event->date['start']['date']) ? $mainClass->date_i18n('d', strtotime($event->date['start']['date'])) : '';
		$event_start_date_month = !empty($event->date['start']['date']) ? $mainClass->date_i18n('M', strtotime($event->date['start']['date'])) : '';
		$event_start_date_year = !empty($event->date['start']['date']) ? $mainClass->date_i18n('Y', strtotime($event->date['start']['date'])) : '';
		$start_time = !empty($event->data->time['start']) ? $event->data->time['start'] : '';
		$end_time = !empty($event->data->time['end']) ? $event->data->time['end'] : '';

		$content = '
		<div class="mec-wrap">
			<div class="mec-map-lightbox-wp mec-event-list-classic">
				<article class="'.((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : '').'mec-event-article mec-clear">
					'.\MEC_kses::element($infowindow_thumb).'
					<a data-event-id="'.esc_attr($event->data->ID).'" href="'.esc_url($link).'"><div class="mec-event-date mec-color"><i class="mec-sl-calendar"></i> <span class="mec-map-lightbox-month">'.esc_html($event_start_date_month).'</span><span class="mec-map-lightbox-day"> ' . esc_html($event_start_date_day) . '</span><span class="mec-map-lightbox-year"> ' . esc_html($event_start_date_year) .  '</span></div></a>
					<h4 class="mec-event-title">
					<div class="mec-map-time" style="display: none">'.\MEC_kses::element($mainClass->display_time($start_time, $end_time)).'</div>
					<a data-event-id="'.esc_attr($event->data->ID).'" class="mec-color-hover" href="'.esc_url($link).'">'.esc_html($event->data->title).'</a>
					'.\MEC_kses::element($mainClass->get_flags($event)).'
					</h4>
				</article>
			</div>
		</div>';

		return $content;
	}

}