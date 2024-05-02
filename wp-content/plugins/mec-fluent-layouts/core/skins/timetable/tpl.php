<?php
/** no direct access **/
defined('MECEXEC') or die();

do_action('mec_start_skin' , $this->id);
do_action('mec_timetable_skin_head');

// Get layout path
$render_path = dirname(__FILE__) . '/render.php';

// Generate Events
ob_start();
include $render_path;
$date_events = ob_get_clean();

// Return the data if called by AJAX
if(isset($this->atts['return_items']) and $this->atts['return_items'])
{
    echo json_encode(array(
        'date_events'=>$date_events,
    ));
    exit;
}

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.$this->id.'").mecWeeklyProgram(
    {
        id: "'.$this->id.'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
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
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div id="mec_skin_<?php echo $this->id; ?>" class="mec-timetable-wrap mec-wrap mec-fluent-wrap <?php echo $event_colorskin . ' ' . $this->html_class; ?>">

    <div class="mec-calendar">
        <?php if($this->sf_status) echo $this->sf_search_form(); ?>
        <div id="mec_skin_events_<?php echo $this->id; ?>">
            <?php echo $date_events; ?>
        </div>
    </div>

</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>