<?php

if( !defined('ABSPATH') ){

    die() || exit;
}


$show_header_and_footer = isset( $settings['event_api']['show_header_and_footer'] ) && '1' == $settings['event_api']['show_header_and_footer'] ? true : false;
$limit = $settings['event_api']['limit'] ?? 12;
?>

<div id="event_api_option" class="mec-options-fields">

    <h4 class="mec-form-subtitle"><?php esc_html_e( 'Event API Settings', 'mec-event-api' ); ?></h4>
    <div class="mec-form-row">
        <div class="mec-col-12">
            <label>
                <input type="hidden" name="mec[settings][event_api][show_header_and_footer]" value="0">
                <input value="1" <?php checked( $show_header_and_footer, true ) ?> type="checkbox" name="mec[settings][event_api][show_header_and_footer]">
                <?php esc_html_e('Show header and footer if referred from iframe','mec-event-api'); ?>
            </label>
        </div>
    </div>
    <div class="mec-form-row">
        <label class="mec-col-3"><?php esc_html_e('Limit','mec-event-api'); ?></label>
        <div class="mec-col-9">
            <input type="number" name="mec[settings][event_api][limit]" min="-1" value="<?php echo esc_attr( $limit ) ?>" >
            <span class="mec-tooltip">
                <div class="box top">
                    <h5 class="title"><?php esc_html_e('Limit Events', 'mec-event-api'); ?></h5>
                    <div class="content">
                        <p>
                            <?php esc_attr_e('limit events in shortcode output.', 'mec-event-api'); ?><br>
                            <?php esc_attr_e('-1 is unlimited.', 'mec-event-api'); ?><br>
                            <?php esc_attr_e('0 or empty is default.', 'mec-event-api'); ?>
                        </p>
                    </div>
                </div>
                <i title="" class="dashicons-before dashicons-editor-help"></i>
            </span>
        </div>
    </div>
</div>