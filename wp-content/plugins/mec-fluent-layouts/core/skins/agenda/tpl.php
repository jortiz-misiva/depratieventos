<?php
/** no direct access **/
defined('MECEXEC') or die();

$current_month_divider = isset($_REQUEST['current_month_divider']) ? sanitize_text_field($_REQUEST['current_month_divider']) : 0;

// Get layout path
$render_path = dirname(__FILE__) . '/render.php';

ob_start();
include $render_path;
$items_html = ob_get_clean();

if(isset($this->atts['return_items']) and $this->atts['return_items']) {
    echo json_encode(array('html'=>$items_html, 'end_date'=>$this->end_date, 'offset'=>$this->next_offset, 'count'=>$this->found, 'current_month_divider'=>$current_month_divider));
    exit;
}

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.$this->id.'").mecAgendaView(
    {
        id: "'.$this->id.'",
        start_date: "'.$this->start_date.'",
        end_date: "'.$this->end_date.'",
		offset: "'.$this->next_offset.'",
		limit: "'.$this->limit.'",
        current_month_divider: "'.$current_month_divider.'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
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
do_action('mec_start_skin' , $this->id);
do_action('mec_agenda_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div class="mec-wrap mec-fluent-wrap mec-events-agenda-container <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
    <div class="mec-calendar">
        <?php if($this->sf_status) echo $this->sf_search_form(); ?>
        <?php if($this->found): ?>
            <div class="mec-skin-agenda-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
                <div class="mec-wrap mec-events-agenda-wrap mec-custom-scrollbar <?php echo $event_colorskin; ?>">
                    <div class="mec-events-agenda-container mec-event-agenda-<?php echo $this->style; ?>">
                        <?php echo $items_html; ?>
                    </div>
                    <?php if($this->load_more_button and $this->found >= $this->limit): ?>
                        <div class="mec-load-more-wrap"><div class="mec-load-more-button" onclick=""><?php echo __('Load More', 'mec-fl'); ?></div></div>
                    <?php endif; ?>
                    <div class="mec-skin-agenda-no-events-container mec-util-hidden" id="mec_skin_no_events_<?php echo $this->id; ?>">
                        <?php _e('No event found!', 'mec-fl'); ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="mec-skin-agenda-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
                <div class="mec-wrap mec-events-agenda-wrap">
                    <span class="mec-skin-agenda-no-events-container"><?php _e('No event found!', 'mec-fl'); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>