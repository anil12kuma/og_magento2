/**
 *  Amasty Cancel Orders PopUp widget
 *
 *  @desc Amasty Cancel Orders PopUp Functionality
 *
 *  @copyright 2009-2020 Amasty Ltd
 *  @license   https://amasty.com/license.html
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    $.widget('mage.amcorderPopUp', {
        options: {
            type: 'popup',
            modalClass: 'amcorder-popup-block',
            title: $t('Cancel Order'),
            trigger: '[data-amcorder-js="cancel"]',
            responsive: true,
            buttons: [{
                text: $t('Cancel'),
                class: 'amcorder-button -primary',
                click: function () {
                    this.closeModal()
                }
            }, {
                text: $t('Submit'),
                class: 'amcorder-button -fill',
                click: function () {
                    this.element.find('[data-amcorder-js="form"]').submit();
                }
            }]
        },

        _create: function () {
            modal(this.options, this.element);
        }
    });

    return $.mage.amcorderPopUp
});
