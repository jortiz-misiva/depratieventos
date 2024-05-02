<?php

$featured_s = isset($settings['advanced_speaker'])?$settings['advanced_speaker']:null;
$sections = array('speaker');
$title = $main->m('taxonomy_speaker', __('Speaker', 'mec-advanced-speaker'));

$single_page =  isset($featured_s['single_page'])?$featured_s['single_page']:null;
$cfg = array('skin'=>array(),'limit'=>array(),'cols'=>array(),'load_more'=>array(),'detaile'=>array());

foreach ($sections as $section) {
    $cfg[$section]['skin'] = isset($featured_s[$section.'_skin'])?$featured_s[$section.'_skin']:'list';
    $cfg[$section]['event_skin'] = isset($featured_s[$section.'_event_skin'])?$featured_s[$section.'_event_skin']:'list';

    $cfg[$section]['limit'] = isset($featured_s[$section.'_limit'])?$featured_s[$section.'_limit']:MEC_ADVANCED_SPEAKER_ROWS_LIMIT;
    $cfg[$section]['event_limit'] = isset($featured_s[$section.'_event_limit'])?$featured_s[$section.'_event_limit']:MEC_ADVANCED_SPEAKER_ROWS_LIMIT;
    $cfg[$section]['cols'] = isset($featured_s[$section.'_cols'])?$featured_s[$section.'_cols']:'3';
    $cfg[$section]['load_more'] = isset($featured_s[$section.'_load_more'])?$featured_s[$section.'_load_more']:'option_website';
//    $cfg[$section]['detaile'] = isset($featured_s[$section.'_detaile'])?$featured_s[$section.'_detaile']:'0';
    $cfg[$section]['type_link'] = isset($featured_s[$section.'_type_link'])?$featured_s[$section.'_type_link']:'dialog';
    $cfg[$section]['show_event_list'] = isset($featured_s[$section.'_show_event_list'])?$featured_s[$section.'_show_event_list']:'1';
    $cfg[$section]['link_target'] = isset($featured_s[$section.'_link_target'])?$featured_s[$section.'_link_target']:'';
    $cfg[$section]['enable_link_section_title'] = isset($featured_s[$section.'_enable_link_section_title'])?$featured_s[$section.'_enable_link_section_title']:'1';
}

$skins = array('list','grid');
//$details = array(
//    'option_website'=>__('Priority Website on input','mec-advanced-speaker'),
//    'force_website'=>__('Force Website Link','mec-advanced-speaker'),
//    'force_single'=>__('Force Single Page','mec-advanced-speaker')
//);
$type_links = array(
    'link'=>__('Link','mec-advanced-speaker'),
    'dialog'=>__('Dialog','mec-advanced-speaker'),
    'dialog_link'=>__('Dialog and Link','mec-advanced-speaker')
);
?>
<script>
    var mec_enable_link_section_title=<?php echo $cfg[$section]['enable_link_section_title']; ?>;
