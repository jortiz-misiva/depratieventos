<div class="mec-advanced-location">
    <div class="mec-wrap mec-skin-grid-container mec-featured-border mec-advanced-location-slider">
        <input type="hidden" class="mec-advanced-location-slider-isup" value="yes">
        <div class="mec-skin-grid-events-container mec-event-article" >
            <div class="mec-wrap ">
                <?php if( !empty( $skin->data ) ): ?>
                    <div class="mec-event-location-grid mec-event-location-slider mec-featured-sidebar">
                        <?php
                        foreach($skin->data as $d){
                            $meta = \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Factory::get_meta_key_val($d->term_id,$skin->atts['exclude_details']);
                            $website = isset($meta['website'])?$meta['website']:null;
                            $link = $skin->single_page_url($d->term_id);
                            $link_target = $skin->single_page_link_target($d->term_id);
                            ?>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <?php if(isset($meta['thumbnail']) && !empty($meta['thumbnail'])): ?><div class="mec-event-image mec-featured-image"><a href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><img width="240" height="201" src="<?php echo $meta['thumbnail']; ?>" class="attachment-medium size-medium wp-post-image" alt="" loading="lazy" data-mec-postid="" srcset="<?php echo $meta['thumbnail']; ?> 300w, <?php echo $meta['thumbnail']; ?> 150w" sizes="(max-width: 300px) 100vw, 300px"></a></div><?php endif; ?>
                                    <div class="mec-featured-info-box">
                                        <span class="mec-featured-box-top mec-featured-box-top-single featured-grid  mec-featured-slider mec-advanced-location-featured-custom <?php echo $atts['html_option'];?>"><?php _e('FEATURED','mec-advanced-location'); ?></span>
                                        <h4 class="mec-event-title mec-featured-title mec-featured-info-box-title" style="margin-top: 10px !important;"><a class="mec-color-hover" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><?php echo ucfirst($d->name); ?></a></h4>
                                        <?php if(isset($meta['job_title']) && !empty($meta['job_title'])): ?><p class="mec-featured-job-title"><?php echo ucfirst($meta['job_title']); ?></p><?php endif; ?>
                                        <p class="mec-featured-info">
                                            <?php if(isset($meta['address']) && !empty($meta['address']) && strlen($meta['address'])>0): ?>
                                                <span class="mec-featured-info-box-title">
                                                    <i class="mec-sl-location-pin mec-featured-color mec-featured-address-icon"></i>
                                                    <em><?php echo $meta['address']; ?></em>
                                                </span>
                                            <?php endif; ?>
                                            <?php if(isset($meta['tel'])  && !empty($meta['tel'])): ?>
                                                <span class="mec-featured-info-box-title">
                                                    <i class="mec-sl-phone mec-color"></i>
                                                    <em><?php echo $meta['tel']; ?></em>
                                                </span>
                                            <?php endif; ?>
                                            <?php if(isset($meta['email']) && !empty($meta['email'])): ?>
                                                <span class="mec-featured-info-box-title">
                                                    <i class="mec-sl-envelope mec-featured-color"></i>
                                                    <a class="mec-featured-link" href="mailto: <?php echo $meta['email']; ?>"><em><?php echo $meta['email']; ?></em></a>
                                                </span>
                                            <?php endif; ?>
                                            <?php if(isset($website) && !empty($website)): ?>
                                                <span class="mec-featured-info-box-title">
                                                    <i class="mec-sl-globe-alt mec-featured-color"></i>
                                                    <a class="mec-featured-link" href="<?php echo $website; ?>"><em><?php echo $website; ?></em></a>
                                                </span>
                                            <?php endif; ?>
                                            <span lass="mec-featured-info-box-title">
                                                <i class="mec-sl-clock mec-color"></i>
                                                <em><?php _e('Ongoing Events','mec-advanced-location'); ?>:</em>
                                                <em class="mec-featured-title-count-show"><?php echo $skin->count_of_ongoing($d->term_id); ?></em>
                                            </span>
                                            <span class="mec-featured-info-box-title">
                                                <i class="mec-sl-calendar mec-featured-color"></i>
                                                <em><?php _e('All Events','mec-advanced-location'); ?>:</em>
                                                <em><?php echo $skin->count_of_all($d->term_id); ?></em>
                                            </span>
                                        </p>
                                        <div class="mec-feature-view-details">
                                            <span class="mec-feature-view-details-left"><a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color mec-featured-none-decoration featured-event-button" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>">
                                                <?php
                                                echo sprintf(
                                                    __('VIEW %s','mec-advanced-location'),
                                                    \MEC\Base::get_main()->m('taxonomy_location', __('Location', 'mec-advanced-location'))
                                                );
                                                ?>
                                            </a></span>
                                            <span class="mec-feature-view-details-right"><?php
                                            $socials = array('facebook','twitter','instagram','linkedin');
                                            foreach ($socials as $so_name):
                                                if( !empty($meta[$so_name])):
                                                    echo '<a class="' . $so_name . 'mec-color mec-featured-info-social-box" href="' . $meta[$so_name] . '" title="' . $so_name . '"><i class="mec-fa-' . $so_name . '"></i></a>';
                                                endif;
                                            endforeach; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php else: ?>
                    <div class="mec-skin-slider-no-location-container" id="mec_featured_skin_no_events_<?php echo $skin->id; ?>">
                        <?php _e('No location found!', 'mec-advanced-location'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>