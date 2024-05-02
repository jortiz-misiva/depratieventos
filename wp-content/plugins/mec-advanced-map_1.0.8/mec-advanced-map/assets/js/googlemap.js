// MEC GOOGLE MAPS PLUGIN
(function($) {

    function rad(x) {return x*Math.PI/180;}
    function find_closest_marker( position, map, markers ) {
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        var R = 6371; // radius of earth in km
        var distances = [];
        var closest = -1;

        $.each(markers, function(i, mark){
            var mlat = mark.latitude;
            var mlng = mark.longitude;
            var dLat  = rad(mlat - lat);
            var dLong = rad(mlng - lng);
            var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(rad(lat)) * Math.cos(rad(lat)) * Math.sin(dLong/2) * Math.sin(dLong/2);
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            var d = R * c;
            distances[i] = d;
            if ( closest == -1 || d < distances[closest] ) {
                closest = i;
            }
        });

        nearest_loc = markers[closest];

        return closest != -1 ? {
            lat: nearest_loc.latitude,
            lng: nearest_loc.longitude,
        } : false;
    }

    $.fn.mecGoogleMaps = function(options) {
        // Default Options
        var settings = $.extend({
            show_on_map_text: "Show on Google Map",
            id: null,
            latitude: "",
            longitude: "",
            autoinit: true,
            zoom: 14,
            scrollwheel: false,
            icon: '../img/m-01.png',
            markers: {},
            sf: {},
            fields: {},
            HTML5geolocation: 0,
            getDirection: 0,
            directionOptions: {
                form: '#mec_get_direction_form',
                reset: '.mec-map-get-direction-reset',
                addr: '#mec_get_direction_addr',
                destination: {},
            },
        }, options);

        var bounds;
        var map;
        var infowindow;
        var loadedMarkers = new Array();
        var markerCluster;

        var currentMarker = 0;
        var allMarker = 0;

        var canvas = this;

        var DOM = canvas[0];

        if (typeof DOM == 'undefined') {
            var canvas_local = jQuery('#mec_googlemap_canvas' + settings.id);
            DOM = canvas_local[0];
        }

        if (typeof DOM == 'undefined') {
            console.error("MAP HTML Element not found");
            return;
        }

        // Init the Map
        if (settings.autoinit) init();

        function init() {

            var clear_markers = function( e, atts ){

                var response = atts.r;
                var settings_id = atts.settings_id;

                if( response.length == 0 ){
                    return;
                }

                if( response.count == 0 ){
                    $('#mec_googlemap_canvas'+settings_id).hide();
                }else{
                    $('#mec_googlemap_canvas'+settings_id).show();
                }
            };

            $(document).on('mec_set_month_process_end', clear_markers );
            $(document).on('mec_search_process_end', clear_markers );

            // Create the options
            bounds = new google.maps.LatLngBounds();
            var lat,lng,center = "";
            if(settings.latitude.length > 0 && settings.longitude.length > 0){
                lat = settings.latitude;
                lng = settings.longitude;

                center = new google.maps.LatLng(lat, lng);
            }

            var mapOptions = {
                scrollwheel: settings.scrollwheel,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                center: center != "" ? center : new google.maps.LatLng("", ""),
                zoom: settings.zoom,
                styles: settings.styles,
            };

            // Init map
            map = new google.maps.Map(DOM, mapOptions);

            var clusterCalculator = function(markers, numStyles) {
                var weight = 0;

                for (var i = 0; i < markers.length; ++i) {
                    weight += markers[i].weight;
                }

                return {
                    text: weight,
                    index: Math.min(String(weight).length, numStyles)
                };
            };

            markerClusterOptions = {
                styles: [{
                    height: 53,
                    url: settings.clustering_images + '1.png',
                    width: 53,
                    textColor: '#fff'
                }, {
                    height: 56,
                    url: settings.clustering_images + '2.png',
                    width: 56,
                    textColor: '#000'
                }, {
                    height: 66,
                    url: settings.clustering_images + '3.png',
                    width: 66,
                    textColor: '#fff'
                }, {
                    height: 78,
                    url: settings.clustering_images + '4.png',
                    width: 78,
                    textColor: '#fff'
                }, {
                    height: 90,
                    url: settings.clustering_images + '5.png',
                    width: 90,
                    textColor: '#fff'
                }]
            }

            markerCluster = new MarkerClusterer(map, null, markerClusterOptions);

            markerCluster.setCalculator(clusterCalculator);
            markerCluster.addMarkers(loadedMarkers);

            var render_markers = false;
            // Search Widget
            if (settings.sf.container !== '') {
                $(settings.sf.container).mecSearchForm({
                    id: settings.id,
                    atts: settings.atts,
                    callback: function(atts) {
                        render_markers = true;
                        settings.atts = atts;
                        getMarkers();
                    },
                    fields: settings.fields
                });
            }

            if ( !render_markers ){
                if( 0 == settings.markers.length ){
                    getMarkers();
                }else{
                    renderMarkers( settings.markers );
                }
            }

            if( center != "" ){

                setTimeout(function() {
                    map.panTo(center);
                    map.setZoom(settings.zoom);
                }, 1000);
            }


            var nexprev = document.createElement('div');

            var prev = document.createElement('input');
            prev.className = 'mec-map-input mec-map-prev';
            prev.type = 'button';
            prev.value = "PREV";
            prev.addEventListener('click', prevBtnHandler);
            nexprev.appendChild(prev);

            var next = document.createElement('input');
            next.className = 'mec-map-input mec-map-next';
            next.type = 'button';
            next.value = "NEXT";
            next.addEventListener('click', nextBtnHandler);
            nexprev.appendChild(next);
            nexprev.index = 2;
            map.controls[google.maps.ControlPosition.TOP_RIGHT].push(nexprev);

            var autocomplete;

            function initAutocomplete() {
                autocomplete = document.createElement('div');

                var searchInp = document.createElement('input');
                searchInp.className = 'mec-map-input mec-map-address-autocomplete';
                searchInp.type = 'text';
                searchInp.style.width = '100%';
                searchInp.style.fontSize = '15px';

                autocomplete.appendChild(searchInp);

                map.controls[google.maps.ControlPosition.LEFT_TOP].push(autocomplete);
                autocomplete = new google.maps.places.Autocomplete(searchInp);

                autocomplete.addListener('place_changed', onPlaceChanged);

            }
            google.maps.event.addDomListener(window, 'load', initAutocomplete);

            function onPlaceChanged() {

                var place = autocomplete.getPlace();

                if (place.geometry) {
                    map.panTo(place.geometry.location);
                    map.setZoom(13);
                }
            }

            // Init Infowindow
            infowindow = new google.maps.InfoWindow({
                pixelOffset: new google.maps.Size(0, -37)
            });

            // Load Markers
            loadMarkers(settings.markers);

            // Initialize get direction feature
            if (settings.getDirection === 1) initSimpleGetDirection();
            else if (settings.getDirection === 2) initAdvancedGetDirection();

            // Geolocation
            if (settings.HTML5geolocation && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var center = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    var zoom = map.getZoom();

                    if (zoom <= 6) zoom = zoom + 5;
                    else if (zoom <= 10) zoom = zoom + 3;
                    else if (zoom <= 14) zoom = zoom + 2;
                    else if (zoom <= 18) zoom = zoom + 1;

                    var nearest =  find_closest_marker( position, map, settings.markers );
                    if( nearest != false ){
                        map.panTo(
                            new google.maps.LatLng(nearest.lat, nearest.lng)
                        );
                    }else{
                        map.panTo(center);
                    }
                    map.setZoom(5);
                });
            }
        }

        function reinitSearch() {
            $('.mec-map-boxshow').click(function(event) {

                var key = $(this).attr('data-id');

                var i = 0;

                for (var k in loadedMarkers) {
                    if (loadedMarkers[k]['ukey'] == key) {
                        currentMarker = i;
                        showMarker();
                        break;
                    }

                    i += 1;

                }
                return false;
            });
        }

        function showMarker() {
            var marker = loadedMarkers[currentMarker];
            google.maps.event.trigger(marker, 'mouseover', loadedMarkers);
        }

        function prevBtnHandler() {

            currentMarker -= 1;
            if (currentMarker < 0) {
                currentMarker = loadedMarkers.length - 1;
            }
            showMarker();
        }

        function nextBtnHandler() {
            currentMarker += 1;
            if (currentMarker >= loadedMarkers.length) {
                currentMarker = 0;
            }
            showMarker();
        }

        function loadMarkers(markers) {
            var f = 0;

            var sideHtml = '';

            for (var i in markers) {
                f++;
                var dataMarker = markers[i];

                var gmap_url = 'https://www.google.com/maps/search/?api=1&query=' + dataMarker.latitude + ',' + dataMarker.longitude;

                var marker = new RichMarker({
                    position: new google.maps.LatLng(dataMarker.latitude, dataMarker.longitude),
                    map: map,
                    event_ids: dataMarker.event_ids,
                    infowindow: dataMarker.infowindow,
                    lightbox: dataMarker.lightbox + '<div class="mec-map-lightbox-link" style="background: #fff;padding: 4px;font-size: 15px;width:auto;"><a class="mec-go-to-map" target="_blank" href="' + gmap_url + '">'+ settings.show_on_map_text +'</a></div>',
                    icon: (dataMarker.icon ? dataMarker.icon : settings.icon),
                    content: '<div class="mec-marker-container"><span class="mec-marker-wrap"><span class="mec-marker">' + dataMarker.count + '</span><span class="mec-marker-pulse-wrap"><span class="mec-marker-pulse"></span></span></span></div>',
                    shadow: 'none',
                    weight: dataMarker.count
                });

                // Marker Info-Window
                if (Math.max(document.documentElement.clientWidth, window.innerWidth || 0) > 960) {
                    google.maps.event.addListener(marker, 'mouseover', function(event) {
                        infowindow.close();
                        infowindow.setContent(this.infowindow);
                        infowindow.open(map, this);
                    });

                    // Marker Lightbox
                    google.maps.event.addListener(marker, 'click', function(event) {
                        lity(this.lightbox);
                        jQuery(document).on('lity:open', function (event, instance) {
                            jQuery('.lity').addClass('mec-map-shortcode-popup');
                        });
                    });
                } else if (Math.max(document.documentElement.clientWidth, window.innerWidth || 0) <= 960) {
                    google.maps.event.addListener(marker, 'click', function(event) {
                        infowindow.close();
                        infowindow.setContent(this.infowindow);
                        infowindow.open(map, this);
                        lity(this.lightbox);
                        jQuery(document).on('lity:open', function (event, instance) {
                            jQuery('.lity').addClass('mec-map-shortcode-popup');
                        });
                    });
                }

                // extend the bounds to include each marker's position
                bounds.extend(marker.position);

                marker['ukey'] = dataMarker.latitude + ',' + dataMarker.longitude;

                // Added to Markers
                loadedMarkers.push(marker);

                sideHtml += '<div class="mec-map-boxshow" data-id="' + marker['ukey'] + '">' + dataMarker.lightbox + '</div>';

            }

            if (f > 1) map.fitBounds(bounds);

            // Set map center if only 1 marker found
            if (f === 1) {
                map.setCenter(new google.maps.LatLng(dataMarker.latitude, dataMarker.longitude));
            }

            var sideElement = $('#mec-map-skin-side-' + settings.id);
            if (typeof sideElement != 'undefined' && sideElement != null) {
                sideElement.html(sideHtml);
                reinitSearch();
            }

            if (f == 0) {
                sideElement.html('<h4>No Event Found</h4>');
            }
        }

        function getMarkers() {
            // Add loader
            $("#mec_googlemap_canvas" + settings.id).addClass("mec-loading");

            $.ajax({
                url: settings.ajax_url,
                data: "action=mec_map_get_markers&" + settings.atts,
                dataType: "json",
                type: "post",
                success: function(response) {

                    renderMarkers( response.markers );
                },
                error: function() {
                    // Remove loader
                    $("#mec_googlemap_canvas" + settings.id).removeClass("mec-loading");
                }
            });
        }

        function renderMarkers( markers ){

            var e_id = "#mec_googlemap_canvas" + settings.id;

            $(e_id).addClass("mec-loading");

            // Remove Markers
            removeMarkers();

            // Load Markers
            loadMarkers(markers);

            markerCluster.clearMarkers();
            markerCluster.addMarkers(loadedMarkers, false);
            markerCluster.redraw();

            $(e_id).removeClass("mec-loading");
        }

        function removeMarkers() {
            bounds = new google.maps.LatLngBounds();

            if (loadedMarkers) {
                for (i = 0; i < loadedMarkers.length; i++) loadedMarkers[i].setMap(null);
                loadedMarkers.length = 0;
            }
        }

        var directionsDisplay;
        var directionsService;
        var startMarker;
        var endMarker;

        function initSimpleGetDirection() {
            $(settings.directionOptions.form).on('submit', function(event) {
                event.preventDefault();

                var from = $(settings.directionOptions.addr).val();
                var dest = new google.maps.LatLng(settings.directionOptions.destination.latitude, settings.directionOptions.destination.longitude);

                // Reset the direction
                if (typeof directionsDisplay !== 'undefined') {
                    directionsDisplay.setMap(null);
                    startMarker.setMap(null);
                    endMarker.setMap(null);
                }

                // Fade Google Maps canvas
                $(canvas).fadeTo(300, .4);

                directionsDisplay = new google.maps.DirectionsRenderer({
                    suppressMarkers: true
                });
                directionsService = new google.maps.DirectionsService();

                var request = {
                    origin: from,
                    destination: dest,
                    travelMode: google.maps.DirectionsTravelMode.DRIVING
                };

                directionsService.route(request, function(response, status) {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                        directionsDisplay.setMap(map);

                        var leg = response.routes[0].legs[0];
                        startMarker = new google.maps.Marker({
                            position: leg.start_location,
                            map: map,
                            icon: settings.directionOptions.startMarker,
                        });

                        endMarker = new google.maps.Marker({
                            position: leg.end_location,
                            map: map,
                            icon: settings.directionOptions.endMarker,
                        });
                    }

                    // Fade Google Maps canvas
                    $(canvas).fadeTo(300, 1);
                });

                // Show reset button
                $(settings.directionOptions.reset).removeClass('mec-util-hidden');
            });

            $(settings.directionOptions.reset).on('click', function(event) {
                $(settings.directionOptions.addr).val('');
                $(settings.directionOptions.form).submit();

                // Hide reset button
                $(settings.directionOptions.reset).addClass('mec-util-hidden');
            });
        }

        function initAdvancedGetDirection() {
            $(settings.directionOptions.form).on('submit', function(event) {
                event.preventDefault();

                var from = $(settings.directionOptions.addr).val();
                var url = 'https://maps.google.com/?saddr=' + encodeURIComponent(from) + '&daddr=' + settings.directionOptions.destination.latitude + ',' + settings.directionOptions.destination.longitude;

                window.open(url);
            });
        }

        return {
            init: function() {
                init();
            }
        };
    };

    $('.mec-map-boxshow a').click(function(e) {
        e.preventDefault();
    });

}(jQuery));