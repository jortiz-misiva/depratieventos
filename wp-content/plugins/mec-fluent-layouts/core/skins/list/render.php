<?php

/** no direct access **/
defined('MECEXEC') or die();

$settings = $this->main->get_settings();
$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$map_events = [];
$showLoadMore = false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$display_cats = isset($this->skin_options['display_categories']) ? (boolean) $this->skin_options['display_categories'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<div class="mec-event-list-standard">
    <?php for ($list_day = 1; $list_day <= $days_in_month; $list_day++) { ?>
        <?php
        $time = strtotime($year . '-' . $month . '-' . $list_day);
        $today = date('Y-m-d', $time);
        ?>
        <?php if (isset($events[$today]) and count($events[$today])) { ?>
            <?php $showLoadMore = true; ?>
            <?php foreach ($this->events[$today] as $event) : ?>
                <?php
                $map_events[] = $event;
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';


                $event_id = $event->ID;
                $tickets = isset($event->data->tickets) ? $event->data->tickets : array();
                $dates = isset($event->dates) ? $event->dates : array($event->date);

                $occurrence_time = isset($dates[0]['start']['timestamp']) ? $dates[0]['start']['timestamp'] : strtotime($dates[0]['start']['date']);

                $default_ticket_number = 0;
                if (count($tickets) == 1) $default_ticket_number = 1;

                $book = $this->getBook();
                $availability = $book->get_tickets_availability($event_id, strtotime($event_start_date . ' ' . $event->data->time['start']));



                $spots = 0;
                foreach ($tickets as $ticket_id => $ticket) {
                    $spots = isset($availability[$ticket_id]) ? $availability[$ticket_id] : -1;
                }

                $label_style = '';
                if (!empty($event->data->labels)) {
                    foreach ($event->data->labels as $label) {
                        if (!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) {
                            continue;
                        }
                        if ($label['style']  == 'mec-label-featured') {
                            $label_style = esc_html__('Featured', 'mec-fl');
                        } elseif ($label['style']  == 'mec-label-canceled') {
                            $label_style = esc_html__('Canceled', 'mec-fl');
                        }
                    }
                }

                $speakers = '""';
                if (!empty($event->data->speakers)) {
                    $speakers = [];
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
                            "@context": "http://schema.org",
                            "@type": "Event",
                            "startDate": "<?php echo !empty($event->data->meta['mec_date']['start']['date']) ? $event->data->meta['mec_date']['start']['date'] : ''; ?>",
                            "endDate": "<?php echo !empty($event->data->meta['mec_date']['end']['date']) ? $event->data->meta['mec_date']['end']['date'] : ''; ?>",
                            "location": {
                                "@type": "Place",
                                "name": "<?php echo (isset($location['name']) ? $location['name'] : ''); ?>",
                                "image": "<?php echo (isset($location['thumbnail']) ? esc_url($location['thumbnail']) : '');; ?>",
                                "address": "<?php echo (isset($location['address']) ? $location['address'] : ''); ?>"
                            },
                            "offers": {
                                "url": "<?php echo $event->data->permalink; ?>",
                                "price": "<?php echo isset($event->data->meta['mec_cost']) ? $event->data->meta['mec_cost'] : ''; ?>",
                                "priceCurrency": "<?php echo isset($settings['currency']) ? $settings['currency'] : ''; ?>"
                            },
                            "performer": <?php echo $speakers; ?>,
                            "description": "<?php echo esc_html(preg_replace('/<p>\\s*?(<a .*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '<div class="figure">$1</div>', preg_replace('/\s/u', ' ', $event->data->post->post_content))); ?>",
                            "image": "<?php echo !empty($event->data->featured_image['full']) ? esc_html($event->data->featured_image['full']) : ''; ?>",
                            "name": "<?php esc_html_e($event->data->title); ?>",
                            "url": "<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"
                        }
                    </script>
                <?php endif; ?>

                <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>" style="border-right-color: <?php echo esc_attr($event_color); ?>;" itemscope>
                    <?php
                    $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';

                    // Safe Excerpt for UTF-8 Strings
                    if (!trim($excerpt)) {
                        $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                        $words = array_slice($ex, 0, 10);

                        $excerpt = implode(' ', $words);
                    }
                    ?>
                    <div class="mec-event-image">
                        <?php
                            if (isset($event->data->featured_image) and isset($event->data->featured_image['mecFluentList']) and trim($event->data->featured_image['mecFluentList'])){

                                $image = $event->data->thumbnails['mecFluentList'];
                            }else{

                                $image = '';
                            }
                            $title = $image;
                            $class = '';
                            echo $this->display_link( $event, $title, $class );
                        ?>
                    </div>
                    <div class="mec-event-content">
                        <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                        <h3 class="mec-event-title">
                            <?php
                                $class = 'mec-color-hover';
                                echo $this->display_link( $event, null, $class );
                                echo $this->display_custom_data( $event );
                            ?>
                            <?php echo $this->main->get_normal_labels($event, $display_label) . $this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                            <?php if (!empty($label_style)) {
                                echo '<span class="mec-fc-style">' . $label_style . '</span>';
                            } ?>
                        </h3>
                        <?php do_action('mec_fluent_booking_button', $event); ?>
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
                                        <?php echo $this->main->module('links.list', array('event' => $event)); ?>
                                    </ul>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="mec-event-meta">
                        <div class="mec-date-details">
                            <?php if (isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                                <span class="mec-event-d"><?php echo esc_html(date_i18n($this->date_format_fluent_1, strtotime($today))); ?></span>
                            <?php else : ?>
                                <span class="mec-event-d">
                                    <?php echo $this->main->dateify($event, $this->date_format_fluent_1); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <?php echo $this->main->display_time($start_time, $end_time); ?>
                        <?php if ($this->localtime) {
                            echo $this->main->module('local-time.type1', array('event' => $event));
                        } ?>
                        <?php if (isset($location['name'])) : ?>
                            <div class="mec-venue-details">
                                <span><?php echo (isset($location['name']) ? $location['name'] : ''); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->display_price and isset($event->data->meta['mec_cost']) and $event->data->meta['mec_cost'] != '') : ?>
                            <div class="mec-price-details">
                                <i class="mec-sl-wallet"></i>
                                <span>
                                    <?php esc_html_e('Price:', 'mec-fl'); ?>
                                    <span class="mec-price-number"><?php echo (is_numeric($event->data->meta['mec_cost']) ? $this->main->render_price($event->data->meta['mec_cost'], $event) : $event->data->meta['mec_cost']); ?></span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->display_available_tickets) : ?>
                            <div class="mec-available-tickets-details">
                                <i class="mec-sl-tag"></i>
                                <span>
                                    <?php esc_html_e('Available Tickets:', 'mec-fl'); ?>
                                    <span class="mec-available-tickets-number"><?php echo ($spots != '-1' ? $spots : __('Unlimited', 'mec-fl')); ?></span>
                                </span>
                            </div>
                        <?php endif; ?>
                        <?php if ( $display_cats ) : ?>
                            <?php if (!empty($this->display_categories($event))) : ?>
                            <div class="mec-list-cats-details">
                                <i class="mec-sl-folder"></i>
                                <?php echo $this->display_categories($event); ?>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php echo MEC_kses::element($this->display_organizers($event)); ?>

                        <?php do_action('mec_list_fluent_right_box', $event); ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php } ?>
    <?php } ?>
    <?php if ($this->loadMoreRunning == false && $showLoadMore == false) { ?>
        <span class="mec-fluent-no-event"><?php esc_html_e('No Events', 'mec-fl'); ?></span>
    <?php } ?>
