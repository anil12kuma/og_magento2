define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui'
], function ($, alert) {
    'use strict';

    $.widget('mage.amRunButton', {
        options: {
            url: ''
        },

        _create: function () {
            this._on({
                'click': $.proxy(this.run, this)
            });
        },

        run: function () {
            this.element.attr("disabled", true);
            $.ajax({
                url: this.options.url,
                type: 'GET',
                success: function (data) {
                    jQuery('.page-main-actions').after(jQuery('<div class="messages"><div class="quote-items-validation message message-success success">'
                        + data
                        + '</div></div>'
                    ));
                    window.scrollTo(0, 0);
                },
                error: function (e) {
                    jQuery('.page-main-actions').after(jQuery('<div class="messages"><div class="quote-items-validation message message-error error">'
                        + e
                        + '</div></div>'
                    ));
                    window.scrollTo(0, 0);
                }
            });
        }
});

    return $.mage.amRunButton;
});
