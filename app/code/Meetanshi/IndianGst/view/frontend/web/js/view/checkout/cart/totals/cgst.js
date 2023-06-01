    define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/view/summary/abstract-total',
], function ($, ko, Component, quote, priceUtils, totals) {
    'use strict';

    quote.shippingAddress.subscribe(function () {
        var destinationRegion = quote.shippingAddress().regionId;
        var oringRegion = window.checkoutConfig.quoteItemData.originState;
        if (destinationRegion == oringRegion) {
            $.cookie('isSame', 'SAME');
        }
    });

    var custom_cgst_amount = window.checkoutConfig.cgst_amount;
    var cgst_label = 'CGST';

    return Component.extend({
        defaults: {
            template: 'Meetanshi_IndianGst/checkout/cart/totals/cgst'
        },
        totals: quote.getTotals(),
        getCgstLabel:ko.observable(cgst_label),
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },
        getPureValue: function () {
            var price = 0;
            if (this.totals() && totals.getSegment('cgst_amount')) {
                price = totals.getSegment('cgst_amount').value;
            }
            return price;
        },
        getValue: function () {
            return priceUtils.formatPrice(this.getPureValue());
        }
    });
});

