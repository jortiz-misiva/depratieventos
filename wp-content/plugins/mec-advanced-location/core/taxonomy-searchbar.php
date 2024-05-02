<?php
/**
 *  version 1.1.2
 */
class Mec_Taxonomy_Search_Bar{

    public static $instance;

    public static function getInstance(){

        if(is_null(self::$instance)){

            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Init
     *
     * @return void
     */
    public function init(){

        add_filter('mec_list_term_query_args',[__CLASS__,'add_search_args_to_term_args'],10,3);
        add_action('mec_before_list_term_output',array($this,'display'),10,2);
    }

    public static function add_search_args_to_term_args($args,$taxonomy,$skin_class){

        if(true === (bool)$skin_class->filter){

            $order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : false;
            $orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : false;

            if($orderby){

                switch($orderby){
                    case 'name':
                        $args['orderby'] = 'name';
                        break;
                    case 'added_date':
                        $args['orderby'] = 'term_id';
                        break;
                    case 'ongoing_events':

                        break;
                    case 'all_events':
                        $args['orderby'] = 'count';
                        break;
                }
            }

            if($order){

                switch($order){
                    case 'DESC':
                        $args['order'] = 'DESC';
                        break;
                    case 'ASC':
                        $args['order'] = 'ASC';
                        break;
                }
            }
        }

        if(true === $skin_class->search){

		    $search = isset($_REQUEST['s2']) ? sanitize_text_field($_REQUEST['s2']) : '';
            $search_in = isset($_REQUEST['search_in']) ? sanitize_text_field($_REQUEST['search_in']) : 'name';

            if(!empty($search)){

                switch($taxonomy){
                    case 'mec_location':

                        switch( $search_in ){

                            case 'address':

                                $args['meta_query'][] = array(
                                    'key' => 'address',
                                    'value' => $search,
                                    'compare' => 'LIKE'
                                );
                                break;
                            case 'name':
                            default:

                                $args['search'] = $search;
                                break;
                        }

                        break;
                    case 'mec_organizer':
                    case 'mec_speaker':

                        $args['search'] = $search;
                        break;
                }
            }
        }

        return $args;
    }

    /**
     * Display search bar
     *
     * @param array $class
     * @param string $taxonomy
     *
     * @return void
     */
    public function display($skin_class,$taxonomy){

        if(!(true === $skin_class->search || true === $skin_class->filter)){

            return;
        }


        do_action('mec_before_taxonomy_searchbar_box',$taxonomy);
        ?>
        <!-- begin searchbar -->
        <div id="mec-search-box" class="mec-advanced-location mec-search-box mec-totalcal-box">
            <?php do_action('mec_before_taxonomy_searchbar_form',$taxonomy); ?>
            <form class="mec-searchbar-form mec-add-on-searchbar-form mec-ajax-form" action="" method="get">
                <?php do_action('mec_begin_taxonomy_searchbar_form',$taxonomy); ?>
                <div class="row">
                    <div class="col-md-6">
                            <?php if(true === $skin_class->search): ?>
                                <input type="text" class="mec-search-field" name="s2" placeholder="<?php esc_attr_e('Search','mec-advanced-location') ?>" value="<?php echo isset($_REQUEST['s2']) ? esc_attr($_REQUEST['s2']) : ''; ?>" />
                            <?php endif; ?>

                            <?php if(true === $skin_class->filter): ?>
                            <select name="orderby" class="mec-search-field">
                                <?php
                                    $orderby = apply_filters(
                                        'mec_search_bar_sortby',
                                        array(
                                            '' => __('Sort by','mec-advanced-location'),
                                            'name' => __('Name','mec-advanced-location'),
                                            'added_date' => __('Added Date','mec-advanced-location'),
                                            // 'ongoing_events' => __('Ongoing Events','mec-advanced-location'),
                                            'all_events' => __('All Events','mec-advanced-location'),
                                        )
                                    );
                                    $selected = $_REQUEST['orderby'] ?? '';
                                    foreach($orderby as $key => $value){

                                        echo '<option value="'.esc_attr($key).'" '.selected($selected,$key,false).'>'.esc_html($value).'</option>';
                                    }
                                ?>
                            </select>
                            <?php
                                $selected = $_REQUEST['order'] ?? '';
                            ?>
                            <select name="order" class="mec-search-field second">
                                <option value=""><?php esc_html_e('Sort','mec-advanced-location') ?></option>
                                <option value="DESC" <?php selected($selected,'DESC') ?>><?php esc_html_e('DESC','mec-advanced-location') ?></option>
                                <option value="ASC" <?php selected($selected,'ASC') ?>><?php esc_html_e('ASC','mec-advanced-location') ?></option>
                            </select>
                            <?php endif; ?>
                            <button class="button"><?php _e('Filter','mec-advanced-location') ?></button>

                            <input type="hidden" class="mec-s2" value="<?php echo esc_attr( $_REQUEST['s2'] ?? '' ) ?>"/>
                            <input type="hidden" class="mec-orderby" value="<?php echo esc_attr( $_REQUEST['orderby'] ?? '' ) ?>"/>
                            <input type="hidden" class="mec-order" value="<?php echo esc_attr( $_REQUEST['order'] ?? '' ) ?>"/>
                    </div>
                </div>
                <?php do_action('mec_end_taxonomy_searchbar_form',$taxonomy); ?>
            </form>
            <?php do_action('mec_after_taxonomy_searchbar_form',$taxonomy); ?>
		</div>
        <!-- end searchbar -->
        <?php
        do_action('mec_after_taxonomy_searchbar_box',$taxonomy);
    }
}
