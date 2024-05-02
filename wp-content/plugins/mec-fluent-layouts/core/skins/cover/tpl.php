<?php
/** no direct access **/
defined('MECEXEC') or die();

$styling = $this->main->get_styling();
$event = $this->events[0];
$settings = $this->main->get_settings();
$this->localtime = isset($this->skin_options['include_local_time']) ? $this->skin_options['include_local_time'] : false;
$display_label = isset($this->skin_options['display_label']) ? $this->skin_options['display_label'] : false;
$reason_for_cancellation = isset($this->skin_options['reason_for_cancellation']) ? $this->skin_options['reason_for_cancellation'] : false;

// Event is not valid!
if(!isset($event->data)) return;

$event_colorskin = (isset($styling['mec_colorskin']) || isset($styling['color'])) ? 'colorskin-custom' : '';
$event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
$event_link = (isset($event->data->permalink) and trim($event->data->permalink)) ? $this->main->get_event_date_permalink($event->data->permalink, $event_date) : get_permalink($event->data->ID);
$event_title = $event->data->title;
$event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
$event_thumb = $event->data->thumbnails['mecFluentCover'];
$start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
$end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
$event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';
$location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();

$label_style = '';
if ( !empty($event->data->labels) ) :
    foreach( $event->data->labels as $label) {
        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
        if ( $label['style']  == 'mec-label-featured' ) {
            $label_style = esc_html__( 'Featured' , 'mec-fl' );
        } elseif ( $label['style']  == 'mec-label-canceled' ) {
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

do_action('mec_start_skin' , $this->id);
do_action('mec_cover_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div class="mec-wrap mec-fluent-wrap mec-skin-cover-container <?php echo $event_colorskin . ' ' . $this->html_class; ?>">
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
    <?php if ($event_thumb) : ?>
        <article class="<?php echo (isset($event->data->meta['event_past']) and trim($event->data->meta['event_past'])) ? 'mec-past-event ' : ''; ?>mec-event-cover-clean mec-event-cover-<?php echo $this->style; ?> <?php echo $this->get_event_classes($event); ?>">
            <div class="mec-event-image"><?php echo $event_thumb; ?></div>
            <div class="mec-date-wrap">
                <i class="mec-sl-calendar"></i>
                <div class="mec-date-wrap-inner">
                    <div class="mec-event-date mec-clear">
                        <span class="mec-event-day-num"><?php echo date_i18n('d', strtotime($event_date)); ?></span>
                        <span><?php echo date_i18n('F, Y', strtotime($event_date)); ?></span>
                    </div>
                    <div class="mec-event-day">
                        <span><?php echo date_i18n('l', strtotime($event_date)); ?></span>
                    </div>
                </div>
            </div>
            <div class="mec-event-content" style="border-left-color: <?php echo esc_attr($event_color); ?>;">
                <div class="mec-event-col">
                    <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
                    <?php if(($this->style === 'fluent-type3' || $this->style === 'fluent-type4') and isset($location['address']) and trim($location['address'])): ?>
                        <div class="mec-event-location">
                            <i class="mec-sl-location-pin"></i>
                            <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                        </div>
                    <?php endif; ?>
                    <h4 class="mec-event-title">
                        <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $event_link; ?>"><?php echo $event_title; ?></a>
                        <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                        <?php
                        echo $this->display_custom_data( $event );
                        echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation);
                        ?>
                    </h4>
                </div>
                <?php if (!($this->style === 'fluent-type3' || $this->style === 'fluent-type4')) { ?>
                    <div class="mec-event-col">
                        <?php if(isset($location['address']) and trim($location['address'])): ?>
                            <div class="mec-event-location">
                                <i class="mec-sl-location-pin"></i>
                                <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                            </div>
                        <?php endif; ?>
                        <?php if ($this->style === 'fluent-type1') { ?>
                            <?php echo $this->main->display_time($start_time, $end_time); ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="mec-event-col">
                    <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                    <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', __('REGISTER', 'mec-fl')) : $this->main->m('view_detail', __('Events Detail', 'mec-fl')) ; ?></a>
                    <?php if ($this->style === 'fluent-type2' and isset($settings['social_network_status']) and $settings['social_network_status'] != '0') : ?>
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
                </div>
            </div>
        </article>
    <?php endif; ?>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>