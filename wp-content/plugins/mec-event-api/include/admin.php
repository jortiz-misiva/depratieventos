<?php

/**
 *	mec external admin menu page
 *	1. add External button to mec event/shortcode
 *	2. add cutome page for showed the javascript/api request/access
 *
 * @category mec-external-admin
 * @package mec-external
 * @author Webnus Team <info@webnus.net>
 * @version 0.0.1
 */
class MEC_External_Admin {

	/**
	 * initialize the admin menu page
	 * @return void
	 */
	public function init() {

		$this->add_dashboard_menu();

		$this->add_table_button();

		add_filter('mec-settings-items-settings', array($this, 'add_setting_menu'), 1);
		add_action('mec-settings-page-before-form-end', array($this, 'setting_menu_content'));
	}

	/**
	 * add dashboard menu, hidden page for mec-external admin page
	 */
	private function add_dashboard_menu() {

		add_action('admin_menu', array($this, 'menus'), 20, 1);
	}

	/**
	 * callback function for dashboard add-external page
	 * @return void
	 */
	public function menus() {

		add_submenu_page(null, __('MEC - add external', 'mec-event-API'), __('MEC - add external', 'mec-event-API'), 'manage_options', 'MEC-add-external', array($this, 'external_add'));
	}

	/**
	 * add external button to mec plugin on shortcode/event page
	 */
	private function add_table_button() {

		add_filter('post_row_actions', array($this, 'mec_external_action_row'), 10, 2);

	}

	/**
	 * callbacl function action hook for wp_DataTable
	 * @param  array $actions action row
	 * @param  object $post    post object row on row
	 * @return  array         custome action button
	 */
	public function mec_external_action_row($actions, $post) {
		if ($post->post_type == MEC_EXT_PTYPE) {
			$actions['to-external'] = '<a href="' . admin_url() . 'admin.php?page=MEC-add-external&event=' . $post->ID . '"><b>Create API</b></a>';
		}
		if ($post->post_type == MEC_EXT_CTYPE) {
			$actions['to-external'] = '<a href="' . admin_url() . 'admin.php?page=MEC-add-external&Calendar=' . $post->ID . '"><b>Create API</b></a>';
		}
		return $actions;
	}


