<input type="hidden" id="mec-advimp-page" value="meetup">

<form id="mec_advimp_setting_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
   <h3><?php _e('MeetUp Settings', 'mec-advanced-importer');?></h3>
   <?php wp_nonce_field( MEC_ADVANCED_IMPORTER_DIR, 'advimp_settings_nonce' ); 
   	$meetup = count($s)>0?$s:array();
      $id = isset($meetup['id'])? $meetup['id']:$this->get_id();
   ?>
   <input type="hidden" name="mec-advimp-setting-section" value="meetup">
   <input type="hidden" name="mec-advimp-setting-id" value="<?php echo $id ?>">
   <input type="hidden" name="meetup[id]" value="<?php echo $id; ?>">
   <input type="hidden" name="meetup[need_auth]" value="1">

   <div class="widefat ime_settings_notice">
      <div class="mec-url-content-message">
         <p class="description"><?php _e('Import all of your MeetUp events into MEC.','mec-advanced-importer'); ?></p>
      </div>
      <div class="mec-url-content-message">
         <strong><?php _e('Note: ','mec-advanced-importer'); ?></strong>
         <?php _e('You have to create a Meetup OAuth Consumer before filling the following details.','mec-advanced-importer'); ?>
         <strong><a href="https://secure.meetup.com/meetup_api/oauth_consumers/create" target="_blank"><?php _e('Click here','mec-advanced-importer'); ?></a></strong><?php _e('to create new OAuth Consumer','mec-advanced-importer'); ?>
      </div>
      <div class="mec-url-content-message">
         <strong><?php _e('Set the Application Website as','mec-advanced-importer'); ?>:</strong>        
         <code><?php echo get_site_url(); ?></code>
      </div>
      <div class="mec-url-content-message">
         <strong><?php _e('Set Redirect URI:','mec-advanced-importer'); ?></strong>
         <code><?php echo admin_url(MEC_ADVANCED_IMPORTER_CALLBACK.'meetup_callback'); ?></code>
      </div>
  </div>

<?php add_thickbox(); ?>
<div id="mec-advimp-account-view" style="display:none;">
     <div id="mec-advimp-account-view-content"></div>
</div>
<a href="#TB_inline?&width=400&height=300&inlineId=mec-advimp-account-view" id="mec-advimp-account-view-click" class="thickbox"></a>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-account-name">
         <?php _e('Account Title', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-account-name" name="meetup[title]" value="<?php echo isset($meetup['title'])?$meetup['title']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-app-id">
      	<?php _e('Key', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-app-id" name="meetup[key]" value="<?php echo isset($meetup['key'])?$meetup['key']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-app-secret">
      	<?php _e('Secret', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-app-secret" name="meetup[secret]" value="<?php echo isset($meetup['secret'])?$meetup['secret']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-update">
      	<?php _e('Update existing events', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">

         <input type="hidden" name="meetup[update_exists]" value="0">
         <label class="mec-col-6" for="mec-advimp-update">
              <input type="checkbox" id="mec-advimp-update" name="meetup[update_exists]" value="1" <?php echo (isset($meetup['update_exists']) and $meetup['update_exists'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Update existing events', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-active">
         <?php _e('Account Active', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-6">

         <input type="hidden" name="meetup[active]" value="0">
         <label class="mec-col-6" for="mec-advimp-active">
              <input type="checkbox" id="mec-advimp-active" name="meetup[active]" value="1" <?php echo (isset($meetup['active']) and $meetup['active'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Active the Account', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-options-fields">
      <input type="hidden" name="mec-advimp-action" value="meetup-calendar-import-start">
      <button id="mec-advimp-action" class="button button-primary mec-button-primary" type="submit"><?php _e(isset($s['title'])?'Update Account':'Add Account', 'mec-advanced-importer');?></button>
   </div>
</form>

<form method="post">
<?php 
$page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );
$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
printf( '<input type="hidden" name="page" value="%s" />', $page );
printf( '<input type="hidden" name="paged" value="%d" />', $paged );
$table->prepare_items();
echo $table->display();
?>
</form>