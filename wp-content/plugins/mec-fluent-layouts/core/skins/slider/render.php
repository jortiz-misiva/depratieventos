<?php

/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event_colorskin = (isset($styling['mec_colorskin']) or isset($styling['color'])) ? 'colorskin-custom' : '';
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;
?>
<div class="mec-wrap <?php echo $event_colorskin; ?>">
    <div class="mec-slider-t1-wrap">
        <div class='mec-slider-t1 mec-owl-carousel mec-owl-theme'>
            <?php
            foreach ($this->events as $date => $events) :
                foreach ($events as $event) :
                    // Featured Image
                    $src = $event->data->featured_image['mecFluentSlider'];

                    $location = isset($event->data->locations[$event->data->meta['mec_location_id']]) ? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                    $event_color = isset($event->data->meta['mec_color']) ? '#' . $event->data->meta['mec_color'] : '';
                    $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                    $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                    $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
                    $event_end_date = !empty($event->date['end']['date']) ? $event->date['end']['date'] : '';

                    $book = $this->getBook();
                    $availability = $book->get_tickets_availability($event->data->ID, $event_start_date);

                    $spots = 0;
                    foreach ($availability as $ticket_id => $count) {
                        if (!is_numeric($ticket_id)) continue;

                        if ($count != '-1') $spots += $count;
                        else {
                            $spots = -1;
                            break;
                        }
                    }

                    $excerpt = trim($event->data->post->post_excerpt) ? $event->data->post->post_excerpt : '';

                    // Safe Excerpt for UTF-8 Strings
                    if (!trim($excerpt)) {
                        $ex = explode(' ', strip_tags(strip_shortcodes($event->data->post->post_content)));
                        $words = array_slice($ex, 0, 25);

                        $excerpt = implode(' ', $words);
                    }

                    $label_style = '';
                    if (!empty($event->data->labels)) {
                        foreach ($event->data->labels as $label) {
                            if (!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                            if ($label['style']  == 'mec-label-featured') $label_style = esc_html__('Featured', 'mec-fl');
                            elseif ($label['style']  == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'mec-fl');
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
            ?>
                    <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>" style="border-left-color: <?php echo esc_attr($event_color); ?>;">
                        <?php
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
                        <div class="mec-slider-t1-img" style="background: <?php echo $src ? 'url(' . esc_attr($src) . ')' : '#f6f7f8'; ?>;"></div>

                        <div class="mec-slider-t1-content mec-event-grid-modern">
                            <div class="event-grid-modern-head clearfix">
                                <div class="mec-date-wrap">
                                    <i class="mec-sl-calendar"></i>
                                    <div class="mec-date-wrap-inner">
                                        <div class="mec-event-date mec-clear">
                                            <span class="mec-event-day-num"><?php echo date_i18n('d', strtotime($date)); ?></span>
                                            <span><?php echo date_i18n('F, Y', strtotime($date)); ?></span>
                                        </div>
                                        <div class="mec-event-day">
                                            <span><?php echo date_i18n('l', strtotime($date)); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mec-event-content">
                                <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                                <h4 class="mec-event-title">
                                    <a class="mec-color-hover" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a>
                                    <?php
                                    echo $this->display_custom_data( $event );
                                    echo $this->main->get_normal_labels($event, $display_label) . $this->main->display_cancellation_reason($event, $reason_for_cancellation);
                                    ?>
                                </h4>
                                <div class="mec-event-meta">
                                    <?php if (isset($location['address']) and trim($location['address'])) : ?>
                                        <div class="mec-event-location">
                                            <i class="mec-sl-location-pin"></i>
                                            <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                        </div>
                                    <?php endif; ?>
                                    <?php echo $this->main->display_time($start_time, $end_time); ?>

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
                                    <?php if ($this->localtime) echo $this->main->module('local-time.type1', array('event' => $event)); ?>
                                    <?php do_action('mec_list_fluent_right_box', $event); ?>
                                </div>
                            </div>
                            <div class="mec-event-footer">
                                <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', __('REGISTER', 'mec-fl')) : $this->main->m('view_detail', __('View Detail', 'mec-fl')); ?></a>
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
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>