<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-event-masonry">
        <?php
        foreach ($this->events as $date => $events) :
            foreach ($events as $event) :
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']])? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';

                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
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

                $speakers = '""';
                if (!empty($event->data->speakers)) {
                    $speakers= [];
                    foreach ($event->data->speakers as $key => $value) {
                        $speakers[] = array(
                        "@type"     => "Person",
                        "name"      => $value['name'],
                        "image"     => $value['thumbnail'],
                        "sameAs"    => $value['facebook'],
                        );
                    }

                    $speakers = json_encode($speakers);
                }
                $schema_settings = isset($settings['schema']) ? $settings['schema'] : '';
                if ($schema_settings == '1') :
                    ?>
            <script type="application/ld+json">
            {
                "@context"      : "http://schema.org",
                "@type"         : "Event",
                "startDate"     : "<?php echo !empty($event->data->meta['mec_date']['start']['date']) ? $event->data->meta['mec_date']['start']['date'] : '' ; ?>",
                "endDate"       : "<?php echo !empty($event->data->meta['mec_date']['end']['date']) ? $event->data->meta['mec_date']['end']['date'] : '' ; ?>",
                "location"      :
                {
                    "@type"         : "Place",
                    "name"          : "<?php echo (isset($location['name']) ? $location['name'] : ''); ?>",
                    "image"         : "<?php echo (isset($location['thumbnail']) ? esc_url($location['thumbnail']) : '');
                    ; ?>",
                    "address"       : "<?php echo (isset($location['address']) ? $location['address'] : ''); ?>"
                },
                "offers": {
                    "url": "<?php echo $event->data->permalink; ?>",
                    "price": "<?php echo isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : '' ; ?>",
                    "priceCurrency" : "<?php echo isset($settings['currency']) ? $settings['currency'] : ''; ?>"
                },
                "performer": <?php echo $speakers; ?>,
                "description"   : "<?php  echo esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', preg_replace('/\s/u', ' ', $event->data->post->post_content))); ?>",
                "image"         : "<?php echo !empty($event->data->featured_image['mecFluentMasonry']) ? esc_html($event->data->featured_image['mecFluentMasonry']) : '' ; ?>",
                "name"          : "<?php esc_html_e($event->data->title); ?>",
                "url"           : "<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"
            }
            </script>
                    <?php
                endif;

                $masonry_filter = '';
                if ($this->filter_by == 'category') {
                    if (isset($event->data->categories) && !empty($event->data->categories)) {
                        $masonry_filter = "[";
                        foreach ($event->data->categories as $key => $value) {
                            $masonry_filter .= '"' . $value['id'] . '",';
                        }

                        $masonry_filter .= "]";
                        $masonry_filter = str_replace(",]", "]", $masonry_filter);
                    }
                } elseif ($this->filter_by == 'label') {
                    if (isset($event->data->labels) && !empty($event->data->labels)) {
                        $masonry_filter = "[";
                        foreach ($event->data->labels as $key => $value) {
                            $masonry_filter .= '"' . $value['id'] . '",';
                        }

                        $masonry_filter .= "]";
                        $masonry_filter = str_replace(",]", "]", $masonry_filter);
                    }
                } elseif ($this->filter_by == 'organizer') {
                    if (isset($event->data->organizers) && !empty($event->data->organizers)) {
                        $masonry_filter = "[";
                        foreach ($event->data->organizers as $key => $value) {
                            $masonry_filter .= '"' . $value['id'] . '",';
                        }

                        $masonry_filter .= "]";
                        $masonry_filter = str_replace(",]", "]", $masonry_filter);
                    }
                } elseif ($this->filter_by == 'location') {
                    if (isset($event->data->locations) && !empty($event->data->locations)) {
                        $masonry_filter = "[";
                        foreach ($event->data->locations as $key => $value) {
                            $masonry_filter .= '"' . $value['id'] . '",';
                        }

                        $masonry_filter .= "]";
                        $masonry_filter = str_replace(",]", "]", $masonry_filter);
                    }
                }

                if (empty($masonry_filter)) {
                    $masonry_filter = "[\"\"]";
                }
                ?>
            <div data-sort-masonry="<?php echo $event->date['start']['date']; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-masonry-item-wrap <?php echo $this->filter_by_classes($event->data->ID); ?>">
                <div class="mec-masonry" style="border-bottom-color: <?php echo esc_attr($event_color); ?>;">
                    <article data-style="<?php echo $label_style; ?>" class="mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>">
                        <div class="mec-masonry-img">
                            <?php
                                if (isset($event->data->featured_image) and $this->masonry_like_grid) {

                                    $image = get_the_post_thumbnail($event->data->ID, 'thumblist');
                                }elseif (isset($event->data->featured_image) and isset($event->data->featured_image['mecFluentMasonry']) and trim($event->data->featured_image['mecFluentMasonry'])){

                                    $image = $event->data->thumbnails['mecFluentMasonry'];
                                }else{

                                    $image = '';
                                }
                                $title = $image;
                                $class = '';
                                echo $this->display_link( $event, $title, $class );
                            ?>
                            <div class="mec-date-wrap<?php echo $event->data->thumbnails['thumblist'] ? ' mec-masonry-has-img' : ''; ?>">
                                <?php
                                $days_number = '';
                                $days_name = '';
                                $months_name = '';
                                $years_number = '';

                                $start_date = $event->date['start']['date'];
                                $start_date_time = strtotime($start_date);
                                $days_number = $this->main->date_i18n($this->date_format_1, $start_date_time);
                                $months_name = $this->main->date_i18n($this->date_format_2, $start_date_time);
                                $days_name = $this->main->date_i18n('l', $start_date_time);
                                $years_number = $this->main->date_i18n('Y', $start_date_time);

                                if(isset($settings['multiple_day_show_method']) and $settings['multiple_day_show_method'] == 'all_days'){

                                    $end_date = $event->date['end']['date'];
                                    $end_date_time = strtotime($end_date);
                                    $day = $this->main->date_i18n($this->date_format_1, $end_date_time);
                                    $m = $this->main->date_i18n($this->date_format_2, $end_date_time);
                                    $y = $this->main->date_i18n('Y', $end_date_time);

                                    if($days_number != $day || $months_name != $m || $years_number != $y){

                                        $days_number .= '-' . $day;
                                        $days_name .= '-'. $this->main->date_i18n('l', $end_date_time);

                                    }

                                    if($months_name != $m || $years_number != $y){

                                        $months_name .= '-' . $m;
                                    }

                                    if($years_number != $y){

                                        $years_number .= '-'. $y;
                                    }
                                }

                                ?>
                                <div class="mec-event-date">
                                    <span class="mec-event-day-num"><?php echo $days_number; ?></span>
                                    <span><?php echo $months_name.' '.$years_number; ?></span>
                                </div>
                                <div class="mec-event-day">
                                    <span><?php echo $days_name; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mec-masonry-content mec-event-grid-modern">
                            <?php do_action('print_extra_fields_masonry', $event); ?>
                            <div class="mec-event-content">
                                <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                                <h4 class="mec-event-title"><?php
                                    $title = null;
                                    $class = 'mec-color-hover';
                                    echo $this->display_link( $event, $title, $class );
                                    echo $this->display_custom_data( $event );
                                ?></h4>
                                <?php echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                                <?php if (isset($location['address']) and trim($location['address'])) : ?>
                                    <div class="mec-event-location">
                                        <i class="mec-sl-location-pin"></i>
                                        <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                    </div>
                                <?php endif; ?>
                                <?php echo $this->main->display_time($start_time, $end_time); ?>
                                <?php if ($this->localtime) {
                                    echo $this->main->module('local-time.type1', array('event'=>$event));
                                } ?>
                                <?php echo MEC_kses::element($this->display_categories($event)); ?>
                                <?php echo MEC_kses::element($this->display_organizers($event)); ?>
                            </div>
                            <div class="mec-event-footer">
                                <?php
                                    $title = $this->get_register_button_title( $event, $event_start_date );
                                    $class = 'mec-booking-button';
                                    echo $this->display_link( $event, $title, $class );
                                ?>
                                <?php if (isset($settings['social_network_status']) and $settings['social_network_status'] != '0') : ?>
                                    <ul class="mec-event-sharing-wrap">
                                        <li class="mec-event-share">
                                            <a href="#" class="mec-event-share-icon">
                                                <i class="mec-sl-share"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <ul class="mec-event-sharing">
                                                <?php echo $this->main->module('links.list', array('event'=>$event)); ?>
                                            </ul>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                                <?php do_action('mec_masonry_button', $event); ?>
                            </div>
                        </div>
                    </article>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
