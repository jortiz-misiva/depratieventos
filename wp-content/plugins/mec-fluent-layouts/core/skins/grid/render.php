<?php

/** no direct access **/
defined('MECEXEC') or die();

$settings = $this->main->get_settings();
$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$this->include_events_times = isset($this->skin_options['include_events_times']) ? $this->skin_options['include_events_times'] : false;
$map_events = array();
$showLoadMore = false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$display_cats = isset($this->skin_options['display_categories']) ? (boolean) $this->skin_options['display_categories'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<div class="mec-event-grid-classic">
    <?php
    $count      = $this->count;
    $grid_div   = $this->count;

    if ($count == 0 or $count == 5) $col = 4;
    else $col = 12 / $count;

    $rcount = 1;
    $i = 0;
    for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {
        $time = strtotime($year . '-' . $month . '-' . $list_day);
        $today = date('Y-m-d', $time);
        if (isset($events[$today]) and count($events[$today])) {
            $showLoadMore = true;
            foreach ($this->events[$today] as $event) :
                $i++;
                $map_events[] = $event;
                echo ($rcount == 1) ? '<div class="row">' : '';
                echo '<div class="col-md-' . $col . ' col-sm-' . $col . '">';

                $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) ? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';

                $label_style = '';
                if (!empty($event->data->labels)) {
                    foreach ($event->data->labels as $label) {
                        if (!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                        if ($label['style'] == 'mec-label-featured') $label_style = esc_html__('Featured', 'mec-fl');
                        elseif ($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'mec-fl');
                    }
                }

                $speakers = '""';
                if (!empty($event->data->speakers)) {
                    $speakers = [];
                    foreach ($event->data->speakers as $key => $value) {
                        $speakers[] = array(
                            "@type"     => "Person",
                            "name"        => $value['name'],
                            "image"        => $value['thumbnail'],
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
                <?php endif;
                echo '<article data-style="' . $label_style . '" class="' . ((isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event' : '') . ' mec-event-article mec-clear ' . $this->get_event_classes($event) . '" style="border-top-color: ' . esc_attr($event_color) . ';" itemscope>';
                ?>
                <div class="mec-event-image">
                    <a data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['mecFluentGrid']; ?></a>
                    <?php if (isset($settings['multiple_day_show_method']) && $settings['multiple_day_show_method'] == 'all_days') : ?>
                        <span class="mec-event-date"><i class="mec-sl-calendar"></i><?php echo esc_html(date_i18n($this->date_format_fluent_1, strtotime($today))); ?></span>
                    <?php else : ?>
                        <span class="mec-event-date">
                            <i class="mec-sl-calendar"></i>
                            <?php echo $this->main->dateify($event, $this->date_format_fluent_1); ?>
                        </span>

                    <?php endif; ?>
                </div>
                <?php do_action('mec_grid_fluent_image', $event); ?>
                <div class="mec-event-content">
                    <?php do_action('mec_fluent_before_title', $event); ?>
                    <?php $soldout = $this->main->get_flags($event, $event_start_date); ?>
                    <h4 class="mec-event-title">
                    <?php
                        $title = null;
                        $class = 'mec-color-hover';
                        echo $this->display_link( $event, $title, $class );
                        echo $this->display_custom_data( $event );
                        echo $this->main->get_normal_labels($event, $display_label) . $this->main->display_cancellation_reason($event, $reason_for_cancellation);
                    ?>
                    </h4>
                    <?php if (!empty($label_style)) echo '<span class="mec-fc-style">' . $label_style . '</span>'; ?>
                    <?php if ($this->localtime) echo $this->main->module('local-time.type1', array('event' => $event)); ?>
                    <?php if($this->include_events_times and trim($start_time)){
                        echo '<div class="mec-event-time">';
                        echo esc_html_e('Event Times:', 'mec-fl');
                        echo MEC_kses::element($this->main->display_time($start_time, $end_time));
                        echo '</div>'; }?>
                    <?php if (isset($location['address']) and trim($location['address'])) : ?>
                        <div class="mec-event-location">
                            <i class="mec-sl-location-pin"></i>
                            <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
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
                    <div class="mec-event-footer">
                        <?php
                            $title = $this->get_register_button_title( $event, $event_start_date );
                            $class = 'mec-booking-button';
                            echo $this->display_link( $event, $title, $class );
                        ?>
                        <?php if ($settings['social_network_status'] != '0') : ?>
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
                    <?php do_action('mec_fluent_view_action', $event); ?>
                </div>
                </article>
</div>

<?php
                if ($rcount == $count) {
                    echo '</div>';
                    $rcount = 0;
                }

                $rcount++;
            endforeach;
        }
    }
    if (($i % $count) != 0) echo '</div>';
?>
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
    if (isset($map_events) and !empty($map_events)) {
        // Include Map Assets such as JS and CSS libraries
        $this->main->load_map_assets();

        // It changing geolocation focus, because after done filtering, if it doesn't. then the map position will not set correctly.
        if ((isset($_REQUEST['action']) and $_REQUEST['action'] == 'mec_grid_load_more') and isset($_REQUEST['sf'])) $this->geolocation_focus = true;

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
            ajax_url: "' . admin_url('admin-ajax.php', NULL) . '",
            geolocation: "' . $this->geolocation . '",
            geolocation_focus: ' . $this->geolocation_focus . ',
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
        if ($this->main->is_ajax()) echo $map_javascript;
        else $this->factory->params('footer', $map_javascript);
    }
endif;
