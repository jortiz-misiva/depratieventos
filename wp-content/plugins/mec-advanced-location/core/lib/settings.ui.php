<?php

$featured_s = isset($settings['advanced_location'])?$settings['advanced_location']:null;
$sections = array('location');
$title = $main->m('taxonomy_location', __('Location', 'mec-advanced-location'));

$single_page =  isset($featured_s['single_page'])?$featured_s['single_page']:null;
$cfg = array('skin'=>array(),'limit'=>array(),'cols'=>array(),'load_more'=>array(),'detaile'=>array());

foreach ($sections as $section) {
    $cfg[$section]['skin'] = isset($featured_s[$section.'_skin'])?$featured_s[$section.'_skin']:'list';
    $cfg[$section]['event_skin'] = isset($featured_s[$section.'_event_skin'])?$featured_s[$section.'_event_skin']:'list';

    $cfg[$section]['limit'] = isset($featured_s[$section.'_limit'])?$featured_s[$section.'_limit']:MEC_ADVANCED_LOCATION_ROWS_LIMIT;
    $cfg[$section]['event_limit'] = isset($featured_s[$section.'_event_limit'])?$featured_s[$section.'_event_limit']:MEC_ADVANCED_LOCATION_ROWS_LIMIT;
    $cfg[$section]['cols'] = isset($featured_s[$section.'_cols'])?$featured_s[$section.'_cols']:'3';
    $cfg[$section]['load_more'] = isset($featured_s[$section.'_load_more'])?$featured_s[$section.'_load_more']:'option_website';
//    $cfg[$section]['detaile'] = isset($featured_s[$section.'_detaile'])?$featured_s[$section.'_detaile']:'0';
    $cfg[$section]['link_target'] = isset($featured_s[$section.'_link_target'])?$featured_s[$section.'_link_target']:'';
    $cfg[$section]['show_event_list'] = isset($featured_s[$section.'_show_event_list'])?$featured_s[$section.'_show_event_list']:'1';
    $cfg[$section]['show_map'] = isset($featured_s[$section.'_show_map'])?$featured_s[$section.'_show_map']:'1';
    $cfg[$section]['enable_link_section_title'] = isset($featured_s[$section.'_enable_link_section_title'])?$featured_s[$section.'_enable_link_section_title']:'1';
}

$skins = array('list','grid');
//$details = array(
//    'option_website'=>__('Priority Website on input','mec-advanced-location'),
//    'force_website'=>__('Force Website Link','mec-advanced-location'),
//    'force_single'=>__('Force Single Page','mec-advanced-location')
//);
?>

