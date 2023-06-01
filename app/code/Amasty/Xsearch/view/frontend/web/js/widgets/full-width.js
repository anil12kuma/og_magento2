/**
 * Amasty Full Width Search Widget
 */

define([
    'jquery',
    'uiRegistry',
    'amsearch_helpers'
], function ($, registry, helpers) {
    'use strict';

    $.widget('mage.amsearchFullWidth', {
        components: [
            'index = amsearch_wrapper'
        ],
        classes: {
            initialize: '-amsearch-full-width',
            open: '-opened'
        },

        /**
         * @inheritDoc
         */
        _create: function () {
            this.element.addClass(this.classes.initialize);

            registry.get(this.components, function () {
                helpers.initComponentsArray(arguments, this);

                this.initObservable();
            }.bind(this));
        },


        /**
         * @inheritDoc
         */
        initObservable: function () {
            this.amsearch_wrapper.opened.subscribe(function (value) {
                if (value) {
                    this.element.addClass(this.classes.open);
                } else {
                    this.element.removeClass(this.classes.open);
                }
            }.bind(this));
        }
    });

    return $.mage.amsearchFullWidth;
});