</div>

<?php if ($showLoadMore && $this->load_more_button and $this->found >= $this->limit) : ?>
    <?php
    $endMonth = $this->year . '-' . $this->month . '-' . date('t', strtotime($this->year . '-' . $this->month));
    $maximumDate = $this->maximum_date && (strtotime($this->maximum_date) < strtotime($endMonth)) ? $this->maximum_date : $endMonth;
    ?>
    <div class="mec-load-more-wrap">
        <div class="mec-load-more-button" data-end-date="<?php echo esc_attr($this->end_date); ?>" data-maximum-date="<?php echo esc_attr($maximumDate); ?>" data-next-offset="<?php echo esc_attr($this->next_offset); ?>" data-year="<?php echo esc_attr($this->year); ?>" data-month="<?php echo esc_attr($this->month); ?>" onclick=""><?php echo __('Load More', 'mec'); ?></div>
    </div>
<?php endif; ?>

<?php
if (isset($this->map_on_top) and $this->map_on_top) :

    // Include Map Assets such as JS and CSS libraries
    $this->main->load_map_assets();
    if (isset($map_events) and !empty($map_events)) {

        // It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
        if ((isset($_REQUEST['action']) and $_REQUEST['action'] == 'mec_list_load_more') and isset($_REQUEST['sf'])) {
            $this->geolocation_focus = true;
        }

        $map_javascript = '<script type="text/javascript">
    var mecmap' . $this->id . ';
    jQuery(document).ready(function()
    {
        var jsonPush = gmapSkin(' . json_encode($this->render->markers($map_events, $this->style)) . ');
        mecmap' . $this->id . ' = jQuery("#mec_googlemap_canvas' . $this->id . '").mecGoogleMaps(
        {
            id: "' . $this->id . '",
            autoinit: false,
            atts: "' . http_build_query(array('atts' => $this->atts), '', '&') . '",
            zoom: ' . (isset($settings['google_maps_zoomlevel']) ? $settings['google_maps_zoomlevel'] : 14) . ',
            icon: "' . apply_filters('mec_marker_icon', $this->main->asset('img/m-04.png')) . '",
            styles: ' . ((isset($settings['google_maps_style']) and trim($settings['google_maps_style']) != '') ? $this->main->get_googlemap_style($settings['google_maps_style']) : "''") . ',
            markers: jsonPush,
            clustering_images: "' . $this->main->asset('img/cluster1/m') . '",
            getDirection: 0,
            ajax_url: "' . admin_url('admin-ajax.php', null) . '",
            geolocation: "' . $this->geolocation . '",
            geolocation_focus: ' . $this->geolocation_focus . ',
            sf:
            {
                container: "'.($this->sf_status ? '#mec_search_form_'.$this->id : '').'",
            },
        });

        var mecinterval' . $this->id . ' = setInterval(function()
        {
            if(jQuery("#mec_googlemap_canvas' . $this->id . '").is(":visible"))
            {
                mecmap' . $this->id . '.init();
                clearInterval(mecinterval' . $this->id . ');
            };
        }, 1000);
    });
    </script>';

        $map_data = new stdClass;
        $map_data->id = $this->id;
        $map_data->atts = $this->atts;
        $map_data->events =  $map_events;
        $map_data->render = $this->render;
        $map_data->geolocation = $this->geolocation;
        $map_data->sf_status = null;
        $map_data->main = $this->main;

        $map_javascript = apply_filters('mec_map_load_script', $map_javascript, $map_data, $settings);

        // Include javascript code into the page
        if ($this->main->is_ajax()) {
            echo $map_javascript;
        } else {
            $this->factory->params('footer', $map_javascript);
        }
    }
endif;
