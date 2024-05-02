<input type="hidden" id="mec-advimp-page" value="facebook">
<form id="mec_advimp_setting_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
   <h3><?php _e('Facebook Settings', 'mec-advanced-importer');?></h3>
   <?php wp_nonce_field( MEC_ADVANCED_IMPORTER_DIR, 'advimp_settings_nonce' ); 
   	$facebook = count($s)>0?$s:array();
      $id = isset($facebook['id'])? $facebook['id']:$this->get_id();
   ?>
   <input type="hidden" name="mec-advimp-setting-section" value="facebook">
   <input type="hidden" name="mec-advimp-setting-id" value="<?php echo $id ?>">
   <input type="hidden" name="facebook[id]" value="<?php echo $id; ?>">
   <input type="hidden" name="facebook[need_auth]" value="1">
   <div class="mec-url-content-message">
      <p class="description">Import all of your Facebook events into MEC. <a href="https://webnus.net/dox/modern-events-calendar/import-facebook-events/" target="_blank">Documentation</a></p>
   </div>
   <div class="mec-url-content-message">
      <p><?php _e('Add URL: ','mec-advanced-importer'); ?><strong><code><?php echo admin_url(MEC_ADVANCED_IMPORTER_CALLBACK.'facebook_callback'); ?></code></strong><?php _e('To Facebook Domain Auth.','mec-advanced-importer'); ?></p>
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
         <input type="text" id="mec-advimp-account-name" name="facebook[title]" value="<?php echo isset($facebook['title'])?$facebook['title']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-app-id">
      	<?php _e('Facebook App ID', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-app-id" name="facebook[app_id]" value="<?php echo isset($facebook['app_id'])?$facebook['app_id']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-app-secret">
      	<?php _e('Facebook App secret', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-app-secret" name="facebook[app_secret]" value="<?php echo isset($facebook['app_secret'])?$facebook['app_secret']:''; ?>" required>
      </div>
   </div>
   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-update">
      	<?php _e('Update existing events', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">

         <input type="hidden" name="facebook[update_exists]" value="0">
         <label class="mec-col-6" for="mec-advimp-update">
              <input type="checkbox" id="mec-advimp-update" name="facebook[update_exists]" value="1" <?php echo (isset($facebook['update_exists']) and $facebook['update_exists'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Update existing events', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-businessapp">
         <?php _e('Business APP', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-6">

         <input type="hidden" name="facebook[businessapp]" value="0">
         <label class="mec-col-6" for="mec-advimp-businessapp">
              <input type="checkbox" id="mec-advimp-businessapp" name="facebook[businessapp]" value="1" <?php echo (isset($facebook['businessapp']) and $facebook['businessapp'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Business App', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-active">
         <?php _e('Account Active', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-6">

         <input type="hidden" name="facebook[active]" value="0">
         <label class="mec-col-6" for="mec-advimp-active">
              <input type="checkbox" id="mec-advimp-active" name="facebook[active]" value="1" <?php echo (isset($facebook['active']) and $facebook['active'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Active', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-options-fields">
      <input type="hidden" name="mec-advimp-action" value="facebook-calendar-import-start">
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