<div id="advanced_location_option" class="mec-options-fields">

    <h4 class="mec-form-subtitle"><?php echo sprintf(__('Advanced %s Settings', 'mec-advanced-location'),$title); ?></h4>
    <div class="mec-form-row">
        <label class="mec-col-3" for="mec_settings_featured_single_page"><?php _e('Single Page','mec-advanced-location'); ?></label>
        <div class="mec-col-9">
            <select id="mec_settings_featured_single_page" name="mec[settings][advanced_location][single_page]">
                <?php foreach (get_pages() as $k => $v):?>
                    <option value="<?php echo $v->ID; ?>" <?php if($single_page==$v->ID) echo 'selected="selected"'; ?>>
                        <?php echo $v->post_title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Single Page','mec-advanced-location'); ?></h5>
                    <div class="content">
                        <p><?php _e('For show the Location details after clicking the "View Details" button. <br> ShortCode on page','mec-advanced-location'); ?>: [advanced-location-single-public]</p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>

    <?php foreach ($sections as $section): ?>

        <h5 class="mec-form-subtitle"><?php echo sprintf( __("%s Default Settings", 'mec-advanced-location'),$title); ?></h5>
        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_skin">
                <?php echo $title; ?> <?php _e('Skin','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <select id="mec_settings_waiting_featured_<?php echo $section; ?>_skin" name="mec[settings][advanced_location][<?php echo $section; ?>_skin]">
                    <?php foreach ($skins as $skin):?>
                        <option value="<?php echo $skin; ?>" <?php if($cfg[$section]['skin']==$skin) echo 'selected="selected"'; ?>>
                            <?php echo ucwords($skin); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo $title; ?> <?php _e('Skin','mec-advanced-location'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Skin for showing the %s skin.','mec-advanced-location'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_event_skin"><?php echo sprintf(__('%s Event Skin','mec-advanced-location'),$title); ?></label>
            <div class="mec-col-9">
                <select id="mec_settings_waiting_featured_<?php echo $section; ?>_event_skin" name="mec[settings][advanced_location][<?php echo $section; ?>_event_skin]">
                    <?php foreach ($skins as $skin):?>
                        <option value="<?php echo $skin; ?>" <?php if($cfg[$section]['event_skin']==$skin) echo 'selected="selected"'; ?>>
                            <?php echo ucwords($skin); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo sprintf(__('%s Event Skin','mec-advanced-location'),$title); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Event skin for showing the %s skin.','mec-advanced-location'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_event_limit"><?php _e('Event Limit','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_event_limit" name="mec[settings][advanced_location][<?php echo $section; ?>_event_limit]" value="<?php echo $cfg[$section]['event_limit']; ?>" placeholder="<?php _e('Default','mec-advanced-location'); ?>: <?php echo MEC_ADVANCED_LOCATION_ROWS_LIMIT; ?>" class="">
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Event Limit','mec-advanced-location'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: %s','mec-advanced-location'),$title,MEC_ADVANCED_LOCATION_ROWS_LIMIT); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_limit"><?php echo sprintf(__('%s Limit','mec-advanced-location'),$title); ?></label>
            <div class="mec-col-9">
                <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_limit" name="mec[settings][advanced_location][<?php echo $section; ?>_limit]" value="<?php echo $cfg[$section]['limit']; ?>" placeholder="<?php _e('Default','mec-advanced-location'); ?>: <?php echo MEC_ADVANCED_LOCATION_ROWS_LIMIT; ?>" class="">
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php echo sprintf(__('%s Limit','mec-advanced-location'),$title); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show Rows for %s on single page. Default: %s','mec-advanced-location'),$title,MEC_ADVANCED_LOCATION_ROWS_LIMIT); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_cols"><?php _e('Event Cols','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <input type="number" id="mec_settings_waiting_featured_<?php echo $section; ?>_cols" name="mec[settings][advanced_location][<?php echo $section; ?>_cols]" value="<?php echo $cfg[$section]['cols']; ?>" placeholder="Default: 4" class="">
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Event Cols','mec-advanced-location'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: 4','mec-advanced-location'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_load_more"><?php _e('Load More','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <label>
                    <input type="hidden" name="mec[settings][advanced_location][<?php echo $section; ?>_load_more]" value="0">
                    <input value="1" <?php if($cfg[$section]['load_more']=='1') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_location][<?php echo $section; ?>_load_more]">
                    <?php _e('Load More Button on event list bottom.','mec-advanced-location'); ?>
                </label>
                <span class="mec-tooltip">
                <div class="box left">
                    <h5 class="title"><?php _e('Load More','mec-advanced-location'); ?></h5>
                    <div class="content">
                        <p><?php echo sprintf(__('Show evented for %s on single page. Default: no','mec-advanced-location'),$title); ?></p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_show_event_list"><?php _e('Show Event List','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <label>
                    <input type="hidden" name="mec[settings][advanced_location][<?php echo $section; ?>_show_event_list]" value="0">
                    <input value="1" <?php if($cfg[$section]['show_event_list']!='0') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_location][<?php echo $section; ?>_show_event_list]">
                    <?php _e('Show Event list on single page.','mec-advanced-location'); ?>
                </label>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_show_map"><?php _e('Show Map','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <label>
                    <input type="hidden" name="mec[settings][advanced_location][<?php echo $section; ?>_show_map]" value="0">
                    <input value="1" <?php if($cfg[$section]['show_map']!='0') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_location][<?php echo $section; ?>_show_map]">
                    <?php _e('Show Map on single page.','mec-advanced-location'); ?>
                </label>
            </div>
        </div>

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_featured_<?php echo $section; ?>_enable_link_section_title"><?php _e('Redirect the location title','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <label>
                    <input type="hidden" name="mec[settings][advanced_location][<?php echo $section; ?>_enable_link_section_title]" value="0">
                    <input value="1" <?php if($cfg[$section]['enable_link_section_title']!='0') echo 'checked="checked"'; ?> type="checkbox" name="mec[settings][advanced_location][<?php echo $section; ?>_enable_link_section_title]">
                    <?php _e('Redirect the location title to single page.','mec-advanced-location'); ?>
                </label>
            </div>
        </div>

