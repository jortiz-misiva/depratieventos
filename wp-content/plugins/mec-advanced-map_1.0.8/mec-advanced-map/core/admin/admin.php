<?php

namespace MEC_Map\Core\Admin;

use MEC_Map\Base;

// don't load directly.
if (!defined('ABSPATH')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit;
}

/**
 * Admin backend wp class.
 *
 * @author      author
 * @package     package
 * @since       1.0.0
 */
class MecAdmin {

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
		add_action('mec_location_after_add_form', array($class, 'location_add_form'));
		add_action('mec_location_after_edit_form', array($class, 'location_edit_form'));
		add_action('mec_location_after_new_form', array($class, 'location_new_form'));

		add_action('created_mec_location', array($class, 'location_save'), 2);
		add_action('edited_mec_location', array($class, 'location_save'), 2);

		// Selected the specific filter filed show
		add_action('mec_location_shortcode_filter', array($class, 'location_shortcode_filter'));

		add_action( 'mec_sf_options_location', array($class, 'sidebar_search_form_filter') );

		add_action( 'mec_map_options_after',  array($class, 'map_options') , 10, 1 );

		add_action('mec_shortcode_filters_save', array($class, 'location_shortcode_filter_save'), 2);

	}

	public static function sidebar_search_form_filter($arg=null){

		$skin = isset($arg['skin'])?$arg['skin']:null;
		$options = isset($arg['options'])?$arg['options']:null;

		if(!$skin || !$options){
			return;
		}

		$show = false;
		if(isset($options['location']) and isset($options['location']['type']) and $options['location']['type'] == 'dropdown'){
			$show = true;
		}
		$id = 'mec_sf_location_filters_'.$skin;

		?>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#mec_sf_<?php echo $skin; ?>_location').change(function(event) {
				if($(this).val()=='dropdown'){
					$('#<?php echo $id; ?>').show();
				}else{
					$('#<?php echo $id; ?>').hide();
				}
			});
		});
		</script>

 		<div id="<?php echo $id; ?>"
 			<?php if($show == false) echo 'style="display:none;"' ?>   >
          	<?php
			foreach (Base::get_map_fields() as $field => $title):
				?>
				<div class="mec-form-row">
                    <label class="mec-col-12" for="mec_sf_<?php echo $skin;?>_location"><?php _e($title, 'mec-map'); ?></label>
                    <select class="mec-col-12" name="mec[sf-options][<?php echo $skin; ?>][location][<?php echo $field;?>]" id="mec_sf_<?php echo $skin; ?>_location">
						<option value="0" <?php if(isset($options[$field]) and isset($options['location'][$field]) and $options['location'][$field] == '0') echo 'selected="selected"'; ?>><?php _e('Disabled', 'mec-map'); ?></option>
                        <option value="dropdown" <?php if(isset($options['location']) and isset($options['location'][$field]) and $options['location'][$field] == 'dropdown') echo 'selected="selected"'; ?>><?php _e('Dropdown', 'mec-map'); ?>
                        </option>
                    </select>
                </div>
			<?php endforeach; ?>
        </div>
		<?php
	}


	public static function map_options($settings){

		$map = isset($settings['default_maps_view'])?$settings['default_maps_view']:'google';
		$scrollwheel = isset($settings['default_maps_scrollwheel'])?$settings['default_maps_scrollwheel']:0;

		?>
		<script>
			jQuery(document).ready(function($){

				$('[name="mec[settings][default_maps_view]"]').on('change',function(){
					if( 'openstreetmap' === $(this).val() ){
						$('[name="mec[settings][google_maps_get_direction_status]"]').parent().parent().hide();
					}else{
						$('[name="mec[settings][google_maps_get_direction_status]"]').parent().parent().show();
					}
				});

				$('[name="mec[settings][default_maps_view]"]').trigger('change');
			});
		</script>
        <div class="mec-form-row">
            <label class="mec-col-3"><?php _e('Default map', 'mec-map'); ?></label>
            <div class="mec-col-4">
                <select name="mec[settings][default_maps_view]" >
                    <option value="google" <?php selected( 'google', $map ); ?> >Google</option>
                	<option value="openstreetmap" <?php selected( 'openstreetmap', $map ); ?>>Openstreetmap</option>
                </select>
            </div>
        </div>
        <div class="mec-form-row">
            <label class="mec-col-3"><?php _e('Scroll Wheel', 'mec-map'); ?></label>
            <div class="mec-col-4">
                <select name="mec[settings][default_maps_scrollwheel]">
                    <option value="0" <?php selected( '0', $scrollwheel ); ?> >Disable</option>
                	<option value="1" <?php selected( '1', $scrollwheel ); ?>>Enable</option>
                </select>
            </div>
        </div>
		<?php
	}

	private static function init_form_map($lat=null,$long=null,$lat_field='mec_latitude',$long_field='mec_longitude',$load_map_assets = false){

		$main = \MEC::getInstance('app.libraries.main');
		$settings = $main->get_settings();
		$map = isset($settings['default_maps_view'])?$settings['default_maps_view']:'google';
		$scrollwheel = isset($settings['default_maps_scrollwheel'])?(int)$settings['default_maps_scrollwheel']:0;

		$zoom = isset($settings['google_maps_zoomlevel'])?$settings['google_maps_zoomlevel']:14;

		if($load_map_assets){
			$main->load_map_assets(array('map'=>$map));
		}

		$mapid = md5(time().mt_rand(1,1000));

		if($lat == null){
			$lat = 39.283777996356946;
		}
		if($long == null){
			$long = -102.0624144054421;
		}

		?>
		<div class="mec-form-row">
			<div class="mec-col-8">
            	<div id="map_<?php echo $mapid; ?>" style="height: 400px;width: 100%;"></div>
			</div>
        </div>
		<script type="text/javascript">
        var map_<?php echo $mapid; ?>;
        var marker_<?php echo $mapid; ?>;
        var latitude = <?php echo $lat; ?> ;
        var longitude = <?php echo $long; ?>;

        function initMap_<?php echo $mapid; ?>_google() {

            var myLatLng = {lat: latitude, lng: longitude};

            map_<?php echo $mapid; ?> = new google.maps.Map(document.getElementById('map_<?php echo $mapid; ?>'), {
              center: myLatLng,
              zoom: <?php echo $zoom; ?>,
              disableDoubleClickZoom: true,
              scrollwheel: <?php echo $scrollwheel==1?'true':'false'; ?>

            });

            google.maps.event.addListener(map_<?php echo $mapid; ?>,'click',function(event) {
                document.getElementById('<?php echo $lat_field; ?>').value = event.latLng.lat();
                document.getElementById('<?php echo $long_field; ?>').value =  event.latLng.lng();
            });

            var marker_<?php echo $mapid; ?> = new google.maps.Marker({
              position: myLatLng,
              map: map_<?php echo $mapid; ?>,
              title: latitude + ', ' + longitude
            });

            marker_<?php echo $mapid; ?>.addListener('click', function(event) {
              document.getElementById('<?php echo $lat_field; ?>').innerHTML = event.latLng.lat();
              document.getElementById('<?php echo $long_field; ?>').innerHTML =  event.latLng.lng();
            });

            google.maps.event.addListener(map_<?php echo $mapid; ?>,'click',function(event) {

                if ( marker_<?php echo $mapid; ?> ) {
                     marker_<?php echo $mapid; ?>.setPosition(event.latLng);
                 }else{
                    marker_<?php echo $mapid; ?> = new google.maps.Marker({
                      position: event.latLng,
                      map: map_<?php echo $mapid; ?>,
                      title: event.latLng.lat()+', '+event.latLng.lng()
                    });
                 }

              document.getElementById('<?php echo $lat_field; ?>').innerHTML = event.latLng.lat();
              document.getElementById('<?php echo $long_field; ?>').innerHTML =  event.latLng.lng();

            });
        }

        function initMap_<?php echo $mapid; ?>_openstreetmap() {
        	console.log('openstreetmap');

        	map_<?php echo $mapid; ?>  = L.map('map_<?php echo $mapid; ?>').setView([latitude, longitude], <?php echo $zoom; ?>);
			L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			    maxZoom: 18,
			    scrollWheelZoom:<?php echo $scrollwheel==1?'true':'false'; ?>
			}).addTo(map_<?php echo $mapid; ?>);

			marker_<?php echo $mapid; ?> = new L.Marker([latitude, longitude]).addTo(map_<?php echo $mapid; ?>);

			map_<?php echo $mapid; ?>.on('click', function(e) {

				var lat = (e.latlng.lat);
			    var lng = (e.latlng.lng);
			    var newLatLng = new L.LatLng(lat, lng);
			    marker_<?php echo $mapid; ?>.setLatLng(newLatLng);

			    document.getElementById('<?php echo $lat_field; ?>').value = lat;
              	document.getElementById('<?php echo $long_field; ?>').value =  lng;

			});
        }

        jQuery(document).ready(function($) {
        	initMap_<?php echo $mapid.'_'.$map; ?>();
        });
        </script>
		<?php
	}

	public static function location_add_form() {

		foreach (Base::get_map_fields() as $field => $title):
			?>
	        <div class="form-field">
	            <label for="mec_<?php echo $field; ?>"><?php _e($title, 'mec-map');?></label>
	            <input type="text" name="<?php echo $field; ?>"  placeholder="<?php esc_attr_e($title . ' (Optional)', 'mec-map');?>" id="mec_<?php echo $field; ?>" value="" />
	        </div>
	       <?php

		endforeach;

        self::init_form_map();
	}

	public static function location_new_form() {

		foreach (Base::get_map_fields() as $field => $title):
			?>
	        <div class="mec-form-row">
	            <input type="text" name="mec[location][<?php echo $field; ?>]" id="mec_location_<?php echo $field; ?>" value="" placeholder="<?php echo __('Location', 'mec-map') . ' '. __($title, 'mec-map');?>" />
	        </div>
	        <?php
		endforeach;
	}

	public static function location_edit_form($term) {

		foreach (Base::get_map_fields() as $field => $title):
			$value = get_metadata('term', $term->term_id, $field, true);
			?>
	        <tr class="form-field">
	            <th scope="row" valign="top">
	                <label for="mec_<?php echo $field; ?>"><?php _e($title, 'mec-map');?></label>
	            </th>
	            <td>
	                <input class="mec-has-<?php echo $field; ?>" type="text" placeholder="<?php esc_attr_e($title . ' (Optional)', 'mec-map');?>" name="<?php echo $field; ?>" id="mec_<?php echo $field; ?>" value="<?php echo $value; ?>" />
	            </td>
	        </tr>
	        <?php

		endforeach;
		echo '<tr class="form-field"><td colspan="2">';
		$latitude = get_metadata('term', $term->term_id, 'latitude', true);
		$longitude = get_metadata('term', $term->term_id, 'longitude', true);
		 self::init_form_map($latitude,$longitude);
		echo '</td></tr>';
	}

	public static function location_save($term_id) {

		$post = $_POST;
		if (isset($_POST['mec']) && isset($_POST['mec']['location']) && count($_POST['mec']['location']) > 0) {
			$post = $_POST['mec']['location'];
		}

		foreach (Base::get_map_fields() as $field => $title) {
			$data = isset($post[$field]) ? sanitize_text_field($post[$field]) : '';
			update_term_meta($term_id, $field, $data);
		}

	}

	/**
	 *
	 * Additional settings for location
	 * @param  object $post post
	 * @return void
	 *
	 * @link https://wiki.openstreetmap.org/wiki/Zoom_levels openstreetmap zoom levels
	 */
	public static function location_shortcode_filter($post) {

		$selected_filter = explode(',', get_post_meta($post->ID, 'location_filter', true));
		$map = get_post_meta($post->ID, 'location_map', true);
		$zoom = get_post_meta($post->ID, 'location_map_zoom', true);
		$center_lat = get_post_meta($post->ID, 'location_center_lat', true);
		$center_long = get_post_meta($post->ID, 'location_center_long', true);
		$view_mode = get_post_meta($post->ID, 'location_view_mode', true);

		if(empty($zoom)){
			$zoom = '8';
		}

		?>

        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_location_map_zoom">Zoom</label>
            <select class="mec-col-4" name="mec_location_map_zoom" id="mec_location_map_zoom">
            	<?php for ($i=0; $i <= 20; $i++): ?>
            	<option value="<?php echo $i; ?>" <?php selected( $i, $zoom ); ?> ><?php echo $i; ?></option>
            	<?php endfor; ?>
            </select>
        </div>
        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_location_view_mode"><?php echo esc_html('View Mode', 'mec-map'); ?></label>
            <select class="mec-col-4" name="mec_location_view_mode" id="mec_location_view_mode">
            	<option value="normal" <?php selected( 'normal', $view_mode ); ?> ><?php echo esc_html('Normal', 'mec-map'); ?></option>
            	<option value="side" <?php selected( 'side', $view_mode ); ?> ><?php echo esc_html('Side', 'mec-map'); ?></option>
            </select>
        </div>
        <div class="mec-form-row">
            <label class="mec-col-4" for="mec_location_map_zoom">Center</label>
            <input type="text" placeholder="Center Lat" name="mec_location_map_center_lat" id="mec_location_map_center_lat" value="<?php echo $center_lat; ?>" />
            <input type="text" placeholder="Center Long" name="mec_location_map_center_long" id="mec_location_map_center_long" value="<?php echo $center_long; ?>"/>
        </div>
        <?php self::init_form_map($center_lat,$center_long,'mec_location_map_center_lat','mec_location_map_center_long',true); ?>

    	<?php

	}

	public static function location_shortcode_filter_save($post_id, $terms = null) {

		$map = isset($_POST['mec_location_map'])?$_POST['mec_location_map']:'google';
		$zoom = isset($_POST['mec_location_map_zoom'])?$_POST['mec_location_map_zoom']:'8';

		$center_lat = isset($_POST['mec_location_map_center_lat'])?$_POST['mec_location_map_center_lat']:null;
		$center_long = isset($_POST['mec_location_map_center_long'])?$_POST['mec_location_map_center_long']:null;

		$view_mode = isset($_POST['mec_location_view_mode'])?$_POST['mec_location_view_mode']:'normal';

		update_post_meta($post_id, 'location_map', $map);
		update_post_meta($post_id, 'location_map_zoom', $zoom);

		update_post_meta($post_id, 'location_center_lat', $center_lat);
		update_post_meta($post_id, 'location_center_long', $center_long);

		update_post_meta($post_id, 'location_view_mode', $view_mode);

	}

} //Mec_Admin