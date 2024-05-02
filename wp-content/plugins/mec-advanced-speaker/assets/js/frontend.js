// MEC GRID VIEW PLUGIN
(function ($) {
    $.fn.mecAdvancedSpeakerGridView = function (options) {
        // Default Options
        var settings = $.extend({
            // These are the defaults.
            id: 0,
            atts: '',
            ajax_url: '',
            style:'list',
            limit: '',
            offset: 0,
            section:'speaker',
        }, options);

        // Set onclick Listeners
        setListeners();

        var sf;

        function setListeners() {

            $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").on("click", function () {
                loadMore();
            });
        }

        function loadMore() {
            // Add loading Class
            $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").addClass("mec-load-more-loading");

            var orderby = $('.mec-orderby').val();
            var order = $('.mec-order').val();
            var search = $('.mec-s').val();
            $.ajax({
                url: settings.ajax_url,
                data: "action=mec_featured_"+settings.section+"_load_more&mec_limit="+settings.limit+'&mec_style='+settings.style+"&mec_offset=" + settings.offset + "&orderby=" + orderby+"&order=" + order+ "&s2=" + search + "&" + settings.atts ,
                dataType: "json",
                type: "post",
                success: function (response) {

                    if (response.count == "0") {
                        // Remove loading Class
                        $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-load-more-loading");

                        // Hide load more button
                        $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").addClass("mec-util-hidden");
                    } else {
                        // Show load more button
                        $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-util-hidden");

                        // Append Items
                        $("#mec_advanced_speaker_skin_events_" + settings.id).append(response.html);

                        // Remove loading Class
                        $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-load-more-loading");

                        // Update the variables
                        settings.end_date = response.end_date;
                        settings.offset = response.offset;
                    }

                    if(settings.limit!='' && settings.limit>0){
                        if(response.count < settings.limit){
                            $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").removeClass("mec-load-more-loading");
                            $("#mec_advanced_speaker_skin_" + settings.id + " .mec-load-more-button").addClass("mec-util-hidden");
                        }
                    }
                },
                error: function () {}
            });
        }
    };

}(jQuery));

jQuery(document).ready(function($) {
    var featuredSlider = $('.mec-advanced-speaker-slider-isup').val();
    if(typeof featuredSlider!= 'undefined' && featuredSlider!='' && featuredSlider=='yes'){
        if ($('body').hasClass('rtl')) {
        var owl_rtl = true;
        } else {
            var owl_rtl = false;
        }

        // MEC WIDGET CAROUSEL
        $(".mec-advanced-speaker .mec-advanced-speaker-slider .mec-event-speaker-slider").addClass('mec-owl-carousel mec-owl-theme');
        $(".mec-advanced-speaker .mec-advanced-speaker-slider .mec-event-speaker-slider").owlCarousel({
            autoplay: true,
            autoplayTimeout: 3000,
            autoplayHoverPause: true,
            loop: true,
            dots: false,
            nav: true,
            navText: [],
            items: 1,
            autoHeight: true,
            responsiveClass: true,
            rtl: owl_rtl,
        });
        $( ".owl-prev").html('<i class="mec-fa-angle-left"></i>');
        $( ".owl-next").html('<i class="mec-fa-angle-right"></i>');
    }
});