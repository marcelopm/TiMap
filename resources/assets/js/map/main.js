/**
 * Main app module
 *
 * @author marcelo moises
 * @type Module
 */

var $ = require('jquery');
var _ = require('lodash');
var g = require('./global');
//var Chart = require('chartjs');
//"chart.js": "^2.4.0",

var _module = {
    image: null,
    /**
     * Map marker click handler, used to show image popup once ther markers plotted on the map get clicked
     *
     * @param {object} marker
     * @returns {void}
     */
    openImage: function (marker) {
        this.image = marker.image;

        // set up the image holder with the image
        $('#image').css('background-image', 'url(' + this.image.url.medium + ')');
        // set up image title
        $('#modal .image-title').html($.trim(this.image.title).length > 0 ? this.image.title : 'No title');

        // in case the image has been analysed already
        if (_.has(this.image, 'analysis.image_recognition')) {
            // proceed with the analysis processing
            this.processAnalysis(this.image.analysis);
        }

        // open the image pop - modal
        $('#modal').modal('open');
    },
    /**
     * Setup the image popup according to its analysis.
     * In case the analysis hasn't been reviewed by the user yet, show the review overlay
     * Configure the extra information panel and button trigger according to its analysis
     *
     * @param {object} analysis
     * @returns {void}
     */
    processAnalysis: function (analysis) {
        var tags = ['Unknown'];
        // for images that has been tagged with a known tag
        if (!_.isEmpty(analysis.image_recognition)) {
            // enable button to show the extra info panel
            $('.btn-floating.red').removeClass('disabled');

            // set up graph labels and values
            var labels = _.keys(analysis.image_recognition);
            var values = _.map(_.values(analysis.image_recognition), function (value) {
                return parseInt(value * 100);
            });

            if (labels.length) {
                // in case there are a couple or more labels
                if (labels.length > 1) {
                    tags = _.map(labels, function (value) {
                        // if the are comma separated, get the first value
                        return value.indexOf(',') ? _.first(value.split(',')) : value;
                    });
                } else {
                    // otherwith, if there is only one label, split any commas separated parts into tags
                    var value = _.first(labels);
                    tags = value.indexOf(',') ? value.split(',') : [value];
                }
            }

            // adjust the sum of the matching probabilities
            var sum = _.sum(values);
            if (sum !== 100) {
                labels.push('other');
                values.push(100 - sum);
            }

            // set up graph
            var data = {
                labels: labels,
                datasets: [
                    {
                        data: values,
                        backgroundColor: [
                            "#FF6384",
                            "#36A2EB",
                            "#FFCE56"
                        ],
                        hoverBackgroundColor: [
                            "#FF6384",
                            "#36A2EB",
                            "#FFCE56"
                        ]
                    }]
            };

            // and create it
            $('.chart-holder').html('<canvas id="chart"></canvas>');
            new Chart($('#chart'), {
                type: 'pie',
                data: data,
                options: {
                    tooltips: {
                        callbacks: {
                            label: function (tooltipItem, data) {
                                var value = data.datasets[0].data[tooltipItem.index];
                                return value + '%';
                            }
                        }
                    }
                }
            });
        }

        // in case the analysis hasn't been reviewed yet, show the review panel
        if (!_.has(_module.image, 'analysis.hashes.review')) {
            $('#overlay').delay(1000).fadeIn('slow');
        }

        $('.btn-floating.red').fadeIn();

        // place the tags
        _.each(tags, function (value) {
            $('.card-chip').append('<div class="right chip blue lighten-1 z-depth-4 grey-text text-lighten-5"></div>');
            $('.card-chip .chip:last')
                    .fadeIn()
                    .html(value);
        });
    },
    /**
     *
     * @param {string} operation
     * @returns {void}
     */
    weight: function (operation) {
        // abort the ongoing xhq
        if (g.xhr['weight']) {
            g.xhr['weight'].abort();
        }

        $(this).addClass('active');
        $('#overlay div.btn').addClass('disabled');
        $('.modal .progress').delay(500).fadeTo('slow', 1, function () {
            g.xhr['weight'] = $.getJSON('/map/analyser/weight/' + operation, {
                id: _module.image.id,
                hash: _module.image.analysis.hashes.analyser
            }).then(function (response) {
                $('#overlay').fadeOut();

                if (!response.length) {
                    return $.Deferred().reject();
                }

                _module.image.analysis.hashes.review = response;
                $('.modal .progress').fadeTo(300, 0);
            }).fail(function () {
                $('#overlay div.btn')
                        .removeClass('active')
                        .removeClass('disabled');

                _module.progress.state.error();
            }).always(function () {
                g.xhr['weight'] = null;
            });
        });
    },
    /**
     * Image popup's progressbar helpers
     */
    progress: {
        state: {
            reset: function () {
                $('.modal .progress')
                        .fadeTo(0, 0)
                        .removeClass('error')
                        .removeClass('red accent-1')
                        .find('.indeterminate')
                        .removeClass('red lighten-1');
            },
            error: function () {
                $('.modal .progress')
                        .addClass('error')
                        .addClass('red accent-1')
                        .find('.indeterminate')
                        .addClass('red lighten-1');
            }
        }
    }
};

// once this modules gets included, execute the method below
(function () {
    // on the page has been loaded
    $(function () {
        // setup the image popup - modal
        $('.modal').modal({
            dismissible: true, // Modal can be dismissed by clicking outside of the modal
            in_duration: 100, // Transition in duration
            out_duration: 100,
            opacity: 0.05, // Opacity of modal background
            ready: function (modal, trigger) { // Callback for Modal open. Modal and trigger parameters available.
                if (!_.has(_module.image, 'analysis.image_recognition')) {
                    $('.modal .progress').delay(500).fadeTo('slow', 1, function () {

                        // abort the ongoing xhq
                        if (g.xhr['analyse']) {
                            g.xhr['analyse'].abort();
                        }

                        // clear the last schedule xhq
                        if (g.timeout['analyse']) {
                            clearTimeout(g.timeout['analyse']);
                        }

                        // request an image analysis from the backend
                        g.xhr['analyse'] = $.getJSON('/map/image/analyse', {
                            id: _module.image.id,
                            url: _module.image.url.original
                        }, function (response) {
                            $('.modal .progress').fadeTo(300, 0);

                            _module.processAnalysis(response);
                            // update the image object
                            _module.image.analysis = response;

                        }).fail(function () {
                            _module.progress.state.error();
                        }).always(function () {
                            g.xhr['analyse'] = null;
                        });
                    });
                }
            },
            complete: function () { // once the popup gets closed

                // abort the ongoing xhq
                if (g.xhr['analyse']) {
                    g.xhr['analyse'].abort();
                }

                // clear image
                $('#image').attr({
                    src: '',
                });

                // reset the review panel
                $('#overlay').fadeOut();
                $('#overlay div.btn')
                        .removeClass('active')
                        .removeClass('disabled');

                // remove any tags
                $('.card-chip').html('');

                // reset extra info button
                $('.btn-floating.red')
                        .addClass('disabled')
                        .fadeOut();

                // reset progress error status
                _module.progress.state.reset();

                // close image details
                $('.card-reveal .material-icons:contains("close")').click();
            }
        });
    });

    // setup the analysis review panel listeners
    $('.increment-weight').click(function (e) {
        _module.weight.apply(this, ['increment']);
    });

    $('.decrement-weight').click(function (e) {
        _module.weight.apply(this, ['decrement']);
    });
})();

module.exports = _module;