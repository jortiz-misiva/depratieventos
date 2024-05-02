<div class="mec-wrap ">
    <div class="mec-event-location-grid">
        <?php
        $count      = $this->count;
        $grid_div   = $this->count;
        $grid_limit = $this->limit;

        if($count == 0 or $count == 5) $col = 4;
        else $col = 12 / $count;
        $close_row = true;
        $rcount = 1 ;

        foreach($this->data as $d):
        $meta = \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Factory::get_meta_key_val($d->term_id,$this->atts['exclude_details']);
        $website= isset($meta['website'])?$meta['website']:null;
        $link = $this->single_page_url($d->term_id);
        $link_target = $this->single_page_link_target($d->term_id);

        if($rcount == 1) {
            echo '<div class="row">';
            $close_row = true;
        }

        echo '<div class="col-md-'.$col.' col-sm-'.$col.'">';
        ?>
        <article data-style="" class="mec-event-article mec-clear featured-article" itemscope="">
            <?php if(isset($meta['thumbnail']) && !empty($meta['thumbnail'])): ?><div class="mec-event-image mec-featured-image"><a href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><img width="300" height="300" src="<?php echo $meta['thumbnail']; ?>" class="attachment-medium size-medium wp-post-image" alt="" loading="lazy" data-mec-postid="47" srcset="<?php echo $meta['thumbnail']; ?> 300w, <?php echo $meta['thumbnail']; ?> 150w" sizes="(max-width: 300px) 100vw, 300px"></a></div><?php endif; ?>
            <div class="mec-featured-info-box">
                <?php if(isset($meta['featured']) && $meta['featured']==1): ?><span class="mec-featured-box-top mec-featured-box-top-single featured-grid mec-advanced-location-featured-custom <?php echo $this->atts['html_option'];?>"><?php _e('FEATURED','mec-advanced-location'); ?></span><?php endif; ?>
                <h2 class="mec-event-title mec-featured-title"><a class="mec-color-hover" data-taxonomy-id="47" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>"><?php echo ucfirst($d->name); ?></a></h2>
                <?php if(isset($meta['job_title']) && !empty($meta['job_title'])): ?><p class="mec-grid-event-location"><?php echo ucfirst($meta['job_title']); ?></p><?php endif; ?>
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
                <?php if(isset($website) && !empty($website)): ?>
                    <span class="mec-featured-info-box-title">
                        <i class="mec-sl-globe-alt mec-featured-color"></i>
                        <a class="mec-featured-link" href="<?php echo $website; ?>">
                        <em><?php echo $website; ?></em></a>
                    </span>
                <?php endif; ?>
                </p>
            </div>
            <div class="mec-featured-bottom">
                <div class="mec-feature-view-details-left">
                    <a class="mec-events-gcal mec-events-button mec-color mec-bg-color-hover mec-border-color mec-featured-none-decoration featured-event-button" href="<?php echo $link; ?>" target="<?php echo $link_target; ?>">
                        <?php
                        echo sprintf(
                            __('VIEW %s','mec-advanced-location'),
                            $this->main->m('taxonomy_location', __('Location', 'mec-advanced-location'))
                        );
                        ?>
                    </a>
                </div>
                <span class="mec-feature-view-details-right"><?php
                $socials = array('facebook','twitter','instagram','linkedin');
                foreach ($socials as $so_name):
                    if( !empty($meta[$so_name])):
                        echo '<a class="' . $so_name . 'mec-featured-color mec-featured-info-social-box" href="' . $meta[$so_name] . '" title="' . $so_name . '"><i class="mec-fa-' . $so_name . '"></i></a>';
                    endif;
                endforeach; ?></span>
            </div>
            <div class="mec-description-box">
                <div class="mec-location-detail-1">
                    <span><?php _e('All Events','mec-advanced-location'); ?>:</span>
                    <span style="color:rgba(137,138,140,1);"><?php echo $this->count_of_all($d->term_id); ?></span>
                </div>
                <div class="mec-location-detail-2">
                    <span><?php _e('Ongoing Events','mec-advanced-location'); ?>:</span>
                    <span style="color:rgba(137,138,140,1);"><?php echo $this->count_of_ongoing($d->term_id); ?></span>
                </div>
            </div>
            <div class="mec-featured-map-box">
                <?php if($this->section=='location' && isset($meta['featured_map']) && $meta['featured_map']==1){
                    $lat = isset($meta['latitude'])?$meta['latitude']:null;
                    $long =isset($meta['longitude'])?$meta['longitude']:null;
                    echo $this->init_form_map($lat,$long,243,'max');
                }
                ?>
            </div>
        </article>
      <?php
        echo '</div>';
        if($rcount == $count) {
            echo '</div>';
            $rcount = 0;
            $close_row = false;
        }
        $rcount++;
        endforeach;
        if($close_row) echo '</div>';
      ?>
  </div>
</div>

