define(
    [
       'jquery',
       'Magento_Checkout/js/view/summary/abstract-total',
       'Magento_Checkout/js/model/quote',
       'Magento_Checkout/js/model/totals',
       'Magento_Catalog/js/price-utils'
    ],
    function ($,Component,quote,totals,priceUtils) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Meetanshi_IndianGst/checkout/summary/indiangst'
            },
            totals: quote.getTotals(),
            isDisplayedIndiangstTotal : function () {
                return true;
            },
            getIndianGst : function () {
              var price = totals.getSegment('cgst').value;
                if (price!=0) {
                return this.getFormattedPrice(price);
                }
            }
         });
    }
);

