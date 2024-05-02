<?php
$cat_name = 'mec_advimp_current_category_ICS';
$selected_category = get_option( $cat_name );
$selected = array();

if($selected_category && is_array($selected_category) && count($selected_category)>0){
  $selected = $selected_category;
}
update_option( $cat_name, null );
?>

<?php
$ctab = isset($_GET['ctab']) ? $_GET['ctab'] : 'fetch';
$args = array(
   'page' => 'MEC-advimp',
   'tab' => 'MEC-ICS',
);

$args['ctab'] = 'fetch';
$url_fetch = add_query_arg($args, admin_url('admin.php'));

$args['ctab'] = 'schedule';
$url_schedule = add_query_arg($args, admin_url('admin.php'));

$args['ctab'] = 'sync';
$url_sync = add_query_arg($args, admin_url('admin.php'));
?>
<div class="wp-filter">
   <ul class="filter-links">
      <li>
         <a class="<?php if ($ctab == 'fetch') {echo 'current';}?>"
            href="<?php echo esc_url($url_fetch); ?>">
            <?php _e('New Import','mec-advanced-importer'); ?>
         </a>
      </li>

      <li>
         <a class="<?php if ($ctab == 'schedule') {echo 'current';}?>"
            href="<?php echo esc_url($url_schedule); ?>">
            <?php _e('Scheduled Imports','mec-advanced-importer'); ?>
         </a>
      </li>

      <li>
         <a class="<?php if ($ctab == 'sync') {echo 'current';}?>"
            href="<?php echo esc_url($url_sync); ?>">
            <?php _e('Sync','mec-advanced-importer'); ?>
         </a>
      </li>
   </ul>
</div>

<input type="hidden" class="mec-advimp-extra-field" id="get_calendar_list" name="get_calendar_list" value="1">
<input type="hidden" class="mec-advimp-extra-field" id="get_calendar_list_item" name="get_calendar_list_item" value="">
<input type="hidden" id="mec-advimp-call-getall" value="MEC_ADVIMP_Clear_ICS_Query">

