<?php
/** no direct access **/
defined('MECEXEC') or die();
$single = new MEC_skin_single();
$main  = new MEC_main();
$booking_options = get_post_meta($event->data->ID, 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = array();
$display_reason = get_post_meta($event->data->ID, 'mec_display_cancellation_reason_in_single_page', true);
$event_color = isset($event->data->meta['mec_color']) ? '#'.$event->data->meta['mec_color'] : '';
?>
<div class="mec-wrap mec-single-fluent-wrap mec-fluent-wrap <?php echo $event_colorskin; ?> clearfix <?php echo $this->html_class; ?>" id="mec_skin_<?php echo $this->uniqueid; ?>">
    <article class="mec-single-event mec-single-modern">

        <!-- start breadcrumbs -->
        <?php
        $breadcrumbs_settings = isset( $settings['breadcrumbs'] ) ? $settings['breadcrumbs'] : '';
        if($breadcrumbs_settings == '1'): $breadcrumbs = new MEC_skin_single(); ?>
            <div class="mec-breadcrumbs mec-breadcrumbs-modern">
                <?php $breadcrumbs->display_breadcrumb_widget($event->data->ID); ?>
            </div>
        <?php endif; ?>
        <!-- end breadcrumbs -->

        <div class="mec-events-event-image">
            <?php echo MEC_kses::element($this->display_image_module( $event,'mecFluentSinglePage' )); ?>
            <?php do_action('mec_custom_dev_image_section', $event); ?>
        </div>
        <div class="mec-single-event-bar" style="border-left-color: <?php echo esc_attr($event_color); ?>;">

        <?php if(!is_active_sidebar('mec-single-sidebar')): ?>
            <?php
            // Event Date and Time
            if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start'])) {
                $midnight_event = $this->main->is_midnight_event($event);
                ?>
                <div class="mec-single-event-date">
                    <i class="mec-sl-calendar"></i>
                    <div class="mec-single-event-bar-inner">
                        <h3 class="mec-date"><?php _e('Date', 'mec-fl'); ?></h3>
                        <?php if($midnight_event): ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $this->main->dateify($event, $this->date_format1); ?></abbr></dd>
                        <?php else: ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date'=>$occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date'=>$occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1); ?></abbr></dd>
                        <?php endif; ?>
                        <?php echo $this->main->holding_status($event); ?>
                    </div>
                </div>
                <?php
                if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0') {
                    $time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
                    $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
                    ?>
                    <span class="mec-single-event-bar-seperator"></span>
                    <div class="mec-single-event-time">
                        <i class="mec-sl-clock " style=""></i>
                        <div class="mec-single-event-bar-inner">
                            <h3 class="mec-time"><?php _e('Time', 'mec-fl'); ?><span><?php echo esc_html($time_comment); ?></span></h3>
                            <?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
                                <dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - '.$event->data->time['end'] : ''); ?></abbr></dd>
                            <?php else: ?>
                                <dd><abbr class="mec-events-abbr"><?php _e('All Day', 'mec-fl'); ?></abbr></dd>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                }
            }
            ?>
        <?php else: ?>
            <?php
            // Event Date and Time
            if(isset($event->data->meta['mec_date']['start']) and !empty($event->data->meta['mec_date']['start']) and $single->found_value('data_time', $settings) == 'on') {
                $midnight_event = $this->main->is_midnight_event($event);
                ?>
                <div class="mec-single-event-date">
                    <i class="mec-sl-calendar"></i>
                    <div class="mec-single-event-bar-inner">
                        <h3 class="mec-date"><?php _e('Date', 'mec-fl'); ?></h3>
                        <?php if($midnight_event): ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $this->main->dateify($event, $this->date_format1); ?></abbr></dd>
                        <?php else: ?>
                            <dd><abbr class="mec-events-abbr"><?php echo $this->main->date_label((trim($occurrence) ? array('date'=>$occurrence) : $event->date['start']), (trim($occurrence_end_date) ? array('date'=>$occurrence_end_date) : (isset($event->date['end']) ? $event->date['end'] : NULL)), $this->date_format1); ?></abbr></dd>
                        <?php endif; ?>
                        <?php echo $this->main->holding_status($event); ?>
                    </div>
                </div>
                <?php
                if(isset($event->data->meta['mec_hide_time']) and $event->data->meta['mec_hide_time'] == '0') {
                    $time_comment = isset($event->data->meta['mec_comment']) ? $event->data->meta['mec_comment'] : '';
                    $allday = isset($event->data->meta['mec_allday']) ? $event->data->meta['mec_allday'] : 0;
                    ?>
                    <span class="mec-single-event-bar-seperator"></span>
                    <div class="mec-single-event-time">
                        <i class="mec-sl-clock " style=""></i>
                        <div class="mec-single-event-bar-inner">
                            <h3 class="mec-time"><?php _e('Time', 'mec-fl'); ?><span><?php echo esc_html($time_comment); ?></span></h3>
                            <?php if($allday == '0' and isset($event->data->time) and trim($event->data->time['start'])): ?>
                                <dd><abbr class="mec-events-abbr"><?php echo $event->data->time['start']; ?><?php echo (trim($event->data->time['end']) ? ' - '.$event->data->time['end'] : ''); ?></abbr></dd>
                            <?php else: ?>
                                <dd><abbr class="mec-events-abbr"><?php _e('All Day', 'mec-fl'); ?></abbr></dd>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php
                }
            }
            ?>
            <?php
            // Event Cost
            $cost = \MEC\Base::get_main()->get_event_cost($event);
            if( $cost ){
                ?>
                <span class="mec-single-event-bar-seperator"></span>
                <div class="mec-event-cost">
                    <i class="mec-sl-wallet"></i>
                    <div class="mec-single-event-bar-inner">
                        <h3 class="mec-cost"><?php echo $this->main->m('cost', __('Cost', 'mec-fl')); ?></h3>
                        <dd class="mec-events-event-cost">
                            <?php
                            if( is_numeric( $cost ) ) {

                                $rendered_cost = \MEC\Base::get_main()->render_price($cost, $events_detail->ID);
                            }else{

                                $rendered_cost = $cost;
                            }

                            echo apply_filters('mec_display_event_cost', $rendered_cost, $cost);
                            ?>
                        </dd>
                    </div>
                </div>
                <?php
            }
            ?>
            <?php do_action('print_extra_costs', $event); ?>
            <!-- Register Booking Button -->
            <?php if($this->main->can_show_booking_module($event) and $single->found_value('register_btn', $settings) == 'on'): ?>
                <?php $data_lity = $data_lity_class =  ''; if( isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ){ $data_lity = 'onclick="openBookingModal();"'; $data_lity_class = 'mec-booking-data-lity'; }  ?>
                <span class="mec-single-event-bar-seperator"></span>
                <a class="mec-booking-button mec-bg-color <?php echo $data_lity_class; ?> <?php if( isset($settings['single_booking_style']) and $settings['single_booking_style'] != 'modal' ) echo 'simple-booking'; ?>" href="#mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" <?php echo $data_lity; ?>><?php echo esc_html($this->main->m('register_button', __('REGISTER', 'mec-fl'))); ?></a>
            <?php elseif($single->found_value('register_btn', $settings) == 'on' and isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://'): ?>
                <span class="mec-single-event-bar-seperator"></span>
                <a class="mec-booking-button mec-bg-color" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php if(isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) echo esc_html(trim($event->data->meta['mec_more_info_title']), 'mec-fl'); else echo esc_html($this->main->m('register_button', __('REGISTER', 'mec-fl'))); ?></a>
            <?php endif; ?>
        <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="mec-left-side">
                    <div class="mec-event-content">
                        <?php echo $this->main->display_cancellation_reason($event, $display_reason); ?>
                        <?php do_action('mec_single_after_content' , $event ); ?>
                        <?php
                        // Event Categories
                        if(isset($event->data->categories) and !empty($event->data->categories)) {
                            ?>
                            <div class="mec-single-event-category">
                                <?php
                                foreach($event->data->categories as $category) {
                                    echo '<a href="'.get_term_link($category['id'], 'mec_category').'" rel="tag">'. $category['name'] .'</a>';
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <h1 class="mec-single-title"><?php echo get_the_title($event->data->ID); ?></h1>
                        <div class="mec-single-event-description mec-events-content"><?php echo $this->main->get_post_content($event->data->ID); ?><?php do_action('mec_custom_dev_content_section' , $event); ?></div>
                        <!-- Custom Data Fields -->
                        <?php $this->display_data_fields($event); ?>
                    </div>

                    <!-- FAQ -->
                    <?php $this->display_faq($event); ?>

                    <div class="mec-single-links-wrap">
                        <!-- Links Module -->
                        <?php echo $this->main->module('links.details', array('event'=>$event)); ?>
                        <!-- Export Module -->
                        <?php echo $this->main->module('export.details', array('event'=>$event)); ?>
                    </div>
                    <!-- Google Maps Module -->
                    <?php if ( $single->found_value('event_location', $settings) == 'on' || $settings['google_maps_status'] == 1) : ?>
                    <div class="mec-events-meta-group mec-events-meta-group-gmap">
                        <?php echo $this->main->module('googlemap.details', array('event'=>$this->events)); ?>
                        <?php
                        if($single->found_value('event_location', $settings) == 'on' and isset($event->data->locations[$event->data->meta['mec_location_id']]) and !empty($event->data->locations[$event->data->meta['mec_location_id']])) {
                            $location = $event->data->locations[$event->data->meta['mec_location_id']];
                            ?>
                            <div class="mec-single-event-location">
                                <div class="mec-single-event-location-inner">
                                    <i class="mec-sl-location-pin"></i>
                                    <div class="mec-single-event-location-content">
                                        <h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'mec-fl')); ?></h3>
                                        <dd class="location location-name"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
                                        <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>
                                    </div>
                                </div>
                                <?php if($location['thumbnail']): ?>
                                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
                                <?php endif; ?>
                            </div>
                            <?php
                            $additional_locations_status = (!isset($settings['additional_locations']) or (isset($settings['additional_locations']) and $settings['additional_locations'])) ? true : false;
                            if ($additional_locations_status) {
                                $locations = array();
                                foreach($event->data->locations as $o) if($o['id'] != $event->data->meta['mec_location_id']) $locations[] = $o;
                                if (count($locations)) {
                                    ?>
                                    <div class="mec-single-event-additional-locations">
                                        <?php $i = 2; ?>
                                        <?php foreach($locations as $location): if($location['id'] == $event->data->meta['mec_location_id']) continue; ?>
                                            <div class="mec-single-event-location">
                                                <div class="mec-single-event-location-inner">
                                                    <i class="mec-sl-location-pin"></i>
                                                    <div class="mec-single-event-location-content">
                                                        <h3 class="mec-events-single-section-title mec-location"><?php echo $this->main->m('taxonomy_location', __('Location', 'mec-fl')); ?> <?php echo $i; ?></h3>
                                                        <dd class="location location-name"><?php echo (isset($location['name']) ? $location['name'] : ''); ?></dd>
                                                        <dd class="location"><address class="mec-events-address"><span class="mec-address"><?php echo (isset($location['address']) ? $location['address'] : ''); ?></span></address></dd>
                                                    </div>
                                                </div>
                                                <?php if($location['thumbnail']): ?>
                                                    <img class="mec-img-location" src="<?php echo esc_url($location['thumbnail'] ); ?>" alt="<?php echo (isset($location['name']) ? $location['name'] : ''); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <?php $i++ ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php
                                }
                            }
                        }
                    ?>
                    </div>
                    <?php endif; ?>
                    <!-- Countdown module -->
                    <?php if($this->main->can_show_countdown_module($event)): ?>
                    <div class="mec-events-meta-group mec-events-meta-group-countdown">
                        <?php echo $this->main->module('countdown.details', array('event'=>$this->events)); ?>
                    </div>
                    <?php endif; ?>
                    <!-- Hourly Schedule -->
                    <?php $this->display_hourly_schedules_widget($event); ?>
                    <?php do_action( 'mec_before_booking_form', get_the_ID() ); ?>
			        <!-- Booking Module -->
                    <?php if($this->main->is_sold($event, (trim($occurrence) ? $occurrence : $event->date['start']['date'])) and count($event->dates) <= 1): ?>
                        <div class="mec-sold-tickets warning-msg"><?php _e('Sold out!', 'wpl'); ?></div>
                    <?php elseif($this->main->can_show_booking_module($event)): ?>
                        <?php $data_lity_class = ''; if( isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' && ! ( isset( $_REQUEST['action'] ) && 'mec_load_single_page' === $_REQUEST['action'] ) ) $data_lity_class = 'lity-hide '; ?>
                        <div id="mec-events-meta-group-booking-<?php echo $this->uniqueid; ?>" class="<?php echo $data_lity_class; ?>mec-events-meta-group mec-events-meta-group-booking">
                            <?php
                            if( isset($settings['booking_user_login']) and $settings['booking_user_login'] == '1' and !is_user_logged_in() ) {
                                echo do_shortcode('[MEC_login]');
                            } elseif ( isset($settings['booking_user_login']) and $settings['booking_user_login'] == '0' and !is_user_logged_in() and isset($booking_options['bookings_limit_for_users']) and $booking_options['bookings_limit_for_users'] == '1' ) {
                                echo do_shortcode('[MEC_login]');
                            } else {
                                echo $this->main->module('booking.default', array('event'=>$this->events));
                            }
                            ?>
                        </div>
                    <?php endif ?>
                    <!-- Tags -->
                    <?php if(get_the_tags()): ?>
                    <div class="mec-events-meta-group mec-events-meta-group-tags">
                        <h3><?php esc_html_e('Tags', 'mec-fl'); ?></h3>
                        <?php the_tags('', '', ''); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!is_active_sidebar('mec-single-sidebar')) { ?>
                <div class="col-md-4">
                    <?php
                    // Event labels
                    if (isset($event->data->labels) && !empty($event->data->labels)) {
                        ?>
                        <div class="mec-event-meta mec-color-before mec-frontbox mec-single-event-label">
                            <h3><?php echo $this->main->m('taxonomy_labels', __('Labels', 'mec-fl')); ?></h3>
                            <?php
                            foreach($event->data->labels as $labels=>$label) {
                                echo '<span style="color:' . $label['color'] . '">' . esc_html($label["name"]) . '</span>';
                            }
                            ?>
                        </div>
                        <?php
                    }
                    // Event Organizer
                    if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']])) {
                        $organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
                        ?>
                        <div class="mec-event-meta mec-color-before mec-frontbox <?php echo ((!$this->main->can_show_booking_module($event) and in_array($event->data->meta['mec_organizer_id'], array('0', '1')) and !trim($event->data->meta['mec_more_info'])) ? 'mec-util-hidden' : '') ; ?>">
                            <div class="mec-single-event-organizer">
                                <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                                    <img class="mec-img-organizer" src="<?php echo esc_url(MEC_Fluent\Core\pluginBase\MecFluent::generateCustomThumbnailURL($organizer['thumbnail'], 83, 83, true)); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
                                <?php endif; ?>
                                <h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'mec-fl')); ?></h3>
                                <?php if(isset($organizer['thumbnail'])): ?>
                                <dd class="mec-organizer">
                                    <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                                </dd>
                                <?php endif;
                                if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                                <dd class="mec-organizer-tel">
                                    <i class="mec-sl-phone"></i>
                                    <h6><?php _e('Phone', 'mec-fl'); ?></h6>
                                    <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
                                </dd>
                                <?php endif;
                                if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                                <dd class="mec-organizer-email">
                                    <i class="mec-sl-envelope"></i>
                                    <h6><?php _e('Email', 'mec-fl'); ?></h6>
                                    <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
                                </dd>
                                <?php endif;
                                if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                                <dd class="mec-organizer-url">
                                    <i class="mec-sl-sitemap"></i>
                                    <h6><?php _e('Website', 'mec-fl'); ?></h6>
                                    <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
                                </dd>
                                <?php endif;
                                $organizer_description_setting = isset( $settings['organizer_description'] ) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer');  if($organizer_description_setting == '1'): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
                                <dd class="mec-organizer-description">
                                    <p><?php echo $organizer_term->description;?></p>
                                </dd>
                                <?php endif; } } endif; ?>
                            </div>
                            <?php $this->show_other_organizers($event); ?>
                        </div>
                        <?php
                    }
                    /**
                     * TODO: convert to an action
                     */
                    ?>
                    <?php do_action('mec_single_virtual_badge', $event->data ); ?>
                    <?php do_action('mec_single_zoom_badge', $event->data ); ?>
                    <!-- QRCode Module -->
                    <?php echo $this->main->module('qrcode.details', array('event'=>$event)); ?>
                    <!-- Speakers Module -->
                    <?php echo $this->main->module('speakers.details', array('event'=>$event)); ?>
                    <!-- Local Time Module -->
                    <?php echo $this->main->module('local-time.details', array('event'=>$event)); ?>
                    <!-- Attendees List Module -->
                    <?php echo $this->main->module('attendees-list.details', array('event'=>$event)); ?>
                    <!-- Next Previous Module -->
                    <?php echo $this->main->module('next-event.details', array('event'=>$event)); ?>
                    <!-- Weather Module -->
                    <?php echo $this->main->module('weather.details', array('event'=>$event)); ?>
                    <?php
                    // More Info
                    if(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') {
                        ?>
                        <div class="mec-event-meta mec-color-before mec-frontbox">
                            <div class="mec-event-more-info">
                                <i class="mec-sl-info"></i>
                                <h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'mec-fl')); ?></h3>
                                <dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'mec-fl')); ?></a></dd>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <!-- Widgets -->
                    <?php dynamic_sidebar('mec-single-sidebar'); ?>
                </div>
            <?php } else { ?>
                <?php if ( $single->found_value('local_time', $settings) == 'on' || $single->found_value('more_info', $settings) == 'on' || $single->found_value('event_label', $settings) == 'on' || $single->found_value('event_location', $settings) == 'on' || $single->found_value('event_categories', $settings) == 'on' || $single->found_value('event_orgnizer', $settings) == 'on'  || $single->found_value('weather_module', $settings) == 'on' || $single->found_value('next_module', $settings) == 'on' || $single->found_value('attende_module', $settings) == 'on' || $single->found_value('event_sponsors', $settings) == 'on' ) { ?>
                    <div class="col-md-4">
                        <?php
                        // Event labels
                        if (isset($event->data->labels) && !empty($event->data->labels) and $single->found_value('event_label', $settings) == 'on') {
                            ?>
                            <div class="mec-event-meta mec-color-before mec-frontbox mec-single-event-label">
                                <h3><?php echo $this->main->m('taxonomy_labels', __('Labels', 'mec-fl')); ?></h3>
                                <?php
                                foreach($event->data->labels as $labels=>$label) {
                                    echo '<span style="color:' . $label['color'] . '">' . esc_html($label["name"]) . '</span>';
                                }
                                ?>
                            </div>
                            <?php
                        }
                        // Event Organizer
                        if(isset($event->data->organizers[$event->data->meta['mec_organizer_id']]) && !empty($event->data->organizers[$event->data->meta['mec_organizer_id']]) and $single->found_value('event_orgnizer', $settings) == 'on') {
                            $organizer = $event->data->organizers[$event->data->meta['mec_organizer_id']];
                            ?>
                            <div class="mec-event-meta mec-color-before mec-frontbox <?php echo ((!$this->main->can_show_booking_module($event) and in_array($event->data->meta['mec_organizer_id'], array('0', '1')) and !trim($event->data->meta['mec_more_info'])) ? 'mec-util-hidden' : '') ; ?>">
                                <div class="mec-single-event-organizer">
                                    <?php if(isset($organizer['thumbnail']) and trim($organizer['thumbnail'])): ?>
                                        <img class="mec-img-organizer" src="<?php echo esc_url(MEC_Fluent\Core\pluginBase\MecFluent::generateCustomThumbnailURL($organizer['thumbnail'], 83, 83, true)); ?>" alt="<?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?>">
                                    <?php endif; ?>
                                    <h3 class="mec-events-single-section-title"><?php echo $this->main->m('taxonomy_organizer', __('Organizer', 'mec-fl')); ?></h3>
                                    <?php if(isset($organizer['thumbnail'])): ?>
                                    <dd class="mec-organizer">
                                        <h6><?php echo (isset($organizer['name']) ? $organizer['name'] : ''); ?></h6>
                                    </dd>
                                    <?php endif;
                                    if(isset($organizer['tel']) && !empty($organizer['tel'])): ?>
                                    <dd class="mec-organizer-tel">
                                        <i class="mec-sl-phone"></i>
                                        <h6><?php _e('Phone', 'mec-fl'); ?></h6>
                                        <a href="tel:<?php echo $organizer['tel']; ?>"><?php echo $organizer['tel']; ?></a>
                                    </dd>
                                    <?php endif;
                                    if(isset($organizer['email']) && !empty($organizer['email'])): ?>
                                    <dd class="mec-organizer-email">
                                        <i class="mec-sl-envelope"></i>
                                        <h6><?php _e('Email', 'mec-fl'); ?></h6>
                                        <a href="mailto:<?php echo $organizer['email']; ?>"><?php echo $organizer['email']; ?></a>
                                    </dd>
                                    <?php endif;
                                    if(isset($organizer['url']) && !empty($organizer['url']) and $organizer['url'] != 'http://'): ?>
                                    <dd class="mec-organizer-url">
                                        <i class="mec-sl-sitemap"></i>
                                        <h6><?php _e('Website', 'mec-fl'); ?></h6>
                                        <span><a href="<?php echo (strpos($organizer['url'], 'http') === false ? 'http://'.$organizer['url'] : $organizer['url']); ?>" class="mec-color-hover" target="_blank"><?php echo $organizer['url']; ?></a></span>
                                    </dd>
                                    <?php endif;
                                    $organizer_description_setting = isset( $settings['organizer_description'] ) ? $settings['organizer_description'] : ''; $organizer_terms = get_the_terms($event->data, 'mec_organizer');  if($organizer_description_setting == '1'): foreach($organizer_terms as $organizer_term) { if ($organizer_term->term_id == $organizer['id'] ) {  if(isset($organizer_term->description) && !empty($organizer_term->description)): ?>
                                    <dd class="mec-organizer-description">
                                        <p><?php echo $organizer_term->description;?></p>
                                    </dd>
                                    <?php endif; } } endif; ?>
                                </div>
                                <?php $this->show_other_organizers($event); ?>
                            </div>
                            <?php
                        }

                        /**
                        * TODO: convert to an action
                        */
                        ?>

                        <!-- Sponsors Module -->
                        <?php
                        if($single->found_value('event_sponsors', $settings) == 'on'){

                            echo MEC_kses::full($this->main->module('sponsors.details', array('event' => $event)));
                        }
                        ?>

                        <?php do_action('mec_single_virtual_badge', $event->data ); ?>
                        <?php do_action('mec_single_zoom_badge', $event->data ); ?>
                        <!-- QRCode Module -->
                        <?php if($single->found_value('qrcode_module', $settings) == 'on') echo $this->main->module('qrcode.details', array('event'=>$event)); ?>
                        <!-- Speakers Module -->
                        <?php if($single->found_value('event_speakers', $settings) == 'on') echo $this->main->module('speakers.details', array('event'=>$event)); ?>
                        <!-- Local Time Module -->
                        <?php if($single->found_value('local_time', $settings) == 'on') echo $this->main->module('local-time.details', array('event'=>$event)); ?>
                        <!-- Attendees List Module -->
                        <?php if($single->found_value('attende_module', $settings) == 'on') echo $this->main->module('attendees-list.details', array('event'=>$event)); ?>
                        <!-- Next Previous Module -->
                        <?php if($single->found_value('next_module', $settings) == 'on') echo $main->module('next-event.details', array('event'=>$event)); ?>
                        <!-- Weather Module -->
                        <?php if($single->found_value('weather_module', $settings) == 'on') echo $this->main->module('weather.details', array('event'=>$event)); ?>
                        <?php
                        // More Info
                        if(isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://' and $single->found_value('more_info', $settings) == 'on') {
                            ?>
                            <div class="mec-event-meta mec-color-before mec-frontbox">
                                <div class="mec-event-more-info">
                                    <i class="mec-sl-info"></i>
                                    <h3 class="mec-cost"><?php echo $this->main->m('more_info_link', __('More Info', 'mec-fl')); ?></h3>
                                    <dd class="mec-events-event-more-info"><a class="mec-more-info-button mec-color-hover" target="<?php echo (isset($event->data->meta['mec_more_info_target']) ? $event->data->meta['mec_more_info_target'] : '_self'); ?>" href="<?php echo $event->data->meta['mec_more_info']; ?>"><?php echo ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : __('Read More', 'mec-fl')); ?></a></dd>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                        <!-- Widgets -->
                        <?php dynamic_sidebar('mec-single-sidebar'); ?>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </article>
    <?php $this->fluent_display_related_posts_widget($event->ID); ?>
    <?php $this->display_next_previous_events($event); ?>
</div>
<?php
    // MEC Schema
    do_action('mec_schema', $event);
?>
<script>
jQuery( ".mec-speaker-avatar a, .mec-schedule-speakers a" ).click(function(e)
{
    e.preventDefault();
    var id =  jQuery(this).attr('href');
    lity(id);

    return false;
});

// Fix modal booking in some themes
function openBookingModal()
{
    jQuery( ".mec-booking-button.mec-booking-data-lity" ).on('click',function(e)
    {
        e.preventDefault();
        var book_id =  jQuery(this).attr('href');
        lity(book_id);

        return false;
    });
}

jQuery(document).ready(function($){

    jQuery('.mec-single-fluent-wrap').find('select').niceSelect();
});
</script>