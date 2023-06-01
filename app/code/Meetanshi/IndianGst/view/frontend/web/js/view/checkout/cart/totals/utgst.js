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
        var unionTerritory = window.checkoutConfig.quoteItemData.unionTerritory;
        if (destinationRegion == oringRegion && unionTerritory) {
            $.cookie('isSame', 'SAME');
        }
    });

    var custom_utgst_amount = window.checkoutConfig.utgst_amount;
    var utgst_label = 'UTGST';

    return Component.extend({
        defaults: {
            template: 'Meetanshi_IndianGst/checkout/cart/totals/utgst'
        },
        totals: quote.getTotals(),
        getUtgstLabel:ko.observable(utgst_label),
        isDisplayed: function () {
            return this.getPureValue() != 0;
        },
        getPureValue: function () {
            var price = 0;
            if (this.totals() && totals.getSegment('utgst_amount')) {
                price = totals.getSegment('utgst_amount').value;
            }
            return price;
        },
        getValue: function () {
            return priceUtils.formatPrice(this.getPureValue());
        }
    });
});

