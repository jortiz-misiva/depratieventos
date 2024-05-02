<?php

namespace MEC_Advanced_Speaker\Core\Lib;

/**
 * Adds Speaker widget.
 */
class MEC_Advanced_Speaker_Lib_Widget extends \WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {

        $main = \MEC::getInstance('app.libraries.main');
        $title_t = $main->m('taxonomy_speaker', __('Speaker', 'mec-advanced-speaker'));

        $title = sprintf(__('Advanced %s Widget','mec-advanced-speaker'),$title_t);

        parent::__construct(
            'featured_speaker_widget',
            $title,
            array( 'description' => $title )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }

        $widget = new \MEC_Advanced_Speaker\Core\SpeakerWidget\MEC_Advanced_Speaker_SpeakerWidget_Frontend();

        echo $widget->speaker_featured_content(array(
            'limit'=>$instance['limit'],
            'html-option'=>$instance['htmloption'],
            'exclude' => $instance['exclude']
        ));
        echo $after_widget;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __('New title','mec-advanced-speaker');
        }

        if ( isset( $instance[ 'limit' ] ) ) {
            $limit = $instance[ 'limit' ];
        }
        else {
            $limit = 0;
        }

        if ( isset( $instance[ 'htmloption' ] ) ) {
            $htmloption = $instance[ 'htmloption' ];
        }
        else {
            $htmloption = '';
        }

        if ( isset( $instance[ 'exclude' ] ) ) {
            $exclude = $instance[ 'exclude' ];
        }
        else {
            $exclude = '';
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
         </p>
         <p>
            <label for="<?php echo $this->get_field_name( 'limit' ); ?>">
                <?php _e( 'Limit:','mec-advanced-speaker' ); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
         </p>
         <p>
            <label for="<?php echo $this->get_field_name( 'exclude' ); ?>">
                <?php _e( 'Exclude:','mec-advanced-speaker' ); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'exclude' ); ?>" name="<?php echo $this->get_field_name( 'exclude' ); ?>" type="text" value="<?php echo esc_attr( $exclude ); ?>" />
         </p>
          <p>
            <label for="<?php echo $this->get_field_name( 'htmloption' ); ?>">
                <?php _e( 'HTML Option:','mec-advanced-speaker' ); ?>
            </label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'htmloption' ); ?>" name="<?php echo $this->get_field_name( 'htmloption' ); ?>" type="text" value="<?php echo esc_attr( $htmloption ); ?>" />
         </p>


    <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['limit'] = ( !empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : '';
        $instance['htmloption'] = ( !empty( $new_instance['htmloption'] ) ) ? strip_tags( $new_instance['htmloption'] ) : '';
         $instance['exclude'] = ( !empty( $new_instance['exclude'] ) ) ? strip_tags( $new_instance['exclude'] ) : '';

        return $instance;
    }

} // class Foo_Widget

?>