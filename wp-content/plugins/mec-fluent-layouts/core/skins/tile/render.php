<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin'] ) || isset($styling['color'])) ? 'colorskin-custom' : '';
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
$settings = $this->main->get_settings();

$map_events = array();
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-event-tile-view">
        <?php
        $count = $this->count;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;

        $rcount = 1 ;
        foreach($this->events as $date):
            foreach($date as $event):
                $map_events[] = $event;
                echo ($rcount == 1) ? '<div class="row">' : '';
                echo '<div class="col-md-'.$col.' col-sm-'.$col.'">';
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $event_end_date = !empty($event->date['end']['date']) ? $event->date['end']['date'] : '';
                $event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
                $background_image = (isset($event->data->featured_image['mecFluentCover']) && trim($event->data->featured_image['mecFluentCover'])) ? ' url(\''.trim($event->data->featured_image['mecFluentCover']).'\')' : '';

                $label_style = '';
                if(!empty($event->data->labels))
                {
                    foreach($event->data->labels as $label)
                    {
                        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;

                        if($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured' , 'mec');
                        elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled' , 'mec');
                    }
                }

                // MEC Schema
                do_action('mec_schema', $event);
                $multi_day_class = $event_start_date !== $event_end_date ? 'tile-multipleday-event' : '';
                ?>
                <article <?php echo 'style="background:' . $event_color . $background_image. '"'; ?> data-style="<?php echo $label_style; ?>" class="<?php echo ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : ''); ?> mec-event-article mec-tile-item mec-clear <?php echo $this->get_event_classes($event); ?> <?php echo $multi_day_class; ?> ">
                    <?php do_action('mec_skin_tile_view', $event); ?>
                    <div class="event-tile-view-head clearfix">
                        <?php if(isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                            <div class="mec-event-date"><?php echo $this->main->date_i18n($this->date_format_clean_1, strtotime($event->date['start']['date'])); ?></div>
                            <div class="mec-event-month"><?php echo $this->main->date_i18n($this->date_format_clean_2, strtotime($event->date['start']['date'])); ?></div>
                        <?php else: ?>
                            <div class="mec-event-month"><?php echo $this->main->dateify($event, $this->date_format_clean_1 .' '. $this->date_format_clean_2); ?></div>
                        <?php endif; ?>
                        <div class="mec-event-time"><i class="mec-sl-clock"></i><?php echo $start_time; ?></div>
                    </div>
                    <div class="mec-event-content">
                        <div class="mec-event-detail">
                            <?php echo (isset($location['name']) ? '<i class="mec-sl-location-pin"></i>' . $location['name'] : ''); ?>
                            <?php echo \MEC_kses::element($this->display_categories($event)); ?>
                            <?php echo \MEC_kses::element($this->display_organizers($event)); ?>
                        </div>
                        <?php echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                        <h4 class="mec-event-title"><?php
                            $title = null;
                            $class = 'mec-color-hover';
                            echo $this->display_link( $event, $title, $class );
                            echo $this->display_custom_data( $event );
                            echo $this->main->get_flags($event->data->ID, $event_start_date);
                        ?></h4>
                    </div>
                </article>
                <?php
                echo '</div>';
                if($rcount == $count)
                {
                    echo '</div>';
                    $rcount = 0;
                }

                $rcount++;
                ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php
        $div_count = count($map_events) - (floor(count($map_events) / $count) * $count);
        if($div_count > 0 and $div_count < $count) echo '</div>';
    ?>
</div>