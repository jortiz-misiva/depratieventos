<?php
/** no direct access **/
defined('MECEXEC') or die();

$has_events = array();
$settings = $this->main->get_settings();
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
foreach ($this->events as $date => $events) :
    $week = $this->week_of_days[$date];
    if (!isset($has_events[$week])) {
        foreach($this->weeks[$week] as $weekday) {
            if (isset($this->events[$weekday]) and count($this->events[$weekday])) {
                $has_events[$week] = true;
            }
        }
    }
    ?>
    <?php if (count($events)) : ?>
        <?php $i = 0; $moreEventsHTML = ''; ?>
        <?php foreach($events as $event) : ?>
            <?php
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                ob_start();
            ?>
            <?php if ($i < 2) { ?>
            <div class="mec-weekly-view-date-events mec-util-hidden mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo $this->id; ?>-<?php echo date('Ym', strtotime($date)).$week; ?>" data-week-number="<?php echo $week; ?>">
            <?php } ?>
                <?php
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
                <?php do_action('mec_weekly_view_content', $event); ?>
                <?php if ($i < 2) { ?>
                    <div data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo $this->get_event_classes($event); ?>" style="border-color: <?php echo esc_attr($event_color); ?>;">
                        <span class="mec-event-bg" style="background-color: <?php echo esc_attr($event_color); ?>;"></span>
                        <h4 class="mec-event-title"><?php
                            $title = null;
                            $class = 'mec-color-hover';
                            $attributes = 'style="color: '.esc_attr($event_color).';"';
                            echo $this->display_link( $event, $title, $class );
                            echo $this->display_custom_data( $event );
                        ?></h4>
                    </div>
                <?php } else { ?>
                    <?php
                    $title = null;
                    $class = 'mec-color-hover';
                    $attributes = 'style="color: '.esc_attr($event_color).';"';
                    $link = $this->display_link( $event, $title, $class );

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
                    ?>
                <?php } ?>
            <?php if ($i < 2) { ?>
            </div>
            <?php } ?>
            <?php
            if (!next($events) && $i >= 2) {
                echo '<span class="mec-more-events-icon">...</span>';
                echo '
                <div class="mec-more-events-wrap">
                    <div class="mec-more-events">
                        <h5 class="mec-more-events-header">' . date('l, F d, Y', strtotime($date)) . '</h5>
                        <div class="mec-more-events-body">
                            ' . $moreEventsHTML . '
                        </div>
                    </div>
                </div>';
            }
            $allEvents[$week][$date][date("H:i", strtotime($start_time)) . '-' . date("H:i", strtotime($end_time))][$i] = ob_get_contents();
            ob_end_clean();
            $i++;
            ?>
        <?php endforeach; ?>
    <?php elseif(!isset($has_events[$week])): $has_events[$week] = 'printed'; ?>
        <?php ob_start(); ?>
        <div class="mec-weekly-view-date-events mec-util-hidden mec-calendar-day-events mec-clear mec-weekly-view-week-<?php echo $this->id; ?>-<?php echo date('Ym', strtotime($date)).$week; ?>" id="mec_weekly_view_date_events<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>" data-week-number="<?php echo $week; ?>">
            <article class="mec-event-article"><h4 class="mec-event-title"><?php _e('No Events', 'mec-fl'); ?></h4><div class="mec-event-detail"></div></article>
        </div>
        <?php
        $allEvents[$week][$date]['noEvent'] = ob_get_contents();
        ob_end_clean();
        ?>
    <?php endif; ?>
<?php endforeach;

$currentTime = date('H:i');
$currentDay = date('d');
$currentMonth = date('m');
$currentTimeFlag = true;
foreach($this->weeks as $week_number => $week) {
    foreach ($times as $timeKey => $time) {
        $nextTime = $timeKey+1 != 24 ? $times[$timeKey+1] : '24:00';
        echo '<dl class="mec-calendar-row mec-more-events-controller" data-week="' . esc_attr($week_number) . '">';
        echo '<span>' . $time . '</span>';
        foreach($week as $day) {
            $currentTimeClass = $this->month == $currentMonth && ($currentDay == date('d', strtotime($day))) && ($time <= $currentTime && $nextTime > $currentTime) ? 'class="mec-fluent-current-time-cell"' : '';
            if (isset($allEvents[$week_number][$day])) {
                if (key($allEvents[$week_number][$day]) === 'noEvent') {
                    echo '<dt ' . $currentTimeClass . '>';
                    if ($this->month == $currentMonth && ($currentDay == date('d', strtotime($day))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                        echo '
                        <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                            <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                            <span class="mec-fluent-current-time-first"></span>
                            <span class="mec-fluent-current-time-last"></span>
                        </span>';
                    }
                    echo '</dt>';
                } else {
                    echo '<dt ' . $currentTimeClass . '>';
                    foreach ($allEvents[$week_number][$day] as $eventTime => $samteTimeEvents) {

                        $eventTime = explode('-', $eventTime);
                        $eventTime[1] = '00:00' == $eventTime[1] ? '24:00' : $eventTime[1];
                        if (strtotime($nextTime) > strtotime($eventTime[0]) && strtotime($time) <= strtotime($eventTime[1])) {
                            foreach ($samteTimeEvents as $event) {
                                echo $event;
                            }
                            if ($this->month == $currentMonth && ($currentDay == date('d', strtotime($day))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                                $currentTimeFlag = false;
                            }
                        }
                    }
                    if ($currentTimeFlag && $this->month == $currentMonth && ($currentDay == date('d', strtotime($day))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                        echo '
                        <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                            <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                            <span class="mec-fluent-current-time-first"></span>
                            <span class="mec-fluent-current-time-last"></span>
                        </span>';
                    }
                    echo '</dt>';
                }
            } else {
                echo '<dt ' . $currentTimeClass . '>';
                if ($this->month == $currentMonth && ($currentDay == date('d', strtotime($day))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                    echo '
                    <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                        <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                        <span class="mec-fluent-current-time-first"></span>
                        <span class="mec-fluent-current-time-last"></span>
                    </span>';
                }
                echo '</dt>';
            }
        }
        if ($timeKey === 23) {
            echo '<span>24:00</span>';
        }
        echo '</dl>';
    }
}
