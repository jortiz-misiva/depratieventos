<?php

use MEC\SingleBuilder\Widgets\EventOrganizers\EventOrganizers;

/** @var MEC_skin_single $this */
/** @var boolean $fes */
/** @var stdClass $event */
/** @var string $event_colorskin */
/** @var string $occurrence */
/** @var array $occurrence_full */
/** @var string $occurrence_end_date */
/** @var array $occurrence_end_full */

wp_enqueue_style('mec-lity-style', $this->main->asset('packages/lity/lity.min.css'));
wp_enqueue_script('mec-lity-script', $this->main->asset('packages/lity/lity.min.js'));

$booking_options = get_post_meta(get_the_ID(), 'mec_booking', true);
if(!is_array($booking_options)) $booking_options = [];

// Compatibility with Rank Math
$rank_math_options = '';
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if(is_plugin_active('schema-markup-rich-snippets/schema-markup-rich-snippets.php')) $rank_math_options = get_post_meta(get_the_ID(), 'rank_math_rich_snippet', true);

$bookings_limit_for_users = $booking_options['bookings_limit_for_users'] ?? 0;

$more_info = (isset($event->data->meta['mec_more_info']) and trim($event->data->meta['mec_more_info']) and $event->data->meta['mec_more_info'] != 'http://') ? $event->data->meta['mec_more_info'] : '';
if(isset($event->date) and isset($event->date['start']) and isset($event->date['start']['timestamp'])) $more_info = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info', $more_info);

$more_info_target = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_target', $event->data->meta['mec_more_info_target'] ?? '');
if(!trim($more_info_target) && isset($settings['fes_event_link_target']) && trim($settings['fes_event_link_target'])) $more_info_target = $settings['fes_event_link_target'];

$more_info_title = MEC_feature_occurrences::param($event->ID, $event->date['start']['timestamp'], 'more_info_title', ((isset($event->data->meta['mec_more_info_title']) and trim($event->data->meta['mec_more_info_title'])) ? $event->data->meta['mec_more_info_title'] : esc_html__('Read More', 'modern-events-calendar-lite')));

// Event Cost
$cost = $this->main->get_event_cost($event);

$location_id = $this->main->get_master_location_id($event);
$location = ($location_id ? $this->main->get_location_data($location_id) : array());

$organizer_id = $this->main->get_master_organizer_id($event);
$organizer = ($organizer_id ? $this->main->get_organizer_data($organizer_id) : array());

$sticky_sidebar = $settings['sticky_sidebar'] ?? '';
if($sticky_sidebar == 1) $sticky_sidebar = 'mec-sticky';

