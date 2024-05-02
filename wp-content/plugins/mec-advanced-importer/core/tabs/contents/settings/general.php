<input type="hidden" id="mec-advimp-page" value="general">

<form id="mec_advimp_setting_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST">
   <h3><?php _e('General Settings', 'mec-advanced-importer');?></h3>
   <?php wp_nonce_field( MEC_ADVANCED_IMPORTER_DIR, 'advimp_settings_nonce' ); 
   $general = get_option( 'mecadvimpgeneral' );
   ?>
   <input type="hidden" name="mec-advimp-setting-section" value="general">
   <input type="hidden" name="mec-advimp-setting-id" value="<?php echo $id ?>">

   <div class="mec-form-row">
      <label class="mec-col-3" for="mec-advimp-account-name">
         <?php _e('Per Page Table', 'mec-advanced-importer');?>
      </label>
      <div class="mec-col-4">
         <input type="text" id="mec-advimp-account-name" name="mecadvimpgeneral[perpage]" value="<?php echo isset($general['perpage'])?$general['perpage']:''; ?>">
      </div>
   </div>

   <div class="mec-options-fields">
      <input type="hidden" name="mec_advimp_action" value="update_general">
      <button id="mec-advimp-action" class="button button-primary mec-button-primary" type="submit"><?php _e('Save', 'mec-advanced-importer');?></button>
   </div>
</form>
