<?php

namespace MEC_Advanced_Location\Core\Lib;

/**
 * Webnus MEC featured class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Advanced_Location_Lib_Skin {

	public $order_by = 'id';
	public $order = 'ASC';
	public $section = 'location';
	public $limit = 0;
	public $offset = 0;
	public $count = 3;
	public $data;
	public $found;
	public $next_offset;
	public $load_more_button = true;
	public $exclude = array();
	public $style = 'list';
	public $out_style = null;
	public $id;

	public $request;

	public $tpl_path;
	public $render_path;
	public $return_items = false;
	public $dir;
	public $atts;

	public $settings = array();
	public $single_page_id = false;
	public $main;

	public $search = false;
	public $search_in = false;
	public $filter = false;

	/**
	 * Constructor method
	 * @author Webnus <info@webnus.biz>
	 */
	public function __construct() {

		// MEC request library
		$this->request = \MEC::getInstance('app.libraries.request');
		$this->main = \MEC::getInstance('app.libraries.main');
		$this->main->load_map_assets(array('map' => 'google'));

		$options = get_option('mec_options', array());

		if (isset($options['settings']) && $options['settings']['advanced_location']) {
			$this->settings = $options['settings']['advanced_location'];
			$this->single_page_id = isset($this->settings['single_page']) ? $this->settings['single_page'] : false;
		}

		do_action('mec_shortcode_list_terms_init');
	}

	/**
	 * Generates skin output
	 * @author Webnus <info@webnus.biz>
	 * @return string
	 */
	public function output() {

		$taxonomy = 'mec_' . $this->section;
		ob_start();
		do_action('mec_before_list_term_output',$this,$taxonomy);
		include $this->tpl_path;
		do_action('mec_after_list_term_output',$this,$taxonomy);
		return ob_get_clean();
	}

	/**
	 * Initialize the skin
	 * @author Webnus <info@webnus.biz>
	 * @param array $atts
	 */
	public function initialize($atts = array()) {

		if (empty($atts) || !is_array($atts)) {
			$atts = array();
		}

		$this->atts = $atts;

		// Generate an ID for the sking
		$this->id = isset($this->atts['id']) ? $this->atts['id'] : mt_rand(1, 999);

		// Set the ID
		if (!isset($this->atts['id'])) {
			$this->atts['id'] = $this->id;
		}

		// HTML class
		$this->html_class = '';
		if (isset($this->atts['html-class']) and trim($this->atts['html-class']) != '') {
			$this->html_class = $this->atts['html-class'];
		}

		// Init MEC
		$this->args['mec-init'] = true;

		$this->order = isset($atts['order']) ? $atts['order'] : 'ASC';
		$this->order_by = isset($atts['order_by']) ? $atts['order_by'] : 'id';

		// Found Events
		$this->found = 0;

		// Detect Load More Running
		$this->loadMoreRunning = false;

		$this->limit = isset($atts['limit']) ? $atts['limit'] : MEC_ADVANCED_LOCATION_ROWS_LIMIT;
		$this->load_more_button = isset($atts['load_more']) ? $atts['load_more'] : false;

		$this->exclude = isset($atts['exclude']) ? $atts['exclude'] : false;

		$this->style = 'list';
		if ($atts['display_style'] == 'grid') {
			$this->style = 'grid';
		}

		$theme_path = get_template_directory() .DS. 'webnus' .DS. 'mec-advanced-location' . DS . 'skins' . DS;

		$custom_tpl_path = $theme_path . $this->style . DS . 'tpl.php';
		if( file_exists( $custom_tpl_path ) ) {

			$this->tpl_path = $custom_tpl_path;
		} else {

			$this->tpl_path = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'skins' . DS . $this->style . DS . 'tpl.php';
		}

		$custom_render_path = $theme_path . $this->style . DS . 'render.php';
		if( file_exists( $custom_render_path ) ) {

			$this->render_path = $custom_render_path;
		} else {

			$this->render_path = MEC_ADVANCED_LOCATION_DIR . 'core' . DS . 'skins' . DS . $this->style . DS . 'render.php';
		}

		$this->count = $atts['cols'];

		$this->out_style = isset($atts['out_style']) && !empty($atts['out_style']) ? $atts['out_style'] : null;

		$this->search = isset($atts['search']) ? $atts['search'] : false;
		$this->search_in = isset($atts['search_in']) ? $atts['search_in'] : false;
		$this->filter = isset($atts['filter']) ? $atts['filter'] : false;

	}

	/**
	 * Perform the search
	 * @author Webnus <info@webnus.biz>
	 * @return array of objects \stdClass
	 */
	public function search() {

		$taxonomy = 'mec_' . $this->section;
		$args = array(
			'taxonomy' => $taxonomy,
			'orderby' => $this->order_by,
			'order' => $this->order,
			'hide_empty' => false,
			'exclude' => $this->exclude,
			'number' => $this->limit,
			'offset' => $this->offset,
			'count' => true,
			'fields' => 'all',
		);

		$args = apply_filters('mec_list_term_query_args',$args,$taxonomy,$this);

		if ($this->out_style != null) {

			$args['meta_query'][] = array(
				'key' => $this->out_style,
				'value' => 1,
			);
		}

		$d = get_terms($args);

		return $d;
	}

	/**
	 * Run the search command
	 * @author Webnus <info@webnus.biz>
	 * @return array of objects
	 */
	public function fetch() {
		$this->data = $this->search();
		$this->found = count($this->data);
		$this->next_offset = $this->found + $this->offset;

		return $this->data;
	}

	/**
	 * Load more events for AJAX requert
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	public function load_more() {

		$atts = isset( $_POST['atts'] ) && is_array( $_POST['atts'] ) ? $_POST['atts'] : array();
		$this->style = isset( $_POST['mec_style'] ) ? $_POST['mec_style'] : 'list';

		// Initialize the skin
		$this->initialize($atts);

		// Return the events
		$this->return_items = true;

		$this->offset = isset( $_POST['mec_offset'] ) ? $_POST['mec_offset'] : 1;
		$this->limit = isset( $_POST['mec_limit'] ) ? $_POST['mec_limit'] : 1;

		// Fetch the events
		$this->fetch();

		// Return the output
		$output = $this->output();

		echo json_encode($output);
		exit;
	}

	public function count_of_ongoing($id) {
		$path = \MEC::import('app.skins.list', true, true);
		$skin_class_name = 'MEC_skin_list';
		$SKO = new $skin_class_name();
		$atts[$this->section] = $id;
		$atts['from_advanced_location_addon'] = 'count_ongoing';
		$SKO->initialize($atts);
		$SKO->skin = 'list';
		$SKO->style = 'fluent';
		$SKO->show_ongoing_events = true;
		$SKO->skin_options['start_date_type'] = 'today';
		$SKO->fetch();
		return $SKO->found;

	}

	public function count_of_all($id) {

		$args = array(
			'post_type' => 'mec-events',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'tax_query' => array(
				'relation' => 'AND',
				array(
					'taxonomy' => "mec_{$this->section}",
					'field' => 'id',
					'terms' => array($id),
				),
			),
		);

		$query = new \WP_Query($args);
		return (int) $query->post_count;

	}

	public function init_form_map($lat = null, $long = null,$height=243,$width=229) {

		$mapid = md5(time() . mt_rand(1, 1000));

		if ($lat == null) {
			$lat = 39.283777996356946;
		}
		if ($long == null) {
			$long = -102.0624144054421;
		}

		$style = 'height:'.$height.'px;';

		if($width == 'max'){
			$style .= 'width:100%;';
		}else{
			$style .= 'width:'.$width.'px;';
		}

		?>

        <div id="map_<?php echo $mapid; ?>" style="<?php echo $style; ?>"></div>

		<script type="text/javascript">
        var map_<?php echo $mapid; ?>;
        var marker_<?php echo $mapid; ?>;

        function initMap_<?php echo $mapid; ?>() {
            var latitude = <?php echo $lat; ?> ;
            var longitude = <?php echo $long; ?>;

            var myLatLng = {lat: latitude, lng: longitude};

            map_<?php echo $mapid; ?> = new google.maps.Map(document.getElementById('map_<?php echo $mapid; ?>'), {
              center: myLatLng,
              zoom: 8,
              disableDoubleClickZoom: true,
            });

            var marker_<?php echo $mapid; ?> = new google.maps.Marker({
              position: myLatLng,
              map: map_<?php echo $mapid; ?>,
              title: latitude + ', ' + longitude
            });
        }

        jQuery(document).ready(function($) {
        	initMap_<?php echo $mapid; ?>();
        });
        </script>
		<?php
	}

	public function single_page_url($id, $website = null) {

//		if( is_null( $website ) ){
//
//			$website = get_term_meta( $id, 'url', true );
//		}

//		$load_type = isset($this->settings[$this->section . '_detaile']) ? $this->settings[$this->section . '_detaile'] : 'option_website';
		$link = get_permalink($this->single_page_id);
		$link = add_query_arg(array(
			'fesection' => $this->section,
			'feparam' => $id,
		), $link);

//		switch ($load_type) {
//		case 'option_website':
//			{
//				if (!empty($website)) {
//					return $website;
//				}
//
//				return $link;
//			}
//			break;
//
//		case 'force_website':{
//				if (!empty($website)) {
//					return $website;
//				}
//				return '#';
//			}break;
//
//		case 'force_single':{
//				return $link;
//			}break;
//		}

        return $link;
	}

	public function single_page_link_target($id) {

		return isset($this->settings[$this->section . '_link_target']) ? $this->settings[$this->section . '_link_target'] : '_blank';
	}
}