// Banner Image
$banner_module = $this->can_display_banner_module($event);
?>
<div class="mec-wrap <?php echo esc_attr($event_colorskin); ?> clearfix <?php echo esc_attr($this->html_class); ?>" id="mec_skin_<?php echo esc_attr($this->uniqueid); ?>">

    <?php if($banner_module) echo MEC_kses::element($this->display_banner_module($event, $occurrence_full, $occurrence_end_full)); ?>
	<?php do_action('mec_top_single_event', get_the_ID()); ?>
	<article class="row mec-single-event <?php echo esc_attr($sticky_sidebar); ?>">

		<!-- start breadcrumbs -->
		<?php
		$breadcrumbs_settings = $settings['breadcrumbs'] ?? '';
        if($breadcrumbs_settings == '1'): ?>
        <div class="mec-breadcrumbs">
            <?php $this->display_breadcrumb_widget(get_the_ID()); ?>
        </div>
		<?php endif; ?>
		<!-- end breadcrumbs -->
			<style>
				.contenedor_evento_abierto{
				margin: 0 auto !important;
    			float: none !important;
				}
				.mec-event-image a img{
					width: 100%;
				}

				.home #site-header .header-inner{
					display:none !important;
				}

				.col-md-6.bloque_datos{
					padding:40px !important;
				}

				.bloque_datos h1.mec-event-title{
					font-size: 25px;
					text-align: center;
					font-weight: 800;
					margin-bottom: 40px;
				}

				.text-label-evento{
					color: #f4343f !important;
					font-size: 24px !important;
					font-weight: 800 !important;
					text-align: center !important;
				}

				.bloque_minuto{
					margin-left: 17%;
					border-right:1px solid #bebebe;
					margin-right: 10px;
				}

				.bloque_descripcion{
					margin-top: 80px;
				}

				.lista-descripcion{
					font-size: 1rem !important;					
					line-height: 1.5 !important;
					color: #000000 !important;
				}

				.lista_hora{				
					font-weight: 800;
					margin-right: 8px;
				}

				.opcion{
					padding-left: 40px;
					background-image: url('./../../assets/img/Check.svg');
					background-repeat: no-repeat;
					background-position: 5px center;			
				}

				.bloque_speakers{
					display: flex;
					justify-content: center;
					align-items: center;
					margin-top: 80px
				}

				.speaker_opcion{
					width: 300px;
					height: 300px;
					cursor: pointer;
					background-repeat: no-repeat;
					background-size: cover;
					background-position: center;	
					margin-right:10px;									
				}

				.hover_item{
					background-color: rgba(192, 56, 62, 0.8);
					width: 100%;
					height: 100%;					
					opacity: 0;
					visibility: hidden;
  					transition: opacity 0.5s, visibility 0.5s;	
		   		    padding: 15px;						
				}				

				.hover_item>p{
					font-size: 16px !important;					
					margin: 0 !important;	
					font-weight: 600;	
					font-family: Montserrat, "Open Sans", Helvetica, Arial, sans-serif;
				}

				.speaker_opcion:hover .hover_item {
					opacity: 1;
  					visibility: visible;					  
				}
				.hover_item > a {					
				top: 180px;
    				position: relative;				
				}

				.icono-instagram{
					width: 30px;
					height: 30px;
					background-repeat: no-repeat;
					background-size: cover;
					background-position: center;
					background-image: url('./../../assets/img/Instagram_blanco.svg');					
					float: left;	
					
				}

				.hover_item > p {
					color:#ffffff !important;					
				}

				@media only screen and (max-width: 600px) {
					.btn-lugar {
						background-image: url(../../assets/img/Ubicacion.svg);
					}

					.contenedor_descripcion::after,
					.bloque_descripcion>.bloque_minuto::after,
					.bloque_descripcion>.bloque_kit::after{
						content: "";
							width: 80%;
							height: 1px;
							background-color: #f4343f;
							float: left;
							margin: 0 10%;
					}

					.bloque_descripcion {
						margin-top: 40px;
						margin-bottom: 0px;
					}

					.bloque_descripcion > .bloque_minuto{
						margin-left: 0;
					}

					.bloque_descripcion>.bloque_minuto>p,
					.bloque_descripcion>.bloque_kit>p{
						padding-left: 0px;
						margin-left: 35px;
					}

					.bloque_descripcion>.bloque_kit>p{
						padding-left: 35px;						
					}

					.bloque_descripcion>.bloque_kit>p.text-label-evento{
						padding-left: 0px;
					}

					.bloque_descripcion>.bloque_minuto::after,
					.bloque_descripcion>.bloque_kit::after{	
						margin-top: 40px;					
						margin-bottom: 40px;						
					}

					.bloque_speakers {	
						margin-top: 0px;
						flex-direction: column;
					}
					.speaker_opcion {
						width: 250px;
						height: 250px;						
						margin-right: 0;
						float: left;
						margin-bottom: 25px;
					}

					.hover_item {	
						opacity: 1;
						visibility: visible;
						transition: opacity 0.5s, visibility 0.5s;	
						background-color: rgba(192, 56, 62, 0.5);					
					}

					.hover_item > a {
					    top: 140px;
    					position: relative;
					}

					.btn-fecha, .btn-hora, .btn-lugar {											
						padding: 15px;
    					padding-left: 40px;					
					}

					.btn-fecha {	
						background-position: 0 !important;
					}

					
				}


				</style>
		
		<div class="col-md-8 contenedor_evento_abierto">
			<!--BLOQUE MISIVA -->
			<div class="row contenedor_descripcion">
				<div class="col-md-6">
					<?php if(!$banner_module): ?>			
						<div class="mec-event-image"><?php echo MEC_kses::element($this->display_link($event, $event->data->thumbnails['full'])); ?></div>            
					<?php endif; ?>
				</div>				
				<?php 
				// echo "<pre>";
				// var_dump($event);
				// $event["speakers"]
				// die;
				$fechaOriginal = $event->date['start']['date'];				
				$fechaObjeto = date_create_from_format('Y-m-d', $fechaOriginal);
				setlocale(LC_TIME, 'es_ES.UTF-8');				
				$fechaFormateada = strftime('%d de %B del %Y', $fechaObjeto->getTimestamp());

				$timestamp = $event->date['start']['timestamp'];					
				$horainicio = date('H\hi', $timestamp); 
				$timestamp = $event->date['end']['timestamp'];					
				$horafin = date('H\hi', $timestamp); 
				?>

				<div class="col-md-6 bloque_datos">					
					<h1 class="mec-event-title"><?php the_title(); ?></h1>
					<div class="btn-fecha"><?php echo $fechaFormateada ?></div>
					<div class="btn-hora"> De <?php echo $horainicio ?> a <?php echo $horafin ?> </div>
					<div class="btn-lugar">
						<?php if(isset($location['name']))
							echo esc_html($location['name']); 
						?>
					</div>
					<div class="btn-agendar">Agregar a calendario</div>					
				</div>	
			</div>		
			<div class="row bloque_descripcion">
				<div class="col-md-4 bloque_minuto">
				<?php if (get_field('field_662a9f57a45b9')):				
					$lista_minuto = get_field('field_662a9f57a45b9');
					$lista_minuto = explode("\n", $lista_minuto);				
					?>
					<p class="text-label-evento"> Minuto a minuto:</p>
					<?php
					foreach ($lista_minuto as $row) { 
						$item = explode("|", $row);
						?>
						<p class="lista-descripcion"><span class="lista_hora"><?php echo $item[0]?></span> <?php echo $item[1]; ?></p>  
					<?php } ?>
				<?php endif; ?>
			</div>
				<div class="col-md-4 bloque_kit">	
				<?php if ( get_field('field_662a9f87a45ba' ) ): 
					$lista_kit = get_field('field_662a9f87a45ba');					
					$lista = explode("\n", $lista_kit);?>
					<p class="text-label-evento"> Kit para invitadas:</p>
					<?php
					foreach ($lista as $row) { ?>
						<p class="lista-descripcion opcion"><?php echo $row; ?></p>
					<?php } ?>
				<?php endif; ?>
				</div>
			</div>	

			<div class="row bloque_speakers">
				<?php 				
				if (isset($event->data->speakers)){

					$speakers=$event->data->speakers;
					foreach($speakers as $row){
						$fondo = $row["thumbnail"];
						$nombre = $row["name"];
						$link = $row["facebook"];
						$cargo = $row["job_title"];					
					?>
					<div class="speaker_opcion" style="background-image: url(<?php echo $fondo?>);">
						<div class="hover_item">
							<p><?php echo $nombre ?></p>
							<p><?php echo $cargo ?></p>						
							<a href="<?php echo $link?>" target="_blank">
							<span class="icono-instagram"></span>
							</a>
						</p>
						</div>	
					</div>	
				<?php } 
				}?>
			</div>	

			


            <?php if(!$banner_module): ?>
			
            <?php endif; ?>
            <?php echo MEC_kses::full($this->main->display_progress_bar($event)); ?>
			<div class="mec-event-content">
                <!-- <?php if(!$banner_module): ?>
                    <?php echo MEC_kses::element($this->main->display_cancellation_reason($event, $this->display_cancellation_reason)); ?>
                    <h1 class="mec-single-title"><?php the_title(); ?></h1>
                <?php endif; ?> -->

				<div class="mec-single-event-description mec-events-content"><?php the_content(); ?></div>
                <?php echo MEC_kses::full($this->display_trailer_url($event)); ?>
                <?php echo MEC_kses::element($this->display_disclaimer($event)); ?>
			</div>

			<?php do_action('mec_single_after_content', $event); ?>

			<!-- Custom Data Fields -->
			<?php $this->display_data_fields($event); ?>

			<!-- FAQ -->
            <?php $this->display_faq($event); ?>

			<div class="mec-event-info-mobile"></div>

			<!-- Export Module -->
			<?php echo MEC_kses::full($this->main->module('export.details', array('event' => $event, 'icons' => $this->icons))); ?>

			<!-- Countdown module -->
			<?php if($this->main->can_show_countdown_module($event)): ?>
            <div class="mec-events-meta-group mec-events-meta-group-countdown">
                <?php echo MEC_kses::full($this->main->module('countdown.details', array('event' => $this->events, 'icons' => $this->icons))); ?>
            </div>
			<?php endif; ?>

			<!-- Hourly Schedule -->
			<?php $this->display_hourly_schedules_widget($event); ?>

			<?php do_action('mec_before_booking_form', get_the_ID()); ?>

			<!-- Booking Module -->
			<?php if(!empty($event->date)):
			   if($this->main->is_sold($event) and count($event->dates) <= 1): ?>
				  <?php		

				  $event_id        = $event->ID;
				  $dates = (isset($event->dates) ? $event->dates : array($event->date));
				  $occurrence_time = ($dates[0]['start']['timestamp'] ?? strtotime($dates[0]['start']['date']));
				  $tickets         = get_post_meta( $event_id, 'mec_tickets', true );
				  $book         = $this->getBook();
				  $availability = $book->get_tickets_availability( $event_id, $occurrence_time );

				  $sales_end                   = 0;
                  $ticket_limit                = -1;
				  $ticket_sales_ended_messages = [];
				  $stop_selling                = '';
				  foreach ( $tickets as $ticket_id => $ticket ) {

					 $ticket_limit = $availability[$ticket_id] ?? -1;
					 $ticket_name  = isset( $ticket['name'] ) ? '<strong>' . esc_html($ticket['name']) . '</strong>' : '';

					 $key          = 'stop_selling_' . $ticket_id;
					 if ( !isset( $availability[ $key ] ) ) {

						continue;
					 }

					 if ( true === $availability[ $key ] ) {

						$sales_end++;
						$ticket_sales_ended_messages[ $ticket_id ] = sprintf( esc_html__( 'The %s ticket sales has ended!', 'modern-events-calendar-lite'), $ticket_name );
					 }
				  }

				  $tickets_sales_end = false;
				  if(count($tickets) === $sales_end){

					 $tickets_sales_end = true;
				  }

				  if ( !empty( $ticket_sales_ended_messages ) ):

					 foreach ( $ticket_sales_ended_messages as $ticket_id => $message ) :

						?>
						<div id="mec-ticket-message-<?php echo esc_attr($ticket_id); ?>" class="mec-ticket-unavailable-spots mec-error <?php echo( $ticket_limit == '0' ? '' : 'mec-util-hidden' ); ?>"><div><?php echo MEC_kses::element($message); ?></div></div>
					 <?php
					 endforeach;
					 ?>

				  <?php else: ?>
					 <div id="mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>" class="mec-sold-tickets warning-msg"><?php esc_html_e( 'Sold out!', 'modern-events-calendar-lite');do_action( 'mec_booking_sold_out', $event, null, null, array( $event->date ) ); ?> </div>
				  <?php endif; ?>
			<?php elseif($this->main->can_show_booking_module($event)): ?>
				<?php $data_lity_class = ''; if(isset($settings['single_booking_style']) and $settings['single_booking_style'] == 'modal' ) $data_lity_class = 'lity-hide '; ?>
				<div id="mec-events-meta-group-booking-<?php echo esc_attr($this->uniqueid); ?>" class="<?php echo esc_attr($data_lity_class); ?> mec-events-meta-group mec-events-meta-group-booking">
					<?php
					if(isset($settings['booking_user_login']) and $settings['booking_user_login'] == '1' and !is_user_logged_in() ) {
						echo do_shortcode('[MEC_login]');
					} elseif (!is_user_logged_in() and isset($booking_options['bookings_limit_for_users']) and $booking_options['bookings_limit_for_users'] == '1' ) {
						echo do_shortcode('[MEC_login]');
					} else {
						echo MEC_kses::full($this->main->module('booking.default', array('event' => $this->events, 'icons' => $this->icons)));
					}
					?>
				</div>
			<?php endif; endif; ?>
			<!-- Tags -->
			<div class="mec-events-meta-group mec-events-meta-group-tags">
                <?php echo get_the_term_list(get_the_ID(), apply_filters('mec_taxonomy_tag', ''), esc_html__('Tags: ', 'modern-events-calendar-lite'), ', ', '<br />'); ?>
			</div>
		</div>		
			<div class="col-md-4">
                <?php
                    $GLOBALS['mec-widget-single'] = $this;
                    $GLOBALS['mec-widget-event'] = $event;
                    $GLOBALS['mec-widget-occurrence'] = $occurrence;
                    $GLOBALS['mec-widget-occurrence_full'] = $occurrence_full;
                    $GLOBALS['mec-widget-occurrence_end_date'] = $occurrence_end_date;
                    $GLOBALS['mec-widget-occurrence_end_full'] = $occurrence_end_full;
                    $GLOBALS['mec-widget-cost'] = $cost;
                    $GLOBALS['mec-widget-more_info'] = $more_info;
                    $GLOBALS['mec-widget-location_id'] = $location_id;
                    $GLOBALS['mec-widget-location'] = $location;
                    $GLOBALS['mec-widget-organizer_id'] = $organizer_id;
                    $GLOBALS['mec-widget-organizer'] = $organizer;
                    $GLOBALS['mec-widget-more_info_target'] = $more_info_target;
                    $GLOBALS['mec-widget-more_info_title'] = $more_info_title;
                    $GLOBALS['mec-banner_module'] = $banner_module;
                    $GLOBALS['mec-icons'] = $this->icons;
                ?>
				<!-- Widgets -->
				
			</div>
		
	</article>

	<?php $this->display_related_posts_widget($event->ID); ?>
	<?php $this->display_next_previous_events($event); ?>

