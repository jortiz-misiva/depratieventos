<?php
/** no direct access **/
defined('MECEXEC') or die();

// Inclue OWL Assets
$this->main->load_owl_assets();

// Generating javascript code tpl
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_'.$this->id.'").mecFullCalendar(
    {
        id: "'.$this->id.'",
        atts: "'.http_build_query(array('atts'=>$this->atts), '', '&').'",
        ajax_url: "'.admin_url('admin-ajax.php', NULL).'",
        sed_method: "'.$this->sed_method.'",
        image_popup: "'.$this->image_popup.'",
        sf:
        {
            container: "'.($this->sf_status ? '#mec_search_form_'.$this->id : '').'",
        },
        skin: "'.$this->default_view.'",
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
do_action('mec_start_skin' , $this->id);
do_action('mec_full_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div id="mec_skin_<?php echo $this->id; ?>" class="mec-wrap mec-fluent-wrap mec-skin-full-calendar-container <?php echo $event_colorskin; ?>">
    <div class="mec-totalcal-box">
        <div class="mec-totalcal-view">
            <?php
            $skins = [];
            if ($this->yearly) {
                $skins['yearly'] = 'yearly';
            }
            if ($this->monthly) {
                $skins['monthly'] = 'monthly';
            }
            if ($this->weekly) {
                $skins['weekly'] = 'weekly';
            }
            if ($this->daily) {
                $skins['daily'] = 'daily';
            }
            if ($this->list) {
                $skins['list'] = 'list';
            }
            if ($this->grid) {
                $skins['grid'] = 'grid';
            }
            if ($this->tile) {
                $skins['tile'] = 'tile';
            }
            if ($skins) {
                switch ($this->default_view) {
                    case 'yearly':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-yearlyview mec-totalcalview-selected" data-skin="yearly">' . esc_html__('Yearly', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'monthly':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-monthlyview mec-totalcalview-selected" data-skin="monthly">' . esc_html__('Monthly', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'weekly':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-weeklyview mec-totalcalview-selected" data-skin="weekly">' . esc_html__('Weekly', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'daily':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-dailyview mec-totalcalview-selected" data-skin="daily">' . esc_html__('Daily', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'list':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-listview mec-totalcalview-selected" data-skin="list">' . esc_html__('List', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'grid':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-gridview mec-totalcalview-selected" data-skin="grid">' . esc_html__('Grid', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                    case 'tile':
                        if(isset($skins[$this->default_view])) :
                            echo '<span class="mec-totalcal-tileview mec-totalcalview-selected" data-skin="tile">' . esc_html__('Tile', 'mec-fl') . '</span>';
                            unset($skins[$this->default_view]);
                        endif;
                        break;
                }
            }

            if ($skins) {
                $total_skins = count($skins) + 1; // 1 for list
                $tab_skins = ( $total_skins - 2 > 0 ) ? $total_skins - 2 : $total_skins;
                $tab_skins = min( [ 3, $tab_skins ] );
                $i = 1;
                foreach ($skins as $skin) {
                    if ($i == $tab_skins ) {
                        break;
                    }
                    switch ($skin) {
                        case 'yearly':
                            if($this->yearly) :
                                echo '<span class="mec-totalcal-yearlyview" data-skin="yearly">' . esc_html__('Yearly', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'monthly':
                            if($this->monthly) :
                                echo '<span class="mec-totalcal-monthlyview" data-skin="monthly">' . esc_html__('Monthly', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'weekly':
                            if($this->weekly) :
                                echo '<span class="mec-totalcal-weeklyview" data-skin="weekly">' . esc_html__('Weekly', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'daily':
                            if($this->daily) :
                                echo '<span class="mec-totalcal-dailyview" data-skin="daily">' . esc_html__('Daily', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'list':
                            if($this->list) :
                                echo '<span class="mec-totalcal-listview" data-skin="list">' . esc_html__('List', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'grid':
                            if($this->grid) :
                                echo '<span class="mec-totalcal-gridview" data-skin="grid">' . esc_html__('Grid', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                        case 'tile':
                            if($this->tile) :
                                echo '<span class="mec-totalcal-tileview" data-skin="tile">' . esc_html__('Tile', 'mec-fl') . '</span>';
                            endif;
                            unset($skins[$skin]);
                            break;
                    }
                    $i++;
                }
            }
            if ($skins) {
                ?>
                <span class="mec-fluent-more-views-icon">...
                    <span class="mec-fluent-more-views-content">
                        <?php
                        foreach ($skins as $skin) {
                            switch ($skin) {
                                case 'yearly':
                                    if($this->yearly) :
                                        echo '<span class="mec-totalcal-yearlyview" data-skin="yearly">' . esc_html__('Yearly', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'monthly':
                                    if($this->monthly) :
                                        echo '<span class="mec-totalcal-monthlyview" data-skin="monthly">' . esc_html__('Monthly', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'weekly':
                                    if($this->weekly) :
                                        echo '<span class="mec-totalcal-weeklyview" data-skin="weekly">' . esc_html__('Weekly', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'daily':
                                    if($this->daily) :
                                        echo '<span class="mec-totalcal-dailyview" data-skin="daily">' . esc_html__('Daily', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'list':
                                    if($this->list) :
                                        echo '<span class="mec-totalcal-listview" data-skin="list">' . esc_html__('List', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'grid':
                                    if($this->grid) :
                                        echo '<span class="mec-totalcal-gridview" data-skin="grid">' . esc_html__('Grid', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                                case 'tile':
                                    if($this->tile) :
                                        echo '<span class="mec-totalcal-tileview" data-skin="tile">' . esc_html__('Tile', 'mec-fl') . '</span>';
                                    endif;
                                    unset($skins[$skin]);
                                    break;
                            }
                        }
                        ?>
                    </span>
                </span>

            <?php } ?>
        </div>
    </div>

    <div id="mec_full_calendar_container_<?php echo $this->id; ?>">
        <?php echo $this->load_skin($this->default_view); ?>
    </div>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>