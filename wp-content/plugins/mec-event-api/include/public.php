<?php

/**
 * MEC external public area
 * json load,show page
 */
class MEC_External_Public {

	public function init() {

		add_filter('template_include', function ($template) {

			if ( is_single() || is_page() ) {

				$external = isset($_GET['external']) ? $_GET['external'] : null;
				if ($external != 1) {

					return $template;
				}

				$settings = \MEC\Settings\Settings::getInstance()->get_settings('event_api');
				$show_header_and_footer = isset( $settings['show_header_and_footer'] ) && '1' == $settings['show_header_and_footer'] ? true : false;
				$host = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
				if( !$host || $show_header_and_footer ){
					return $template;
				}

				$template = MEC_External_Public::get_external_template();
			}

			return $template;
		}, 100);

		add_action('wp_head', function () {

			$external = isset($_GET['external']) ? $_GET['external'] : null;
			if( 'mec-external.php' == get_page_template_slug() || ( is_single() and get_post_type() == MEC_EXT_PTYPE && $external == 1 ) ) {
				add_filter('show_admin_bar', '__return_false');
				echo '<style>body{background-color:unset;}</style>';
			}
		});

		add_action( 'wp_footer', function(){
			?>
			<script>
				jQuery(document).ready(function($){
					var links = $('a');
					$.each(links, function(i,v){

						if ( window.location === window.parent.location ) {
							return;
						}

						var param = 'external=1';
						var href = $(v).attr('href');
						if( 'undefined' === typeof href ){
							return;
						}

						if( -1 != href.search('#') ){
							return;
						}

						if( -1 != href.search('&external=1') ){
							return;
						}
						href += (href.split('?')[1] ? '&':'?') + param;
						$(v).attr( 'href', href );
					});
				});
			</script>
			<?php
		});

		add_action('init', function () {
			$url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');

			if (strpos($url_path, 'mecExternalScript') != false || "{$url_path}"=='mecExternalScript') {

				header('Content-type: application/javascript;charset=utf-8');

				$id = isset($_GET['id']) ? $_GET['id'] : null;
				$t = isset($_GET['t']) ? $_GET['t'] : null;

				if (!$id || !$t) {
					echo "cannot found id or type";
					exit();
				}

				$model = new MEC_Sites_Model($id,$t);
				if(!$model->access()){
					echo "cannot access the page";
					exit();
				}

				$iframe_id = md5($id.$t.time().mt_rand(1,100));
				$i_id = mt_rand(1,100);

				$url = get_permalink($id);
				$title = get_the_title($id);

				$url = add_query_arg(array(
						'external' => '1',
					),
					$url
				);

				$uid = md5(mt_rand(1,100).time().$t.$id);
				$script_url = MEC_EXT_URL.'assets/iframeResizer.min.js';
				echo "document.write ('<div align=\"center\"><iframe area-label=\"{$title}\" title=\"{$title}\" id=\"mec_external_id_{$i_id}\" width=\"100%\" style=\"border: none;display: block;border=0\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"no\" scroll=\"no\" src=\"{$url}\" ></iframe><script src=\"{$script_url}\"></script><script>iFrameResize({ log: false,heightCalculationMethod : \'lowestElement\',interval: 3000 }, \"#mec_external_id_{$i_id}\")</script></div>');";
				exit();

			}
		});

	}

	public static function get_external_template() {

		$type = 'mec-events' == get_post_type() ? 'event' : 'calendar';
		switch( $type ){

			case 'event':
			case 'single':

				$template_name = 'single-mec-events-external';
				break;
			case 'calendar':
			default:

				$template_name = 'mec-external';
				break;
		}

		$filename = MEC_EXT_PATH . "them/{$template_name}.php";
		if( file_exists( $filename ) ) {

			return $filename;
		}

		return locate_template( "{$template_name}.php" );
	}
}