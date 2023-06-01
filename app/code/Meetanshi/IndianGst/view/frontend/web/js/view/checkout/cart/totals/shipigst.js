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
        if (destinationRegion) {
        if (destinationRegion != oringRegion) {
                $.cookie('isSSame', 'SDIFF');
            }
        }
    });

    var custom_shipping_igst_amount = window.checkoutConfig.shipping_igst_amount;
    var shipping_igst_label = 'Shipping IGST';

    return Component.extend({
        defaults: {
            template: 'Meetanshi_IndianGst/checkout/cart/totals/shipigst'
        },
        totals: quote.getTotals(),
        getShippingIgstLabel:ko.observable(shipping_igst_label),
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },
        getPureValue: function () {
            var price = 0;
            if (this.totals() && totals.getSegment('shipping_igst_amount')) {
                price = totals.getSegment('shipping_igst_amount').value;
            }
            return price;
        },
        getValue: function () {
            return priceUtils.formatPrice(this.getPureValue());
        }
    });
});

