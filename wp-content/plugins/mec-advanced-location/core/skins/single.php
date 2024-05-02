<?php
$skin = new \MEC_Advanced_Location\Core\Lib\MEC_Advanced_Location_Lib_Skin();
?>
<div class="mec-advanced-location">
    <div class="mec-event-content">
        <div class="mec-single-event-description mec-events-content">
            <div class="mec-wrap mec-skin-list-container">
                <div class="mec-featured-wrap mec-skin-list-events-container">
                    <div class="mec-wrap">
                        <div class="mec-featured-top-title mec-featured-location">
                            <article data-style="" class="mec-event-article  mec-clear  mec-divider-toggle" itemscope="">
                                <div class="mec-event-image mec-featured-image"><?php if(isset($meta['thumbnail']) && !empty($meta['thumbnail'])): ?><img width="200" height="200" src="<?php echo $meta['thumbnail']; ?>" class="attachment-full size-full wp-post-image" alt="" loading="lazy" data-mec-postid="32" srcset="<?php echo $meta['thumbnail']; ?> 300w, <?php echo $meta['thumbnail']; ?> 150w" sizes="(max-width: 200px) 100vw, 200px"><?php endif; ?></div>
                                <div class="mec-featured-info-box">
                                    <?php if(isset($featured) && $featured==true): ?><span class="mec-featured-box-top-single <?php echo $atts['html_option'];?>"><?php _e('FEATURED','mec-advanced-location'); ?></span><?php endif; ?>
                                    <?php if(isset($term['name']) && !empty($term['name'])): ?><h2><?php echo ucfirst($term['name']); ?></h2><?php endif; ?>
                                    <?php if(isset($meta['job_title']) && !empty($meta['job_title'])&& strlen($meta['job_title'])>0): ?><span class="mec-featured-job-title"><?php echo ucfirst($meta['job_title']); ?></span><?php endif; ?>
                                    <div class="mec-featured-address-box"></div>
                                    <p class="mec-featured-info">
                                        <?php if(isset($meta['address']) && !empty($meta['address']) && strlen($meta['address'])>0): ?>
                                            <span class="mec-featured-info-box-title">
                                                <i class="mec-sl-location-pin mec-featured-color mec-featured-address-icon"></i>
                                                <em class="mec-featured-normal-text"><?php echo $meta['address']; ?></em>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(isset($meta['tel']) && !empty($meta['tel']) && strlen($meta['tel'])>0): ?>
                                            <span class="mec-featured-info-box-title">
                                                <i class="mec-sl-phone mec-featured-color"></i>
                                                <em class="mec-featured-normal-text"><?php echo $meta['tel']; ?></em>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(isset($meta['email'])&&!empty($meta['email'])&& strlen($meta['email'])>0): ?>
                                            <span class="mec-featured-info-box-title-single">
                                                <i class="mec-sl-envelope mec-featured-color"></i>
                                                <a class="mec-featured-link" href="mailto: <?php echo $meta['email'] ?>"><em class="mec-featured-normal-text"><?php echo $meta['email']; ?></em></a>
                                            </span>
                                        <?php endif; ?>
                                        <?php if(isset($meta['website'])&&!empty($meta['website'])&& strlen($meta['website'])>0): ?>
                                            <span class="mec-featured-info-box-title-single">
                                                <i class="mec-sl-globe-alt mec-featured-color"></i>
                                                <a class="mec-featured-link" href="<?php echo $meta['website'] ?>"><em class="mec-featured-normal-text"><?php echo $meta['website']; ?></em></a>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                    <div class="mec-social-networks">
                                    <?php $socials = array('facebook','twitter','instagram','linkedin');
                                        foreach ($socials as $so_name):
                                            if(isset($meta[$so_name]) && !empty($meta[$so_name])):
                                                echo '<a class="' . $so_name . 'mec-featured-color mec-featured-info-social-box" href="' . $meta[$so_name] . '" title="' . $so_name . '"><i class="mec-fa-' . $so_name . '"></i></a>';
                                            endif;
                                        endforeach;
                                    ?></div>
                                </div>
                                <div class="mec-description-box">
                                    <?php if(isset($term['description']) && !empty($term['description'])): ?>
                                        <h3 class="mec-featured-about-single">
                                            <?php _e('About','mec-advanced-location'); ?>
                                            <?php echo $SKO->main->m('taxonomy_location', __('Location', 'mec-advanced-location')); ?>
                                        </h3>
                                        <p class="mec-featured-content-single"><?php echo wpautop( $term['description'] ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
                <?php if( isset($meta['featured_map']) && $meta['featured_map']==1 && $atts['show_map']==true){
                    $lat = isset($meta['latitude'])?$meta['latitude']:null;
                    $long =isset($meta['longitude'])?$meta['longitude']:null;
                    echo $skin->init_form_map($lat,$long,300,'max');
                } ?>
                <?php if($atts['show_event_list']==true) : ?>
                    <div class="" id="">
                        <?php echo $SKO->output(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

