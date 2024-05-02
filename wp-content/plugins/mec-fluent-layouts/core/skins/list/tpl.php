<?php
/** no direct access **/
defined('MECEXEC') or die();

$this->next_previous_button = 1;

$year = $this->year;
$month = $this->month;
$day = $this->day;

$events = $this->events;

$this->active_day = $year.'-'.$month.'-'.current_time('d');

// Get layout path
$render_path = dirname(__FILE__) . '/render.php';

// before/after Month
$_1month_before = strtotime('first day of -1 month', strtotime($this->start_date));
$_1month_after = strtotime('first day of +1 month', strtotime($this->start_date));

// Current month time
$current_month_time = strtotime($this->start_date);

ob_start();
include $render_path;
$month_html = ob_get_clean();

$navigator_html = '';
// Generate Month Navigator
if ($this->next_previous_button) {
    $navigator_html .= '<div class="mec-calendar-header"><h2>'.date_i18n('F Y', $current_month_time).'</h2></div>';

    // Show previous month handler if showing past events allowed
    if (!isset($this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and $this->atts['show_past_events']) or
       (isset($this->atts['show_past_events']) and !$this->atts['show_past_events'] and strtotime(date('Y-m-t', $_1month_before)) >= time())
    ) {
        $navigator_html .= '<div class="mec-previous-month mec-load-month" data-mec-year="'.date('Y', $_1month_before).'" data-mec-month="'.date('m', $_1month_before).'"><i class="mec-sl-angle-left"></i></div>';
    }

    // Show next month handler if needed
    if (isset($this->maximum_date) && $this->maximum_date && strtotime($this->maximum_date) < $_1month_after) {
        $navigator_html .= '';
    } else {
        if (!$this->show_only_expired_events or
           ($this->show_only_expired_events and strtotime(date('Y-m-01', $_1month_after)) <= time())
        ) {
            $navigator_html .= '<div class="mec-next-month mec-load-month" data-mec-year="'.date('Y', $_1month_after).'" data-mec-month="'.date('m', $_1month_after).'"><i class="mec-sl-angle-right"></i></div>';
        }
    }
}

// Return the data if called by AJAX
if (isset($this->atts['return_items']) and $this->atts['return_items']) {
    echo json_encode(array(
        'count'=>$this->found,
        'current_month_divider'=>"0",
        'month'=>$month_html,
        'navigator'=>$navigator_html,
        'previous_month'=>array('label'=>date_i18n('Y F', $_1month_before), 'id'=>date('Ym', $_1month_before), 'year'=>date('Y', $_1month_before), 'month'=>date('m', $_1month_before)),
        'current_month'=>array('label'=>date_i18n('Y F', $current_month_time), 'id'=>date('Ym', $current_month_time), 'year'=>date('Y', $current_month_time), 'month'=>date('m', $current_month_time)),
        'next_month'=>array('label'=>date_i18n('Y F', $_1month_after), 'id'=>date('Ym', $_1month_after), 'year'=>date('Y', $_1month_after), 'month'=>date('m', $_1month_after)),
    ));
    exit;
}

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_list_view_month_'.$this->id.'_'.date('Ym', $current_month_time).'").mecListViewFluent(
    {
        id: "'.$this->id.'",
        today: "'.date('Ymd', strtotime($this->active_day)).'",
        month_id: "'.date('Ym', $current_month_time).'",
        active_month: {year: "'.date('Y', strtotime($this->start_date)).'", month: "'.date('m', strtotime($this->start_date)).'"},
        next_month: {year: "'.date('Y', $_1month_after).'", month: "'.date('m', $_1month_after).'"},
        events_label: "'.esc_attr__('Events', 'mec-fl').'",
        event_label: "'.esc_attr__('Event', 'mec-fl').'",
        month_navigator: '.($this->next_previous_button ? 1 : 0).',
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        style: "'.(isset($this->skin_options['style']) ? $this->skin_options['style'] : null).'",
        ajax_url: "'.admin_url('admin-ajax.php', null).'",
        sed_method: "'.$this->sed_method.'",
        image_popup: "'.$this->image_popup.'",
        sf:
        {
            container: "'.($this->sf_status ? '#mec_search_form_'.$this->id : '').'",
        },
    });
});
</script>';

// Include javascript code into the page
if ($this->main->is_ajax()) {
    echo $javascript;
} else {
    $this->factory->params('footer', $javascript);
}

do_action('mec_start_skin', $this->id);
do_action('mec_list_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div id="mec_skin_<?php echo $this->id; ?>" class="mec-wrap mec-fluent-wrap mec-skin-list-wrap <?php echo ' ' . $this->html_class; ?>">
    <?php if ($this->next_previous_button) : ?>
        <div class="mec-skin-list-view-month-navigator-container mec-calendar-a-month">
            <div class="mec-month-navigator" id="mec_month_navigator_<?php echo $this->id; ?>_<?php echo date('Ym', $current_month_time); ?>">
                <?php echo $navigator_html; ?>
            </div>
        </div>
    <?php else : ?>
        <div class="mec-calendar-header"><h2><?php echo date_i18n('Y F', $current_month_time); ?></h2></div>
    <?php endif; ?>

    <div class="mec-calendar mec-custom-scrollbar">
        <?php if ($this->sf_status) {
            echo $this->sf_search_form();
        } ?>
        <?php if ($this->map_on_top == '1') : ?>
            <div class="mec-wrap mec-skin-map-container <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
                <div class="mec-googlemap-skin" id="mec_googlemap_canvas<?php echo $this->id; ?>" style="height: 500px;">
                <?php
                $map = isset($this->settings['default_maps_view'])?$this->settings['default_maps_view']:'google';
                do_action('mec_map_inner_element_tools', array('map'=>$map));
                ?>
                </div>
                <input type="hidden" id="gmap-data" value="">
            </div>
        <?php endif; ?>
        <div class="mec-calendar-inner" id="mec_skin_events_<?php echo $this->id; ?>">
            <div class="mec-month-container mec-month-container-selected" id="mec_list_view_month_<?php echo $this->id; ?>_<?php echo date('Ym', $current_month_time); ?>" data-month-id="<?php echo date('Ym', $current_month_time); ?>">
                <?php echo $month_html; ?>
            </div>
        </div>
    </div>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>
