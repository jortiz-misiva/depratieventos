<div class="mec-wrap ">
    <div class="mec-event-list-standard">
        <?php
        foreach($this->data as $d):
        $meta = \MEC_Advanced_Speaker\Core\Lib\MEC_Advanced_Speaker_Lib_Factory::get_meta_key_val($d->term_id,$this->atts['exclude_details']);
        $website= isset($meta['website'])?$meta['website']:null;
        $link = $this->single_page_url($d->term_id);
        $link_target = $this->single_page_link_target($d->term_id);
        ?>
        <article data-style="" class="mec-event-article mec-clear  mec-divider-toggle mec-featured-skin-list featured-article">
            <div class="mec-topsec mec-featured-top-sec">
                <?php if(isset($meta['thumbnail']) && !empty($meta['thumbnail'])): ?>
                <div class="col-md-3 mec-event-image-wrap mec-col-table-c">
                    <div class="mec-event-image mec-featured-image">
                        <a href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><img width="300" height="300" src="<?php echo $meta['thumbnail']; ?>" class="attachment-medium size-medium wp-post-image" alt="" loading="lazy" data-mec-postid="" srcset="<?php echo $meta['thumbnail']; ?> 300w, <?php echo $meta['thumbnail']; ?> 150w" sizes="(max-width: 300px) 100vw, 300px"></a>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-md-6 mec-col-table-c mec-event-content-wrap mec-featured-text-top">
                    <div class="mec-featured-info-box">
                        <h4 class="mec-event-title mec-featured-title"><a class="mec-color-hover" data-taxonomy-id="" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><?php echo $d->name; ?></a></h4>
                        <?php if(isset($meta['job_title']) && !empty($meta['job_title'])): ?><p class="mec-featured-job-title"><?php echo ucfirst($meta['job_title']); ?></p><?php endif; ?>
                        <p class="mec-featured-info">
                            <?php if(isset($meta['address']) && !empty($meta['address']) && strlen($meta['address'])>0): ?>
                                <span class="mec-featured-info-box-title">
                                    <i class="mec-sl-location-pin mec-featured-color mec-featured-address-icon"></i>
                                    <em><?php echo $meta['address']; ?></em>
                                </span>
                            <?php endif; ?>
                            <?php if(isset($meta['tel']) && !empty($meta['tel'])): ?>
                                <span class="mec-featured-info-box-title">
                                    <i class="mec-sl-phone mec-featured-color"></i>
                                    <em><?php echo $meta['tel']; ?></em>
                                </span>
                            <?php endif; ?>
                            <?php if(isset($meta['email']) && !empty($meta['email'])): ?>
                                <span class="mec-featured-info-box-title">
                                    <i class="mec-sl-envelope mec-featured-color"></i>
                                    <a class="mec-featured-link" href="mailto: <?php echo $meta['email']; ?>"><em><?php echo $meta['email']; ?></em></a>
                                </span>
                            <?php endif; ?>
                            <?php if($website != null): ?>
                                <span class="mec-featured-info-box-title">
                                    <i class="mec-sl-globe-alt mec-featured-color"></i>
                                    <a class="mec-featured-link" href="<?php echo $website; ?>"><em><?php echo $website; ?></em></a>
                                </span>
                            <?php endif; ?>
                            <span lass="mec-featured-info-box-title">
                                <i class="mec-sl-clock mec-featured-color"></i>
                                <em class="mec-featured-title-count"><?php _e('Ongoing Events:','mec-advanced-speaker'); ?></em>
                                <em class="mec-featured-title-count-show"><?php echo $this->count_of_ongoing($d->term_id); ?></em>
                            </span>
                            <span lass="mec-featured-info-box-title">
                                <i class="mec-sl-calendar mec-featured-color"></i>
                                <em class="mec-featured-title-count"><?php _e('All Events:','mec-advanced-speaker'); ?></em>
                                <em class="mec-featured-title-count-show"><?php echo $this->count_of_all($d->term_id); ?></em>
                            </span>
                            <div class="mec-featured-bottom">
                                <div class="mec-feature-view-details-left">
                                    <a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color mec-featured-none-decoration featured-event-button" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>">
                                        <?php
                                        echo sprintf(
                                            __('VIEW %s','mec-advanced-speaker'),
                                            $this->main->m('taxonomy_speaker', __('Speaker', 'mec-advanced-speaker'))
                                        );
                                        ?>
                                    </a>
                                </div>
                            </div>
                        </p>
                    </div>
                </div>
                <div class="col-md-3 mec-col-table-c mec-event-meta-wrap mec-featured-meta-info mec-featured-text-top">
                    <?php if(isset($meta['featured']) && $meta['featured']==1): ?>
                        <div class="mec-featured-info-box">
                            <span class="mec-featured-box-top mec-featured-box-top-single <?php echo $this->atts['html_option'];?>"><?php _e('FEATURED','mec-advanced-speaker'); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="mec-social-networks">
                        <p class="mec-featured-meta-box-list"><?php $socials = array('facebook','twitter','instagram','linkedin');
                            foreach ($socials as $so_name):
                                if( !empty($meta[$so_name])):
                                    echo '<a class="' . $so_name . ' mec-featured-color mec-featured-info-social-box" href="' . $meta[$so_name] . '" title="' . $so_name . '"><i class="mec-fa-' .  $so_name . '"></i></a>';
                                endif;
                            endforeach;
                        ?></p>
                    </div>
                </div>
            </div>
        </article>
      <?php endforeach; ?>
	</div>
</div>
