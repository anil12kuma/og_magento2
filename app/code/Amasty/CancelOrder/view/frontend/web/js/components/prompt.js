/**
 *  Amasty Cancel Orders Prompt widget
 *
 *  @desc Amasty Cancel Orders Prompt Functionality
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, confirm, $t) {
    'use strict';

    $.widget('mage.amcorderPrompt', {
        options: {
            modalClass: 'amcorder-popup-block',
            responsive: true,
            title: $t('Are you sure you want to cancel the order?'),
            cancellationLink: '',
            action: {}
        },

        _create: function () {
            var options = this.options;

            options.buttons = [{
                text: $t('Cancel'),
                class: 'amcorder-button -primary',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $t('Ok'),
                class: 'amcorder-button -fill',
                click: function () {
                    this.closeModal();
                    eval(options.action);
                }
            }];

            this.element.click(function () {
                confirm(options);
            });
        }
    });

    return $.mage.amcorderPrompt
});