<?php if($ctab=='fetch'): ?>
   <div id="general_option" class="mec-options-fields active">

      <br><h3><?php _e('Import .ics File OR Upload from url', 'mec'); ?></h3>

      <div class="mec-form-row">
         <p><?php echo sprintf(__("ICS format is supported by many different service providers like Facebook, Apple Calendar etc. You can import your ICS files into the %s using this form.", 'mec'), '<strong>'.__('Modern Events Calendar', 'mec').'</strong>'); ?></p>
      </div>
      <script type="text/javascript">
         <?php if(isset($this->table_ajax) && $this->table_ajax != null): ?>
            window.MEC_ADVIMP_Table_Date = <?php echo json_encode($this->table_ajax); ?>;
            MEC_ADVIMP_Direct_Get_All();
         <?php endif; ?>
         jQuery(document).ready(function($){
            $('[name="mec-advimp-ics-url"]').on('change',function(e){
               if( $(this).val().length > 0 ){
                  $('#mec-advimp-import-add-to-sync-wrap').show();
               }else{
                  $('#mec-advimp-import-add-to-sync-wrap').hide();
               }
            });

            $('[name="mec-advimp-import-add-to-sync"]').on('change',function(e){
               if( $(this).is(':checked') ){
                  $('#mec-advimp-import-batch').show();
               }else{
                  $('#mec-advimp-import-batch').hide();
               }
            });


         });
      </script>

      <form id="mec_import_ics_form" action="<?php echo $this->main->get_full_url(); ?>" method="POST" enctype="multipart/form-data">

         <div class="mec-form-row">
            <label class="mec-col-3" for="mec-advimp-importby-inp">
               <?php _e('ICS URL','mec-advanced-importer'); ?>
            </label>
            <div class="mec-col-4">

               <input type="text" name="mec-advimp-ics-url">
            </div>
         </div>

         <div id="mec-advimp-import-add-to-sync-wrap" style="display:none;">
            <div class="mec-form-row">
               <label class="mec-col-3" for="mec-advimp-import-add-to-sync"><?php _e('Sync', 'mec-advanced-importer'); ?></label>
               <div class="mec-col-4">
                  <label for="mec-advimp-import-add-to-sync"><input type="checkbox" name="mec-advimp-import-add-to-sync" value="1" /><?php esc_html_e( 'Add to sync', 'mec-advanced-importer' ) ?></label>
               </div>
            </div>

            <div class="mec-form-row" id="mec-advimp-import-batch" style="display:none">
               <label class="mec-col-3" for="mec-advimp-import-type-inp">
                  <?php _e('Import Type','mec-advanced-importer'); ?>
               </label>
               <div class="mec-col-4">
                  <select name="mec-advimp-import-type-inp" id="mec-advimp-import-type-inp">
                     <option value="onestep" selected="selected">
                        <?php _e('One-Step','mec-advanced-importer'); ?>
                     </option>
                     <option value="sheduled">
                        <?php _e('Scheduled','mec-advanced-importer'); ?>
                     </option>
                  </select>
               </div>
               <div class="mec-col-4 mec-advinp-dnone" id="mec-advimp-import-type-scheduled">
                  <select name="mec-advimp-import-type-scheduled-inp" id="mec-advimp-import-type-scheduled-inp">
                     <option value="hourly">
                           <?php _e('Once Hourly','mec-advanced-importer'); ?>
                        </option>
                     <option value="twicedaily">
                           <?php _e('Twice Daily','mec-advanced-importer'); ?>
                        </option>
                     <option value="daily">
                           <?php _e('Once Daily','mec-advanced-importer'); ?>
                        </option>
                     <option value="weekly">
                           <?php _e('Once Weekly','mec-advanced-importer'); ?>
                        </option>
                     <option value="monthly">
                           <?php _e('Once a Month','mec-advanced-importer'); ?>
                     </option>
                  </select>
               </div>
            </div>
         </div>

         <div class="mec-form-row">
            <label class="mec-col-3" for="mec-advimp-importby-inp">
               <?php _e('ICS/CSV File','mec-advanced-importer'); ?>
            </label>
            <div class="mec-col-4">
               <input type="file" name="mec-advimp-ics" id="ics" title="<?php esc_attr_e('ICS Feed', 'mec'); ?>">
               <input type="hidden" name="mec-advimp-action" value="import-ics">
            </div>
         </div>

         <div class="mec-form-row">
            <label class="mec-col-3" for="mec-advimp-importby-inp">
               <?php _e('Categories','mec-advanced-importer'); ?>
            </label>
            <div class="mec-col-4">
               <div class="mec-categories-tab-contents mec-form-row mec-advimp-category">
                  <select id="mec-advimp-import-category" class="mec-advimp-category-select2" name="mecadvimp-category[]" multiple="multiple">
                     <?php
                     $all = get_categories(array(
                        'taxonomy'=>'mec_category',
                        'hide_empty'       => 0,
                        'hierarchical'     => true,
                        'post_type'=>'mec-events'
                     ));
                     foreach ($all as $k => $v) {
                     $is_selected = in_array($v->term_id, $selected)?'selected="selected"':'';
                     ?>
                     <option value="<?php echo $v->term_id; ?>" <?php echo $is_selected; ?>><?php echo $v->cat_name; ?></option>
                     <?php
                     }
                     ?>
                  </select>
               </div>
            </div>
         </div>

         <div class="mec-form-row">
            <button class="button button-primary mec-button-primary mec-btn-2"><?php _e('Upload & Import', 'mec'); ?></button>
         </div>

      </form>

      <form id="email-sent-list" method="get">
         <input type="hidden" name="page" value="<?php echo isset($_REQUEST['page'])?$_REQUEST['page']:''; ?>" />
         <input type="hidden" name="order" value="<?php echo isset($_REQUEST['order'])?$_REQUEST['order']:''; ?>" />
         <input type="hidden" name="orderby" value="<?php echo isset($_REQUEST['orderby'])?$_REQUEST['orderby']:''; ?>" />
         <div id="mec-advimp-dialog-content-id-body">
            <?php
            wp_nonce_field( 'ajax-custom-list-nonce', '_ajax_custom_list_nonce' );
            ?>
         </div>
      </form>
      <div id="mec-advimp-dialog-content-id-message"><b></b></div>
      <div class="mec-advimp-showlog" >
         <ul id="mec-advimp-showlog">
         </ul>
      </div>
   </div>
<?php elseif( 'sync' === $ctab ): ?>
   <h3><?php esc_html_e('Sync', 'mec-advanced-importer' ); ?></h3>
   <form id="advimp-sync-table" method="post">
      <?php MEC_Advanced_Importer_Sync::display_sync_table_list( 'ICS' ); ?>
   </form>
<?php else: ?>

   <h3><?php _e('Scheduled Imports','mec-advanced-importer'); ?></h3>
   <form id="advimp-scheduled-table" method="post">
      <?php
      if (!class_exists('WP_List_Table')) {
         require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
      }

      $table = new \MEC_Advanced_Importer_Schedule_Table();
      $table->event_class = $this->name;
      $table->prepare_items();
      echo $table->display();

      ?>
   </form>
<?php endif; ?>
<p>
    <?php if($this->error!=null) echo $this->error; ?>
</p>