</div>
<?php
// MEC Schema
if($rank_math_options != 'event') do_action('mec_schema', $event);
?>
<script>
// Fix modal speaker in some themes
jQuery(".mec-speaker-avatar-dialog a, .mec-schedule-speakers a").on('click', function(e)
{
    e.preventDefault();
    lity(jQuery(this).attr('href'));

	return false;
});

jQuery(".mec-booking-button-register").on('click', function(e)
{
    if(jQuery(".mec-booking-button.mec-booking-data-lity").length>0){
        return false;
    }

    e.preventDefault();
    jQuery([document.documentElement, document.body]).animate({
        scrollTop: jQuery(jQuery(this).data('bookingform')).offset().top
    }, 300);

    jQuery([parent.document.documentElement, parent.document.body]).animate({
        scrollTop: jQuery(jQuery(this).data('bookingform')).offset().top
    }, 300);

    return false;
});

// Fix modal booking in some themes
jQuery(window).on('load', function()
{
    jQuery(".mec-booking-button.mec-booking-data-lity").on('click', function(e)
    {
        e.preventDefault();
        lity(jQuery(this).data('bookingform'));

		return false;
    });

    jQuery(".mec-booking-button-register").on('click', function(e)
    {
        if(jQuery(".mec-booking-button.mec-booking-data-lity").length>0){
            return false;
        }

        e.preventDefault();
        jQuery([document.documentElement, document.body]).animate({
            scrollTop: jQuery(jQuery(this).data('bookingform')).offset().top
        }, 300);

        jQuery([parent.document.documentElement, parent.document.body]).animate({
            scrollTop: jQuery(jQuery(this).data('bookingform')).offset().top
        }, 300);

        return false;
    });
});
</script>