</script>
<div id="advanced_speaker_option" class="mec-options-fields">

    <h4 class="mec-form-subtitle"><?php echo sprintf(__('Advanced %s Settings', 'mec-advanced-speaker'),$title); ?></h4>
    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_featured_single_page"><?php _e('Single Page','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <select id="mec_settings_featured_single_page" name="mec[settings][advanced_speaker][single_page]">
                <?php foreach (get_pages() as $k => $v):?>
                <option value="<?php echo $v->ID; ?>" <?php if($single_page==$v->ID) echo 'selected="selected"'; ?>>
                    <?php echo $v->post_title; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Single Page','mec-advanced-speaker'); ?></h5>
                    <div class="content">
                        <p><?php _e('For show the Speaker details after clicking the "View Details" button. <br> ShortCode on page','mec-advanced-speaker'); ?>: [advanced-speaker-single-public]</p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <?php foreach ($sections as $section): ?>

    <h5 class="mec-form-subtitle"><?php echo sprintf( __("%s Default Settings", 'mec-advanced-speaker'),$title); ?></h5>
    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_skin"><?php echo $title; ?> <?php _e('Skin','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <select id="mec_settings_waiting_featured_<?php echo $section; ?>_skin" name="mec[settings][advanced_speaker][<?php echo $section; ?>_skin]">
                <?php foreach ($skins as $skin):?>
                <option value="<?php echo $skin; ?>" <?php if($cfg[$section]['skin']==$skin) echo 'selected="selected"'; ?>>
                    <?php echo ucwords($skin); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo $title; ?> <?php _e('Skin','mec-advanced-speaker'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Skin for showing the %s skin.','mec-advanced-speaker'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_event_skin"><?php echo sprintf(__('%s Event Skin','mec-advanced-speaker'),$title); ?></label>
        <div class="mec-col-9">
            <select id="mec_settings_waiting_featured_<?php echo $section; ?>_event_skin" name="mec[settings][advanced_speaker][<?php echo $section; ?>_event_skin]">
                <?php foreach ($skins as $skin):?>
                <option value="<?php echo $skin; ?>" <?php if($cfg[$section]['event_skin']==$skin) echo 'selected="selected"'; ?>>
                    <?php echo ucwords($skin); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo sprintf(__('%s Event Skin','mec-advanced-speaker'),$title); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Event skin for showing the %s skin.','mec-advanced-speaker'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_event_limit"><?php _e('Event Limit','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_event_limit" name="mec[settings][advanced_speaker][<?php echo $section; ?>_event_limit]" value="<?php echo $cfg[$section]['event_limit']; ?>" placeholder="Default: <?php echo MEC_ADVANCED_SPEAKER_ROWS_LIMIT; ?>" class="">
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Event Limit','mec-advanced-speaker'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: %s','mec-advanced-speaker'),$title,MEC_ADVANCED_SPEAKER_ROWS_LIMIT); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_limit">
        <?php echo sprintf(__('%s Limit','mec-advanced-speaker'),$title); ?>
        </label>
        <div class="mec-col-9">
            <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_limit" name="mec[settings][advanced_speaker][<?php echo $section; ?>_limit]" value="<?php echo $cfg[$section]['limit']; ?>" placeholder="<?php _e('Default','mec-advanced-speaker'); ?>: <?php echo MEC_ADVANCED_SPEAKER_ROWS_LIMIT; ?>" class="">
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo sprintf(__('%s Limit','mec-advanced-speaker'),$title); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show Rows for %s on single page. Default: %s','mec-advanced-speaker'),$title,MEC_ADVANCED_SPEAKER_ROWS_LIMIT); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_cols"><?php _e('Event Cols','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_cols" name="mec[settings][advanced_speaker][<?php echo $section; ?>_cols]" value="<?php echo $cfg[$section]['cols']; ?>" placeholder="Default: 4" class="">
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Event Cols','mec-advanced-speaker'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: 4','mec-advanced-speaker'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_load_more"><?php _e('Load More','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <label>
                <input type="hidden" name="mec[settings][advanced_speaker][<?php echo $section; ?>_load_more]" value="0">
                <input value="1" <?php if($cfg[$section]['load_more']=='1') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_speaker][<?php echo $section; ?>_load_more]">
                <?php _e('Load More Button on event list bottom.','mec-advanced-speaker'); ?>
            </label>
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Load More','mec-advanced-speaker'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: no','mec-advanced-speaker'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_show_event_list"><?php _e('Show Event List','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <label>
                <input type="hidden" name="mec[settings][advanced_speaker][<?php echo $section; ?>_show_event_list]" value="0">
                <input value="1" <?php if($cfg[$section]['show_event_list']!='0') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_speaker][<?php echo $section; ?>_show_event_list]">
                <?php _e('Show Event list on single page.','mec-advanced-speaker'); ?>
            </label>
        </div>
    </div>

    <div class="mec-form-row">
       <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_enable_link_section_title"><?php _e('Redirect the Speaker title','mec-advanced-speaker'); ?></label>
       <div class="mec-col-9">
           <label>
               <input type="hidden"  name="mec[settings][advanced_speaker][<?php echo $section; ?>_enable_link_section_title]" value="0" >
               <input value="1" id="mec_settings_enable_link_section_title" <?php if($cfg[$section]['enable_link_section_title']!='0') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_speaker][<?php echo $section; ?>_enable_link_section_title]" onchange="mec_enable_link_section_title_changed()">
               <?php _e('Redirect the Speaker title to single page.','mec-advanced-speaker'); ?>
           </label>
       </div>
    </div>

     <div class="mec-form-row mec_select_speaker_type_link">
            <label class="mec-col-3" for="mec_settings_<?php echo $section; ?>_type_link"><?php _e('Type Link','mec-advanced-speaker'); ?></label>
            <div class="mec-col-9">
                <select id="mec_settings_<?php echo $section; ?>_type_link" name="mec[settings][advanced_speaker][<?php echo $section; ?>_type_link]">
                    <?php foreach ($type_links as $type_link=>$type_link_title):?>
                        <option value="<?php echo $type_link; ?>" <?php if($cfg[$section]['type_link']==$type_link) echo 'selected="selected"'; ?>>
                            <?php echo $type_link_title; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
     </div>

