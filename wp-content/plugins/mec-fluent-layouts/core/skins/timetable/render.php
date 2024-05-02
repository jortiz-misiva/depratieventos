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
?>
<div class="mec-timetable-t2-wrap mec-custom-scrollbar mec-more-events-controller">
    <div class="mec-timetable-t2-wrap-inner mec-clear">
        <?php
        $firstDay = true;
        $currentTime = date('H:i');
        $currentDay = date('d');
        foreach ($this->events as $date => $events) {
            ?>
            <div class="mec-timetable-t2-col mec-timetable-col-<?php echo $this->number_of_days; ?>">
                <div class="mec-ttt2-title">
                    <span class="mec-day-name"><?php echo date_i18n('D', strtotime($date)); ?></span>
                    <span class="mec-day-num"><?php echo date_i18n('j', strtotime($date)) . '/' . date_i18n('m', strtotime($date)); ?></span>
                </div>
                <?php
                $i = 0;
                $moreEventsHTML = '';
                foreach ($events as $event) :
                    ob_start();
                    $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                    $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                    $label_style = '';
                    if (!empty($event->data->labels)) {
                        foreach ($event->data->labels as $label) {
                            if (!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) {
                                continue;
                            }

                            if ($label['style'] == 'mec-label-featured') {
                                $label_style = esc_html__('Featured', 'mec-fl');
                            } elseif ($label['style'] == 'mec-label-canceled') {
                                $label_style = esc_html__('Canceled', 'mec-fl');
                            }
                        }
                    }
                    ?>
                    <?php if ($i < 2) { ?>
                        <div data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article <?php echo $this->get_event_classes($event); ?>" style="border-color: <?php echo esc_attr($event_color); ?>;">
                            <span class="mec-event-bg" style="background-color: <?php echo esc_attr($event_color); ?>;"></span>
                            <h4 class="mec-event-title"><?php
                                $title = null;
                                $class = 'mec-color-hover';
                                echo $this->display_link( $event, $title, $class );
                                echo $this->display_custom_data( $event );
                            ?></h4>
                        </div>
                    <?php } else { ?>
                        <?php
                        $title = null;
                        $class = 'mec-color-hover';
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
                    $allEvents[date("H:i", strtotime($start_time)) . '-' . date("H:i", strtotime($end_time))][$i] = ob_get_contents();
                    ob_end_clean();
                    $i++;
                endforeach;

                foreach ($times as $timeKey => $time) {
                    $nextTime = $timeKey+1 != 24 ? $times[$timeKey+1] : '24:00';
                    $currentTimeClass = ($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime) ? ' mec-fluent-current-time-cell' : '';
                    echo '<div class="mec-cell mec-more-events-inner-controller' . esc_attr($currentTimeClass) . '">';
                    if ($firstDay === true) {
                        echo '<span class="mec-time">' . $time . '</span>';
                    }
                    if ($allEvents) {
                        foreach ($allEvents as $eventTime => $samteTimeEvents) {
                            $eventTime = explode('-', $eventTime);
                            if ($nextTime > $eventTime[0] && $time <= $eventTime[1]) {
                                if ($samteTimeEvents) {
                                    foreach ($samteTimeEvents as $event) {
                                        echo $event;
                                    }
                                }
                            } else {
                                if (($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                                    echo '
                                    <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                                        <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                                        <span class="mec-fluent-current-time-first"></span>
                                        <span class="mec-fluent-current-time-last"></span>
                                    </span>';
                                }
                            }
                        }
                    } else {
                        if (($currentDay == date('d', strtotime($date))) && ($time <= $currentTime && $nextTime > $currentTime)) {
                            echo '
                            <span class="mec-fluent-current-time" data-time="' . esc_attr(date('i', strtotime($currentTime))) . '">
                                <span class="mec-fluent-current-time-text">' . esc_html__('Current time is', 'mec-fl') . ' ' . esc_html($currentTime) . '</span>
                                <span class="mec-fluent-current-time-first"></span>
                                <span class="mec-fluent-current-time-last"></span>
                            </span>';
                        }
                    }
                    if ($firstDay === true && $timeKey === 23) {
                        echo '<span class="mec-time mec-time-end">24:00</span>';
                    }
                    echo '</div>';
                }
                ?>
            </div>
            <?php
            $allEvents = [];
            $firstDay = false;
        }
        ?>
    </div>
</div>
