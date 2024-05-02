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

$location = isset($event->data->locations[$event->data->meta['mec_location_id']])? $event->data->locations[$event->data->meta['mec_location_id']] : array();
$event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
$event_date = (isset($event->date['start']) ? $event->date['start']['date'] : $event->data->meta['mec_start_date']);
$event_start_time = (isset($event->data->time) ? $event->data->time['start'] : '');
$event_end_time = (isset($event->data->time) ? $event->data->time['end'] : '');
$event_start_date = !empty($event->date['start']['date']) ? $event->date['start']['date'] : '';

$label_style = '';
if(!empty($event->data->labels)) {
    foreach($event->data->labels as $label) {
        if(!isset($label['style']) or (isset($label['style']) and !trim($label['style']))) continue;
        if($label['style'] == 'mec-label-featured') {
            $label_style = esc_html__('Featured' , 'mec-fl');
        } elseif($label['style'] == 'mec-label-canceled') {
            $label_style = esc_html__('Canceled' , 'mec-fl');
        }
    }
}

$start_date = (isset($event->date['start']) and isset($event->date['start']['date'])) ? $event->date['start']['date'] : date('Y-m-d H:i:s');
$end_date = (isset($event->date['end']) and isset($event->date['end']['date'])) ? $event->date['end']['date'] : date('Y-m-d H:i:s');

$event_time = '';
$event_time .= sprintf("%02d", (isset($event->data->meta['mec_date']['start']['hour']) ? $event->data->meta['mec_date']['start']['hour'] : 8)).':';
$event_time .= sprintf("%02d", (isset($event->data->meta['mec_date']['start']['minutes']) ? $event->data->meta['mec_date']['start']['minutes'] : 0));
$event_time .= (isset($event->data->meta['mec_date']['start']['ampm']) ? $event->data->meta['mec_date']['start']['ampm'] : 'AM');

$event_etime = '';
$event_etime .= sprintf("%02d", (isset($event->data->meta['mec_date']['end']['hour']) ? $event->data->meta['mec_date']['end']['hour'] : 6)).':';
$event_etime .= sprintf("%02d", (isset($event->data->meta['mec_date']['end']['minutes']) ? $event->data->meta['mec_date']['end']['minutes'] : 0));
$event_etime .= (isset($event->data->meta['mec_date']['end']['ampm']) ? $event->data->meta['mec_date']['end']['ampm'] : 'PM');

$start_time = date('D M j Y G:i:s', strtotime($start_date.' '.date('H:i:s', strtotime($event_time))));
$end_time = date('D M j Y G:i:s', strtotime($end_date.' '.date('H:i:s', strtotime($event_etime))));

$d1 = new DateTime($start_time);
$d2 = new DateTime(current_time("D M j Y G:i:s"));
$d3 = new DateTime($end_time);

$ongoing = (isset($settings['hide_time_method']) and trim($settings['hide_time_method']) == 'end') ? true : false;
if($ongoing) if($d3 < $d2) $ongoing = false;

// Skip if event is ongoing
if($d1 < $d2 and !$ongoing) return;

$gmt_offset = $this->main->get_gmt_offset();
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') === false) $gmt_offset = ' : '.$gmt_offset;
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Edge') == true) $gmt_offset = substr(trim($gmt_offset), 0 , 3);
if(isset($_SERVER['HTTP_USER_AGENT']) and strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') == true) $gmt_offset = substr(trim($gmt_offset), 2 , 3);

$speakers = '""';
if(!empty($event->data->speakers)) {
    $speakers= [];
    foreach($event->data->speakers as $key => $value) {
        $speakers[] = array(
            "@type" 	=> "Person",
            "name"		=> $value['name'],
            "image"		=> $value['thumbnail'],
            "sameAs"	=> $value['facebook'],
        );
    }
    $speakers = json_encode($speakers);
}

// Generating javascript code of countdown module
$javascript = '<script type="text/javascript">
jQuery(document).ready(function()
{
    jQuery("#mec_skin_countdown'.$this->id.'").mecCountDown(
    {
        date: "'.(($ongoing and (isset($event->data->meta['mec_repeat_status']) and $event->data->meta['mec_repeat_status'] == 0)) ? $end_time : $start_time).$gmt_offset.'",
        format: "off"
    },
    function()
    {
    });
});
</script>';

// Include javascript code into the page
if($this->main->is_ajax()) echo $javascript;
else $this->factory->params('footer', $javascript);
do_action('mec_start_skin' , $this->id);
do_action('mec_countdown_skin_head');
?>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    <div class="mec-fluent-bg-wrap" style="background-color: <?php echo esc_attr($this->skin_options['wrapper_bg_color']); ?>">
<?php } ?>