<!--    <div class="mec-form-row">-->
<!--        <label class="mec-col-3" for="mec_settings_waiting_details_page">--><?php //_e('Details Link','mec-advanced-speaker'); ?><!--</label>-->
<!--        <div class="mec-col-9">-->
<!--            <select id="mec_settings_waiting_featured_--><?php //echo $section; ?><!--_detaile" name="mec[settings][advanced_speaker][--><?php //echo $section; ?><!--_detaile]">-->
<!--                --><?php //foreach ($details as $detaile=>$detaile_title):?>
<!--                <option value="--><?php //echo $detaile; ?><!--" --><?php //if($cfg[$section]['detaile']==$detaile) echo 'selected="selected"'; ?>
<!--                    --><?php //echo $detaile_title; ?>
<!--                </option>-->
<!--                --><?php //endforeach; ?>
<!--            </select>-->
<!--            <span class="mec-tooltip">-->
<!--                <div class="box left">-->
<!--                    <h5 class="title">--><?php //_e('Details View Link','mec-advanced-speaker'); ?><!--</h5>-->
<!--                    <div class="content">-->
<!--                        <p>-->
<!--                            <ul>-->
<!--                                <li><strong>--><?php //_e('Force Website Link','mec-advanced-speaker'); ?><!--:</strong> --><?php //_e('Force Loaded from Website Filed.','mec-advanced-speaker'); ?><!--</li>-->
<!--                                <li><strong>--><?php //_e('Force Single Page','mec-advanced-speaker'); ?><!--:</strong> --><?php //_e('Force Loaded from Auto Single page.','mec-advanced-speaker'); ?><!--</li>-->
<!--                                <li><strong>--><?php //_e('Priority Website on input','mec-advanced-speaker'); ?><!--:</strong> --><?php //_e('On input website field loaded from website field, then loaded on single page.','mec-advanced-speaker'); ?><!--</li>-->
<!--                            </ul>-->
<!--                        </p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <i title="" class="dashicons-before dashicons-editor-help"></i>-->
<!--            </span>-->
<!--        </div>-->
<!--    </div>-->
    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_waiting_details_page"><?php _e('Redirect and Website Link Target','mec-advanced-speaker'); ?></label>
        <div class="mec-col-9">
            <select id="mec_settings_waiting_featured_<?php echo $section; ?>_link_target" name="mec[settings][advanced_speaker][<?php echo $section; ?>_link_target]">
                <?php
                    $targets = array(
                        '_blank' => __( 'New Window', 'mec-advanced-speaker' ),
                        '' => __( 'Current Window', 'mec-advanced-speaker' ),
                    );
                    foreach ($targets as $t_key => $t_title):
                        $selected = isset( $cfg[$section]['link_target'] ) && $cfg[$section]['link_target'] == $t_key ? true : false;
                        ?>
                        <option value="<?php echo $t_key; ?>" <?php if($selected) echo 'selected="selected"'; ?>>
                            <?php echo $t_title; ?>
                        </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php endforeach; ?>

    <h5 class="mec-form-subtitle"><?php _e('Shortcode Guideline', 'mec-advanced-speaker'); ?></h5>

    <div class="mec-form-row">
        <strong><?php _e('Single Speaker:','mec-advanced-speaker'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[mec-speaker id="{SPEAKER_ID}" limit_events="{NUMBER}" only_ongoing_events="{true/false}" load_more="{true/false}" events_style="{list/grid}" exclude_details="{Exclude-List}" cols="{NUMBER}" show_event_list="{true/false}" show_only_past_events="false/true"]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <strong><?php _e('Speakers List:','mec-advanced-speaker'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[speaker-list limit="{NUMBER}" load_more="{true/false}" display_style="{list/grid}" filter="{true/false}" search="{true/false}" exclude="{SPEAKER_ID}" cols="{NUMBER}" exclude_details="{Exclude-List}" order_by="{name|added_date|all_events}" order="{DESC|ASC}"]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <strong><?php _e('Featured Speakers:','mec-advanced-speaker'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[speaker-featured limit="{NUMBER}"]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <hr>
        <p><strong><?php _e('Exclude-List:','mec-advanced-speaker'); ?></strong> <?php _e('website, address, tel, email','mec-advanced-speaker'); ?></p>
        <p><strong><?php _e('Note:','mec-advanced-speaker'); ?></strong> <?php _e('instead {} or {true/false} or {ID} just use text or number without {} for example : instead load_more="{true/false}" use this: load_more="false"','mec-advanced-speaker'); ?></p>
    </div>
</div>