<!--        <div class="mec-form-row">-->
<!--            <label class="mec-col-3" for="mec_settings_waiting_details_page">--><?php //_e('Details Link','mec-advanced-location'); ?><!--</label>-->
<!--            <div class="mec-col-9">-->
<!--                <select id="mec_settings_waiting_featured_--><?php //echo $section; ?><!--_detaile" name="mec[settings][advanced_location][--><?php //echo $section; ?><!--_detaile]">-->
<!--                    --><?php //foreach ($details as $detaile=>$detaile_title):?>
<!--                        <option value="--><?php //echo $detaile; ?><!--" --><?php //if($cfg[$section]['detaile']==$detaile) echo 'selected="selected"'; ?>
<!--                            --><?php //echo $detaile_title; ?>
<!--                        </option>-->
<!--                    --><?php //endforeach; ?>
<!--                </select>-->
<!--                <span class="mec-tooltip">-->
<!--                <div class="box left">-->
<!--                    <h5 class="title">--><?php //_e('Details View Link','mec-advanced-location'); ?><!--</h5>-->
<!--                    <div class="content">-->
<!--                        <p>-->
<!--                            <ul>-->
<!--                                <li><strong>--><?php //_e('Force Website Link','mec-advanced-location'); ?><!--:</strong> --><?php //_e('Force Loaded from Website Filed.','mec-advanced-location'); ?><!--</li>-->
<!--                                <li><strong>--><?php //_e('Force Single Page','mec-advanced-location'); ?><!--:</strong> --><?php //_e('Force Loaded from Auto Single page.','mec-advanced-location'); ?><!--</li>-->
<!--                                <li><strong>--><?php //_e('Priority Website on input','mec-advanced-location'); ?><!--:</strong> --><?php //_e('On input website field loaded from website field, then loaded on single page.','mec-advanced-location'); ?><!--</li>-->
<!--                            </ul>-->
<!--                        </p>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <i title="" class="dashicons-before dashicons-editor-help"></i>-->
<!--            </span>-->
<!--            </div>-->
<!--        </div>-->

        <div class="mec-form-row">
            <label class="mec-col-3" for="mec_settings_waiting_details_page"><?php _e('Redirect and Website Link Target','mec-advanced-location'); ?></label>
            <div class="mec-col-9">
                <select id="mec_settings_waiting_featured_<?php echo $section; ?>_link_target" name="mec[settings][advanced_location][<?php echo $section; ?>_link_target]">
                    <?php
                    $targets = array(
                        '_blank' => __( 'New Window', 'mec-advanced-location' ),
                        '' => __( 'Current Window', 'mec-advanced-location' ),
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

    <h5 class="mec-form-subtitle"><?php _e('Shortcode Guideline', 'mec-advanced-location'); ?></h5>

    <div class="mec-form-row">
        <strong><?php _e('Single Pages','mec-advanced-location'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[mec-location id="{LOCATION_ID}" limit_events="{NUMBER}" only_ongoing_events="true/false" load_more="true/false" events_style="list/grid" exclude_details="{Exclude-List}" cols="{NUMBER}" show_event_list="false/true" show_map="false/true" show_only_past_events="false/true"]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <strong><?php _e('List Pages','mec-advanced-location'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[location-list limit="{NUMBER}" load_more="{true/false}" display_style="{list/grid}" filter="{true/false}" search="{true/false}" search_in="{name/address}" exclude="{LOCATION_ID}" cols="{NUMBER}" exclude_details="{Exclude-List}" order_by="{name|added_date|all_events}" order="{DESC|ASC}"]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <strong><?php _e('Featured Pages','mec-advanced-location'); ?></strong>
        <div class="mec-box-option">
            <p class="description">[location-featured limit="{NUMBER}" ]</p>
        </div>
    </div>

    <div class="mec-form-row">
        <hr>
        <p><strong><?php _e('Exclude-List:','mec-advanced-location'); ?></strong> <?php _e('website, address, tel, email','mec-advanced-location'); ?></p>
        <p><strong><?php _e('Note:','mec-advanced-location'); ?></strong> <?php _e('instead {} or {true/false} or {ID} just use text or number without {} for example : instead load_more="{true/false}" use this: load_more="false"','mec-advanced-location'); ?></p>
    </div>

</div>
