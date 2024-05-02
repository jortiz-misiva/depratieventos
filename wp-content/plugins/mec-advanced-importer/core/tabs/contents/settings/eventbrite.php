<?php 
$eventbrite = count($s)>0?$s:array();
$id = isset($eventbrite['id'])? $eventbrite['id']:$this->get_id();

 ?>

<input type="hidden" id="mec-advimp-page" value="eventbrite">
<form id="mec_facebook_import_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
     
   <input type="hidden" name="mec-advimp-setting-section" value="eventbrite">
   <input type="hidden" name="mec-advimp-setting-id" value="<?php echo $id ?>">
   <input type="hidden" name="eventbrite[id]" value="<?php echo $id; ?>">

   <h3><?php _e('EventBrite Settings', 'mec-advanced-importer');?></h3>
   <?php wp_nonce_field( MEC_ADVANCED_IMPORTER_DIR, 'advimp_settings_nonce' ); ?>
   <div class="mec-url-content-message">
      <p class="description"><?php _e('Import all of your EventBrite events into MEC.','mec-advanced-importer'); ?> <a href="https://www.eventbrite.com/platform/api-keys" target="_blank"><?php _e('Get API Token','mec-advanced-importer'); ?></a></p>
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
         <input type="text" id="mec-advimp-account-name" name="eventbrite[title]" value="<?php echo isset($eventbrite['title'])?$eventbrite['title']:''; ?>" required>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-eventbrite-token">
         <?php _e('API Token', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-eventbrite-token" name="eventbrite[token]" value="<?php echo isset($eventbrite['token'])?$eventbrite['token']:''; ?>" required>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-update">
         <?php _e('Update existing events', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">

         <input type="hidden" name="eventbrite[update_exists]" value="0">
         <label class="mec-col-6" for="mec-advimp-update">
              <input type="checkbox" id="mec-advimp-update" name="eventbrite[update_exists]" value="1" <?php echo (isset($eventbrite['update_exists']) and $eventbrite['update_exists'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Update existing events', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-active">
         <?php _e('Account Active', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-6">

         <input type="hidden" name="eventbrite[active]" value="0">
         <label class="mec-col-6" for="mec-advimp-active">
              <input type="checkbox" id="mec-advimp-active" name="eventbrite[active]" value="1" <?php echo (isset($eventbrite['active']) and $eventbrite['active'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Active', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-options-fields">
      <input type="hidden" name="mec-advimp-action" value="eventbrite-calendar-import-start">
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