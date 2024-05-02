<?php

/**
 * Webnus MEC_Sync admin class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_Sync_Admin
{

	/**
	 * direct init class
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	function init()
	{
		if(! MEC_Sync_Libs::is_parent_blog()){
			return;
		}

		// Check Multisite
		add_action( 'admin_notices', array($this,'check_multisite') );

		// add admin menu
		add_action('admin_menu', array($this, 'menus'), 20, 1);

		// create options config
		add_action('admin_init', array($this, 'register_settings'));



	}

	/**
	 * callback function for add multisite menu to mec plugin
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	public function menus(){
		add_submenu_page('mec-intro', __('MEC - Multisite', 'mec-sync'), __('Multisite', 'mec-sync'), 'manage_options', 'MEC-sync', array($this, 'settings_page'));
	}

	/**
	 * setting page register configuration
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	function register_settings() {
		// create whitelist of options
		register_setting('mec_sync_settings_group', 'mec_sync_settings');
	}

	public function check_multisite() {
		if ( !is_multisite() ) {
			$class = 'notice notice-error is-dismissible';
			$message = __( 'You need to install WordPress Multisite first. Please follow this link:', 'mec-sync' );
			$link = 'https://wordpress.org/support/article/create-a-network/';
			printf( '<div class="%1$s"><p>%3$s <a href="%2$s" target="_blank">'.__('How to create Multisite', 'mec-sync').'</a></p></div>', esc_attr( $class ), esc_html( $link ), esc_html( $message ) );
		}
	}

	/**
	 * setting menu form generate
	 * @author Webnus <info@webnus.biz>
	 * @return void
	 */
	public function settings_page(){
		wp_register_style('mec-sync-admin-css', MEC_SYNC_URL . '/assets/css/style.css');
		wp_enqueue_style('mec-sync-admin-css');

		$options = get_option('mec_sync_settings');
		$sync_events = isset($options['sync_events']) ? $options['sync_events'] : null;
		$sync_settings = isset($options['sync_settings']) ? $options['sync_settings'] : null;

		$sites = isset($options['sites']) ? $options['sites'] : array();

		?>
		<br>
		<form method="post" id="mec-sync-form" action="options.php" class="mec-sync-options-form">
		<?php settings_errors() ?>
		<?php settings_fields('mec_sync_settings_group');?>

		<div class="mec-container wns-be-container">
			<div class="wns-be-group-tab">
			<?php
				if ( !is_multisite() ) return;
				$blog_ids = get_sites();
				if ( count($blog_ids) <= 1 ){
					echo '<h4>'. __('First, create at least one site.','mec-sync') .'</h4>';
					return;
				}

			?>
				<h4>
					<?php _e('Step 1: Choose Sync Settings','mec-sync'); ?>
				</h4>
				<ul class="mec-form-row">

					<li class="mec-form-row">
						<label class="mec-col-3">Sync Events</label>
						<div class="mec-col-2">
							<label class="switch mec-switcher">
							<input type="checkbox" name="mec_sync_settings[sync_events]" value="1" <?php checked(1,  $sync_events, true ); ?> >
							<span class="slider round"></span>
							</label>
						</div>
					</li>

					<li class="mec-form-row">
						<label class="mec-col-3">Sync Settings</label>
						<div class="mec-col-2">
							<label class="switch">
							<input type="checkbox" name="mec_sync_settings[sync_settings]" value="1" <?php checked(1,  $sync_settings, true ); ?>>
							<span class="slider round"></span>
							</label>
						</div>
					</li>

				</ul>

				<div class="mec-options-fields" style="display: block;">

					<h4 class="mec-form-subtitle">
						<?php echo _e('Step 2: Sites to be synced','mec-sync'); ?>
					</h4>

					<div class="mec-form-row mec-row-sync mec-sync-row-1">
						<?php

						$select_all = true;
						if ( count($blog_ids) > 1 ) :
						foreach ($blog_ids as $b) {

							// on the admin blog ignore
							if($b->blog_id == 1){
								continue;
							}

							// get blog name
							$d = get_blog_details($b->blog_id , 'blogname');
							$selected =  array_key_exists($b->blog_id, $sites);
							if(!array_key_exists($b->blog_id, $sites)){
								$select_all = false;
							}
							?>
							<div class="mec-form-row">

								<label>
									<input type="checkbox" name="mec_sync_settings[sites][<?php echo $b->blog_id; ?>]" value="1" <?php checked(1, $selected, true ); ?> class="mec-sync-checkbox"> <?php echo $d->blogname; ?>
								</label>
							</div>

						<?php }
						endif;
						?>

							<div class="sync-box">
								<a class="dpr-btn dpr-save-btn" href="#" type="submit" onclick="return jQuery('#mec-sync-form').submit();" ><?php _e('Save Changes','mec-sync'); ?></a>
								&nbsp;&nbsp;
								<label>
									<input type="checkbox" value="1" <?php checked(1,  $select_all, true ); ?> id="mec-sync-checkbox">
									<?php _e('Select/Deselect All Sites','mec-sync'); ?>
								</label>

							</div>
					</div>

				</div>

			</div>
		</div>

		<?php if ( count($blog_ids) > 1 ) :
			$all = get_posts(array('post_type'=>'mec-events', 'posts_per_page' => -1));

			if(count($all)>0 && MEC_Sync_Libs::is_event_sync() &&  count(MEC_Sync_Libs::sync_sites())>0 ){
			?>
<br>
			<div class="mec-container wns-be-container">
				<div class="wns-be-group-tab">
					<div class="mec-options-fields">
						<h4 class="mec-form-subtitle">
							<?php echo _e('Configuration','mec-sync'); ?>
						</h4>
						<p><?php
						echo wp_kses('From now on, any event you add to your main site will be synced automatically with all of your subsites. Use the section below for these two scenarios:<br>
						1. Syncing the events that were added before the add-on.<br>
						2. If you added a new site in the future, you can use this part to sync the old events with the new site.',
						array('br' => array())
						);
						?></p>

						<div id="sync-progress" class="mec-form-row mec-row-sync">
							<div class="sync-box">
								<a href="#" id="sync-now" type="button" class="dpr-btn dpr-save-btn" ><?php _e('Sync Now','mec-sync'); ?></a>
								&nbsp;&nbsp;
								<label>
									<input type="checkbox" value="1" id="sync-req">
									<?php _e('Select/Deselect All Events','mec-sync'); ?>
								</label>
							</div>
						</div>

						<br>

						<?php
						foreach ($all as $k => $v):

						?>

						<div class="mec-form-row">
							<label>
								<input type="hidden" name="mec-sync[<?php echo $v->ID; ?>]" value="<?php echo $v->ID; ?>" >
								<input value="<?php echo $v->ID; ?>" type="checkbox" class="sync-req"><?php echo $v->post_title; ?>
							</label>
							<b id="sync-res-<?php echo $v->ID; ?>"></b>
						</div>

						<?php

						endforeach;
						?>
						<?php } ?>

					</div>
				</div>
			</div>
		<?php endif; ?>
		</form>

		<script type="text/javascript">
			jQuery(document).ready(function($) {

				$('#mec-sync-checkbox').change(function(event) {
					$('.'+$(this).attr('id')).prop('checked', $(this).is(':checked'));
				});

				$('#sync-req').change(function(event) {
					$('.'+$(this).attr('id')).prop('checked', $(this).is(':checked'));
				});

				$('#sync-now').click(function(event) {
					var ids = [];
					$('.sync-req').each(function(i, obj) {
					    if($(obj).is(':checked')){
					    	ids.push($(obj).val());
					    }
					});

					if(ids.length == 0){
						return false;
					}

					var progress = $('#sync-progress');
					progress.addClass('sync-progress');

					jQuery.ajaxSetup({async:false});

					setTimeout(function() {

						for(var k in ids){
							$.ajax({
		                        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
		                        type: 'POST',
		                        dataType: 'json',
		                        data: {'action':'mec_sync_event','id':ids[k]},
		                        async: false,
		                    })
		                    .done(function(ret) {
		                        if (ret && ret.success == true) {
		                        	$('#sync-res-'+ids[k]).html('<span class="mec-success"> - Success Synced</span>');
		                        }else{
									$('#sync-res-'+ids[k]).html('<span class="mec-error"> - Failed</span>');
								}
		                    })
		                    .fail(function(ret) {
		                        console.log(ret);
		                    });
						}

						jQuery.ajaxSetup({async:true});
						progress.removeClass('sync-progress');
					}, 1000);

					return false;
				});
			});
		</script>
		<?php
	}


}