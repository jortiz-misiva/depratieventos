<?php
/** no direct access **/
defined('MECEXEC') or die();

$settings = $this->main->get_settings();
$count = 1;
$months_html = '';
$calendar_type = 'calendar';
for($i = 1; $i <= 12; $i++) {
    $monthHTML = $this->draw_monthly_calendar($this->year, $i, $this->events, $calendar_type);
    $doc = new \DOMDocument();
    $doc->loadHTML($monthHTML);
    $node = $doc->getElementsByTagName('div')->item(0);
    $node->setAttribute('data-month',$i);
    $months_html .= $doc->saveHTML($node);
}

$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>

<div class="mec-yearly-calendar-sec mec-custom-scrollbar">
    <div class="mec-yearly-calendar-sec-inner">
        <?php echo $months_html ?>
    </div>
</div>

<div class="mec-yearly-agenda-sec mec-custom-scrollbar">
    <div class="mec-yearly-agenda-sec-inner">
        <?php
        $nextMonth = 0;
        foreach ($this->events as $date => $events) :
            if ($nextMonth === 0) {
                echo '<h3 class="mec-yearly-agenda-sec-title">' . esc_html__('Events on', 'mec-fl') . ' <span class="mec-month-name" data-year="' . $this->year . '">' . date('F', strtotime($date)) . '</span></h3>';
            }

            $limitation_class = 'mec-events-agenda';
            $currentMonth = date('n', strtotime($date));
            if ($nextMonth === 0) {
                $nextMonth = $currentMonth + 1;
            } else {
                if ($currentMonth >= $nextMonth) {
                    $limitation_class = 'mec-events-agenda mec-util-hidden';
                }
            }
            ?>
            <div class="<?php echo $limitation_class; ?>" data-month="<?php echo esc_attr($currentMonth); ?>">
                <div class="mec-agenda-events-wrap" id="mec_yearly_view<?php echo $this->id; ?>_<?php echo date('Ymd', strtotime($date)); ?>">
                    <?php
                    foreach($events as $event) {
                        $count++;
                        $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                        $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                        $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                        $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                        $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                        $label_style = '';
                        if ( !empty($event->data->labels) ):
                            foreach( $event->data->labels as $label) {
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
                        if ( !empty($event->data->speakers)) {
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
                        <div class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-agenda-event <?php echo $this->get_event_classes($event); ?>">
                            <div class="mec-event-image"><?php echo $event->data->thumbnails['mecFluentThumb']; ?></div>
                            <div class="mec-event-content">
                                <h4 class="mec-event-title mec-agenda-event-title"><?php
                                    $title = null;
                                    $class = 'mec-color-hover';
                                    $attributes = 'style="color: '.esc_attr($event_color).';"';
                                    echo $this->display_link( $event, $title, $class );
                                    echo $this->display_custom_data( $event );

                                    if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>';
                                ?></h4>
                                <?php if (trim($start_time)): ?>
                                    <span class="mec-event-time"><?php echo $this->main->display_time($start_time,$end_time); ?></span>
                                <?php endif; ?>
                                <?php if (isset($location['name'])) { ?>
                                    <span class="mec-seperator">|</span>
                                    <span class="mec-event-detail"><?php echo esc_html($location['name']); ?></span>
                                <?php } ?>
                            </div>
                            <?php echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                            <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
