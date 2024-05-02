<?php

ob_start();
include $this->render_path;
$items_html = ob_get_clean();

if($this->return_items)
{
    echo json_encode(array('html'=>$items_html, 'offset'=>$this->next_offset, 'count'=>$this->found));
    exit;
}

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_advanced_location_skin_'.$this->id.'").mecAdvancedLocationGridView(
    {
        id: "'.$this->id.'",
		offset: "'.$this->next_offset.'",
        style: "grid",
		limit: "'.$this->limit.'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        section: "'.$this->section.'"
    });
});
</script>';
echo $javascript;
?>
<div class="mec-advanced-location">
<div class="mec-featured-wrap mec-wrap mec-skin-grid-container <?php echo $this->html_class; ?>" id="mec_advanced_location_skin_<?php echo $this->id; ?>">

    <?php if($this->found): ?>

    <div class="mec-skin-grid-events-container" id="mec_advanced_location_skin_events_<?php echo $this->id; ?>">
        <?php echo $items_html; ?>
    </div>
    <div class="mec-skin-grid-no-events-container mec-util-hidden" id="mec_featured_skin_no_events_<?php echo $this->id; ?>">
        <?php _e('No item found!', 'mec-advanced-location'); ?>
    </div>
    <?php else: ?>
    <div class="mec-skin-grid-events-container" id="mec_advanced_location_skin_events_<?php echo $this->id; ?>">
        <?php _e('No item found!', 'mec-advanced-location'); ?>
    </div>
    <?php endif; ?>

    <?php if($this->load_more_button and $this->found >= $this->limit): ?>
    <div class="mec-load-more-wrap"><div class="mec-load-more-button" onclick=""><?php echo __('Load More', 'mec-advanced-location'); ?></div></div>
    <?php endif; ?>

</div>
</div>