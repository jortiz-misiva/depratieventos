<?php
$ctab = isset($_GET['ctab']) ? $_GET['ctab'] : 'fetch';
$args = array(
   'page' => 'MEC-advimp',
   'tab' => 'MEC-Thirdparty',
);

$args['ctab'] = 'fetch';
$url_fetch = add_query_arg($args, admin_url('admin.php'));

$args['ctab'] = 'schedule';
$url_schedule = add_query_arg($args, admin_url('admin.php'));
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
               <option value="all" selected="selected">
                  <?php _e('All Events','mec-advanced-importer'); ?>
               </option>
               <option value="single">
                  <?php _e('Event ID','mec-advanced-importer'); ?>
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
            <?php _e('Event IDs','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <textarea rows="2" id="mec-advimp-importby-single-inp"></textarea>
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Event ID','mec-advanced-importer'); ?>
                  </h5>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>

      <div class="mec-form-row mec-advimp-import-option mec-advinp-dnone" id="mec-advimp-importby-page">
         <label class="mec-col-3" for="mec-advimp-importby-page-inp">
            <?php _e('Page ID','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <input type="text" id="mec-advimp-importby-page-inp">
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Page username / ID to fetch events from','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">

                  </div>
               </div>
               <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
         </div>
      </div>

      <div class="mec-form-row mec-advimp-import-option mec-advinp-dnone" id="mec-advimp-importby-group">
         <label class="mec-col-3" for="mec-advimp-importby-group-inp">
            <?php _e('Group ID','mec-advanced-importer'); ?>
         </label>
         <div class="mec-col-4">
            <input type="text" id="mec-advimp-importby-group-inp">
            <span class="mec-tooltip">
               <div class="box">
                  <h5 class="title">
                     <?php _e('Group URL / Numeric ID to fetch events from','mec-advanced-importer'); ?>
                  </h5>
                  <div class="content">

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
      <?php

      $plugin_dir = ABSPATH.'wp-content'.DS.'plugins'.DS;
      $ths = [
        'eo'=>[
          'install'=>file_exists($plugin_dir.'event-organiser'.DS.'event-organiser.php'),
          'active'=>function_exists('eo_get_events')
        ],
        'mc'=>[
          'install'=>file_exists($plugin_dir.'my-calendar'.DS.'my-calendar.php'),
          'active'=>function_exists('my_calendar_get_events')
        ],
        'eventum'=>[
          'install'=>file_exists($plugin_dir.'themeum-eventum'.DS.'themeum_eventum.php'),
          'active'=>function_exists('themeum_cat_list')
        ]

      ];

      $is_eo = function_exists('eo_get_events');
      $is_mc = function_exists('my_calendar_get_events');
      $is_eventum = false;
      ?>

      <input type="hidden" id="mec-advimp-page" value="thirdparty">

      <div class="mec-form-row">

         <div class="mec-col-4" for="mec-advimp-importby-inp">
         <select id="mec-advimp-selected-one" class="mec-advimp-select" name="apps[]">
           <option value="eo" <?php if(!$ths['eo']['active']) echo 'disabled="disabled"'; ?>>
            Event Organiser
            <?php if(!$ths['eo']['install']) echo ' Not Installed'; else if(!$ths['eo']['active']) echo 'No Active'; ?>
           </option>

            <option value="myc" <?php if(!$ths['mc']['active']) echo 'disabled="disabled"'; ?>>
            My Calendar
            <?php if(!$ths['mc']['install']) echo ' Not Installed'; else if(!$ths['mc']['active']) echo 'No Active'; ?>
         </option>
              <option value="eventum" <?php if(!$ths['eventum']['active']) echo 'disabled="disabled"'; ?>>
              Eventum (Tevolution-Events)
              <?php if(!$ths['eventum']['install']) echo ' Not Installed'; else if(!$ths['eventum']['active']) echo 'No Active'; ?>
           </option>
         </select>
         </div>
         <div class="mec-col-4">
       <button style="margin: 0px;" data-url="" data-action="getall" data-section="thirdparty" class="button button-primary mec-button-primary mec-advimp-action" type="button" id="mec-advimp-getallevent">
            <?php _e('Get All Events','mec-advanced-importer'); ?>
         </button>
         <input type="hidden" value="thirdparty-calendar-import-start">

      <div id="mec-advimp-loading" class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>

         </div>
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



<?php else: ?>
<h3><?php _e('Scheduled Imports','mec-advanced-importer'); ?></h3>
<form id="advimp-scheduled-table" method="post">
<?php
if (!class_exists('WP_List_Table')) {
   require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

$table = new \MEC_Advanced_Importer_Schedule_Table();
$table->event_class = 'thirdparty';
$table->prepare_items();
echo $table->display();

 ?>

<?php endif; ?>
</form>