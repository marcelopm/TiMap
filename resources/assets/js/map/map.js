/**
 * Map module, based on google api examples
 *
 * @author marcelo moises
 * @type Module
 */

var $ = require('jquery');
var g = require('./global');
var main = require('./main');

var _module = {
    map: null,
    places: [],

    /**
     * Creates location button
     *
     * @returns {void}
     */
    addLocationButton: function () {
        var _this = this;
        // create html elements
        var controlDiv = document.createElement('div');

        var firstChild = document.createElement('button');
        firstChild.style.backgroundColor = '#fff';
        firstChild.style.border = 'none';
        firstChild.style.outline = 'none';
        firstChild.style.width = '28px';
        firstChild.style.height = '28px';
        firstChild.style.borderRadius = '2px';
        firstChild.style.boxShadow = '0 1px 4px rgba(0,0,0,0.3)';
        firstChild.style.cursor = 'pointer';
        firstChild.style.marginRight = '10px';
        firstChild.style.padding = '0';
        firstChild.title = 'Your Location';
        controlDiv.appendChild(firstChild);

        var secondChild = document.createElement('div');
        secondChild.style.margin = '5px';
        secondChild.style.width = '18px';
        secondChild.style.height = '18px';
        secondChild.style.backgroundImage = 'url(https://maps.gstatic.com/tactile/mylocation/mylocation-sprite-2x.png)';
        secondChild.style.backgroundSize = '180px 18px';
        secondChild.style.backgroundPosition = '0 0';
        secondChild.style.backgroundRepeat = 'no-repeat';
        firstChild.appendChild(secondChild);

        // set up listeners
        google.maps.event.addListener(this.map, 'center_changed', function () {
            secondChild.style['background-position'] = '0 0';
        });

        firstChild.addEventListener('click', function () {
            var imgX = '0',
                    animationInterval = setInterval(function () {
                        imgX = imgX === '-18' ? '0' : '-18';
                        secondChild.style['background-position'] = imgX + 'px 0';
                    }, 500);

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    _this.map.setCenter(latlng);
                    clearInterval(animationInterval);
                    secondChild.style['background-position'] = '-144px 0';
                });
            } else {
                clearInterval(animationInterval);
                secondChild.style['background-position'] = '0 0';
            }
        });

        controlDiv.index = 1;
        this.map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(controlDiv);

        // try to get user's location
        firstChild.click();
    },

    /**
     * Creates search box
     *
     * @returns {void}
     */
    addSearchBar: function () {
        // Create the search box and link it to the UI element.
        var input = document.getElementById('searchbar');
        this.searchBox = new google.maps.places.SearchBox(input);
        this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    },

    /**
     * Configure map's listners
     * 
     * @returns {void}
     */
    addListeners: function () {
        var _this = this;

        // once a new place is searched through the search box
        this.searchBox.addListener('places_changed', function () {
            var places = _this.searchBox.getPlaces();

            if (places.length === 0) {
                return;
            }

            // Clear out the old markers.
            _this.places.forEach(function (place) {
                place.setMap(null);
            });
            _this.places = [];

            // For each place, get the icon, name and location.
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function (place) {
                if (!place.geometry) {
                    console.log("Returned place contains no geometry");
                    return;
                }
                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };

                // Create a marker for each place.
                _this.places.push(new google.maps.Marker({
                    map: _this.map,
                    icon: icon,
                    title: place.name,
                    position: place.geometry.location
                }));

                if (place.geometry.viewport) {
                    // Only geocodes have viewport.
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            _this.map.fitBounds(bounds);
        });

        // once any changes on the map bounds happens
        this.map.addListener('bounds_changed', function () {

            // reset progress error status
            $('.map-container .progress')
                    .fadeTo(0, 0)
                    .removeClass('error')
                    .removeClass('red accent-1')
                    .find('.indeterminate')
                    .removeClass('red lighten-1');


            var bounds = _this.map.getBounds();
            _this.searchBox.setBounds(bounds);

            // abort the ongoing xhq
            if (g.xhr['bounds_changed']) {
                g.xhr['bounds_changed'].abort();
            }

            // clear the last schedule xhq
            if (g.timeout['bounds_changed']) {
                clearTimeout(g.timeout['bounds_changed']);
            }

            g.timeout['bounds_changed'] = setTimeout(function () {
                g.timeout['bounds_changed'] = null;

                // show progress and request an image search request to the backend for the given bounding box coordinates
                $('.map-container .progress').delay(500).fadeTo('slow', 1, function () {
                    g.xhr['bounds_changed'] = $.getJSON('/map/image/search', {
                        maxlat: bounds.getNorthEast().lat(),
                        maxlng: bounds.getNorthEast().lng(),
                        minlat: bounds.getSouthWest().lat(),
                        minlng: bounds.getSouthWest().lng(),
                    }, function (response) {
                        g.xhr['bounds_changed'] = null;
                        $('.map-container .progress').fadeTo(300, 0);

                        // in case the search response has any images
                        if (response && response.images.length > 0) {
                            // create markers on the map for each one of them
                            $.each(response.images, function (index, image) {
                                var marker = new google.maps.Marker({
                                    map: _this.map,
                                    image: image,
                                    title: image.title,
                                    position: {
                                        lat: parseFloat(image.lat),
                                        lng: parseFloat(image.lng)
                                    }
                                });

                                // bind image popup method to the click event
                                marker.addListener('click', function () {
                                    main.openImage(marker);
                                });
                            });
                        }

                    }).fail(function () {
                        $('.map-container .progress')
                                .addClass('error')
                                .addClass('red accent-1')
                                .find('.indeterminate')
                                .addClass('red lighten-1');
                    }).always(function () {
                        g.xhr['bounds_changed'] = null;
                    });
                });
            }, 500);
        });
    },

    /**
     * Once the api get's loaded, create the map and set it up
     *
     * @returns {void}
     */
    init: function () {
        this.map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: -33.8688, lng: 151.2195},
            zoom: 13,
            mapTypeId: 'roadmap',
            streetViewControl: false,
            mapTypeControl: false,
            clickableIcons: false
        });

        this.addSearchBar();
        this.addListeners();
        this.addLocationButton();
    }

};

module.exports = _module;