	/**
	 * submit event/calendar to mec-external.
	 * @return html html page for show the javascript code/json api request/config access sites.
	 */
	public function external_add() {
		$event = isset($_GET['event']) ? $_GET['event'] : null;
		$Calendar = isset($_GET['Calendar']) ? $_GET['Calendar'] : null;

		if (!$Calendar && !$event) {
			return false;
		}


		if ($Calendar) { // the post is calendar submit new page for shortcode and set template is single page

			// get calendar page, extract content for saved to mec-external page
			$cpost = get_post($Calendar);
			if(!$cpost){
				return false;
			}

			$postid = null; // the post is event use the original id, then create page for calendar and get the post_id

			// unique title for mec-external page shortcode
			$title = "mec-external-{$Calendar}";

			$posts = get_posts(
				array(
					'post_type'              => 'page',
					'title'                  => $title,
					'post_status'            => 'all',
					'numberposts'            => 1,
					'update_post_term_cache' => false,
					'update_post_meta_cache' => false,
					'orderby'                => 'post_date ID',
					'order'                  => 'ASC',
				)
			);

			$last = current( $posts ); // last page is created, then fetch the page and get post_id

			if ($last) {
				$postid = $last->ID;
			}else{
				// Create custome page by specific them for external show
				$page = array(
					'post_title' => $title,
					'post_content' => '[MEC id="' . $Calendar . '"]',
					'post_status' => 'publish',
					'post_author' => '1',
					'post_type' => 'page',
					'page_template' => 'mec-external.php',
					'post_name' => $title,
				);

				$postid = wp_insert_post($page);
			}

			$type = 'calendar';

		}else if($event){
			$type = 'event';
			$postid = $event;

		} else { // wrong request!
			echo 'cannot found';
			return false;
		}

		// url for show javascript code
		$url = get_site_url(null,'mecExternalScript').'?id='.$postid.'&t='.$type;

		// database access model
		$model = new MEC_Sites_Model($postid,$type);
		$model->process(); // process form submited
		$sites = $model->sites(); // get all sites saved
		$any = $model->any(); // access to any erquest

		$pid = $type=='event'?$event:$Calendar; // original event/calendar for api request
		$json = get_site_url(null, 'wp-json/mecexternal/v1/'.$type.'/'. $pid );
		$events_json = get_site_url(null, 'wp-json/mecexternal/v1/events/'. $pid );;
		?>

		<!-- template for add dynamic row to site access -->
		<script id="tmpl-mec-add-row" type='text/x-jquery-tmpl'>
		  <p id="mec-external-row-${id}" class="mec-api-external-row">
	         	<input name="sites[]" class="pure-input-1-2" type="text" placeholder="site.com">
	         	<button type="button" class="pure-button pure-button-primary button-error button-xsmall mec-delrow" data-id="${id}">
		        	<span class="dashicons dashicons-trash"></span>
		        </button>
	         </p>
		</script>

		<?php
		wp_enqueue_style('mec-external-style', MEC_EXT_URL.'/assets/pure-min.css');
		wp_enqueue_script( 'mec-external-tmpl', MEC_EXT_URL.'/assets/jquery.tmpl.min.js');
		wp_enqueue_script( 'mec-external-main', MEC_EXT_URL.'/assets/main.js',array(), '0.0.1' );

		// title show on admin page
		$section = $event ? ' Event' : ' Calendar';
		?>

		<!-- hilight and copy javascript code -->
		<script type="text/javascript">
			function mecExternalToClipboard(){
			  var copyText = document.getElementById("mec-external-script");
			  copyText.select();
			  document.execCommand("copy");
			}

			function mecExternalEventsJsonToClipboard(){

				var copyText = document.getElementById("mec-external-json-events");
				copyText.select();
				document.execCommand("copy");
			}

			ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
		</script>


		<!-- hilight and copy JSON code -->
		<script type="text/javascript">
			function mecExternalJsonToClipboard(){
			  var copyText = document.getElementById("mec-external-json");
			  copyText.select();
			  document.execCommand("copy");
			}
			ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
		</script>

		<div class="wrap mec-api-wrap nosubsub">
		    <h1 class="wp-heading-inline">Create API</h1>
		    <p class="mec-api-title-description">
		    	MEC External sites access
		    </p>
		    <hr class="wp-header-end">
		    <div id="col-container" class="wp-clearfix mec-api-col-wrap">
		    <div id="col-left" class="mec-api-col">
		        <div class="col-wrap">

	        	<form method="post">
	        	<?php wp_nonce_field( 'mec_external_sites_action', 'mec_external_sites_nonce_field' ); ?>
	        	<table class="pure-table pure-table-bordered" style="width: 96%;">
	        	<thead>
			        <tr>
			            <th class="mec-api-input-title">Site Access to <?php echo $section; ?></th>
			        </tr>
			    </thead>
			    <tbody>
			        <tr class="mec-api-table-row">
			            <td>
			            	<label>
			            		<input type="radio" name="any" value="no" <?php echo checked('no',$any); ?> >
			            		Custom Domains
			            	</label>

					        <div id="mec-external-sites">
					    	<?php if($sites): foreach ($sites as $k => $v): ?>
					        	<?php $id = md5($v['domain'].mt_rand(0,100)); ?>
						        <p id="mec-external-row-<?php echo $id; ?>" class="mec-api-external-row">
						        	<input name="sites[<?php echo $v['id']; ?>]" value="<?php echo $v['domain']; ?>" class="pure-input-1-2" type="text" placeholder="site.com">
						        	<button type="button" class="pure-button pure-button-primary button-error button-xsmall  mec-delrow" data-delid="<?php echo $v['id']; ?>" data-id="<?php echo $id; ?>">
						        		<span class="dashicons dashicons-trash"></span>
						        	</button>
					    		</p>
					    	<?php endforeach;endif; ?>
					        </div>

				    		<button type="button" class="mec-api-add-site" id="mec-external-addrow">
						        <span class="dashicons dashicons-plus-alt"></span> <span style="font-size: 14px;">Add Domain</span>
						    </button>
			            </td>
			        </tr>

			        <tr class="mec-api-table-row">
			            <td>
			            	<label>
			            		<input type="radio" name="any" value="yes" <?php echo checked('yes',$any); ?>>
			            		All Domains
			            	</label>
			            </td>
			        </tr>
			    </tbody>
				</table>
				<br>
				 <button value="submit" name="submit" type="submit" class="mec-api-generate ">Generate</button>
				</form>

				</div>
		     </div>
	        <div id="col-right" class="mec-api-col">
		        <form class="mec-api-form">
	        	 	<legend class="mec-api-input-title">
			        	<?php echo esc_html__('Copy Javascript for' , 'mec-event-API') . $section; ?>
			        </legend>
				    <fieldset class="mec-api-fieldset">
				        <textarea id="mec-external-script" class="mec-api-textarea" rows="1"><script type="text/javascript" src="<?php echo $url;?>"></script></textarea>
						<button  onclick="mecExternalToClipboard()" type="button" class="pure-button pure-button-primary"><?php echo esc_html('Copy' , 'mec-event-API'); ?></button>		
					</fieldset>
				    
				</form>
				<br>
				<form class="mec-api-form">
					<legend class="mec-api-input-title">
						<?php echo esc_html__('API Json Result url for' , 'mec-event-API') . $section; ?>
					</legend>
					<fieldset class="mec-api-fieldset">
						<textarea id="mec-external-json" class="mec-api-textarea" rows="1"><?php echo $json; ?></textarea>
						<button  onclick="mecExternalJsonToClipboard()" type="button" class="pure-button pure-button-primary"><?php echo esc_html('Copy' , 'mec-event-API'); ?></button>
					</fieldset>
					
				</form>

				<?php if( 'calendar' === $type ): ?>
					<br>
					<form class="mec-api-form">
						<legend class="mec-api-input-title">
							<?php echo esc_html__('API Json Result url for Calendar Events' , 'mec-event-API'); ?>
						</legend>
						<fieldset class="mec-api-fieldset">
							<textarea id="mec-external-json-events" class="mec-api-textarea" rows="1"><?php echo $events_json; ?></textarea>
							<button  onclick="mecExternalEventsJsonToClipboard()" type="button"><?php echo esc_html('Copy' , 'mec-event-API'); ?></button>
						</fieldset>
					</form>
				<?php endif; ?>
	        </div>
		    </div>
		</div>
		<?php

	}

	public static function add_setting_menu($menus) {

		$title = __( 'Event API', 'mec-event-api');
		$menus[$title] = 'event_api_option';
		return $menus;
	}

	public static function setting_menu_content($settings) {

		include plugin_dir_path( API_PLUGIN_MAIN_FILE_PATH ) . '/them/template-settings.php';;
	}
}