<div class="mec-wrap mec-fluent-wrap mec-skin-countdown-container <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->id; ?>">
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
    <article class="mec-event-countdown-style3 <?php echo $this->get_event_classes($event); ?>" style="border-left-color: <?php echo esc_attr($event_color); ?>;">
        <div class="mec-event-countdown-part1">
            <div class="mec-event-countdown-part-title">
                <div class="mec-event-upcoming"><?php echo sprintf(__('%s Upcoming Event', 'mec-fl'), '<span>'.__('Next', 'mec-fl').'</span>'); ?></div>
                <div class="mec-date-wrap">
                    <div class="mec-event-date">
                        <span class="mec-event-day-num"><?php echo date_i18n('d', strtotime($event_date)); ?></span>
                        <span><?php echo date_i18n('F, Y', strtotime($event_date)); ?></span>
                    </div>
                    <div class="mec-event-day">
                        <span><?php echo date_i18n('l', strtotime($event_date)); ?></span>
                    </div>
                </div>
            </div>
            <div class="mec-event-countdown-part-details">
                <div class="mec-event-countdown" id="mec_skin_countdown<?php echo $this->id; ?>">
                    <div class="mec-event-content">
                        <h4 class="mec-event-title">
                            <a class="mec-color-hover" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->title; ?></a>
                            <?php if ( !empty($label_style) ) echo '<span class="mec-fc-style">'.$label_style.'</span>'; ?>
                            <?php
                            echo $this->display_custom_data( $event );
                            echo $this->main->get_normal_labels($event, $display_label).$this->main->display_cancellation_reason($event, $reason_for_cancellation);
                            ?>
                        </h4>
                    </div>
                    <ul class="clockdiv" id="countdown">
                        <div class="days-w block-w">
                            <li>
                                <span class="mec-days">00</span>
                                <p class="mec-timeRefDays label-w"><?php _e('DAY', 'mec-fl'); ?></p>
                            </li>
                        </div>
                        <div class="hours-w block-w">
                            <li>
                                <span class="mec-hours">00</span>
                                <p class="mec-timeRefHours label-w"><?php _e('HRS', 'mec-fl'); ?></p>
                            </li>
                        </div>
                        <div class="minutes-w block-w">
                            <li>
                                <span class="mec-minutes">00</span>
                                <p class="mec-timeRefMinutes label-w"><?php _e('MIN', 'mec-fl'); ?></p>
                            </li>
                        </div>
                        <div class="seconds-w block-w">
                            <li>
                                <span class="mec-seconds">00</span>
                                <p class="mec-timeRefSeconds label-w"><?php _e('SEC', 'mec-fl'); ?></p>
                            </li>
                        </div>
                    </ul>
                </div>
                <div class="mec-event-content">
                    <?php if(isset($location['address']) and trim($location['address'])): ?>
                        <div class="mec-event-location">
                            <i class="mec-sl-location-pin"></i>
                            <address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address>
                        </div>
                    <?php endif; ?>
                    <?php echo $this->main->display_time($event_start_time, $event_end_time); ?>
                    <?php if($this->localtime) echo $this->main->module('local-time.type1', array('event'=>$event)); ?>
                </div>
                <div class="mec-event-footer">
                    <?php $soldout = $this->main->get_flags($event->data->ID, $event_start_date); ?>
                    <a class="mec-booking-button" data-event-id="<?php echo $event->data->ID; ?>" href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>" target="_self"><?php echo (is_array($event->data->tickets) and count($event->data->tickets) and !strpos($soldout, '%%soldout%%')) ? $this->main->m('register_button', __('REGISTER', 'mec-fl')) : $this->main->m('view_detail', __('View Detail', 'mec-fl')) ; ?></a>
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
                    <?php do_action( 'mec_countdown_button', $event ); ?>
                </div>
            </div>
        </div>
        <div class="mec-event-countdown-part2">
            <div class="mec-event-image">
                <a href="<?php echo $this->main->get_event_date_permalink($event, $event->date['start']['date']); ?>"><?php echo $event->data->thumbnails['mecFluentCountdown']; ?></a>
            </div>
        </div>
    </article>
</div>

<?php if (isset($this->skin_options['wrapper_bg_color']) and trim($this->skin_options['wrapper_bg_color'])) { ?>
    </div>
<?php } ?>