<?php
/** no direct access **/
defined('MECEXEC') or die();
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
$allEvents = [];
$times = [
    0 => '00:00',
    1 => '01:00',
    2 => '02:00',
    3 => '03:00',
    4 => '04:00',
    5 => '05:00',
    6 => '06:00',
    7 => '07:00',
    8 => '08:00',
    9 => '09:00',
    10 => '10:00',
    11 => '11:00',
    12 => '12:00',
    13 => '13:00',
    14 => '14:00',
    15 => '15:00',
    16 => '16:00',
    17 => '17:00',
    18 => '18:00',
    19 => '19:00',
    20 => '20:00',
    21 => '21:00',
    22 => '22:00',
    23 => '23:00',
];
$dates = [];
?>

<?php foreach ($this->events as $date => $events) : ?>
    <?php $dates[] = $date; ?>
    <?php $i = 0; ?>
    <?php if (count($events)) : ?>
        <?php $moreEventsHTML = ''; ?>
        <?php foreach ($events as $event) : ?>
            <?php
            $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
            $start_time = isset($event->data->time) ? $event->data->time['start'] : '';
            $end_time = isset($event->data->time) ? $event->data->time['end'] : '';
            if (isset($event->data->meta['mec_allday']) && $event->data->meta['mec_allday']) {
                $start_time = '12:00 am';
                $end_time = '11:59 pm';
            }
            if (isset($event->data->meta['mec_hide_time']) && $event->data->meta['mec_hide_time']) {
                $startTimeHour = isset($event->data->meta['mec_start_time_hour']) ? $event->data->meta['mec_start_time_hour'] : '';
                $startTimeMinutes = isset($event->data->meta['mec_start_time_minutes']) ? sprintf("%02d", $event->data->meta['mec_start_time_minutes']) : '';
                $startTimeAmPm = isset($event->data->meta['mec_start_time_ampm']) ? $event->data->meta['mec_start_time_ampm'] : '';
                $start_time = $startTimeHour . ':' . $startTimeMinutes . ' ' . $startTimeAmPm;
                $endTimeHour = isset($event->data->meta['mec_end_time_hour']) ? $event->data->meta['mec_end_time_hour'] : '';
                $endTimeMinutes = isset($event->data->meta['mec_end_time_minutes']) ? sprintf("%02d", $event->data->meta['mec_end_time_minutes']) : '';
                $endTimeAmPm = isset($event->data->meta['mec_end_time_ampm']) ? $event->data->meta['mec_end_time_ampm'] : '';
                $end_time = $endTimeHour . ':' . $endTimeMinutes . ' ' . $endTimeAmPm;
            }
            $label_style = '';
            if ( !empty($event->data->labels) ) :
                foreach( $event->data->labels as $label) {
                    if (!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                    if ( $label['style']  == 'mec-label-featured' ) {
                        $label_style = esc_html__( 'Featured' , 'mec-fl' );
                    } elseif ( $label['style']  == 'mec-label-canceled' ) {
                        $label_style = esc_html__( 'Canceled' , 'mec-fl' );
                    }
                }
            endif;
            $eventPastClassName = (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : '';
            if ($i >= 2) {
                $title = null;
                $class = 'mec-color-hover';
                $attributes = 'style="color: '.esc_attr($event_color).';"';
                $link = $this->display_link( $event, $title, $class );
                $link .= $this->display_custom_data( $event );
                $moreEventsHTML .= '
                    <div class="'.((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : '').'ended-relative simple-skin-ended" style="border-color:' . $event_color . ';">
                        <div class="mec-event-image">' . $event->data->thumbnails['thumbnail'] . '</div>
                        <div class="mec-more-events-content">
                            <h4 class="mec-event-title">'.$link.'</h4>
                            <i class="mec-sl-clock"></i>
                            <span class="mec-event-start-time">' . $start_time . '</span>
                            <span class="mec-event-end-time">' . $end_time . '</span>
                        </div>
                    </div>';
            } else {
                $title = null;
                $class = 'mec-color-hover';
                $attributes = 'style="color: '.esc_attr($event_color).';"';
                $link = $this->display_link( $event, $title, $class ,$attributes);
                $link .= $this->display_custom_data( $event );
                $allEvents[$date][date("H:i", strtotime($start_time)) . '-' . date("H:i", strtotime($end_time))][$i] = '
                <div data-style="' . $label_style . '" class="' . $eventPastClassName . 'mec-event-article ' . $this->get_event_classes($event) . '" style="border-color: ' . esc_attr($event_color) . ';">
                    <span class="mec-event-bg" style="background-color: ' . esc_attr($event_color) . ';"></span>
                    <h4 class="mec-event-title">'.$link.'</h4>
                </div>';
            }
            if (!next($events) && $i >= 2) {
                $allEvents[$date][date("H:i", strtotime($start_time)) . '-' . date("H:i", strtotime($end_time))][$i] = '
                <span class="mec-more-events-icon">...</span>
                <div class="mec-more-events-wrap">
                    <div class="mec-more-events">
                        <h5 class="mec-more-events-header">' . date('l, F d, Y', strtotime($date)) . '</h5>
                        <div class="mec-more-events-body">
                            ' . $moreEventsHTML . '
                        </div>
                    </div>
                </div>';
            }
            $i++;
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>

<?php
$currentTime = date('H:i');
$currentDay = date('d');
$currentMonth = date('m');
$currentTimeFlag = true;
?>
<div class="mec-daily-view-events-left-side mec-custom-scrollbar mec-clear">
    <?php foreach ($dates as $date) { ?>
        <div class="mec-daily-view-events mec-util-hidden" id="mec-daily-view-events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>">
            <h5 class="mec-daily-today-title">
                <span><?php echo esc_html(date('D', strtotime($date))); ?></span>
                <span><?php echo esc_html(date('d/m', strtotime($date))); ?></span>
            </h5>
            <div class="mec-daily-view-events-inner">
                <?php foreach ($times as $timeKey => $time) { ?>
                    <?php
                    $nextTime = $timeKey+1 != 24 ? $times[$timeKey+1] : '24:00';
                    $currentTimeClass = $this->month == $currentMonth && ($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime) ? ' mec-fluent-current-time-cell' : '';
                    ?>
                    <div class="mec-daily-view-events-item mec-more-events-controller<?php echo esc_attr($currentTimeClass); ?>">
                        <span class="mec-time"><?php echo esc_html($time); ?></span>
                        <?php
                        if (isset($allEvents[$date])) {
                            foreach ($allEvents[$date] as $eventTime => $samteTimeEvents) {
                                $eventTime = explode('-', $eventTime);
                                if ($nextTime > $eventTime[0] && $time <= $eventTime[1]) {
                                    foreach ($samteTimeEvents as $event) {
                                        echo $event;
                                    }
                                    if ($this->month == $currentMonth && ($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                                        $currentTimeFlag = false;
                                    }
                                }
                            }
                            if ($currentTimeFlag && $this->month == $currentMonth &&  ($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                                echo '
                                <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                                    <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                                    <span class="mec-fluent-current-time-first"></span>
                                    <span class="mec-fluent-current-time-last"></span>
                                </span>';
                            }
                        } else {
                            if ($this->month == $currentMonth && ($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                                echo '
                                <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                                    <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                                    <span class="mec-fluent-current-time-first"></span>
                                    <span class="mec-fluent-current-time-last"></span>
                                </span>';
                            }
                        }
                        ?>
                        <?php if ($timeKey === 23) { ?>
                            <span class="mec-time-end">24:00</span>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

<div class="mec-daily-view-events-right-side mec-custom-scrollbar mec-clear">
    <?php $i = 0; ?>
    <?php foreach ($this->events as $date => $events) : ?>
    <div class="mec-daily-view-date-events mec-util-hidden" id="mec_daily_view_date_events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>">
        <?php if ($i === 0) { ?>
            <h5 class="mec-daily-today-title"><?php echo esc_html__('Events on', 'mec-fl') . ' ' . date('F d', strtotime($date)); ?></h5>
        <?php } ?>
        <?php if (count($events)) : ?>
        <?php foreach ($events as $event) : ?>
            <?php
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                if (isset($event->data->meta['mec_allday']) && $event->data->meta['mec_allday']) {
                    $start_time = '00:00';
                    $end_time = '23:59';
                }
                $event_color =  isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                $label_style = '';
                if ( !empty($event->data->labels) ):
                foreach( $event->data->labels as $label)
                {
                    if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                    if ( $label['style']  == 'mec-label-featured' )
                    {
                        $label_style = esc_html__( 'Featured' , 'mec-fl' );
                    }
                    elseif ( $label['style']  == 'mec-label-canceled' )
                    {
                        $label_style = esc_html__( 'Canceled' , 'mec-fl' );
                    }
                }
                endif;
                $speakers = '""';
                if ( !empty($event->data->speakers))
                {
                    $speakers= [];
                    foreach ($event->data->speakers as $key => $value) {
                        $speakers[] = array(
                            "@type" 	=> "Person",
                            "name"		=> $value['name'],
                            "image"		=> $value['thumbnail'],
                            "sameAs"	=> $value['facebook'],
                        );
                    }
                    $speakers = json_encode($speakers);
                }
            $schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
            if($schema_settings == '1' ):
            ?>
            <script type="application/ld+json">
            {
                "@context" 		: "http://schema.org",
                "@type" 		: "Event",
                "startDate" 	: "<?php echo !empty( $event->data->meta['mec_date']['start']['date'] ) ? $event->data->meta['mec_date']['start']['date'] : '' ; ?>",
                "endDate" 		: "<?php echo !empty( $event->data->meta['mec_date']['end']['date'] ) ? $event->data->meta['mec_date']['end']['date'] : '' ; ?>",
                "location" 		:
                {
                    "@type" 		: "Place",
                    "name" 			: "<?php echo (isset($location['name']) ? $location['name'] : ''); ?>",
                    "image"			: "<?php echo (isset($location['thumbnail']) ? esc_url($location['thumbnail'] ) : '');; ?>",
                    "address"		: "<?php echo (isset($location['address']) ? $location['address'] : ''); ?>"
                },
                "offers": {
                    "url": "<?php echo $event->data->permalink; ?>",
                    "price": "<?php echo isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : '' ; ?>",
                    "priceCurrency" : "<?php echo isset($settings['currency']) ? $settings['currency'] : ''; ?>"
                },
                "performer": <?php echo $speakers; ?>,
                "description" 	: "<?php  echo esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', preg_replace('/\s/u', ' ', $event->data->post->post_content))); ?>",
                "image" 		: "<?php echo !empty($event->data->featured_image['full']) ? esc_html($event->data->featured_image['full']) : '' ; ?>",
                "name" 			: "<?php esc_html_e($event->data->title); ?>",
                "url"			: "<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"
            }
            </script>
            <?php endif; ?>
            <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo $this->get_event_classes($event); ?>">
                <div class="mec-event-image"><?php echo $event->data->thumbnails['mecFluentThumb']; ?></div>
                <div class="mec-event-content">
                    <h4 class="mec-event-title">
                        <?php
                            $title = null;
                            $class = 'mec-color-hover';
                            $attributes = 'style="color: '.esc_attr($event_color).';"';
                            echo $this->display_link( $event, $title, $class ,$attributes);
                            echo $this->display_custom_data( $event );
                        ?>
                        <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                    </h4>
                    <?php if (trim($start_time) && !(isset($event->data->meta['mec_hide_time']) && $event->data->meta['mec_hide_time'])): ?>
                        <span class="mec-event-time"><?php echo $this->main->display_time($start_time,$end_time); ?></span>
                    <?php endif; ?>
                    <?php if (isset($location['name'])) { ?>
                        <?php if (!(isset($event->data->meta['mec_hide_time']) && $event->data->meta['mec_hide_time'])) { ?>
                            <span class="mec-seperator">|</span>
                        <?php } ?>
                        <span class="mec-event-detail"><?php echo esc_html($location['name']); ?></span>
                    <?php } ?>
                    <?php echo \MEC_kses::element($this->display_categories($event)); ?>
                    <?php echo \MEC_kses::element($this->display_organizers($event)); ?>
                </div>
                <?php echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
            </article>
        <?php endforeach; ?>
        <?php else: ?>
            <article class="mec-event-article"><div class="mec-daily-view-no-event"><?php _e('No events', 'mec-fl'); ?></div></article>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>