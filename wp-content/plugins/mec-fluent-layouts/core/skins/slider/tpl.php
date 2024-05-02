<?php
/** no direct access **/
defined('MECEXEC') or die();

// Get layout path
$render_path = dirname(__FILE__) . '/render.php';

ob_start();
include $render_path;
$items_html = ob_get_clean();

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.$this->id.'").mecSliderView(
    {
        id: "'.$this->id.'",
        start_date: "'.$this->start_date.'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        autoplay: "'.$this->autoplay.'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        navText: ' . wp_json_encode(['<i class="mec-fa-angle-left"></i>', '<i class="mec-fa-angle-right"></i>']) . ',
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);
do_action('mec_start_skin' , $this->id);
do_action('mec_slider_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div class="mec-wrap mec-fluent-wrap mec-skin-slider-container<?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
    <?php if($this->found): ?>
        <div class="mec-skin-slider-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
            <?php echo $items_html; ?>
        </div>
    <?php else: ?>
        <div class="mec-skin-slider-events-container" id="mec_skin_events_<?php echo $this->id; ?>">
            <?php _e('No event found!', 'mec-fl'); ?>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>