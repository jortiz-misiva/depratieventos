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
    <div class="mec-event-carousel-type2 mec-event-carousel-<?php echo $this->style; ?>">
        <?php $carousel_type = 'type2'; ?>
        <div class='mec-owl-crousel-skin-<?php echo $carousel_type; ?> mec-owl-carousel mec-owl-theme'>
            <?php
            foreach ($this->events as $date => $events) :
                foreach($events as $event):
                $location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
                $organizer = isset($event->data->organizers[$event->data->meta['mec_organizer_id']])? $event->data->organizers[$event->data->meta['mec_organizer_id']] : array();
                $event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
                $event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
                $start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
                $end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
                $event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

                $label_style = '';
                if(!empty($event->data->labels)) {
                    foreach($event->data->labels as $label) {
                        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
                        if($label['style']  == 'mec-label-featured') $label_style = esc_html__('Featured', 'mec-fl');
                        elseif($label['style'] == 'mec-label-canceled') $label_style = esc_html__('Canceled', 'mec-fl');
                    }
                }

                $speakers = '""';
                if(!empty($event->data->speakers)) {
                    $speakers = [];
                    foreach($event->data->speakers as $key=>$value) {
                        $speakers[] = array(
                            "@type" 	=> "Person",
                            "name"		=> $value['name'],
                            "image"		=> $value['thumbnail'],
                            "sameAs"	=> $value['facebook'],
                        );
                    }
                    $speakers = json_encode($speakers);
                }
                ?>
                <article data-style="<?php echo $label_style; ?>" class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-article mec-clear <?php echo $this->get_event_classes($event); ?>" itemscope>
                    <?php
                    $schema_settings = isset( $settings['schema'] ) ? $settings['schema'] : '';
                    if ($schema_settings == '1') :
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
                    <div class="event-carousel-type2-head clearfix" style="border-bottom-color: <?php echo esc_attr($event_color); ?>;">
                        <div class="mec-event-img">
                            <?php
                            if ($event->data->thumbnails['mecFluentCarousel']) {
                                echo $event->data->thumbnails['mecFluentCarousel'];
                            }
                            ?>
                            <div class="mec-date-wrap<?php echo $event->data->thumbnails['mecFluentCarousel'] ? ' mec-event-has-img' : ''; ?>">
                                <?php do_action('mec_carousel_fluent_action_date' , $event); ?>
                                <div class="mec-event-date">
                                    <span class="mec-event-day-num"><?php echo date_i18n('d', strtotime($date)); ?></span>
                                    <span><?php echo date_i18n('F, Y', strtotime($date)); ?></span>
                                </div>
                                <div class="mec-event-day">
                                    <span><?php echo date_i18n('l', strtotime($date)); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="mec-carousel-content">
                            <div class="mec-event-content">
                                <h4 class="mec-event-title mec-event-carousel-title"><?php
                                    $title = null;
                                    $class = 'mec-color-hover';
                                    $attributes = 'style="color: '.esc_attr($event_color).';"';
                                    echo $this->display_link( $event, $title, $class );
                                    echo $this->display_custom_data( $event );
                                ?></h4>
                                <?php echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation); ?>
                                <?php if(isset($location['address']) and trim($location['address'])): ?>
                                    <div class="mec-event-location">
                                        <i class="mec-sl-location-pin"></i>
                                        <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                                    </div>
                                <?php endif; ?>
                                <?php echo $this->main->display_time($start_time, $end_time); ?>
                                <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
                            </div>
                            <div class="mec-event-footer">
                                <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                                <?php
                                    $title = $this->get_register_button_title( $event, $event_start_date );
                                    echo $this->display_link($event, $title, 'mec-booking-button');
                                 ?>
                                <?php if(isset($settings['social_network_status']) and $settings['social_network_status'] != '0') : ?>
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
                                <?php do_action( 'mec_masonry_button', $event ); ?>
                            </div>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
	</div>
</div>