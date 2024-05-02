<?php
$ctab = isset($_GET['ctab']) ? $_GET['ctab'] : 'fetch';
$args = array(
   'page' => 'MEC-advimp',
   'tab' => 'MEC-Facebook',
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

<?php if($ctab=='fetch'): ?>

   <div id="general_option" class="mec-options-fields active">
      <h4 class="mec-form-subtitle">
         <?php _e('Import Options','mec-advanced-importer'); ?>
      </h4>

      <div class="mec-form-row">
         <label class="mec-col-3" for="mec-advimp-importby-inp">
            <?php _e('Category','mec-advanced-importer'); ?>
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
                 ?>
                 <option value="<?php echo $v->term_id; ?>"><?php echo $v->cat_name; ?></option>
                 <?php
               }
                ?>
            </select>
              </div>
            </div>
      </div>

      <div class="mec-form-row">
         <label class="mec-col-3" for="mec-advimp-importby-inp">
            <?php _e('Import By','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">

            <select id="mec-advimp-importby-inp">
               <!-- <option value="all" selected="selected">
                  <?php _e('My Events','mec-advanced-importer'); ?>
               </option> -->
               <option value="single">
                  <?php _e('Facebook Event ID','mec-advanced-importer'); ?>
               </option>
               <option value="page">
                  <?php _e('Facebook Page','mec-advanced-importer'); ?>
               </option>
               <option value="group">
                  <?php _e('Facebook Group','mec-advanced-importer'); ?>
               </option>
            </select>
            <input type="hidden" id="mec-advimp-importby-my-inp" value="getall">
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Imported by','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">
                     <p>
                        <?php _e('Import options selected by my-events, page, group or specific ID.','mec-advanced-importer'); ?>
                     </p>
                  </div>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>

      <div class="mec-form-row  mec-advimp-import-option mec-advinp-dnone" id="mec-advimp-importby-single">
         <label class="mec-col-3" for="mec-advimp-importby-single-inp">
            <?php _e('Facebook Event IDs','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <textarea rows="2" id="mec-advimp-importby-single-inp"></textarea>
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Event ID','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">
                     <p>
                        <?php _e('One event ID per line, ( Eg. Event ID for https://www.facebook.com/events/123456789/ is "123456789" ).','mec-advanced-importer'); ?>
                     </p>
                  </div>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>

      <div class="mec-form-row mec-advimp-import-option mec-advinp-dnone" id="mec-advimp-importby-page" style="display: block;">
         <label class="mec-col-3" for="mec-advimp-importby-page-inp">
            <?php _e('Facebook Page ID','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <input type="text" id="mec-advimp-importby-page-inp">
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Page ID to fetch events from','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">
                     <p>
                        <?php _e(' You can find facebook id at more/about - Page ID.','mec-advanced-importer'); ?>
                     </p>
                  </div>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>

      <div class="mec-form-row mec-advimp-import-option mec-advinp-dnone" id="mec-advimp-importby-group">
         <label class="mec-col-3" for="mec-advimp-importby-group-inp">
            <?php _e('Facebook Group ID','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <input type="text" id="mec-advimp-importby-group-inp">
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Facebook Group URL / Numeric ID to fetch events from','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">
                     <p>
                        <?php _e('Eg.Input value for https://www.facebook.com/groups/123456789123456/
https://www.facebook.com/groups/123456789123456/ OR "123456789123456"','mec-advanced-importer'); ?>
                     </p>
                  </div>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>


      <div class="mec-form-row" id="mec-advimp-import-batch">
         <label class="mec-col-3" for="mec-advimp-import-type-inp">
            <?php _e('Import Type','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <select id="mec-advimp-import-type-inp">
               <option value="onestep" selected="selected">
                  <?php _e('One-Step','mec-advanced-importer'); ?>
               </option>
               <option value="sheduled">
                  <?php _e('Scheduled','mec-advanced-importer'); ?>
               </option>

            </select>
         </div>
         <div class="mec-col-4 mec-advinp-dnone" id="mec-advimp-import-type-scheduled">
            <select id="mec-advimp-import-type-scheduled-inp">


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
      <div class="mec-form-row">
         <label class="mec-col-3" for="mec-advimp-import-status">
            <?php _e('Status','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <select id="mec-advimp-import-status">
               <option value="all" selected="selected">
                  <?php _e('All','mec-advanced-importer'); ?>
               </option>
               <option value="publish" >
                  <?php _e('Published','mec-advanced-importer'); ?>
               </option>
               <option value="canceled">
                  <?php _e('Canceled','mec-advanced-importer'); ?>
               </option>
               <option value="draft">
                  <?php _e('Drafted','mec-advanced-importer'); ?>
               </option>

            </select>
         </div>
      </div>
       <div class="mec-form-row">
         <label class="mec-col-3" for="mec-advimp-import-sdate"><?php _e('Start Date', 'mec'); ?></label>
         <div class="mec-col-4">
             <input type="text" id="mec-advimp-import-sdate" value="<?php echo  date('Y-m-d', strtotime('-1 Month')); ?>" class="mec_date_picker" />
         </div>
     </div>
     <div class="mec-form-row">
         <label class="mec-col-3" for="mec-advimp-import-edate"><?php _e('End Date', 'mec'); ?></label>
         <div class="mec-col-4">
             <input type="text" id="mec-advimp-import-edate" value="<?php echo date('Y-m-d', strtotime('+3 Months')); ?>" class="mec_date_picker" />
         </div>
     </div>
   </div>

 <div class="mec-options-fields">
      <?php include 'shared.php'; ?>
      <input type="hidden" id="mec-advimp-page" value="facebook">
      <div class="mec-form-row mec-messages">
         <?php
         $list = \MEC_Advanced_Importer\Core\Lib\MEC_Advanced_Importer_Main::active_account('facebook',true);
         if($list == null){
            echo '<span class="mec-no-activate-account-message">';
               echo __('No Active Account','mec-advanced-importer');
            echo '</span">';
         }else if(count($list)>1):
          ?>

         <div class="mec-col-4" for="mec-advimp-importby-inp">
             <select class="mec-advimp-select2" name="states[]" multiple="multiple">
               <?php foreach ($list as $k => $v) {
                  echo '<option value="'.$k.'">'.$v.'</option>';
               } ?>
         </select>
         </div>
         <div class="mec-col-12">
            <button style="margin: 0px;" data-url="<?php echo $getall; ?>" data-action="getall" data-section="facebook" class="button button-primary mec-button-primary mec-advimp-action" type="button" id="mec-advimp-getallevent">
               <?php _e('Get All Events','mec-advanced-importer'); ?>
            </button>
            <button style="margin: 0px;" data-url="<?php echo $add_to_auto_sync; ?>" data-action="add-to-schedule" data-section="facebook" class="button button-primary mec-button-primary mec-advimp-action" type="button" id="mec-advimp-add-to-sync">
               <?php _e('Add to auto sync','mec-advanced-importer'); ?>
            </button>
            <input type="hidden" value="facebook-calendar-import-start">
            <div id="mec-advimp-loading" class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>
         </div>
      <?php else: ?>
         <input type="hidden" id="mec-advimp-account-single" value="<?php echo array_keys($list)[0]; ?>">
         <div class="mec-col-12" for="mec-advimp-importby-inp">
            <button style="margin: 0px;" data-url="<?php echo $getall; ?>" data-action="getall" data-section="facebook" class="button button-primary mec-button-primary mec-advimp-action" type="button" id="mec-advimp-getallevent">
               <?php _e('Get All Events From:','mec-advanced-importer'); ?>
               <?php echo array_values($list)[0]; ?>
            </button>
            <button style="margin: 0px;" data-url="<?php echo $add_to_auto_sync; ?>" data-action="add-to-schedule" data-section="facebook" class="button button-primary mec-button-primary mec-advimp-action" type="button" id="mec-advimp-add-to-sync">
               <?php _e('Add to auto sync:','mec-advanced-importer'); ?>
               <?php echo array_values($list)[0]; ?>
            </button>
         </div>
      <?php endif; ?>
      </div>
   </div>

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
<?php elseif( 'sync' === $ctab ): ?>
   <h3><?php esc_html_e('Sync', 'mec-advanced-importer' ); ?></h3>
   <form id="advimp-sync-table" method="post">
      <?php MEC_Advanced_Importer_Sync::display_sync_table_list( 'facebook' ); ?>

<?php else: ?>
<h3><?php esc_html_e('Scheduled Imports', 'mec-advanced-importer' ); ?></h3>
<form id="advimp-scheduled-table" method="post">
<?php
if (!class_exists('WP_List_Table')) {
   require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$table = new \MEC_Advanced_Importer_Schedule_Table();
$table->event_class = 'facebook';
$table->prepare_items();
echo $table->display();
?>

<?php endif; ?>
</form>