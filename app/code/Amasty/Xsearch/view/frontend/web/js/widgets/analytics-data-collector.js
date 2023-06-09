define([
    'jquery',
    'mage/url',
    'underscore',
    'mage/translate',
    'jquery-ui-modules/widget',
    'mage/cookies'
], function ($, urlBuilder, _) {
    'use strict';

    $.widget('mage.amXsearchAnalyticsDataCollector', {
        dataCollectorPool: [],
        dataForSend: [],
        options: {
            baseUrl: window.BASE_URL,
            backendUrl: 'amasty_xsearch/analytics/collect',
            throttleTime: 500,
            selectors: {
                wrapper: '[data-amsearch-wrapper="block"]',
                input: '[data-amsearch-block="input"]',
                item: '[data-amsearch-js="product-item"]',
                popup: '[data-amsearch-js="results"]',
                searchClick: '[data-amsearch-analytics="block"] .amsearch-link, [data-amsearch-analytics="block"]' +
                    ' .action.view, [data-amsearch-analytics="block"] .tocart'
            }
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            this.initUrls();
            this.addListener();
            this.sendData = _.throttle(this.sendData.bind(this), this.options.throttleTime);
            this.initDataCollectors();
        },


        addListener: function () {
            $(document).on('click', this.options.selectors.popup, this.handleClick.bind(this));
            $(document).on('amXsearchAnalyticsAddDataCollector', function (event, collector) {
                this.addDataCollector(collector);
            }.bind(this));
        },

        initDataCollectors: function () {
            this.addDataCollector(this.handleSearchClick.bind(this));
        },

        initUrls: function () {
            urlBuilder.setBaseUrl(this.options.baseUrl);
            this.options.backendUrl = urlBuilder.build(this.options.backendUrl);
        },

        /**
         * Add telemetry collector function to queue
         *
         * @param {function(jQuery): object|false} collector
         */
        addDataCollector: function (collector) {
            if (!(collector instanceof Function)) {
                throw new Error($.mage.__('The argument must be a function'));
            }

            this.dataCollectorPool.push(collector);
        },

        /**
         * @param {Event} event
         */
        handleClick: function (event) {
            var clickedElement = $(event.target);

            this.dataCollectorPool.forEach(function (dataCollector) {
                var result = dataCollector(clickedElement);

                if (result !== false) {
                    this.dataForSend.push(result);
                }
            }.bind(this));

            this.sendData();
        },

        sendData: function () {
            if (this.dataForSend.length > 0) {
                $.ajax({
                    url: this.options.backendUrl,
                    data: {
                        form_key: $.mage.cookies.get('form_key'),
                        telemetry: this.dataForSend
                    },
                    method: 'GET',
                    success: function () {
                        this.dataForSend = [];
                    }.bind(this)
                });
            }
        },

        /**
         *
         * @param {string} type
         * @param {object} additionalData
         */
        getTelemetryObject: function (type, additionalData) {
            return Object.assign(
                { type: type },
                additionalData
            );
        },

        /**
         * User behavior analysis
         * In case user go to a page or add a product to the cart, data about engagement will changing
         *
         * @param {jQuery} element
         * @returns {boolean|object}
         */
        handleSearchClick: function (element) {
            var result = false,
                wrapper = element.closest(this.options.selectors.wrapper),
                inputValue = wrapper.find(this.options.selectors.input)[0].value,
                item = element.closest(this.options.selectors.item),
                isSearched = inputValue.length >= this.options.minChars;

            if (isSearched && item.length || element.closest(this.options.selectors.searchClick).length) {
                result = this.getTelemetryObject('search_click', {});
            }

            return result;
        }
    });

    return $.mage.amXsearchAnalyticsDataCollector;
});
