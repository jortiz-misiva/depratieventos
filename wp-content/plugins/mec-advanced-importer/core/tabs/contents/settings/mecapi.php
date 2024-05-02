<?php 
$mecapi = count($s)>0?$s:array();
$id = isset($mecapi['id'])? $mecapi['id']:$this->get_id();
 ?>

 <?php add_thickbox(); ?>
<div id="mec-advimp-account-view" style="display:none;">
     <div id="mec-advimp-account-view-content"></div>
</div>
<a href="#TB_inline?&width=400&height=300&inlineId=mec-advimp-account-view" id="mec-advimp-account-view-click" class="thickbox"></a>


<input type="hidden" id="mec-advimp-page" value="mecapi">
<form id="mec_facebook_import_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
     
   <input type="hidden" name="mec-advimp-setting-section" value="mecapi">
   <input type="hidden" name="mec-advimp-setting-id" value="<?php echo $id ?>">
   <input type="hidden" name="mecapi[id]" value="<?php echo $id; ?>">

   <h3><?php _e('MEC API Settings', 'mec-advanced-importer');?></h3>
   <?php wp_nonce_field( MEC_ADVANCED_IMPORTER_DIR, 'advimp_settings_nonce' ); ?>
   <div class="mec-url-content-message">
      <p class="description"><?php _e('Your MEC API token: ','mec-advanced-importer'); ?><input class="mec-api-text" type="text" value="<?php echo md5(get_bloginfo('url')); ?>"></p>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-account-name">
         <?php _e('Account Title', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-account-name" name="mecapi[title]" value="<?php echo isset($mecapi['title'])?$mecapi['title']:''; ?>" required>
      </div>
   </div>

      <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-account-name">
         <?php _e('Site Address', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-account-name" name="mecapi[address]" value="<?php echo isset($mecapi['address'])?$mecapi['address']:''; ?>" required>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-mecapi-token">
         <?php _e('API Token', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-mecapi-token" name="mecapi[token]" value="<?php echo isset($mecapi['token'])?$mecapi['token']:''; ?>" required>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-update">
         <?php _e('Update existing events', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">

         <input type="hidden" name="mecapi[update_exists]" value="0">
         <label class="mec-col-6" for="mec-advimp-update">
              <input type="checkbox" id="mec-advimp-update" name="mecapi[update_exists]" value="1" <?php echo (isset($mecapi['update_exists']) and $mecapi['update_exists'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Update existing events', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-active">
         <?php _e('Account Active', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-6">

         <input type="hidden" name="mecapi[active]" value="0">
         <label class="mec-col-6" for="mec-advimp-active">
              <input type="checkbox" id="mec-advimp-active" name="mecapi[active]" value="1" <?php echo (isset($mecapi['active']) and $mecapi['active'] == '1') ? 'checked="checked"' : ''; ?>>
             <?php _e('Active the Account', 'mec-advanced-importer');?>
          </label>
      </div>
   </div>

   <div class="mec-options-fields">
      <input type="hidden" name="mec-advimp-action" value="mecapi-calendar-import-start">
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