// MEC OPENSTREETMAP MAPS PLUGIN
(function($) {
    $.fn.mecOpenstreetMaps = function(options) {
        // Default Options
        var settings = $.extend({
            show_on_openstreetmap_text: "Show on OpenstreetMap",
            id: null,
            latitude: 0,
            longitude: 0,
            autoinit: true,
            scrollwheel: false,
            zoom: 14,
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
        var map = null;
        var infowindow;
        var loadedMarkers = new Array();
        var markerCluster;

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


        var defaultLatLong = null;

        var markers = null;
        var markersList = [];

        var currentMarker = 0;
        var allMarker = 0;

        if (settings.latitude != 0 && settings.longitude != 0) {
            defaultLatLong = new L.LatLng(settings.latitude, settings.longitude);
        } else {
            for (var firstLocation in settings.markers) break;
            if (typeof firstLocation == 'undefined' && typeof settings.markers[firstLocation] == 'undefined') {
                firstLocation = 'firstLocation';
                settings.markers[firstLocation] = {};
                settings.markers[firstLocation]['latitude'] = settings.latitude;
                settings.markers[firstLocation]['longitude'] = settings.longitude;
            }
            defaultLatLong = new L.LatLng(settings.markers[firstLocation].latitude, settings.markers[firstLocation].longitude);
        }

        // Init the Map
        if (settings.autoinit) init();

        function populate() {

            var sideHtml = '';

            for (var k in settings.markers) {

                var latlong = new L.LatLng(settings.markers[k].latitude, settings.markers[k].longitude);
                var openstreetmap_url = 'http://www.openstreetmap.org/?mlat=' + settings.markers[k].latitude + '&mlon=' + settings.markers[k].longitude + '&zoom=' + settings.zoom;

                var m = new L.Marker(latlong).bindPopup(settings.markers[k].lightbox + '<a target="_blank" href="' + openstreetmap_url + '">'+ settings.show_on_openstreetmap_text +'</a>');
                m['locationData'] = settings.markers[k];
                m['key'] = k;
                markersList.push(m);
                markers.addLayer(m);

                allMarker += 1;

                sideHtml += '<div class="mec-map-boxshow" data-id="' + k + '">' + settings.markers[k].lightbox + '</div>';

                if (defaultLatLong == null) {
                    defaultLatLong = latlong;
                }

            }

            if (allMarker > 0 && map !== null && defaultLatLong != null) {
                map.panTo(defaultLatLong);
                defaultLatLong = null;
            }

            var sideElement = $('#mec-map-skin-side-' + settings.id);
            if (typeof sideElement != 'undefined' && sideElement != null) {
                sideElement.html(sideHtml);
                reinitSearch();
            }

            if (allMarker == 0) {
                sideElement.html('<h4>No Event Found</h4>');
            }

            return false;
        }

        map.addLayer(markers);

        $('#mec-map-myposition').click(function(event) {
            geoLocation();
        });

        function reinitSearch() {
            $('.mec-map-boxshow').click(function(event) {

                var key = $(this).attr('data-id');
                console.log(key);

                var i = 0;

                for (var k in markersList) {
                    if (markersList[k]['key'] == key) {
                        currentMarker = i;
                        showMarker();
                        break;
                    }

                    i += 1;

                }
                return false;
            });
        }

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

            var tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    scrollWheelZoom: settings.scrollwheel,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }),
                latlng = defaultLatLong;
            map = new L.Map(DOM, {
                scrollwheel: settings.scrollwheel,
                scrollWheelZoom: settings.scrollwheel,
                center: latlng,
                minZoom: 1,
                maxZoom: 19,
                layers: [tiles],
                fullscreenControl: true,
            }).setView([latlng.lat, latlng.lng], settings.zoom);

            markers = new L.MarkerClusterGroup({
                iconCreateFunction: function(cluster) {

                    var mrks = cluster.getAllChildMarkers();
                    var childCount = 0;
                    for (var k in mrks) {
                        childCount += mrks[k].locationData.count;
                    }

                    var c = ' marker-cluster-';
                    if (childCount < 10) {
                        c += 'small';
                    } else if (childCount < 100) {
                        c += 'medium';
                    } else {
                        c += 'large';
                    }

                    return new L.DivIcon({
                        html: '<div><span>' + childCount + '</span></div>',
                        className: 'marker-cluster' + c,
                        iconSize: new L.Point(40, 40)
                    });
                }
            });

            populate();

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

            if( !render_markers && 0 == settings.markers.length ){
                getMarkers();
            }
        }


        function geoLocation() {

            if (navigator.geolocation) {

                navigator.geolocation.getCurrentPosition(function(data) {

                    var latlong = new L.LatLng(data.coords.latitude, data.coords.longitude);
                    map.panTo(latlong);

                }, function(err) {
                    console.log('Failed', err);
                }, {
                    timeout: 10000
                });


            } else {
                console.log('Cannot detect my position');

            }

        }

        function getMarkers() {

            $.ajax({
                url: settings.ajax_url,
                data: "action=mec_map_get_markers&" + settings.atts,
                dataType: "json",
                type: "post",
                success: function(response) {
                    serchResult = true;
                    removeMarkers();
                    settings.markers = response.markers;
                    populate();

                },
                error: function() {

                }
            });
        }

        function removeMarkers() {

            if (markersList) {
                for (i = 0; i < markersList.length; i++) {
                    map.removeLayer(markersList[i]);
                    markers.removeLayer(markersList[i]);

                }
                currentMarker = 0;
                allMarker = 0;
                markersList = [];
            }
        }

        function showMarker() {
            var marker = markersList[currentMarker];
            map.panTo(marker._latlng);

            markers.zoomToShowLayer(marker, function() {
                marker.openPopup();
            });

            setTimeout(function() {
                marker.fire('click');

            }, 100);

            marker.fire('click');
        }


        $('#mec-map-next').click(function(event) {

            currentMarker += 1;
            if (currentMarker >= allMarker) {
                currentMarker = 0;
            }
            showMarker();

        });

        $('#mec-map-prev').click(function(event) {

            currentMarker -= 1;
            if (currentMarker < 0) {
                currentMarker = allMarker - 1;
            }
            showMarker();
        });

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